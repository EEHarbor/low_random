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
from native_outcomes import validate, configured
import xml.etree.ElementTree as ET

root = pathlib.Path(__file__).resolve().parents[2]
reports = root / 'tests' / 'reports'
reports.mkdir(exist_ok=True)
run = pathlib.Path(tempfile.mkdtemp(prefix='compat-strict-', dir=reports))
binary = os.environ.get('PHP_BIN', '/Users/tomjaeger/Library/Application Support/Herd/bin/php82')
arguments = sys.argv[1:]
junit = pathlib.Path(arguments[arguments.index('--log-junit') + 1]).resolve() if '--log-junit' in arguments else run / 'junit.xml'
if '--log-junit' not in arguments:
    arguments += ['--log-junit', str(junit)]
if junit.exists():
    sys.exit('Refusing an existing JUnit report: ' + str(junit))
captures = run / 'native'
captures.mkdir()
environment = dict(os.environ, LOW_RANDOM_STRICT_JOURNAL=str(run / 'diagnostics.jsonl'),
                   LOW_RANDOM_STRICT_CAPTURES=str(captures))
command = [binary, '-d', 'error_reporting=-1', '-d', 'display_errors=stderr', '-d',
           'display_startup_errors=1', '-d', 'log_errors=0', '-d',
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
native, native_issues = validate(captures, root, binary, parent_pid)
issues.extend(native_issues)
receipt = {'command': command, 'php_exit': php_exit, 'counts': counts, 'issues': issues,
           'runtime_receipts': len(identities), 'native_captures': len(native), 'junit': str(junit),
           'junit_sha256': hashlib.sha256(junit.read_bytes()).hexdigest() if junit.exists() else None,
           'exit': php_exit if php_exit else 70 if issues else 0}
(run / 'receipt.json').write_text(json.dumps(receipt, indent=2))
sys.stdout.write('LOW_RANDOM_STRICT_RECEIPT ' + str(run / 'receipt.json') + '\n')
sys.exit(receipt['exit'])
