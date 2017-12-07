<?php

error_reporting(E_ALL);

// journal

require_once (dirname(__FILE__) . '/couchsimple.php');
require_once (dirname(__FILE__) . '/lib.php');

require_once (dirname(__FILE__) . '/api_utils.php');

//----------------------------------------------------------------------------------------
function default_display()
{
	echo "hi";
}

/*
//----------------------------------------------------------------------------------------
// sort works based on volume and page
function cmp($a, $b)
{
	$result = 0;
	
	$volume_a = 0;
	$volume_b = 0;
	
	if (isset($a->journal->volume))
	{
		$volume_a = $a->journal->volume;
	}

	if (isset($b->journal->volume))
	{
		$volume_b = $b->journal->volume;
	}
	
	if ($volume_a == $volume_b)
	{
		$result = 0;
	}
	else
	{
		$result = ($volume_a < $volume_b) ? -1 : 1;
	}
	
	if ($result == 0)
	{			
		// spage
		$spage_a = 0;
		$spage_b = 0;
		
		if (isset($a->journal->pages))
		{
			if (preg_match('/^(?<spage>.*)--(?<epage>.*)/', $a->journal->pages, $m))
			{
				$spage_a = $m['spage'];
			}
			else
			{
				$spage_a = $a->journal->pages;
			}
		}

		if (isset($b->journal->pages))
		{
			if (preg_match('/^(?<spage>.*)--(?<epage>.*)/', $b->journal->pages, $m))
			{
				$spage_b = $m['spage'];
			}
			else
			{
				$spage_b = $b->journal->pages;
			}
		}
		
		if ($spage_a == $spage_b)
		{
			$result = 0;
		}
		else
		{
			$result = ($spage_a < $spage_b) ? -1 : 1;
		}
		
	}
	
	return $result;
}
*/

//----------------------------------------------------------------------------------------
function display_works_year($title, $year, $callback = '')
{
	global $config;
	global $couch;
	
	$startkey = array($title, (Integer)$year);
	$endkey   = array($title, (Integer)($year + 1));
	
	$url = '_design/container/_view/title-year-page?startkey=' . json_encode($startkey) . '&endkey=' . json_encode($endkey) . '&include_docs=true';				

	if ($config['stale'])
	{
		$url .= '&stale=ok';
	}		
	
	$resp = $couch->send("GET", "/" . $config['couchdb_options']['database'] . "/" . $url);
	
	$response_obj = json_decode($resp);
	
	$obj = new stdclass;
	$obj->status = 404;
	$obj->url = $url;
	
	if (isset($response_obj->error))
	{
		$obj->error = $response_obj->error;
	}
	else
	{
		if (count($response_obj->rows) == 0)
		{
			$obj->error = 'Not found';
		}
		else
		{	
			$obj->status = 200;
			
			$obj->works = array();
			foreach ($response_obj->rows as $row)
			{
				$obj->works[] = $row->doc;
			}
			
			// sort 
			//usort($obj->works, "cmp");
		}
	}
	
	api_output($obj, $callback);
}

//----------------------------------------------------------------------------------------
// Works within a container clustered by decade, then year. Return counts for each year.
function display_container_decade_volumes ($title, $callback = '')
{
	global $config;
	global $couch;
	
	$startkey = array($title);
	$endkey = array($title);
	$endkey[] = new stdclass;
	
	$url = '_design/container/_view/title-decade-year?startkey=' . json_encode($startkey) . '&endkey=' . json_encode($endkey) . '&group_level=3';	

	
	if ($config['stale'])
	{
		$url .= '&stale=ok';
	}	
	
	
	$resp = $couch->send("GET", "/" . $config['couchdb_options']['database'] . "/" . $url);
	
	$response_obj = json_decode($resp);
	
	$obj = new stdclass;
	$obj->status = 404;
	$obj->url = $url;
	
	if (isset($response_obj->error))
	{
		$obj->error = $response_obj->error;
	}
	else
	{
		if (count($response_obj->rows) == 0)
		{
			$obj->error = 'Not found';
		}
		else
		{	
			$obj->status = 200;
			
			// group into decades	
			$obj->decades = array();
			foreach ($response_obj->rows as $row)
			{
				if (!isset($obj->decades[$row->key[1]]))
				{
					$obj->decades[$row->key[1]] = array();
				}
		
				if (!isset($obj->decades[$row->key[1]][$row->key[2]]))
				{
					$obj->decades[$row->key[1]][$row->key[2]] = array();
				}
				
				$obj->decades[$row->key[1]][$row->key[2]] = $row->value;
			}	
		}
	}
	
	api_output($obj, $callback);
}

