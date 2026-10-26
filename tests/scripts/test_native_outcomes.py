"""Regression checks for ignored child failures at the checked-parent boundary."""
import base64
import hashlib
import json
import tempfile
import unittest
from pathlib import Path
from native_outcomes import validate


class NativeOutcomesTest(unittest.TestCase):
    def setUp(self):
        self.directory = tempfile.TemporaryDirectory()
        self.root = Path(self.directory.name)
        self.captures = self.root / 'captures'
        self.captures.mkdir()
        (self.root / 'tests/Support').mkdir(parents=True)
        (self.root / 'tests/Support/native-control-profiles.json').write_text('{"profiles":[]}')
        self.case = self.root / 'OwnedCase.php'
        self.case.write_text('<?php // actual owned caller identity')
        self.binary = '/usr/bin/true'
        self.record = dict(state='complete', cleanup_complete=True, pid=101, owner_pid=100,
                           command=[self.binary, '-r', 'echo "healthy";'], arguments=['-r', 'echo "healthy";'],
                           timeout=20, temporary_directory=str(self.root / 'temporary'), timed_out=False, exit=0,
                           case=dict(file=str(self.case), sha256=hashlib.sha256(self.case.read_bytes()).hexdigest(),
                                     method='testOwned', **{'class': 'OwnedCase'}),
                           diagnostics_raw_base64='', runtime_raw_base64=base64.b64encode(json.dumps(dict(
                               pid=101, binary=self.binary, reporting=-1, display_errors='stderr',
                               display_startup_errors='1')).encode()).decode())
        for stream in ('stdout', 'stderr', 'native_error_log'):
            self.stream(stream, b'')

    def tearDown(self):
        self.directory.cleanup()

    def stream(self, name, raw):
        path = self.captures / name
        path.write_bytes(raw)
        self.record.update({name: str(path), name + '_bytes': len(raw),
                            name + '_sha256': hashlib.sha256(raw).hexdigest()})

    def issues(self):
        (self.captures / 'child.json').write_text(json.dumps(self.record))
        return validate(self.captures, self.root, self.binary, 100)[1]

    def test_healthy_child_has_one_joined_startup(self):
        self.assertEqual([], self.issues())

    def test_ignored_nonzero_status_rejects_parent(self):
        self.record['exit'] = 7
        self.assertTrue(self.issues())

    def test_ignored_stderr_rejects_parent(self):
        self.stream('stderr', b'warning emitted before bootstrap')
        self.assertTrue(self.issues())

    def test_native_engine_log_rejects_otherwise_green_child(self):
        self.stream('native_error_log', b'[native timestamp] PHP Warning: owned native warning\n')
        self.assertTrue(self.issues())

    def test_literal_diagnostic_stdout_remains_application_data(self):
        self.stream('stdout', b'PHP Warning: literal output in /tmp/literal.php on line 7\n')
        self.assertEqual([], self.issues())

    def test_missing_engine_log_cannot_publish_native_success(self):
        Path(self.record['native_error_log']).unlink()
        self.assertTrue(self.issues())

    def test_changed_engine_log_cannot_publish_native_success(self):
        Path(self.record['native_error_log']).write_bytes(b'changed')
        self.assertTrue(self.issues())

    def test_caught_timeout_rejects_parent(self):
        self.record.update(timed_out=True, exit=None)
        self.assertTrue(self.issues())

    def test_missing_child_startup_cannot_borrow_parent_receipt(self):
        self.record['runtime_raw_base64'] = ''
        self.assertTrue(self.issues())

    def test_compiler_keeps_its_input_receipt_after_fixture_cleanup(self):
        path = str(self.root / 'removed.php')
        self.record['arguments'] = ['-l', path]
        self.record['lint_input'] = dict(path=path, sha256='1' * 64, bytes=23, unchanged=True)
        self.record['runtime_raw_base64'] = ''
        self.stream('stdout', ('No syntax errors detected in ' + path + '\n').encode())
        self.assertEqual([], self.issues())

    def test_wrong_runtime_flags_reject_parent(self):
        raw = base64.b64decode(self.record['runtime_raw_base64'])
        identity = json.loads(raw)
        identity['reporting'] = 0
        self.record['runtime_raw_base64'] = base64.b64encode(json.dumps(identity).encode()).decode()
        self.assertTrue(self.issues())

    def test_caught_diagnostic_cannot_be_green(self):
        self.record['diagnostics_raw_base64'] = base64.b64encode(b'{"pid":101,"message":"caught"}\n').decode()
        self.assertTrue(self.issues())

    def test_stream_replacement_rejects_parent(self):
        Path(self.record['stdout']).write_bytes(b'replacement')
        self.assertTrue(self.issues())

    def test_unrelated_native_owner_is_rejected(self):
        self.record['owner_pid'] = 999
        self.assertTrue(self.issues())


if __name__ == '__main__':
    unittest.main()
