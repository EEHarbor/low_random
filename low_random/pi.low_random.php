<?php

if (! defined('BASEPATH')) {
    exit('No direct script access allowed');
}

/**
 * Low Random Plugin class
 *
 * @package        low_random
 * @author         Lodewijk Schutte <hi@gotolow.com>
 * @link           http://gotolow.com/addons/low-nice-date
 * @license        http://creativecommons.org/licenses/by-sa/3.0/
 */
include_once "addon.setup.php";
use Low\Random\FluxCapacitor\Base\Pi;

class Low_random extends Pi
{
    // --------------------------------------------------------------------
    // PROPERTIES
    // --------------------------------------------------------------------

    /**
     * Set of items to choose from
     *
     * @var array
     */
    private $set = array();

    // --------------------------------------------------------------------
    // METHODS
    // --------------------------------------------------------------------

    /**
     * Randomize given items, pipe delimited
     *
     * @return string
     * @throws InvalidArgumentException When items cannot be represented as a string.
     */
    public function item()
    {
        $items = ee()->TMPL->fetch_param('items');
        if (!is_scalar($items) && $items !== null && !(is_object($items) && method_exists($items, '__toString'))) {
            throw new InvalidArgumentException('Items must be a string-compatible template parameter.');
        }

        $this->set = explode('|', $items);

        return $this->_random_item_from_set();
    }

    // --------------------------------------------------------------------

    /**
     * Randomize tagdata
     *
     * @since   2.1
     * @return string
     * @throws InvalidArgumentException When the separator is empty.
     * @throws Throwable When a stringable separator cannot be converted.
     */
    public function items()
    {
        // get tagdata
        $str = ee()->TMPL->tagdata ?? '';

        // trim if necessary
        if (ee()->TMPL->fetch_param('trim', 'yes') != 'no') {
            $str = trim($str);
        }

        // get separator
        $sep = ee()->TMPL->fetch_param('separator', "\n");
        if (is_object($sep) && method_exists($sep, '__toString')) {
            $sep = (string) $sep;
        }
        if ($sep === '' || $sep === false || $sep === null) {
            throw new InvalidArgumentException('Separator must not be empty.');
        }

        // create array from tagdata
        $this->set = explode($sep, $str);

        return $this->_random_item_from_set();
    }

    // --------------------------------------------------------------------

    /**
     * Randomize the given letter range
     *
     * @return  string
     */
    public function letter()
    {
        return $this->_range('a', 'z');
    }

    /**
     * Random number between 2 values
     */
    public function number()
    {
        return $this->_range(0, 9);
    }

    /**
     * Random item from range set
     */
    private function _range($from = '', $to = '')
    {
        // Get params with given defaults
        $from = ee()->TMPL->fetch_param('from', $from);
        $to = ee()->TMPL->fetch_param('to', $to);

        // fill set
        $this->set = range($from, $to);

        return $this->_random_item_from_set();
    }

    // --------------------------------------------------------------------

    /**
     * Get random file from file system
     *
     * @return  string
     */
    public function file()
    {
        $this->set = array();
        $folder = ee()->TMPL->fetch_param('folder');
        $filter = ee()->TMPL->fetch_param('filter');

        // Convert filter to array
        $filters = strlen($filter) ? explode('|', $filter) : array();

        // is folder a number?
        if (is_numeric($folder)) {
            $bob = ee('Model')
                ->get('File')
                ->with('UploadDestination')
                ->filter('UploadDestination.id', $folder);

            foreach ($filters as $needle) {
                $bob->filter('file_name', 'LIKE', '%' . $needle . '%');
            }

            foreach ($bob->all() as $file) {
                $this->set[] = rtrim($file->UploadDestination->url, '/') . '/' . $file->file_name;
            }
            return $this->_random_item_from_set();
        }

        if (is_dir($folder)) {
            ee()->load->helper('file');
            $this->set = $files = get_filenames($folder);

            if ($filters) {
                $this->filter($filters);
            }
        }

        return $this->_random_item_from_set();
    }

    // --------------------------------------------------------------------

    /**
     * Filter the array of items based on the absence of filters
     *
     * @param   string  $folder
     * @return  string
     */
    private function filter(array $filters)
    {
        foreach ($this->set as $i => $val) {
            foreach ($filters as $needle) {
                if (strpos($val, $needle) === false) {
                    unset($this->set[$i]);

                    continue;
                }
            }
        }

        $this->set = array_values($this->set);
    }

    // --------------------------------------------------------------------

    /**
     * Random item from set (array)
     *
     * @return  string
     */
    private function _random_item_from_set()
    {
        ee()->TMPL->log_item('Low Random: getting random item from:<br>' . implode('<br>', $this->set));

        return $this->set ? (string) $this->set[array_rand($this->set)] : '';
    }

    // --------------------------------------------------------------------
}
// END CLASS

/* End of file pi.low_random.php */
