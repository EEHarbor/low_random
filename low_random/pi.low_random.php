<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

/**
 * Low Random Plugin class
 *
 * @package        low_random
 * @author         Lodewijk Schutte <hi@gotolow.com>
 * @link           http://gotolow.com/addons/low-nice-date
 * @license        http://creativecommons.org/licenses/by-sa/3.0/
 */
class Low_random {

	// --------------------------------------------------------------------
	// PROPERTIES
	// --------------------------------------------------------------------

	/**
	 * Set of items to choose from
	 *
	 * @var	array
	 */
	private $set = array();

	// --------------------------------------------------------------------
	// METHODS
	// --------------------------------------------------------------------

	/**
	 * Randomize given items, pipe delimited
	 *
	 * @param	string	$str
	 * @return	string
	 */
    public function item()
    {
		$this->set = explode('|', ee()->TMPL->fetch_param('items'));
		return $this->_random_item_from_set();
    }

	// --------------------------------------------------------------------

	/**
	 * Randomize tagdata
	 *
	 * @since	2.1
	 * @param	string	$str
	 * @return	string
	 */
    public function items()
    {
		// get tagdata
		$str = ee()->TMPL->tagdata;

		// trim if necessary
		if (ee()->TMPL->fetch_param('trim', 'yes') != 'no')
		{
			$str = trim($str);
		}

		// get separator
		$sep = ee()->TMPL->fetch_param('separator', "\n");

		// create array from tagdata
		$this->set = explode($sep, $str);

		return $this->_random_item_from_set();
    }

	// --------------------------------------------------------------------

	/**
	 * Randomize the given letter range
	 *
	 * @return	string
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
		$to	= ee()->TMPL->fetch_param('to', $to);

		// fill set
		$this->set = range($from, $to);

		return $this->_random_item_from_set();
	}

	// --------------------------------------------------------------------

	/**
	 * Get random file from file system
	 *
	 * @param	string	$folder
	 * @param	string	$filter
	 * @return	string
	 */
	public function file()
	{
		$folder = ee()->TMPL->fetch_param('folder');
		$filter = ee()->TMPL->fetch_param('filter');

		// Convert filter to array
		$filters = strlen($filter) ? explode('|', $filter) : array();

		// is folder a number?
		if (is_numeric($folder))
		{
			$bob = ee('Model')
				->get('File')
				->with('UploadDestination')
				->filter('UploadDestination.id', $folder);

			foreach($filters as $needle)
			{
				$bob->filter('file_name', 'LIKE', '%'.$needle.'%');
			}

			foreach ($bob->all() as $file)
			{
				$this->set[] = rtrim($file->UploadDestination->url, '/') .'/'. $file->file_name;
			}
		}
		elseif (is_dir($folder))
		{
			ee()->load->helper('file');
			$this->set = $files = get_filenames($folder);

			if ($filters)
			{
				$this->filter($filters);
			}
		}

		return $this->_random_item_from_set();
	}

	// --------------------------------------------------------------------

	/**
	 * Filter the array of items based on the absence of filters
	 *
	 * @param	string	$folder
	 * @return	string
	 */
	private function filter(array $filters)
	{
		foreach ($this->set as $i => $val)
		{
			foreach ($filters as $needle)
			{
				if (strpos($val, $needle) === FALSE)
				{
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
	 * @return	string
	 */
	private function _random_item_from_set()
	{
		ee()->TMPL->log_item('Low Random: getting random item from:<br>'.implode('<br>', $this->set));
		return (string) $this->set[array_rand($this->set)];
	}

	// --------------------------------------------------------------------

}
// END CLASS

/* End of file pi.low_random.php */
