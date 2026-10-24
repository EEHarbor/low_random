<?php

namespace Tests;

final class FoundationBuildTest extends TestCase
{
    /**
     * Rebuild a complete owned distribution and inspect its actual source pins.
     *
     * @return void
     */
    public function testCompleteCopyBuildRefreshesTheDistributedArchive(): void
    {
        $root = dirname(__DIR__, 3);
        $copy = $this->temporaryDirectory . '/build-copy';
        $this->copyDirectory($root . '/build', $copy . '/build');
        $this->copyDirectory($root . '/low_random', $copy . '/low_random');
        mkdir($copy . '/tests/scripts', 0700, true);
        copy($root . '/tests/scripts/inventory.php', $copy . '/tests/scripts/inventory.php');
        $result = $this->native(array('-d', 'phar.readonly=0', $copy . '/build/foundation/build.php'));
        $this->assertSame(0, $result[0], $result[1] . $result[2]);
        $this->assertSame('', $result[2]);
        $inventory = json_decode(file_get_contents($copy . '/tests/SOURCE_INVENTORY.json'), true);
        $this->assertSame(hash_file('sha256', $copy . '/low_random/low_random.phar'), $inventory['archive_sha256']);
        $source = $root . '/build/foundation/source';
        $count = 0;

        foreach (new \RecursiveIteratorIterator(new \RecursiveDirectoryIterator($source, \FilesystemIterator::SKIP_DOTS)) as $file) {
            $member = substr($file->getPathname(), strlen($source) + 1);
            $this->assertSame(hash_file('sha256', $file->getPathname()), $inventory['sources']['low_random/low_random.phar!/' . $member]['sha256']);
            $count++;
        }

        $this->assertSame(22, $count);
        $this->assertSame(hash_file('sha256', $root . '/build/foundation/stub.php'), $inventory['sources']['low_random/low_random.phar!stub']['sha256']);
    }

    /**
     * Copy only the selected source tree into this test's owned directory.
     *
     * @param string $source Source directory.
     * @param string $destination Destination directory.
     * @return void
     */
    private function copyDirectory($source, $destination): void
    {
        mkdir($destination, 0700, true);

        foreach (new \RecursiveIteratorIterator(new \RecursiveDirectoryIterator($source, \FilesystemIterator::SKIP_DOTS), \RecursiveIteratorIterator::SELF_FIRST) as $file) {
            $target = $destination . '/' . substr($file->getPathname(), strlen($source) + 1);

            if ($file->isDir()) {
                mkdir($target, 0700);
                continue;
            }

            copy($file->getPathname(), $target);
        }
    }
}
