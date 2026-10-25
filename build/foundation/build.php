<?php

// Derived from packettide/tools foundation/phar.php, scoped to this add-on.
$root = dirname(__DIR__, 2);
$source = __DIR__ . '/source';
$target = $root . '/low_random/low_random.phar';
$inventoryTarget = $root . '/tests/SOURCE_INVENTORY.json';
if (is_link(dirname($inventoryTarget)) || is_link($inventoryTarget) || !is_file($inventoryTarget)) {
    throw new RuntimeException('The inventory target must be an owned regular file.');
}
$inventoryIdentity = lstat($inventoryTarget);
if ($inventoryIdentity['nlink'] !== 1) {
    throw new RuntimeException('The inventory target must not share external links.');
}

$temporary = sys_get_temp_dir() . '/low_random-foundation-' . bin2hex(random_bytes(8));
mkdir($temporary, 0700);

try {
    $contents = $temporary . '/contents';
    mkdir($contents, 0700);
    $provenance = json_decode(file_get_contents(__DIR__ . '/provenance.json'), true, 512, JSON_THROW_ON_ERROR);
    $files = new RecursiveIteratorIterator(new RecursiveDirectoryIterator($source, FilesystemIterator::SKIP_DOTS));
    $expected = array();

    foreach ($files as $file) {
        $member = substr($file->getPathname(), strlen($source) + 1);
        $destination = $contents . '/' . $member;

        if (!is_dir(dirname($destination))) {
            mkdir(dirname($destination), 0700, true);
        }

        $bytes = file_get_contents($file->getPathname());
        file_put_contents($destination, $bytes);
        $record = $provenance['members'][$member] ?? $provenance['added_members'][$member] ?? null;
        if ($record === null) {
            throw new RuntimeException('Unexpected Foundation source member: ' . $member);
        }
        chmod($destination, $record['permissions']);
        $expected[$member] = hash('sha256', $bytes);
    }

    $expectedNames = array_keys($expected);
    $declaredNames = array_merge(array_keys($provenance['members']), array_keys($provenance['added_members']));
    sort($expectedNames);
    sort($declaredNames);
    if ($expectedNames !== $declaredNames) {
        throw new RuntimeException('Foundation source inventory differs from its declared members.');
    }

    $archive = new Phar($temporary . '/low_random.phar', FilesystemIterator::CURRENT_AS_FILEINFO | FilesystemIterator::KEY_AS_FILENAME, 'low_random.phar');
    $archive->buildFromDirectory($contents);
    $stub = file_get_contents(__DIR__ . '/stub.php');
    $archive->setStub($stub);
    $archive->setSignatureAlgorithm(Phar::SHA256);

    if ($archive->getStub() !== $stub || count($archive) !== count($expected) || $archive->getMetadata() !== null) {
        throw new RuntimeException('Foundation archive layout differs from source.');
    }

    foreach ($expected as $member => $hash) {
        $file = $archive[$member];
        $record = $provenance['members'][$member] ?? $provenance['added_members'][$member];
        $mode = $record['permissions'];

        if (hash('sha256', $file->getContent()) !== $hash || ($file->getPerms() & 0777) !== $mode || $file->isCompressed() || $file->getMetadata() !== null) {
            throw new RuntimeException('Foundation archive member differs from source: ' . $member);
        }
    }

    // PharFileInfo keeps its archive alias registered on PHP 8.5.
    unset($file, $archive);

    if (!rename($temporary . '/low_random.phar', $target)) {
        throw new RuntimeException('Unable to publish the rebuilt archive.');
    }

    require $root . '/tests/scripts/inventory.php';
    $manifest = json_encode(lowRandomInventory(), JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES) . "\n";
    json_decode($manifest, true, 512, JSON_THROW_ON_ERROR);
    clearstatcache(true, $inventoryTarget);
    $currentInventory = lstat($inventoryTarget);
    if (is_link($inventoryTarget) || $currentInventory['dev'] !== $inventoryIdentity['dev']
        || $currentInventory['ino'] !== $inventoryIdentity['ino'] || $currentInventory['nlink'] !== 1) {
        throw new RuntimeException('The inventory target changed during the build.');
    }
    $inventoryTemporary = tempnam(dirname($inventoryTarget), '.source-inventory-');
    if ($inventoryTemporary === false) {
        throw new RuntimeException('Unable to prepare the inventory output.');
    }
    $temporaryIdentity = lstat($inventoryTemporary);
    try {
        if (file_put_contents($inventoryTemporary, $manifest, LOCK_EX) !== strlen($manifest)
            || !chmod($inventoryTemporary, $inventoryIdentity['mode'] & 0777)
            || !rename($inventoryTemporary, $inventoryTarget)) {
            throw new RuntimeException('Unable to publish the rebuilt inventory.');
        }
    } finally {
        clearstatcache(true, $inventoryTemporary);
        if (file_exists($inventoryTemporary) || is_link($inventoryTemporary)) {
            $remaining = lstat($inventoryTemporary);
            if (is_link($inventoryTemporary) || $remaining['dev'] !== $temporaryIdentity['dev']
                || $remaining['ino'] !== $temporaryIdentity['ino'] || $remaining['nlink'] !== 1) {
                throw new RuntimeException('The inventory temporary output changed.');
            }
            unlink($inventoryTemporary);
        }
    }

    echo hash_file('sha256', $target) . "  low_random/low_random.phar\n";
} finally {
    $files = new RecursiveIteratorIterator(new RecursiveDirectoryIterator($temporary, FilesystemIterator::SKIP_DOTS), RecursiveIteratorIterator::CHILD_FIRST);

    foreach ($files as $file) {
        if ($file->isDir()) {
            rmdir($file->getPathname());
            continue;
        }

        unlink($file->getPathname());
    }

    rmdir($temporary);
}
