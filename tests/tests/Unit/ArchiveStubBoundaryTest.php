<?php

namespace Tests;

final class ArchiveStubBoundaryTest extends TestCase
{
    /**
     * Supply deterministic high-entropy bytes independently of the extractor.
     *
     * @param int $size
     * @return string
     */
    private function body($size)
    {
        $body = '';

        for ($index = 0; strlen($body) < $size; $index++) {
            $body .= hash('sha256', pack('N', $index), true);
        }

        return substr($body, 0, $size);
    }

    /**
     * Execute termination and extraction in a child using the original stub class.
     *
     * @param string $mode
     * @param string $compression
     * @param string $body
     * @return array
     */
    private function extract($mode, $compression, $body)
    {
        $options = array('mode' => $mode, 'compression' => $compression, 'body' => base64_encode($body), 'size' => strlen($body));
        return $this->probe($options);
    }

    /**
     * Observe a helper in the disposable process that owns every side effect.
     *
     * @param array $options
     * @return array
     * @throws \RuntimeException
     */
    private function probe(array $options)
    {
        return $this->native(array(dirname(__DIR__, 2) . '/Support/native-stub-boundary.php', json_encode($options, JSON_THROW_ON_ERROR)));
    }

    /**
     * Native compressed and plain inputs cross the actual 8 KiB read boundary.
     *
     * @dataProvider extractionCases
     * @param int $size
     * @param string $compression
     * @return void
     */
    public function testBinaryReadAndDecompression($size, $compression)
    {
        $body = $this->body($size);
        $result = $this->extract('valid', $compression, $body);
        $this->assertSame(0, $result[0], $result[1] . $result[2]);
        $this->assertSame('', $result[2]);
        $data = json_decode($result[1], true, 512, JSON_THROW_ON_ERROR);
        $this->assertSame(strlen($body), $data['length']);
        $this->assertSame(hash('sha256', $body), $data['sha256']);
        $this->assertSame($data['compressed'], $data['position']);

        if ($size >= 8192) {
            $this->assertGreaterThanOrEqual(8192, $data['compressed']);
        }
    }

    /**
     * Exercise partial, exact and repeated reads with each native codec.
     *
     * @return array
     */
    public static function extractionCases()
    {
        $cases = array();

        foreach (array(0, 1, 8191, 8192, 8193, 20000) as $size) {
            foreach (array('plain', 'gzip', 'bzip') as $compression) {
                $cases[$size . '/' . $compression] = array($size, $compression);
            }
        }

        return $cases;
    }

    /**
     * A corrupt CRC terminates with the helper's explicit checksum diagnostic.
     *
     * @return void
     */
    public function testChecksumMismatchTerminatesWithItsDiagnostic()
    {
        $result = $this->extract('checksum', 'plain', 'a');
        $this->assertSame(0, $result[0], $result[1] . $result[2]);
        $this->assertSame('', $result[2]);
        $this->assertSame('Invalid internal .phar file (checksum error)', $result[1]);
    }

    /**
     * A corrupt size must report the declared size without an undefined variable.
     *
     * @return void
     */
    public function testSizeMismatchReportsItsDeclaredSize()
    {
        $result = $this->extract('size', 'plain', 'a');
        $this->assertSame(0, $result[0], $result[1] . $result[2]);
        $this->assertSame('', $result[2]);
        $this->assertSame('Invalid internal .phar file (size error 1 != 2)', $result[1]);
    }

    /**
     * Unix temporary selection preserves unset and empty environment boundaries.
     *
     * @return void
     */
    public function testUnixTemporaryDirectoryBoundaries()
    {
        $result = $this->probe(array('mode' => 'temporary-directory'));
        $this->assertSame(0, $result[0], $result[1] . $result[2]);
        $this->assertSame('', $result[2]);
        $this->assertSame(array('override' => $this->temporaryDirectory . '/uncreated-stub-fixture', 'empty' => realpath('/tmp'), 'missing' => realpath('/tmp'), 'restored' => true, 'created' => false), json_decode($result[1], true, 512, JSON_THROW_ON_ERROR));
    }

    /**
     * Cleanup must remove the owned shape and restore the original working path.
     *
     * @dataProvider cleanupCases
     * @param string $kind
     * @return void
     */
    public function testOwnedTreeCleanup($kind)
    {
        $result = $this->probe(array('mode' => 'cleanup', 'kind' => $kind));
        $this->assertSame(0, $result[0], $result[1] . $result[2]);
        $this->assertSame('', $result[2]);
        $this->assertSame(array('removed' => true, 'cwd_restored' => true), json_decode($result[1], true, 512, JSON_THROW_ON_ERROR));
    }

    /**
     * Include ordinary extraction trees and explicitly scoped robustness shapes.
     *
     * @return array
     */
    public static function cleanupCases()
    {
        return array('empty' => array('empty'), 'files' => array('files'), 'empty child' => array('empty-directory'), 'nonempty child' => array('populated-directory'), 'hidden file robustness' => array('dotfile'), 'dangling link robustness' => array('dangling-link'));
    }
}
