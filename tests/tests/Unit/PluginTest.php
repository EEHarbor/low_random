<?php

namespace Tests;

final class PluginTest extends TestCase
{
    /**
     * Verify metadata is loaded through the real archive foundation.
     *
     * @return void
     */
    public function testConstructorReadsAddonMetadata()
    {
        $plugin = $this->plugin();
        $this->assertSame('4.1.1', $plugin->version);
        $this->assertInstanceOf('Low\\Random\\FluxCapacitor\\FluxCapacitor', $plugin->flux);
    }

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
     * Missing and null template parameters use EE's false default.
     *
     * @return void
     */
    public function testMissingAndNullItemsYieldAnEmptyString()
    {
        foreach (array(array(), array('items' => null), array('items' => false), array('items' => 0), array('items' => '0')) as $parameters) {
            $result = $this->plugin($parameters)->item();
            $expected = isset($parameters['items']) && $parameters['items'] !== false ? '0' : '';

            if (isset($parameters['items']) && $parameters['items'] === 0 && PHP_VERSION_ID < 80000) {
                $expected = 'yes';
            }

            $this->assertSame($expected, $result);
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
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('Separator must not be empty.');
        $this->plugin(array('separator' => ''), 'one')->items();
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
     * Preserve descending, negative, fractional and single-element ranges.
     *
     * @return void
     */
    public function testCustomRangeBoundaries()
    {
        foreach (array(array('3', '1'), array('-2', '0'), array(1.5, 3.5), array('0', '0'), array('Z', 'X')) as $bounds) {
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

    /**
     * Each file call should select only from its current upload destination.
     *
     * @return void
     */
    public function testRepeatedFileCallsDoNotLeakPreviousCandidates()
    {
        $plugin = $this->plugin(array('items' => 'previous'));
        $this->assertSame('previous', $plugin->item());
        $this->ee->TMPL->tagparams = array('folder' => '12');
        $GLOBALS['low_random_services']['Model']->rows = array((object) array('file_name' => 'current.jpg', 'UploadDestination' => (object) array('url' => '/media/')));
        $plugin->file();
        $this->assertCandidates(array('/media/current.jpg'));
    }

    /**
     * Null template content should not reach deprecated native string coercion.
     *
     * @return void
     */
    public function testNullTagdataDoesNotEmitDiagnostics()
    {
        $this->assertSame('', $this->plugin(array(), null)->items());
    }

    /**
     * Arrays are invalid string parameters and must be rejected explicitly.
     *
     * @return void
     */
    public function testStructuredItemsAreRejected()
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('Items must be a string-compatible template parameter.');
        $this->plugin(array('items' => array('one')))->item();
    }

    /**
     * Preserve native conversion for explicitly stringable template parameter values.
     *
     * @return void
     */
    public function testStringableItemsRemainSupported(): void
    {
        $value = new class {
            /**
             * Return the fixture's single delimited candidate.
             *
             * @return string
             */
            public function __toString(): string
            {
                return 'owned-stringable-value';
            }
        };
        $this->assertSame('owned-stringable-value', $this->plugin(array('items' => $value))->item());
    }

    /**
     * Reject a separator that becomes empty through its supported native conversion.
     *
     * @return void
     */
    public function testEmptyStringableSeparatorIsRejected(): void
    {
        $separator = new class {
            /**
             * Return the fixture's empty delimiter.
             *
             * @return string
             */
            public function __toString(): string
            {
                return '';
            }
        };
        $this->ee->TMPL->ignore_fetch[] = 'separator';
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('Separator must not be empty.');
        $this->plugin(array('separator' => $separator), 'one')->items();
    }

    /**
     * Convert a supported separator once before splitting the candidate set.
     *
     * @return void
     */
    public function testNonemptyStringableSeparatorIsConvertedOnce(): void
    {
        $separator = new class {
            public $calls = 0;

            /**
             * Count this fixture's native conversion.
             *
             * @return string
             */
            public function __toString(): string
            {
                $this->calls++;
                return '|';
            }
        };
        $this->ee->TMPL->ignore_fetch[] = 'separator';
        $this->assertContains($this->plugin(array('separator' => $separator), 'one|two')->items(), array('one', 'two'));
        $this->assertCandidates(array('one', 'two'));
        $this->assertSame(1, $separator->calls);
    }

    /**
     * Preserve the exact exception raised by an existing conversion callback.
     *
     * @return void
     */
    public function testStringableSeparatorConversionExceptionPropagates(): void
    {
        $failure = new \RuntimeException('owned separator conversion exception');
        $separator = new class($failure) {
            private $failure;

            /**
             * Retain the exact owned callback exception.
             *
             * @param \RuntimeException $failure
             * @return void
             */
            public function __construct(\RuntimeException $failure)
            {
                $this->failure = $failure;
            }

            /**
             * Propagate this fixture's native callback exception unchanged.
             *
             * @return string
             * @throws \RuntimeException
             */
            public function __toString(): string
            {
                throw $this->failure;
            }
        };
        $this->ee->TMPL->ignore_fetch[] = 'separator';

        try {
            $this->plugin(array('separator' => $separator), 'one')->items();
            $this->fail('The conversion callback exception was swallowed.');
        } catch (\RuntimeException $caught) {
            $this->assertSame($failure, $caught);
        }
    }

}
