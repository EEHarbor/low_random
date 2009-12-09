<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

$plugin_info = array(
	'pi_name'			=> 'Low Random',
	'pi_version'		=> '2.1',
	'pi_author'			=> 'Lodewijk Schutte ~ Low',
	'pi_author_url'		=> 'http://loweblog.com/software/low-random/',
	'pi_description'	=> 'Returns randomness',
	'pi_usage'			=> Low_random::usage()
);

/**
* Low Random Plugin class
*
* @package			low-random-ee2_addon
* @version			2.1
* @author			Lodewijk Schutte ~ Low <low@loweblog.com>
* @link				http://loweblog.com/software/low-random/
* @license			http://creativecommons.org/licenses/by-sa/3.0/
*/
class Low_random {

	/**
	* Plugin return data
	*
	* @var	string
	*/
	var $return_data;

	/**
	* Set of items to choose from
	*
	* @var	array
	*/
	var $set = array();
	
	/**
	* Debug mode
	*
	* @var	bool
	*/
    var $debug	= FALSE;

	// --------------------------------------------------------------------

	/**
	* PHP4 Constructor
	*
	* @see	__construct()
	*/
	function Low_random()
	{
		$this->__construct();
	}

	// --------------------------------------------------------------------

	/**
	* PHP5 Constructor
	*
	* @return	null
	*/
	function __construct()
	{
		/** -------------------------------------
		/**  Get global instance
		/** -------------------------------------*/

		$this->EE =& get_instance();
	}
	
	// --------------------------------------------------------------------
	
	/**
	 * Randomize given items, pipe delimited
	 *
	 * @param	string	$str
	 * @return	string
	 */
    function item($str = '')
    {
		if ($str == '')
		{
			$str = $this->EE->TMPL->fetch_param('items', '');
		}
		
		$this->set = explode('|', $str);

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
    function items($str = '')
    {
		// get tagdata
		if ($str == '')
		{
			$str = $this->EE->TMPL->tagdata;
		}
		
		// trim if necessary
		if ($this->EE->TMPL->fetch_param('trim', 'yes') != 'no')
		{
			$str = trim($str);
		}
		
		// get separator
		$sep = $this->EE->TMPL->fetch_param('separator', "\n");
		
		// create array from tagdata
		$this->set = explode($sep, $str);

		return $this->_random_item_from_set();
    }

	// --------------------------------------------------------------------
	
	/**
	 * Randomize the given letter range
	 *
	 * @param	string	$from
	 * @param	string	$to
	 * @return	string
	 */
	function letter($from = '', $to = '')
	{
		// Parameters
		if ($from == '')
		{
			$from = $this->EE->TMPL->fetch_param('from', 'a');
		}
		
		if ($to == '')
		{
			$to	= $this->EE->TMPL->fetch_param('to', 'z');
		}

		// no from? Set to a
		if (!preg_match('/^[a-z]$/i', $from))
		{
			$from = 'a';
		}
		
		// no to? Set to z
		if (!preg_match('/^[a-z]$/i', $to))
		{
			$to = 'z';
		}

		// fill set
		$this->set = range($from, $to);
		
		return $this->_random_item_from_set();
	}

	// --------------------------------------------------------------------
	
	/**
	 * Random number between 2 values
	 *
	 * @param	string	$from
	 * @param	string	$to
	 * @return	string
	 */
	function number($from = '', $to = '')
	{
		// Parameters
		if ($from == '')
		{
			$from = $this->EE->TMPL->fetch_param('from', '0');
		}
		
		if ($to == '')
		{
			$to	= $this->EE->TMPL->fetch_param('to', '9');
		}
		
		// no from? Set to 0
		if (!is_numeric($from))
		{
			$from = '0';
		}
		
		// no to? Set to 9
		if (!is_numeric($to))
		{
			$to = '9';
		}
		
		// return random number
		return strval(rand(intval($from), intval($to)));
	}

	// --------------------------------------------------------------------
	
	/**
	 * Get random file from file system
	 *
	 * @param	string	$folder
	 * @param	string	$filter
	 * @return	string
	 */
	function file($folder = '', $filter = '')
	{
		// init var
		$error = FALSE;
		
		// Parameters
		if ($folder == '')
		{
			$folder = $this->EE->TMPL->fetch_param('folder');
		}
		
		if ($filter == '')
		{
			$filter = $this->EE->TMPL->fetch_param('filter', '');
		}

		// Convert filter to array
		$filters = strlen($filter) ? explode('|', $filter) : array();

		// is folder a number?
		if (is_numeric($folder))
		{
			// get server path from upload prefs
			$this->EE->db->select('server_path');
			$this->EE->db->from('exp_upload_prefs');
			$this->EE->db->where('id', $folder);
			$query = $this->EE->db->get();

			// Do we have a match? get path
			if ($query->num_rows())
			{
				$folder = $query->row('server_path');
			}
		}
		
		// Simple folder check
		if (!strlen($folder))
		{
			$error = TRUE;
		}
		else
		{
			// check for trailing slash
			if (substr($folder, -1, 1) != '/')
			{
				$folder .= '/';
			}
		}

		// Another folder check
		if (!is_dir($folder))
		{
			$error = TRUE;
		}
		else
		{
			// open dir
			$dir = opendir($folder);

			// loop through folder
			while($f = readdir($dir))
			{
				// no file? skip
				if (!is_file($folder.$f)) continue;

				// set addit to 0, check filters
				$addit = 0;

				// check if filter applies
				foreach ($filters AS $filter)
				{
					if (strlen($filter) && substr_count($f, $filter))
					{
						$addit++;
					}
				}

				// if we have a match, add file to array
				if ($addit == count($filters))
				{
					$this->set[] = $f;
				}
			}

			// close dir
			closedir($dir);
		}
		
		// return data
		return $error ? $this->_invalid_folder($folder) : $this->_random_item_from_set();
	}
	
	// --------------------------------------------------------------------
	
	/**
	 * Display invalid folder if debug is on
	 *
	 * @param	string	$folder
	 * @return	string
	 */
	function _invalid_folder($folder = '')
	{
		// return error message if debug-mode is on
		return $this->debug ? "{$folder} is an invalid folder" : '';
	}

	// --------------------------------------------------------------------
	
	/**
	 * Random item from set (array)
	 *
	 * @return	string
	 */
	function _random_item_from_set()
	{
		return $this->set[array_rand($this->set)];
	}

	// --------------------------------------------------------------------
	
	/**
	 * Usage
	 *
	 * Plugin Usage
	 *
	 * @return	string
	 */
	function usage()
	{
		ob_start(); 
		?>
			{exp:low_random:item items="item1|item2|item3"}
			
			{exp:low_random:items}
				item 1
				item 2
				item 3
			{/exp:low_random:items}
			
			{exp:low_random:items separator=","}
				item 1, item 2, item 3
			{/exp:low_random:items}

			{exp:low_random:number from="1" to="200"}

			{exp:low_random:letter from="A" to="F"}

			{exp:low_random:file folder="images" filter="masthead|.jpg"}
		<?php
		$buffer = ob_get_contents();
  
		ob_end_clean(); 

		return $buffer;
	}

	// --------------------------------------------------------------------

}
// END CLASS

/* End of file pi.low_random.php */