#!/usr/bin/env python3
"""Run the pinned suite and reject diagnostics retained by any owned process."""

import hashlib
import json
import os
import pathlib
import subprocess
import sys
import tempfile
import signal
import stat
from native_outcomes import validate, configured
import xml.etree.ElementTree as ET

root = pathlib.Path(__file__).resolve().parents[2]
reports = root / 'tests' / 'reports'
reports.mkdir(exist_ok=True)
run = pathlib.Path(tempfile.mkdtemp(prefix='compat-strict-', dir=reports))
binary = os.environ.get('PHP_BIN', '/Users/tomjaeger/Library/Application Support/Herd/bin/php82')
arguments = sys.argv[1:]
junit_argument = None
for index, argument in enumerate(arguments):
    if argument == '--log-junit':
        if index + 1 == len(arguments) or not arguments[index + 1]:
            sys.exit('Missing --log-junit destination')
        junit_argument = arguments[index + 1]
    if argument.startswith('--log-junit='):
        junit_argument = argument[len('--log-junit='):]
        if not junit_argument:
            sys.exit('Missing --log-junit destination')
junit = pathlib.Path(junit_argument).resolve() if junit_argument is not None else run / 'junit.xml'
if junit_argument is None:
    arguments += ['--log-junit', str(junit)]
if junit.exists():
    sys.exit('Refusing an existing JUnit report: ' + str(junit))
captures = run / 'native'
captures.mkdir()
capture_identity = captures.lstat()
environment = dict(os.environ, LOW_RANDOM_STRICT_JOURNAL=str(run / 'diagnostics.jsonl'),
                   LOW_RANDOM_STRICT_CAPTURES=str(captures))
engine_log = run / 'php-errors.log'
engine_file = engine_log.open('x+b')
engine_identity = os.fstat(engine_file.fileno())
command = [binary, '-d', 'error_reporting=-1', '-d', 'display_errors=stderr', '-d',
           'display_startup_errors=1', '-d', 'log_errors=1', '-d', 'error_log=' + str(engine_log), '-d',
           'auto_prepend_file=' + str(root / 'tests' / 'strict-diagnostics.php'),
           str(root / 'tests' / '.phpunit' / 'phpunit-9.phar')] + arguments
with (run / 'stdout.log').open('wb') as stdout, (run / 'stderr.log').open('wb') as stderr:
    process = subprocess.Popen(command, cwd=root, env=environment, stdout=stdout, stderr=stderr, start_new_session=True)
    parent_pid = process.pid
    parent_timeout = False
    try:
        process.wait(timeout=150)
    except subprocess.TimeoutExpired:
        parent_timeout = True
        if process.poll() is None:
            os.killpg(parent_pid, signal.SIGTERM)
        try:
            process.wait(timeout=5)
        except subprocess.TimeoutExpired:
            if process.poll() is None:
                os.killpg(parent_pid, signal.SIGKILL)
            process.wait(timeout=2)
    php_exit = process.returncode
stdout = (run / 'stdout.log').read_bytes()
stderr = (run / 'stderr.log').read_bytes()
sys.stdout.buffer.write(stdout)
sys.stderr.buffer.write(stderr)
issues = ['Parent process timed out'] if parent_timeout else []
counts = {}
try:
    xml = ET.parse(junit).getroot()
    suite = xml if xml.tag == 'testsuite' else xml.find('testsuite')
    counts = {key: int(suite.attrib.get(key, 0)) for key in ['tests', 'assertions', 'errors', 'failures', 'warnings', 'skipped']}
    if not counts['tests']:
        issues.append('No tests discovered')
    if any(counts[key] for key in ['errors', 'failures', 'warnings', 'skipped']):
        issues.append('JUnit problem categories are nonzero')
except (OSError, ET.ParseError, AttributeError, ValueError) as error:
    issues.append('Invalid or missing fresh JUnit: ' + str(error))
if stderr:
    issues.append('Nonempty stderr')
engine_file.seek(0)
engine_bytes = engine_file.read()
engine_named = engine_log.lstat()
if (engine_log.is_symlink() or engine_named.st_nlink != 1
        or (engine_named.st_dev, engine_named.st_ino) != (engine_identity.st_dev, engine_identity.st_ino)):
    issues.append('Parent PHP engine log changed identity')
engine_file.close()
if engine_bytes:
    issues.append('Unexpected parent PHP engine diagnostic')
journal = run / 'diagnostics.jsonl'
if journal.exists() and journal.stat().st_size:
    issues.append('Unexpected retained diagnostic events')
identities = []
runtime = run / 'diagnostics.jsonl.runtime'
if runtime.exists():
    identities = [json.loads(line) for line in runtime.read_text().splitlines()]
if not identities:
    issues.append('No runtime startup receipts')
if len(identities) != 1 or identities[0].get('pid') != parent_pid:
    issues.append('Missing, duplicated or unowned main startup receipt')
for identity in identities:
    if not configured(identity, binary):
        issues.append('Incorrect process runtime or strict startup configuration')
native = []
try:
    capture_named = captures.lstat()
    if (not stat.S_ISDIR(capture_named.st_mode)
            or (capture_named.st_dev, capture_named.st_ino) != (capture_identity.st_dev, capture_identity.st_ino)):
        raise ValueError('Native capture directory changed identity')
    native, native_issues = validate(captures, root, binary, parent_pid)
except (OSError, ValueError) as error:
    native_issues = [str(error)]
issues.extend(native_issues)
receipt = {'command': command, 'php_exit': php_exit, 'counts': counts, 'issues': issues,
           'native_error_log': str(engine_log), 'native_error_log_bytes': len(engine_bytes),
           'native_error_log_sha256': hashlib.sha256(engine_bytes).hexdigest(),
           'native_capture_directory': {'path': str(captures), 'device': capture_identity.st_dev,
                                        'inode': capture_identity.st_ino},
           'runtime_receipts': len(identities), 'native_captures': len(native), 'junit': str(junit),
           'junit_sha256': hashlib.sha256(junit.read_bytes()).hexdigest() if junit.exists() else None,
           'exit': php_exit if php_exit else 70 if issues else 0}
(run / 'receipt.json').write_text(json.dumps(receipt, indent=2))
sys.stdout.write('LOW_RANDOM_STRICT_RECEIPT ' + str(run / 'receipt.json') + '\n')
sys.exit(receipt['exit'])
