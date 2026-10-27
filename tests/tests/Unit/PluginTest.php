<?php

namespace Tests;

final class PluginTest extends TestCase
{
    /**
     * Preserve pipes, empty members and string coercion in item candidates.
     *
     * @return void
     */
    public function testItemPreservesEveryDelimitedCandidate()
    {
        $plugin = $this->plugin(array('items' => 'one||0| two |one'));
        $this->assertContains($plugin->item(), array('one', '', '0', ' two '));
        $this->assertCandidates(array('one', '', '0', ' two ', 'one'));
    }

    /**
     * Missing items use the default while a literal zero remains a candidate.
     *
     * @return void
     */
    public function testMissingItemsAndLiteralZero()
    {
        foreach (array(array(), array('items' => '0')) as $parameters) {
            $result = $this->plugin($parameters)->item();
            $this->assertSame(isset($parameters['items']) ? '0' : '', $result);
        }
    }

    /**
     * Strip exterior whitespace while preserving internal blank lines.
     *
     * @return void
     */
    public function testItemsTrimsByDefault()
    {
        $plugin = $this->plugin(array(), " \nfirst\n\nlast\n ");
        $this->assertContains($plugin->items(), array('first', '', 'last'));
        $this->assertCandidates(array('first', '', 'last'));
    }

    /**
     * EE's legacy no aliases preserve whitespace verbatim.
     *
     * @return void
     */
    public function testItemsRespectsTrimAliasesAndCustomSeparators()
    {
        foreach (array('no', 'n', 'off') as $trim) {
            $plugin = $this->plugin(array('trim' => $trim, 'separator' => '::'), ' one :: two ');
            $this->assertContains($plugin->items(), array(' one ', ' two '));
            $this->assertCandidates(array(' one ', ' two '));
        }
    }

    /**
     * Empty tagdata remains an empty item after splitting.
     *
     * @return void
     */
    public function testEmptyTagdataYieldsAnEmptyString()
    {
        $this->assertSame('', $this->plugin()->items());
        $this->assertCandidates(array(''));
    }

    /**
     * Delimiter validation must fail instead of silently returning candidates.
     *
     * @return void
     */
    public function testEmptySeparatorIsRejected()
    {
        $plugin = $this->plugin(array('separator' => ''), 'one');
        $this->expectException(\Throwable::class);
        $plugin->items();
    }

    /**
     * Verify the full default letter and numeric sets, then selected membership.
     *
     * @return void
     */
    public function testDefaultRanges()
    {
        $plugin = $this->plugin();
        $this->assertContains($plugin->letter(), range('a', 'z'));
        $this->assertCandidates(range('a', 'z'));
        $this->assertContains($plugin->number(), array_map('strval', range(0, 9)));
        $this->assertCandidates(range(0, 9));
    }

    /**
     * Preserve descending, negative and single-element numeric ranges.
     *
     * @return void
     */
    public function testCustomRangeBoundaries()
    {
        foreach (array(array('3', '1'), array('-2', '0'), array('0', '0')) as $bounds) {
            $plugin = $this->plugin(array('from' => $bounds[0], 'to' => $bounds[1]));
            $expected = range($bounds[0], $bounds[1]);
            $this->assertContains($plugin->number(), array_map('strval', $expected));
            $this->assertCandidates($expected);
        }
    }

    /**
     * Missing directories produce no candidate and require no helper.
     *
     * @return void
     */
    public function testMissingFolderReturnsEmpty()
    {
        $this->assertSame('', $this->plugin(array('folder' => $this->temporaryDirectory . '/missing'))->file());
        $this->assertCandidates(array());
        $this->assertSame(array(), $this->ee->load->helpers);
    }

    /**
     * Directories use basename results and apply all case-sensitive needles.
     *
     * @return void
     */
    public function testFileDirectoryFiltersRequireEveryNeedle()
    {
        foreach (array('keep-blue.jpg', 'keep-red.jpg', 'blue.txt', 'KEEP-blue.jpg') as $name) {
            file_put_contents($this->temporaryDirectory . '/' . $name, 'fixture');
        }

        $plugin = $this->plugin(array('folder' => $this->temporaryDirectory, 'filter' => 'keep|blue'));
        $this->assertSame('keep-blue.jpg', $plugin->file());
        $this->assertCandidates(array('keep-blue.jpg'));
        $this->assertSame(array('file'), $this->ee->load->helpers);
    }

    /**
     * An empty filter keeps all files, including recursive basenames.
     *
     * @return void
     */
    public function testFileDirectoryWithoutFilter()
    {
        mkdir($this->temporaryDirectory . '/nested');
        file_put_contents($this->temporaryDirectory . '/nested/one.txt', 'fixture');
        $this->assertSame('one.txt', $this->plugin(array('folder' => $this->temporaryDirectory))->file());
        $this->assertCandidates(array('one.txt'));
    }

    /**
     * Removing every file returns an empty string after reindexing.
     *
     * @return void
     */
    public function testFileFilterCanRemoveEveryCandidate()
    {
        file_put_contents($this->temporaryDirectory . '/one.txt', 'fixture');
        $this->assertSame('', $this->plugin(array('folder' => $this->temporaryDirectory, 'filter' => '.jpg'))->file());
        $this->assertCandidates(array());
    }

    /**
     * Numeric folders use EE File's related UploadDestination and AND filters.
     *
     * @return void
     */
    public function testNumericFolderBuildsModelFiltersAndNormalizesUrls()
    {
        $query = $GLOBALS['low_random_services']['Model'];
        $query->rows = array((object) array('file_name' => 'cat.jpg', 'UploadDestination' => (object) array('url' => 'https://example.test/media///')));
        $this->assertSame('https://example.test/media/cat.jpg', $this->plugin(array('folder' => '12', 'filter' => 'cat|.jpg'))->file());
        $this->assertSame(array(
            array('get', array('File')),
            array('with', array('UploadDestination')),
            array('filter', array('UploadDestination.id', '12')),
            array('filter', array('file_name', 'LIKE', '%cat%')),
            array('filter', array('file_name', 'LIKE', '%.jpg%')),
            array('all', array()),
        ), $query->calls);
    }

    /**
     * Zero is a numeric upload destination, with an empty query result.
     *
     * @return void
     */
    public function testNumericZeroAndNoModelRows()
    {
        $this->assertSame('', $this->plugin(array('folder' => '0'))->file());
        $this->assertCandidates(array());
        $this->assertSame(array('filter', array('UploadDestination.id', '0')), $GLOBALS['low_random_services']['Model']->calls[2]);
    }

}