//----------------------------------------------------------------------------------------
function display_starting_with($letter='A', $callback)
{
	global $config;
	global $couch;
	
	$startkey = array($letter);
	if ($letter == 'Z')
	{
		$endkey = array(new stdclass);
	}
	else
	{
		$endkey = array(chr(ord($letter) + 1));
	}

	$url = '_design/container/_view/sort-by-letter?startkey=' . json_encode($startkey) . '&endkey=' . json_encode($endkey) . '&group_level=4';

	if ($config['stale'])
	{
		$url .= '&stale=ok';
	}	
	
	
	$resp = $couch->send("GET", "/" . $config['couchdb_options']['database'] . "/" . $url);
	
	$response_obj = json_decode($resp);
	
	$obj = new stdclass;
	$obj->status = 404;
	$obj->url = $url;
	
	if (isset($response_obj->error))
	{
		$obj->error = $response_obj->error;
	}
	else
	{
		$obj->status = 200;
		$obj->results = array();
				
		// group by cleaned name
		foreach ($response_obj->rows as $row)
		{
			$cleanedname = $row->key[1];
			$identifier  = $row->key[2];
			$name	 	 = $row->key[3];
			
			if (!isset($obj->results[$cleanedname]))
			{
				$obj->results[$cleanedname] = new stdclass;
				$obj->results[$cleanedname]->name = $name;
				$obj->results[$cleanedname]->identifier = $identifier;
				$obj->results[$cleanedname]->count = $row->value;
			}
			else
			{
				$obj->results[$cleanedname]->identifier = array_merge($obj->results[$cleanedname]->identifier, $identifier);
				$obj->results[$cleanedname]->count += $row->value;
			}
		}	
	}
	
	api_output($obj, $callback);
}



//--------------------------------------------------------------------------------------------------
function main()
{
	$callback = '';
	$handled = false;
	
	
	// If no query parameters 
	if (count($_GET) == 0)
	{
		default_display();
		exit(0);
	}
	
	if (isset($_GET['callback']))
	{	
		$callback = $_GET['callback'];
	}
	
	// Optional fields to include
	$fields = array('all');
	if (isset($_GET['fields']))
	{	
		$field_string = $_GET['fields'];
		$fields = explode(",", $field_string);
	}
	
	if (!$handled)
	{
		if (isset($_GET['letter']))
		{
			display_starting_with(strtoupper($_GET['letter']), $callback);
			$handled = true;
		}
	
	}	
	
	if (!$handled)
	{
		if (isset($_GET['title']))
		{	
			$title = $_GET['title'];
			
			
			if (!$handled)
			{
				if (isset($_GET['year']))
				{
					$year = $_GET['year'];
					display_works_year($title, $year, $callback);
					$handled = true;
				}	
			}
			
			if (!$handled)
			{
				display_container_decade_volumes($title, $callback);
				$handled = true;			
			}
		}
	}
			
			
			
			
			
			
	
	
	
	
	/*
	if (!$handled)
	{
		// OCLC
		if (isset($_GET['oclc']))
		{	
			$oclc = $_GET['oclc'];
			
			if (!$handled)
			{
				if (isset($_GET['volumes']))
				{
					display_decade_volumes('oclc', $oclc, $callback);
					$handled = true;
				}
			}
			
			if (!$handled)
			{
				if (isset($_GET['year']))
				{
					$year = $_GET['year'];
					display_articles_year('oclc', $oclc, $year, $callback);
					$handled = true;
				}	
			}
			
			
			if (!$handled)
			{
				display_oclc($oclc, $callback);
				$handled = true;			
			}
			
		}
		
	
		// ISSN	
		if (isset($_GET['issn']))
		{	
			$issn = $_GET['issn'];
			
			if (!$handled)
			{
				if (isset($_GET['volumes']))
				{
					display_decade_volumes('issn', $issn, $callback);
					$handled = true;
				}
			}
			
		
			if (!$handled)
			{
				if (isset($_GET['year']))
				{
					$year = $_GET['year'];
					display_articles_year('issn', $issn, $year, $callback);
					$handled = true;
				}	
			}
								
			
			if (!$handled)
			{
				if (isset($_GET['romeo']) )
				{
					display_romeo($issn, $callback);
					$handled = true;
				}			
			}
			
				
			
			if (!$handled)
			{
				display_issn($issn, $callback);
				$handled = true;			
			}
			
		}
		

	}
	*/
	




}



main();

?>
