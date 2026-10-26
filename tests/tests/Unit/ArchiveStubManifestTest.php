<?php

namespace Tests;

final class ArchiveStubManifestTest extends TestCase
{
    /**
     * Compare every original manifest entry and extracted body with native Phar.
     *
     * @return void
     */
    public function testManifestMatchesEveryOriginalArchiveMember()
    {
        $path = PATH_THIRD . 'low_random/low_random.phar';
        $archive = new \Phar($path);
        $stream = fopen($path, 'rb');

        try {
            fseek($stream, \Extract_low_random::LEN);
            $length = unpack('V', fread($stream, 4))[1];
            $manifest = \Extract_low_random::_unpack(fread($stream, $length));
            $dataStart = ftell($stream);
            $this->assertCount(count($archive), $manifest['m']);
            $this->assertSame(0, $manifest['c']);
            $offset = 0;

            foreach ($manifest['m'] as $name => $entry) {
                $native = $archive[$name];
                $this->assertSame($native->getSize(), $entry[0], $name);
                $this->assertSame($native->getMTime(), $entry[1], $name);
                $this->assertSame($native->getCompressedSize(), $entry[2], $name);
                $this->assertSame(sprintf('%u', $native->getCRC32()), $entry[3], $name);
                $this->assertSame($offset, $entry[7], $name);
                fseek($stream, $dataStart + $offset);
                $this->assertSame($native->getContent(), \Extract_low_random::extractFile($name, $entry, $stream), $name);
                $this->assertSame($dataStart + $offset + $entry[2], ftell($stream), $name);
                $offset += $entry[2];
            }
        } finally {
            fclose($stream);
        }
    }

    /**
     * Skip alias and metadata bytes without corrupting entry offsets or flags.
     *
     * @return void
     */
    public function testStructuredManifestMetadataAndUnsignedCrc()
    {
        $alias = 'test-fixture';
        $metadata = serialize(array('zero' => 0));
        $fileMetadata = serialize(array('nullable' => null));
        $entries = array(
            'zero.bin' => array(0, 1700000000, 0, '0', 0, 0),
            'gzip.bin' => array(8, 1700000001, 6, '4294967295', \Extract_low_random::GZ, strlen($fileMetadata)),
            'bzip.bin' => array(16, 1700000002, 9, '2147483648', \Extract_low_random::BZ2, 0),
        );
        $bytes = pack('VvVV', count($entries), 0x11, 0, strlen($alias)) . $alias . pack('V', strlen($metadata)) . $metadata;

        foreach ($entries as $name => $entry) {
            $bytes .= pack('V', strlen($name)) . $name . pack('V6', ...$entry);

            if ($entry[5]) {
                $bytes .= $fileMetadata;
            }
        }

        $parsed = \Extract_low_random::_unpack($bytes);
        $this->assertSame(\Extract_low_random::GZ | \Extract_low_random::BZ2, $parsed['c']);
        $offset = 0;

        foreach ($entries as $name => $entry) {
            $entry[7] = $offset;
            $this->assertSame($entry, $parsed['m'][$name]);
            $offset += $entry[2];
        }

        $this->assertSame(array('c' => 0), \Extract_low_random::_unpack(pack('VvVVV', 0, 0x11, 0, 0, 0)));
    }
}
