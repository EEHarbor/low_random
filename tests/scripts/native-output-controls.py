#!/usr/bin/env python3
"""Verify native engine diagnostics reject a green checked parent on every PHP."""
import argparse
import hashlib
import json
import os
from pathlib import Path
import subprocess


def engine_message(path):
    """Read one native log event without treating its timestamp as provenance."""
    raw = Path(path).read_bytes()
    parts = raw.split(b'] ', 1)
    if len(parts) != 2 or not parts[0].startswith(b'[') or b'\n' in parts[0] or len(parts[1].splitlines()) != 1:
        raise RuntimeError('The target engine log must contain exactly one native event.')
    return parts[1]


def verify_target(root, receipt, data, mode, version):
    """Bind a rejected invocation to its requested engine fault and completed source."""
    if mode == 'clean':
        if data['issues'] or data['native_error_log_bytes'] or data['counts']['assertions'] != 1:
            raise RuntimeError('The clean control did not retain its ordinary outcome.')
        return {'target': 'clean'}

    native = mode.startswith('engine-native-')
    startup = mode.startswith('engine-native-startup-')
    fixture = root / 'tests/Fixtures' / ('native-engine-output.php' if native else 'StrictDiagnosticProbeTest.php')
    severity = b'Notice' if version == '74' else b'Warning'
    if native:
        captures = []
        for path in (receipt.parent / 'native').glob('*.json'):
            capture = json.loads(path.read_text())
            if capture.get('native_error_log_bytes'):
                captures.append((path, capture))
        if len(captures) != 1 or data['native_error_log_bytes']:
            raise RuntimeError('The requested native engine fault was not the sole rejected channel.')
        path, capture = captures[0]
        expected_case = root / 'tests/Fixtures/StrictDiagnosticProbeTest.php'
        identity = capture['case']
        if (identity['file'] != str(expected_case)
                or identity['sha256'] != hashlib.sha256(expected_case.read_bytes()).hexdigest()
                or identity['class'] != 'Tests\\StrictDiagnosticProbeTest'
                or identity['method'] != 'testRetainedDiagnostics'
                or capture['exit'] != 0 or capture['timed_out'] or not capture['cleanup_complete']
                or capture['diagnostics_raw_base64']
                or data['issues'] != [str(path) + ': Unexpected native PHP engine diagnostic']):
            raise RuntimeError('An unrelated native outcome cannot satisfy the engine control.')
        requested = mode.removeprefix('engine-native-')
        expected_arguments = [str(fixture), requested]
        if startup:
            expected_arguments = ['-d', 'extension=__owned_low_random_absent__.so', '-d',
                                  'html_errors=' + ('1' if requested == 'startup-html' else '0'),
                                  '-r', "require '" + str(fixture) + "';", '--', requested]
        if capture['arguments'] != expected_arguments:
            raise RuntimeError('The native invocation did not execute the requested fault mode.')
        output = Path(capture['stdout']).read_bytes()
        if output.count(b'source-kept') != 1:
            raise RuntimeError('The actual distributed source did not complete its guard.')
        log = Path(capture['native_error_log'])
        if log.stat().st_size != capture['native_error_log_bytes'] or hashlib.sha256(log.read_bytes()).hexdigest() != capture['native_error_log_sha256']:
            raise RuntimeError('The completed native engine stream changed.')
    else:
        if data['issues'] != ['Unexpected parent PHP engine diagnostic'] or data['counts']['assertions'] != 1:
            raise RuntimeError('An unrelated parent outcome cannot satisfy the engine control.')
        output = (receipt.parent / 'stdout.log').read_bytes()
        log = Path(data['native_error_log'])

    body = engine_message(log)
    if startup:
        if (not body.startswith(b"PHP Warning:  PHP Startup: Unable to load dynamic library '__owned_low_random_absent__.so'")
                or not body.endswith(b' in Unknown on line 0\n')):
            raise RuntimeError('The startup control did not emit its owned missing-extension warning.')
    else:
        lines = [number for number, line in enumerate(fixture.read_text().splitlines(), 1)
                 if "$unused = (string) array('owned native output');" in line]
        if len(lines) != 1:
            raise RuntimeError('The owned warning source is ambiguous.')
        expected = b'PHP ' + severity + b':  Array to string conversion in ' + str(fixture).encode() + b' on line ' + str(lines[0]).encode() + b'\n'
        if body != expected or b'Array to string conversion' not in output:
            raise RuntimeError('The engine control did not emit its exact owned warning.')
        html = mode.endswith('-html') or mode.endswith('-late')
        if (b'<b>' + severity + b'</b>' in output) != html:
            raise RuntimeError('The native formatter does not match the requested fault mode.')
    return {'target': mode, 'engine_log': str(log), 'engine_log_sha256': hashlib.sha256(log.read_bytes()).hexdigest(),
            'source_fixture_sha256': hashlib.sha256(fixture.read_bytes()).hexdigest()}


def main():
    """Retain complete streams, fresh JUnit and checked outcome for each control."""
    parser = argparse.ArgumentParser(description=__doc__)
    parser.add_argument('--output', type=Path, required=True)
    parser.add_argument('--php', choices=['74', '80', '81', '82', '83', '84', '85'], action='append')
    args = parser.parse_args()
    root = Path(__file__).resolve().parents[2]
    args.output.mkdir(parents=True, exist_ok=False)
    rows = []
    for version in args.php or ['74', '80', '81', '82', '83', '84', '85']:
        binary = '/Users/tomjaeger/Library/Application Support/Herd/bin/php' + version
        for mode in ['clean', 'engine-native-plain', 'engine-native-html', 'engine-native-late',
                     'engine-native-startup-plain', 'engine-native-startup-html',
                     'engine-main-plain', 'engine-main-html', 'engine-main-late']:
            command = ['tests/.phpunit/run-phpunit.sh', '-c', 'phpunit.xml', 'tests/Fixtures/StrictDiagnosticProbeTest.php']
            result = subprocess.run(command, cwd=root, capture_output=True, timeout=60,
                                    env=dict(os.environ, PHP_BIN=binary, LOW_RANDOM_STRICT_PROBE=mode))
            prefix = args.output / (version + '-' + mode)
            prefix.with_suffix('.stdout').write_bytes(result.stdout)
            prefix.with_suffix('.stderr').write_bytes(result.stderr)
            receipt = next(line.split(' ', 1)[1] for line in result.stdout.decode().splitlines()
                           if line.startswith('LOW_RANDOM_STRICT_RECEIPT '))
            data = json.loads(Path(receipt).read_text())
            expected = 0 if mode == 'clean' else 70
            row = dict(version=version, binary=binary, command=command, mode=mode, exit=result.returncode,
                       expected=expected, receipt=receipt, receipt_sha256=hashlib.sha256(Path(receipt).read_bytes()).hexdigest(),
                       counts=data['counts'], issues=data['issues'])
            rows.append(row)
            (args.output / 'results.json').write_text(json.dumps(rows, indent=2) + '\n')
            if result.returncode != expected or data['counts']['tests'] != 1 or any(
                    data['counts'][key] for key in ['errors', 'failures', 'warnings', 'skipped']):
                raise RuntimeError('Native diagnostic authority control failed: ' + str(row))
            row['verified_target'] = verify_target(root, Path(receipt), data, mode, version)
            (args.output / 'results.json').write_text(json.dumps(rows, indent=2) + '\n')
        print(version + ': native engine output controls passed', flush=True)


if __name__ == '__main__':
    main()
