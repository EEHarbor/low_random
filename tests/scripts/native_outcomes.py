"""Join every native result to its strict startup and four finite tooling controls."""
import base64
import hashlib
import json
import re
from pathlib import Path


def digest(path):
    return hashlib.sha256(Path(path).read_bytes()).hexdigest()


def decode(record, key):
    return base64.b64decode(record[key], validate=True)


def configured(identity, binary):
    return (Path(identity['binary']).resolve() == Path(binary).resolve()
            and identity['reporting'] == -1 and identity['display_errors'] in ('1', 'stderr')
            and identity['display_startup_errors'] == '1')


def validate(directory, root, binary, parent_pid):
    catalogue = json.loads((root / 'tests/Support/native-control-profiles.json').read_text())
    captures = []
    issues = []
    pids = set()
    for path in sorted(directory.glob('*.json')):
        try:
            record = json.loads(path.read_text())
            if record['state'] != 'complete' or not record['cleanup_complete']:
                raise ValueError('Incomplete native process capture')
            pid = record['pid']
            if type(pid) is not int or pid in pids:
                raise ValueError('Missing/duplicated native PID')
            pids.add(pid)
            if Path(record['command'][0]).resolve() != Path(binary).resolve():
                raise ValueError('Native command uses another binary')
            streams = {}
            for stream in ('stdout', 'stderr', 'native_error_log'):
                raw = Path(record[stream]).read_bytes()
                if len(raw) != record[stream + '_bytes'] or hashlib.sha256(raw).hexdigest() != record[stream + '_sha256']:
                    raise ValueError('Native stream changed after completion')
                streams[stream] = raw
            if streams['native_error_log']:
                raise ValueError('Unexpected native PHP engine diagnostic')
            diagnostics = decode(record, 'diagnostics_raw_base64')
            events = [json.loads(line) for line in diagnostics.splitlines()]
            identities = [json.loads(line) for line in decode(record, 'runtime_raw_base64').splitlines()]
            if any(item['pid'] != pid or not configured(item, binary) for item in identities):
                raise ValueError('Wrong native strict runtime startup')
            case = record['case']
            if digest(case['file']) != case['sha256']:
                raise ValueError('Native caller input changed')
            args = [arg.replace(str(root), '{ROOT}').replace(record['temporary_directory'], '{TMP}') for arg in record['arguments']]
            profile = None
            for item in catalogue['profiles']:
                if (case['file'] == str(root / item['case_file']) and case['sha256'] == item['case_sha256']
                        and case['class'] == item['case_class'] and case['method'] == item['method']
                        and args == item['arguments'] and record['timeout'] == item['timeout']):
                    profile = item['kind']
            compiler = record.get('lint_input')
            lint = (isinstance(compiler, dict) and record['arguments'] == ['-l', compiler['path']]
                    and compiler['path'].startswith('/') and compiler.get('unchanged') is True
                    and re.fullmatch('[a-f0-9]{64}', compiler['sha256']) is not None
                    and type(compiler['bytes']) is int and compiler['bytes'] >= 0
                    and streams['stdout'] == ('No syntax errors detected in ' + compiler['path'] + '\n').encode())
            if len(identities) != 1 and not lint and not (profile == 'timeout' and not identities):
                raise ValueError('Missing/duplicated native startup receipt')
            accepted_control = False
            if profile == 'output':
                accepted_control = (record['exit'] == 0 and not record['timed_out'] and not events
                                    and streams['stdout'] == b'o' * 1048576 and streams['stderr'] == b'e' * 1048576)
            if profile == 'timeout':
                accepted_control = (record['timed_out'] and record['exit'] is None and not events
                                    and not streams['stdout'] and not streams['stderr'])
            if profile == 'diagnostic':
                accepted_control = (record['exit'] == 70 and not record['timed_out'] and len(events) == 1
                                    and events[0]['pid'] == pid and events[0]['severity'] == 16384
                                    and events[0]['message'] == 'diagnostic harness probe'
                                    and events[0]['file'] == 'Command line code' and events[0]['line'] == 1
                                    and not streams['stdout'] and streams['stderr'] == b'LOW_RANDOM_DIAGNOSTIC ' + diagnostics)
            if profile == 'coverage':
                expected = ('Coverage report already exists: ' + str(record['coverage_prefix']) + '.' + str(pid) + '.json\n').encode()
                accepted_control = (digest(root / 'tests/Support/native.php') == catalogue['fixture_sha256']
                                    and digest(root / 'tests/Support/coverage.php') == catalogue['collector_sha256']
                                    and record['coverage_prefix'] == record['temporary_directory'] + '/new/reports/graph'
                                    and record['exit'] == 255 and not record['timed_out'] and not events
                                    and not streams['stdout'] and streams['stderr'] == expected)
            if profile is not None and not accepted_control:
                raise ValueError('Finite native tooling control no longer matches')
            if profile is None and (record['exit'] != 0 or record['timed_out'] or events or streams['stderr']):
                raise ValueError('Unexpected native status, stderr, timeout or retained diagnostic')
            captures.append(record)
        except (OSError, ValueError, TypeError, KeyError) as error:
            issues.append(str(path) + ': ' + str(error))
    # Nested native owners are legal only when their recorded parent joins this run.
    for record in captures:
        if record['owner_pid'] not in pids | {parent_pid}:
            issues.append('Unknown native process owner: ' + str(record['pid']))
    return captures, issues
