<?php
/*
=====================================================
 This plugin was created by Lodewijk Schutte
 - freelance@loweblog.com
 - http://loweblog.com/freelance/
=====================================================
 File: pi.low_random.php
-----------------------------------------------------
 Purpose: Return randomness
=====================================================
*/

$plugin_info = array(
                 'pi_name'          => 'Low Random',
                 'pi_version'       => '1.1',
                 'pi_author'        => 'Lodewijk Schutte',
                 'pi_author_url'    => 'http://loweblog.com/software/low-random/',
                 'pi_description'   => 'Returns randomness',
                 'pi_usage'         => low_random::usage()
               );


class Low_random {

	var $set	= array();
    var $debug	= FALSE;

	// ----------------------------------------
	//  Randomize given items
	// ----------------------------------------

    function item()
    {
		global $TMPL;

		$this->set = explode('|', $TMPL->fetch_param('items'));

		return $this->_random_item_from_set();
    }
    // END


	// ----------------------------------------
	//  Randomize tagdata items
	// ----------------------------------------

    function items()
    {
		global $TMPL;
		
		// Get separator, new line by default
		$sep = $TMPL->fetch_param('separator') ? $TMPL->fetch_param('separator') : "\n";

		// get tagdata
		$data = $TMPL->tagdata;
		
		// trim if necessary
		if ($TMPL->fetch_param('trim') != 'no')
		{
			$data = trim($data);
		}
		
		// Turn data into array
		$this->set = explode($sep, $data);

		return $this->_random_item_from_set();
    }
    // END


	// ----------------------------------------
	//  Randomize the alphabet
	// ----------------------------------------

	function letter()
	{
		global $TMPL;

		// Parameters
		$from	= $TMPL->fetch_param('from');
		$to		= $TMPL->fetch_param('to');

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
	// END



	// ----------------------------------------
	//  Random number between 2 values
	// ----------------------------------------

	function number()
	{
		global $TMPL;
		
		// Parameters
		$from	= $TMPL->fetch_param('from');
		$to		= $TMPL->fetch_param('to');
		
		// no from? Set to 0
		if (!is_numeric($from))
		{
			$from = 0;
		}
		
		// no to? Set to 9
		if (!is_numeric($to))
		{
			$to = 9;
		}
		
		// return random number
		return rand($from, $to);
	}
	// END
	
	

	// ----------------------------------------
	//  Random file
	// ----------------------------------------
	
	function file()
	{
		global $TMPL, $DB, $PREFS;

		// init some vars
		$folder  = '';
		$filters = array();

		// get filter param
		if ($TMPL->fetch_param('filter'))
		{
			$filters = explode('|', $TMPL->fetch_param('filter'));
		}

		// get folder param
		$folder = str_replace("&#47;","/",$TMPL->fetch_param('folder'));

		// get folder from upload prefs?
		if (is_numeric($folder))
		{
			$sql = "SELECT server_path FROM exp_upload_prefs WHERE id = '".$DB->escape_str($folder)."'";
			$res = $DB->query($sql);
			if ($res->num_rows)
			{
				$folder = $res->row['server_path'];
			}
		}

		// check for trailing slash
		if (substr($folder, -1, 1) != "/")
		{
			$folder .= "/";
		}

		// check folder
		if (is_dir($folder))
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

			return $this->_random_item_from_set();

		}
		else
		{
			// return error message if debug-mode is on
			if ($this->debug)
			{
				return "{$folder} = Invalid folder!";
			}	
		}
	}
	// END
    


	// ----------------------------------------
	//  Random item from set (array)
	// ----------------------------------------

	function _random_item_from_set()
	{
		return $this->set[array_rand($this->set)];
	}
	// END


    
// ----------------------------------------
//  Plugin Usage
// ----------------------------------------

// This function describes how the plugin is used.

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

{exp:low_random:number from="1" to="200"}

{exp:low_random:letter from="A" to="F"}

{exp:low_random:file folder="images" filter="masthead|.jpg"}
<?php
$buffer = ob_get_contents();
  
ob_end_clean(); 

return $buffer;
}
// END

}
// END CLASS
?>