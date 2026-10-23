<?php

namespace Tests;

use Tests\Support\Query;

final class FixtureContractTest extends TestCase
{
    /**
     * Native output cannot deadlock when stdout and stderr exceed pipe capacity.
     *
     * @return void
     */
    public function testNativeOutputFilesAvoidPipeDeadlock()
    {
        $result = $this->native(array('-r', 'fwrite(STDOUT, str_repeat("o", 1048576)); fwrite(STDERR, str_repeat("e", 1048576));'));
        $this->assertSame(0, $result[0]);
        $this->assertSame(1048576, strlen($result[1]));
        $this->assertSame(1048576, strlen($result[2]));
    }

    /**
     * Hung native children are terminated without changing global runtime limits.
     *
     * @return void
     */
    public function testNativeDeadlineIsEnforced()
    {
        $this->expectException(\RuntimeException::class);
        $this->expectExceptionMessage('exceeded its deadline');
        $this->native(array('-r', 'usleep(500000);'), array(), 0.05);
    }

    /**
     * License-check crypto runs cannot consume or append to the caller's SSL errors.
     *
     * @return void
     */
    public function testNativeCryptoPreservesCallerErrorQueue()
    {
        $result = $this->native(array(dirname(__DIR__, 2) . '/Support/native.php', 'crypto-queue-probe', $this->temporaryDirectory));
        $this->assertSame(0, $result[0], $result[1] . $result[2]);
        $this->assertSame('', $result[2]);
        $this->assertSame('caller queue preserved', $result[1]);
    }

    /**
     * Nested fixture execution restores the caller's include path after cleanup.
     *
     * @return void
     */
    public function testHarnessRestoresIncludePath()
    {
        $original = get_include_path();
        $probe = new class('probe') extends TestCase {
            /**
             * Enter the real harness lifecycle for this contract probe.
             *
             * @return void
             */
            public function enter()
            {
                parent::setUp();
            }

            /**
             * Leave the real harness lifecycle for this contract probe.
             *
             * @return void
             */
            public function leave()
            {
                parent::tearDown();
            }
        };
        $probe->enter();

        try {
            set_include_path($this->temporaryDirectory);
        } finally {
            $probe->leave();
        }

        $this->assertSame($original, get_include_path());
    }

    /**
     * Match EE DB_result's empty, field, SQL NULL and selected-row behavior.
     *
     * @return void
     */
    public function testQueryRowContracts()
    {
        $empty = new Query(array());
        $this->assertSame(array(), $empty->row());
        $this->assertSame(array(), $empty->row('missing'));
        $this->assertSame(array(), $empty->row_array());
        $this->assertSame(array(), $empty->result());
        $this->assertSame(0, $empty->num_rows());

        $rows = array(array('id' => 1, 'nullable' => null), array('id' => 2, 'nullable' => 'value'));
        $query = new Query($rows);
        $first = $query->row();
        $this->assertSame($rows[0], (array) $first);
        $this->assertSame($first, $query->row('missing'));
        $this->assertNull($query->row('nullable'));
        $this->assertSame(1, $query->row('id'));
        $this->assertSame($rows[1], $query->row(1, 'array'));
        $this->assertSame(2, $query->row_object(99)->id);
        $this->assertSame(1, $query->row('id'));
        $this->assertSame($rows[0], $query->row_array(0));
        $this->assertSame($first, $query->result()[0]);
        $this->assertSame($rows, $query->result('array'));
        $this->assertSame(2, $query->num_rows());
    }

    /**
     * EE skips hidden directories before descending and skips hidden files.
     *
     * @return void
     */
    public function testFileHelperSkipsHiddenPaths()
    {
        mkdir($this->temporaryDirectory . '/.hidden');
        mkdir($this->temporaryDirectory . '/visible');
        file_put_contents($this->temporaryDirectory . '/.hidden/secret.txt', 'hidden');
        file_put_contents($this->temporaryDirectory . '/visible/.secret', 'hidden');
        file_put_contents($this->temporaryDirectory . '/visible/public.txt', 'visible');
        $this->assertSame(array('public.txt'), get_filenames($this->temporaryDirectory));
        $this->assertSame(array(realpath($this->temporaryDirectory) . '/visible/public.txt'), get_filenames($this->temporaryDirectory, true));
        $this->assertSame('public.txt', $this->plugin(array('folder' => $this->temporaryDirectory))->file());
        $this->assertCandidates(array('public.txt'));
    }

    /**
     * Helpers preserve native readdir depth-first order and do not share lists.
     *
     * @return void
     */
    public function testFileHelperOrderAndIndependentCalls()
    {
        file_put_contents($this->temporaryDirectory . '/z-first.txt', 'first');
        mkdir($this->temporaryDirectory . '/nested');
        file_put_contents($this->temporaryDirectory . '/nested/inner.txt', 'inner');
        file_put_contents($this->temporaryDirectory . '/a-last.txt', 'last');
        $expected = array();
        $handle = opendir($this->temporaryDirectory);

        try {
            while (($name = readdir($handle)) !== false) {
                if ($name === '.' || $name === '..') {
                    continue;
                }

                $expected[] = $name === 'nested' ? 'inner.txt' : $name;
            }
        } finally {
            closedir($handle);
        }

        $this->assertSame($expected, get_filenames($this->temporaryDirectory));
        $this->assertSame(array('inner.txt'), get_filenames($this->temporaryDirectory . '/nested'));
        $this->assertFalse(get_filenames($this->temporaryDirectory . '/missing'));
    }
}
