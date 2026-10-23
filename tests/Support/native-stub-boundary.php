<?php

error_reporting(E_ALL);
ini_set('display_errors', '1');
set_error_handler(static function ($severity, $message, $file, $line) {
    throw new ErrorException($message, 0, $severity, $file, $line);
}, E_ALL);
$root = dirname(__DIR__, 2);
$options = json_decode($argv[1], true, 512, JSON_THROW_ON_ERROR);
$_SERVER['LOW_RANDOM_NATIVE_CASE'] = 'stub-boundary/' . $options['mode'] . '/' . ($options['kind'] ?? $options['compression'] ?? 'none') . '/' . ($options['size'] ?? 'none');
require __DIR__ . '/coverage.php';
define('PATH_THIRD', $root . '/');
define('APP_VER', '7.5.0');
require $root . '/low_random/autoload.php';

if (!class_exists('Low\\Random\\FluxCapacitor\\Base\\Pi') || !class_exists('Extract_low_random', false)) {
    throw new RuntimeException('The original archive loader did not declare its extraction helper.');
}

if ($options['mode'] === 'temporary-directory') {
    $before = getenv('TMPDIR');
    $probe = sys_get_temp_dir() . '/uncreated-stub-fixture';

    try {
        putenv('TMPDIR=' . $probe);
        $override = Extract_low_random::tmpdir();
        putenv('TMPDIR=');
        $empty = Extract_low_random::tmpdir();
        putenv('TMPDIR');
        $missing = Extract_low_random::tmpdir();
    } finally {
        putenv($before === false ? 'TMPDIR' : 'TMPDIR=' . $before);
    }

    echo json_encode(array('override' => $override, 'empty' => $empty, 'missing' => $missing, 'restored' => getenv('TMPDIR') === $before, 'created' => file_exists($probe)), JSON_THROW_ON_ERROR);
    exit(0);
}

if ($options['mode'] === 'cleanup') {
    $tree = sys_get_temp_dir() . '/stub-tree-' . bin2hex(random_bytes(6));
    $original = getcwd();
    mkdir($tree, 0700);

    try {
        if (in_array($options['kind'], array('empty-directory', 'populated-directory'), true)) {
            mkdir($tree . '/nested', 0700);
        }

        if ($options['kind'] === 'populated-directory') {
            file_put_contents($tree . '/nested/child.bin', 'fixture');
        }

        if ($options['kind'] === 'files') {
            file_put_contents($tree . '/first.bin', 'fixture');
            file_put_contents($tree . '/second.bin', 'fixture');
        }

        if ($options['kind'] === 'dotfile') {
            file_put_contents($tree . '/.owned', 'fixture');
        }

        if ($options['kind'] === 'dangling-link') {
            symlink($tree . '/absent-target', $tree . '/owned-link');
        }

        Extract_low_random::_removeTmpFiles($tree, $original);
        $result = array('removed' => !file_exists($tree), 'cwd_restored' => getcwd() === $original);
    } finally {
        chdir($original);

        if (is_dir($tree)) {
            $remaining = new RecursiveIteratorIterator(new RecursiveDirectoryIterator($tree, FilesystemIterator::SKIP_DOTS), RecursiveIteratorIterator::CHILD_FIRST);

            foreach ($remaining as $file) {
                if ($file->isLink() || !$file->isDir()) {
                    unlink($file->getPathname());
                    continue;
                }

                rmdir($file->getPathname());
            }

            rmdir($tree);
        }
    }

    echo json_encode($result, JSON_THROW_ON_ERROR);
    exit(0);
}

$body = base64_decode($options['body'], true);
if ($body === false || strlen($body) !== $options['size']) {
    throw new InvalidArgumentException('The binary fixture did not match its declared size.');
}

$data = $body;
$flags = 0;
if ($options['compression'] === 'gzip') {
    $data = gzdeflate($body);
    $flags = Extract_low_random::GZ;
}
if ($options['compression'] === 'bzip') {
    $data = bzcompress($body);
    $flags = Extract_low_random::BZ2;
}

$crc = sprintf('%u', crc32($body) & 0xffffffff);
$entry = array(strlen($body), 0, strlen($data), $crc, $flags);
if ($options['mode'] === 'size') {
    $entry[0]++;
}
if ($options['mode'] === 'checksum') {
    $entry[3] = $crc === '0' ? '1' : '0';
}

$stream = fopen('php://memory', 'w+b');
try {
    fwrite($stream, $data);
    rewind($stream);
    $extracted = Extract_low_random::extractFile('binary-fixture.bin', $entry, $stream);
    echo json_encode(array('length' => strlen($extracted), 'sha256' => hash('sha256', $extracted), 'compressed' => strlen($data), 'position' => ftell($stream)), JSON_THROW_ON_ERROR);
} finally {
    fclose($stream);
}
