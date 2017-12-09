<?php

error_reporting(E_ALL);

require_once (dirname(__FILE__) . '/api_utils.php');
require_once (dirname(__FILE__) . '/couchsimple.php');
require_once (dirname(__FILE__) . '/query.php');

//----------------------------------------------------------------------------------------
function default_display()
{
	echo "hi";
}

//----------------------------------------------------------------------------------------
// One record
function display_one ($id, $format= '', $callback = '')
{
	global $config;
	global $couch;

	$obj = null;
	
	// grab JSON from CouchDB
	$couch_id = $id;
	
	// fetch from CouchDB
	$obj = new stdclass;

	switch ($format)
	{
		case 'geojson':
			$resp = $couch->send("GET", "/" . $config['couchdb_options']['database'] . "/_design/text-mining-geo/_view/geojson?key=" . urlencode('"' . $couch_id . '"'));

			$obj->type = "FeatureCollection";
	  		$obj->features = array();

			$resp_obj = json_decode($resp);
			
			//print_r($resp_obj);
			
			if (isset($resp_obj->error))
			{
				$obj->status = 404;
			}
			else
			{
				foreach ($resp_obj->rows as $row)
				{
					$obj->features[] = $row->value;
				}
				$obj->status = 200;			
			}
			api_output($obj, 'json', $callback);
			break;
			
		case 'txt':
			$resp = $couch->send("GET", "/" . $config['couchdb_options']['database'] . "/" . urlencode($couch_id));
			$work = json_decode($resp);
			if (isset($work->error))
			{
				$obj->status = 404;
			}
			else
			{
				$obj->txt = '';
				
				if (isset($work->message->{'page-text'}))
				{
					$obj->txt = join("", $work->message->{'page-text'});
				}				
				$obj->status = 200;			
			}
			api_output($obj, 'txt', $callback);
			break;			
	
		case 'xml':
			$resp = $couch->send("GET", "/" . $config['couchdb_options']['database'] . "/_design/export/_view/jats-xml?key=" . urlencode('"' . $couch_id . '"'));
			$work = json_decode($resp);
			if (isset($work->error))
			{
				$obj->status = 404;
			}
			else
			{
				$obj->xml = '';
				if (isset($work->rows[0]))
				{
					$obj->xml = $work->rows[0]->value;
				}
				
				$obj->status = 200;			
			}
			api_output($obj, 'xml');
			break;
			
		default:
			$resp = $couch->send("GET", "/" . $config['couchdb_options']['database'] . "/" . urlencode($couch_id));
			$work = json_decode($resp);
			if (isset($work->error))
			{
				$obj->status = 404;
			}
			else
			{
				$obj = $work;
				$obj->status = 200;			
			}
			api_output($obj, 'json', $callback);
			break;
	}
	
}


//----------------------------------------------------------------------------------------
// Get versions (if any) of this record
function display_versions ($id, $callback = '')
{
	global $config;
	global $couch;
	
	$url = '_design/cluster/_view/versions?key=' . json_encode($id);				

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
			$obj->works[] = $id; // cluster_id work is itself a version
			foreach ($response_obj->rows as $row)
			{
				$obj->works[] = $row->value;
			}
		}
	}
	
	api_output($obj, 'json', $callback);
}

//----------------------------------------------------------------------------------------
// Full text search
function display_search ($q, $rows_per_page = 20, $bookmark = '', $callback = '')
{
	global $config;
	global $couch;
				
	if ($q == '')
	{
		$obj = new stdclass;
		$obj->rows = array();
		$obj->total_rows = 0;
		$obj->bookmark = '';	
		
		// Add status
		$obj->status = 404;
			
	}
	else
	{		
		$query = parse_query($q);	
	
		$parameters = array(
				'q'					=> $query['string'],
				'highlight_fields' 	=> '["default"]',
				'highlight_pre_tag' => '"<span class=\"highlight\">"',
				'highlight_post_tag'=> '"</span>"',
				'highlight_number'	=> 5,
				'counts'			=> '["author","container","year"]',
				'include_docs' 		=> 'true',
				'limit' 			=> $rows_per_page
			);
			
		if ($bookmark != '')
		{
			$parameters['bookmark'] = $bookmark;
		}
		
		$url = '_design/search/_search/metadata?' . http_build_query($parameters);
		
		$resp = $couch->send("GET", "/" . $config['couchdb_options']['database'] . "/" . $url);
		$obj = json_decode($resp);

		// delete large fields from results, such as OCR text and list of names extracted
		if (isset($obj->rows))
		{
			$n = count($obj->rows);
			for ($i = 0; $i < $n; $i++)
			{
				if (isset($obj->rows[$i]->doc->text))
				{
					unset($obj->rows[$i]->doc->text);
				}
				if (isset($obj->rows[$i]->doc->names))
				{
					unset($obj->rows[$i]->doc->names);
				}
				
			}
		}
		
		// Add status
		$obj->status = 200;
	}
	
	api_output($obj, $callback);
}




//----------------------------------------------------------------------------------------
function main()
{
	$callback = '';
	$handled = false;
	
	//print_r($_GET);
	
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
	
	// Submit job
	if (!$handled)
	{
		if (isset($_GET['id']))
		{	
			$id = $_GET['id'];
			
			$format = '';
			
			if (isset($_GET['format']))
			{
				$format = $_GET['format'];
			}
			
			if (isset($_GET['versions']))
			{
				display_versions($id, $callback);
				$handled = true;
			}
			
			/*
			if (isset($_GET['cites']))
			{
				display_cites($id, $callback);
				$handled = true;
			}
			*/
			
			if (!$handled)
			{
				display_one($id, $format, $callback);
				$handled = true;
			}
			
		}
	}
	
	if (!$handled)
	{
		if (isset($_GET['q']))
		{	
			$q = $_GET['q'];
			
			$rows = 20;
			if (isset($_GET['rows']))
			{
				$rows = $_GET['rows'];
			}			

			
			$bookmark = '';
			if (isset($_GET['bookmark']))
			{
				$bookmark = $_GET['bookmark'];
			}			
			
			display_search($q, $rows, $bookmark, $callback);
			$handled = true;
		}
			
	}
	

	
	if (!$handled)
	{
		default_display();
	}
	
		

}


main();

?>