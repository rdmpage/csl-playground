<?php

require_once (dirname(__FILE__) . '/lib.php');
require_once (dirname(__FILE__) . '/couchsimple.php');
require_once (dirname(__FILE__) . '/fingerprint.php');
require_once (dirname(__FILE__) . '/query.php');


//----------------------------------------------------------------------------------------
function create_openurl($work)
{
	// OpenURL
	$parameters = array();
	
	if (isset($work->message->title))
	{
		if (is_array($work->message->title))
		{
			$parameters['atitle'] = trim($work->message->title[0]);
		}
		else
		{
			$parameters['atitle'] = trim($work->message->title);
		}
	}

	if (isset($work->message->issued))
	{
		$parameters['year'] = $work->message->issued->{'date-parts'}[0][0];
	}

	if (isset($work->message->author))
	{
		foreach ($work->message->author as $author)
		{
			if (isset($author->literal))
			{
				$parameters['au'] = $author->literal;
			}
			else
			{
				$parts = array();
				if (isset($author->given))
				{
					$parts[] = $author->given;
				}
				if (isset($author->family))
				{
					$parts[] = $author->family;
				}
				$parameters['au'] = join(' ', $parts);
				
			}
		}
	}


	if (isset($work->message->{'container-title'}))
	{
		if (is_array($work->message->{'container-title'}))
		{
			$parameters['title'] = $work->message->{'container-title'}[0];
		}
		else
		{
			$parameters['title'] = $work->message->{'container-title'};
		}	
	}

	if (isset($work->message->volume))
	{
		$parameters['volume'] = $work->message->volume;
	}
	if (isset($work->message->issue))
	{
		$parameters['issue'] = $work->message->issue;
	}

	if (isset($work->message->page))
	{
		if (preg_match('/^(?<spage>.*)-(?<epage>.*)/', $work->message->page, $m))
		{
			$parameters['spage'] = $m['spage'];
			$parameters['epage'] = $m['epage'];
		}
		else
		{
			$parameters['pages'] = $work->message->page;
		}
	}				
		
	$openurl = http_build_query($parameters);
	
	
	return $openurl;
	
				

}

//----------------------------------------------------------------------------------------
function clean_string($s)
{
	$s = finger_print($s);
	
	$s = preg_replace('/\s+no\.?$/i', '', $s);

	$s = preg_replace('/\bfor\b/', '', $s);
	$s = preg_replace('/\band\b/', '', $s);
	$s = preg_replace('/\bof\b/', '', $s);
	$s = preg_replace('/\bthe\b/', '', $s);
	
	$s = preg_replace('/\bde\b/', '', $s);
	$s = preg_replace('/\bla\b/', '', $s);
	$s = preg_replace('/\bet\b/', '', $s);

	$s = preg_replace('/\baus\b/', '', $s);
	$s = preg_replace('/\bdem\b/', '', $s);
	$s = preg_replace('/\bder\b/', '', $s);
	$s = preg_replace('/\bfur\b/', '', $s);
	$s = preg_replace('/\bund\b/', '', $s);
	$s = preg_replace('/\bzur\b/', '', $s);
	
	// abbreviated stop words
	$s = preg_replace('/\s*\&\s*/', '', $s);

	// white space
	$s = preg_replace('/\s+/', '', $s);

	// punctuation
	$s = preg_replace('/\./', '', $s);
	$s = preg_replace('/\'/', '', $s);
	$s = preg_replace('/,/', '', $s);
	$s = preg_replace('/\(/', '', $s);
	$s = preg_replace('/\)/', '', $s);
	$s = preg_replace('/\-/', '', $s);
	$s = preg_replace('/’/', '', $s);
	
	return $s;
}

//----------------------------------------------------------------------------------------
// Display path titles / container/ year
function display_record_path_container ($work)
{
	// Breadcrumbs -----------------------------------------------------------------------
	echo '<ol class="breadcrumb">' . "\n";	
	echo '<li><a href="titles">Titles</a></li>' . "\n";	
	
	if (isset($work->message->{'container-title'}))
	{
		if (is_array($work->message->{'container-title'}))
		{
			$container = $work->message->{'container-title'}[0];
		}
		else
		{
			$container = $work->message->{'container-title'};
		}
		
		// clean
		$cleaned_container = clean_string($container);
		
		echo '<li>';
		echo '<a href="container/' . $cleaned_container . '">' . $container . '</a>';	
		echo '</li>';
		
		if (isset($work->message->issued)) {
			if (isset($work->message->issued->{'date-parts'}))
			{
				$year = $work->message->issued->{'date-parts'}[0][0];
				echo '<li>';
				echo '<a href="container/' . $cleaned_container . '/year/' . $year . '">' . $year . '</a>';	
				echo '</li>';
			}
		}
		
		echo '<li>';
		echo $work->_id;
		echo '</li>';
		
	}
	echo '</ol>';
}

//----------------------------------------------------------------------------------------
function display_record_path_issn ()
{

}

//----------------------------------------------------------------------------------------
function display_record($id, $full = false)
{
	global $config;
	
	$url = $config['web_server'] . $config['web_root'] . 'api.php?id=' . urlencode($id);
	$json = get($url);
	
	//echo $json;

	$work = json_decode($json);
	
	$title = 'Untitled';
	if (isset($work->message->title))
	{
		if (is_array($work->message->title))
		{
			$title = $work->message->title[0];
		}
		else
		{
			$title = $work->message->title;
		}
	}
	
	// need to have Google metadata tags at some point
	
	
	// idea 1: Convert to XML and display that
	
	$script = '';
	$script .= '<script>
				function show_versions(id) {
					$.getJSON("api.php?id=" + encodeURIComponent(id) + "&versions&callback=?",
						function(data){
						  if (data.works) {
							var html = "";
							html += "<ol>";
							for (var i in data.works) {
								html += "<li>"
								html += \'<a href="work/\' + data.works[i] + \'">\' + data.works[i] + \'</a></li>\';
								html += "</li>";
							}
							html += "</ol>";
							$("#versions").html(html);
						}
					});
				}
				
			</script>';		
	
	
	
	display_html_start($title, '', $script);
	display_navbar();
	
	display_record_path_container ($work);
	
	echo '<div class="container">
	  <div class="row">
      <div class="col-md-8">
        <div style="padding:10px;" class="row" id="content">';
        
    // Do we have full text XML?
    
    //$xml_type = 'summary';
    
    $xml = '';
    
    if ($full) 
    {
 		$xsl_filename = dirname(__FILE__) . '/xsl/' . 'scanned-pages.xsl';    
    }
    else
    {
	    $xsl_filename = dirname(__FILE__) . '/xsl/' . 'no-full-text.xsl';
	}
   
    
    if (isset($work->message->xml))
    {
    	$xml = $work->message->xml;
    	$xsl_filename = dirname(__FILE__) . '/xsl/' . 'full-text.xsl';
    }
    else
    {
		$url = $config['web_server'] . $config['web_root'] . 'api.php?id=' . urlencode($id) . '&format=xml';
		$xml = get($url);
	}
			
	if ($xml == '')
	{
		// handle error
	}
	else
	{
		// display
		//header("Content-type: text/xml");
		//echo $xml;
		
		// style sheet

		$xp = new XsltProcessor();
		$xsl = new DomDocument;
		$xsl->load($xsl_filename);
		$xp->importStylesheet($xsl);
		
		$xp->setParameter('', 'work', $id);
		
		$dom = new DOMDocument;
		$dom->loadXML($xml);
		$xpath = new DOMXPath($dom);
	
		$html = $xp->transformToXML($dom);
		
		echo $html;
		
		echo '<br style="clear:both;" />';
		
		if (!$full)
		{
			echo '<div id="map" style="width:100%; height:300px;border:1px solid rgb(192,192,192);">
			Map
        	</div>';
        }
				
		
	}        
        
    
    echo '    </div>
      </div>
      <div class="col-md-4">
        <div class="row affix" id="meta"> 
          <div>
            <h4>
              Files
            </h4>
            <div id="files" lass="btn-group" role="group">
            </div>
          </div>
          
          <div>
            <h4>
              Identifiers
            </h4>';
            
     if (isset($work->message->DOI))
     {
     	echo '<div id="doi">' . $work->message->DOI . '</div>';
     }
     if (isset($work->message->HANDLE))
     {
     	echo '<div id="handle">' . $work->message->HANDLE . '</div>';
     }
     if (isset($work->message->JSTOR))
     {
     	echo '<div id="jstor">' . $work->message->JSTOR . '</div>';
     }
     if (isset($work->message->PMID))
     {
     	echo '<div id="pmid">' . $work->message->PMID . '</div>';
     }
     if (isset($work->message->PMC))
     {
     	echo '<div id="pmc">' . $work->message->PMC . '</div>';
     }
            
            /*
            <div id="doi"></div>
            <div id="jstor"></div>
            <div id="pmid"></div>
            <div id="pmc"></div>
            <div id="handle"></div> 
            <div id="wikidata"></div> */
            
    echo '      </div>
          
          <!-- author identifiers, such as ORCID -->
          <div>
            <h4>
              Author identifiers
            </h4>
            <div id="orcid"></div>
          </div>
          
          <!-- other versions of this record -->
          <div>
            <h4>
              Versions
            </h4>
            <div id="versions"></div>        
          </div>
          
          
       </div>
      </div>
    </div>
  </div>';
      

	if (isset($work->cluster_id))
	{
		echo '<script>show_versions("' . $work->cluster_id . '");</script>';
	}

	if (!$full)
	{
		echo '<script>show_map("' . $id . '");</script>';
	}
	
	if (isset($work->message->DOI))
	{
		echo '<script>' . "\n";	
    	echo '// Wikidata, ORCID, etc.' . "\n";
      	echo 'doi_in_wikidata("' . $work->message->DOI . '");' . "\n";
      	echo 'doi_in_orcid("' . $work->message->DOI . '");' . "\n";
    	echo '</script>' . "\n";
    }
	
	display_html_end();	




/*	global $config;
	
	// API call
	$ok = false;
	
	$url = $config['web_server'] . $config['web_root'] . 'api.php?id=' . urlencode($id) . '&format=xml';
	$xml = get($url);
			
	if ($xml == '')
	{
		// handle error
	}
	else
	{
		// display
		//header("Content-type: text/xml");
		//echo $xml;
		
		// style sheet
		
		$xslt_file = dirname(__FILE__) . '/' . 'no-full-text.xsl';

		$xp = new XsltProcessor();
		$xsl = new DomDocument;
		$xsl->load($xslt_file);
		$xp->importStylesheet($xsl);
		
		$dom = new DOMDocument;
		$dom->loadXML($xml);
		$xpath = new DOMXPath($dom);
	
		$html = $xp->transformToXML($dom);
		
		echo $html;
		
		
		
	}
*/


}

//----------------------------------------------------------------------------------------
function display_html_start($title = '', $meta = '', $script = '')
{
	global $config;
	
	echo '<!DOCTYPE html>
<html lang="en">
<head>';

	echo '<meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <!-- <meta name="viewport" content="width=device-width, initial-scale=1">-->'
    
    . $meta . 
    
    '<!-- base -->
    <base href="' . $config['web_root'] . '" /><!--[if IE]></base><![endif]-->

    <!-- Boostrap -->
    <!-- Latest compiled and minified CSS -->
	<link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.5/css/bootstrap.min.css">
	<script src="https://cdnjs.cloudflare.com/ajax/libs/jquery/2.1.4/jquery.min.js"></script>

	<!-- Latest compiled and minified JavaScript -->
	<script src="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.5/js/bootstrap.min.js"></script>
	
    <!-- leaflet -->
	<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/leaflet/0.7.3/leaflet.css" />
	<script src="https://cdnjs.cloudflare.com/ajax/libs/leaflet/0.7.3/leaflet.js" type="text/javascript"></script>

    <script src="js/map.js"></script>
    <script src="js/identifiers.js"></script>
	
	'	
	. $script . '
	<title>' . $title . '</title>
	
	<style>
		.highlight { 
			font-weight: bold; 
			/* background-color:orange; */
		}
		
		body {
 			padding-top:70px;
 			padding-left:20px;
 			padding-right:20px;
 			padding-bottom:20px;		
		}
		
		h1 {
		 line-height:1.33;
		 font-size:2em;
		}
		
		p {
			font-family: Georgia, serif;
			line-height:1.5;
			font-size:1.3em;
		}
		
		.thumbnail {
 			box-shadow:2px 2px 2px #ccc;
 			width:64px;
 			border:1px solid rgb(242,242,242);				
		}
		
		.mydivicon{
			width: 12px
			height: 12px;
			border-radius: 10px;
			/* background: #408000; */
			background: #ff0000; 
			border: 1px solid #fff;
			opacity: 0.6
		}	  		
		
		
	</style>	
	</head>
<body>';

}

//----------------------------------------------------------------------------------------
function display_html_end()
{
	global $config;

	echo '</body>';
	echo '</html>';
}

//----------------------------------------------------------------------------------------
// Displaynavigation bar
function display_navbar($q = "")
{

	global $config;

echo '<nav class="navbar navbar-default navbar-fixed-top">
  <div class="container-fluid">
    <div class="navbar-header">
      <a class="navbar-brand" href=".">
        <!-- <img alt="Brand" src="static/biostor-shadow32x32.png" height="20"> -->
        Home
      </a>      
     <form class="navbar-form navbar-left" role="search" action="' . $config['web_root'] . '">
       <div class="form-group">
         <input type="text" style="width:300px;" class="form-control" placeholder="Search" name="q" value="' . $q . '">
       </div>
      </form>     
      <ul class="nav navbar-nav">
        <li><a href="titles">Titles A-Z</a></li>
      </ul>
    </div>
  </div>
</nav>';
}

//----------------------------------------------------------------------------------------
// Display all the containers in the database (essentially all the journals)
// Split by first letter of journal name
function display_containers($letter= 'A')
{
	global $config;
	global $couch;
	
	display_html_start('Titles');
	display_navbar();
	
	echo '<div  class="container-fluid">' . "\n";
	
	echo '<h1>Titles starting with the letter "' . $letter . '"</h1>';

	// all container titles
	$url = $config['web_server'] . $config['web_root'] . 'api_container.php?letter=' . $letter;
	
	$json = get($url);
	
	if ($json != '')
	{
		$obj = json_decode($json);
					
		echo '<nav>' . "\n";
  		echo '<ul class="pagination">' . "\n";
		
		$all_letters = range("A", "Z");
		foreach ($all_letters as $starting_letter)
		{
			echo '<li';
			if ($letter == $starting_letter)
			{
				echo ' class="active"';
			}
//			echo '><a href="?titles&letter=' . $starting_letter . '">' .  $starting_letter . '</a>';
			echo '><a href="titles/letter/' . $starting_letter . '">' .  $starting_letter . '</a>';
			echo '</li>' . "\n";
		}
		echo '</ul>';
		echo '</nav>';
		
		// containers
		
		echo '<div>';
		echo '<ul>';
		foreach ($obj->results as $shortname => $container)
		{
			echo '<li>';
			
			echo '<a href="container/' . $shortname . '">' . $container->name . '</a>';
			
			echo ' <span class="badge">' .  $container->count . '</span>';
			
			/*
			if (isset($title->issn))
			{
				//echo '<a href="issn/' . $title->issn . '">';
				echo '<a href="issn/' . $title->issn . '">';
			}
			if (isset($title->oclc))
			{
				//echo '<a href="?oclc=' . $title->oclc . '">';
				echo '<a href="oclc/' . $title->oclc . '">';
			}
			
			echo $title->title;
			 
			if (isset($title->issn) || isset($title->oclc))
			{
				echo '</a>';
			}
			*/
			 
			echo '</li>';
		}
		echo '</ul>';
		echo '</div>';
	}
	
	echo '</div>' . "\n";
	
	display_html_end();
}

//----------------------------------------------------------------------------------------
function display_container_decade_volumes($title, $year='')
{
	global $config;

	$url = $config['web_server'] . $config['web_root'] . 'api_container.php?title=' . $title;
		
	$json = get($url);
		
	if ($json != '')
	{
		$obj = json_decode($json);
			
		echo '<div class="panel-group" id="accordion" role="tablist" aria-multiselectable="true">' . "\n";

		foreach ($obj->decades as $k => $decade)
		{
			$current_decade = false;
			if ($year != '')
			{
				$current_decade = (floor($year / 10) == $k);
			}
			
			echo '  <div class="panel panel-default">' . "\n";

			// heading
			
    		echo '<div class="panel-heading" role="tab" id="heading' . $k . '">' . "\n";
      		echo '<h4 class="panel-title">' . "\n";
        	echo '<a role="button" data-toggle="collapse" data-parent="#accordion" href="#collapse' . $k . '"';
        	
			if ($current_decade)
			{
				echo 'aria-expanded="true"';
			}
			else
			{
				echo 'aria-expanded="false"';
			}
			       	
        	echo ' aria-controls="collapse' . $k .'">' . "\n";
          	echo $k . '0\'s';
        	echo '</a>' . "\n";
      		echo '</h4>' . "\n";
    		echo '</div>' . "\n";
						
			echo '<div id="collapse' . $k . '" class="panel-collapse collapse';
			if ($current_decade)
			{
				echo ' in';
			}
			echo '" role="tabpanel" aria-labelledby="id' . $k . '">' . "\n";
      		echo '<div class="panel-body">' . "\n";

			echo '<ul>';
			foreach ($decade as $k => $v)
			{
				$current_year = false;
				if ($year != '')
				{
					$current_year = ($year == $k);
				}
				echo '<li>';
				if ($current_year)
				{
					echo '<b>';
				}

				echo '<a href="container/' . $title . '/year/' . $k . '">' . $k . '</a>';
								
				if ($current_year)
				{
					echo '</b>';
				}
				
				echo ' <span class="badge">' .$v . '</span>';
				echo '</li>';
			}
			echo '</ul>';

			echo '</div>';	
			echo '</div>';	
				
			echo '</div>';
			
		}
		
		echo '</div>';
	}
}

//----------------------------------------------------------------------------------------
// Display container, list counts by decade
function display_one_container($title, $year = '')
{
	global $config;
	
	display_html_start();
	display_navbar();

	// Breadcrumbs -----------------------------------------------------------------------
	echo '<ol class="breadcrumb">' . "\n";	
	echo '<li><a href="titles">All titles</a></li>' . "\n";		
	echo '<li class="active">' .$title . '</li>' . "\n";
	echo '</ol>';
	
	echo '<div class="container-fluid">' . "\n";
	echo '  <div class="row">' . "\n";
	echo '		<div class="col-md-2">' . "\n";
	
	display_container_decade_volumes($title, $year);

	echo '      </div>' . "\n";
			
	echo '      <div class="col-md-10">' . "\n";
	
	if ($year == '')
	{
		// Display info about this container
		echo        '<div id="journal_info"></div>';
	}
	else
	{
		// List works for this year
		display_container_works_year($title, $year);		
	}
	
	
	echo '      </div>' . "\n";
	echo '   </div>' . "\n";
	echo '</div>' . "\n";
	
	/*
	echo '<script>' . "\n";
	echo '   wikidata("' . $identifier . '");' . "\n";
	echo '</script>' . "\n";
	*/
	
	display_html_end();
}

//----------------------------------------------------------------------------------------
function display_container_works_year($title, $year)
{
	global $config;

	$url = $config['web_server'] . $config['web_root'] . 'api_container.php?title=' . $title . '&year=' . $year;
		
	$json = get($url);
	
	//echo $json;
		
	if ($json != '')
	{
		$obj = json_decode($json);
			
		foreach ($obj->works as $work)
		{
		  echo '<div class="media">
		  <div class="media-left media-top">';
			echo '<a href="work/' . $work->_id . '">';
			if (isset($work->message->thumbnail))
			{
				echo '<img class="thumbnail" src="' . $work->message->thumbnail . '">';	
			}
			else
			{		
				echo '<img class="thumbnail" src="http://via.placeholder.com/64x80">';	
			}
		  echo '  </a>
		  </div>
		  <div class="media-body">
			<h4 class="media-heading">';
		
			echo '<a href="work/' . $work->_id . '">';	

			$have_title = false;
	
			if (isset($work->message->multi))
			{
				foreach ($work->message->multi->_key as $k => $v)
				{
					switch ($k)
					{
						case 'title':
							$have_title = true;
							foreach ($v as $language => $string)
							{
								echo $string . '<br />';
							}
						break;
				
						default:
							break;
					}		
				}
			}
	
			if (!$have_title)
			{
				$title = '';
				if (isset($work->message->title))
				{
					if (is_array($work->message->title))
					{
						$title = $work->message->title[0];
					}
					else
					{
						$title = $work->message->title;
					}
				}
				echo $title . '<br />';
			}	
	
			echo '</a>';
			echo '</h4>';
	
		
			echo '<div>';
			if (isset($work->message->unstructured))
			{
				//echo '<span style="color:green;">' . $work->message->unstructured . '</span>';
				echo '<span>' . $work->message->unstructured . '</span>';
			}
			echo '</div>';
			
	
			if (isset($work->message->DOI)) 
			{
				echo '<div>' . '<b style="color:blue;">' . $work->message->DOI . '</b>' . '</div>';
			}
			if (isset($work->message->URL)) 
			{
				echo '<div>' . '<b style="color:blue;">' . $work->message->URL . '</b>' . '</div>';
			}
	
			echo '</div>';
			echo '</div>';
		}	


	}
}

//----------------------------------------------------------------------------------------
function display_search($q, $bookmark = '')
{
	global $config;
	$rows_per_page = 20;	
	
	// Interpret query
	$query = parse_query($q);
		
	// Search database
	$url = $config['web_server'] . $config['web_root'] . 'api.php?q=' . urlencode($q);
	if ($bookmark != '')
	{
		$url .= '&bookmark=' . $bookmark;
	}
	
	//echo $url;
	
	$json = get($url);
	$obj = json_decode($json);
	
	
	if (0)
	{
		echo '<pre>';
		print_r($obj);
		echo '</pre>';
	}	
	
	display_html_start();
	display_navbar(htmlentities($q));	
	
	// echo '<h4>Search results for "' . htmlentities($q) . '"</h4>';
	echo '<div class="container-fluid">' . "\n";
	echo '  <div class="row">' . "\n";
	echo '	  <div class="col-md-8">' . "\n";
	
	echo '<h5>' . $obj->total_rows . ' hit(s)' . '</h3>';
		
	// Results
	foreach ($obj->rows as $row)
	{
	  echo '<div class="media">
	  <div class="media-left media-top">';
		echo '<a href="work/' . $row->id . '">';
		
		if (isset($row->doc->message->thumbnail))
		{
			echo '<img class="thumbnail" src="' . $row->doc->message->thumbnail . '">';	
		}
		else
		{		
			echo '<img class="thumbnail" src="http://via.placeholder.com/64x80">';	
		}
	  echo '  </a>
	  </div>
	  <div class="media-body">
		<h4 class="media-heading">';
		
		echo '<a href="work/' . $row->id . '">';	

		$have_title = false;
	
		if (isset($row->doc->message->multi))
		{
			foreach ($row->doc->message->multi->_key as $k => $v)
			{
				switch ($k)
				{
					case 'title':
						$have_title = true;
						foreach ($v as $language => $string)
						{
							echo $string . '<br />';
						}
					break;
				
					default:
						break;
				}		
			}
		}
	
		if (!$have_title)
		{
			$title = '';
			if (isset($row->doc->message->title))
			{
				if (is_array($row->doc->message->title))
				{
					$title = $row->doc->message->title[0];
				}
				else
				{
					$title = $row->doc->message->title;
				}
			}
			echo $title . '<br />';
		}	
	
		echo '</a>';
		echo '</h4>';
	
		
		echo '<div>';
		foreach ($row->highlights->default as $highlight)
		{
			echo '<div>';
			echo '<span style="color:green;">' . $highlight . '</span>';
			//echo '<span>' . $highlight . '</span>';
			echo '</div>';
		}
		echo '</div>';
	
		if (isset($row->doc->message->DOI)) 
		{
			echo '<div>' . '<b style="color:blue;">' . $row->doc->message->DOI . '</b>' . '</div>';
		}
		if (isset($row->doc->message->URL)) 
		{
			echo '<div>' . '<b style="color:blue;">' . $row->doc->message->URL . '</b>' . '</div>';
		}
		
		$openurl = create_openurl($row->doc);
		echo '<div>' . '<b style="color:blue;"><a href="http://direct.biostor.org/openurl?' . $openurl . '">OpenURL</a></b>' . '</div>';
		
		
		
		
	
		echo '</div>';
		echo '</div>';
	}	
	
	if ($obj->total_rows > $rows_per_page)
	{
		echo '<nav>';
		echo '  <ul class="pager">';
		echo '    <li class="next">';
		echo '      <a class="btn" href="search/' . urlencode($q) . '/bookmark/' . $obj->bookmark . '">More results »</a>';
		echo '   </li>';
		echo '  </ul>';
		echo '</nav>';
	}

	echo '   </div>';
	
	// Put further info about results here...
	echo '	 <div class="col-md-4">' . "\n";	
	if ($obj->counts)
	{		
		if (isset($obj->counts->year))
		{
			echo 	'<h4>Year</h4>';
			echo '<ul class="nav nav-list">';
			
			if (is_object($obj->counts->year))
			{			
				foreach ($obj->counts->year as $year => $count)
				{
					echo '<li>';
				
					if (isset($query['year']) && ($query['year'] == $year))
					{
						//echo '<a href="search/' . urlencode($query['q']) . '">';
						echo '<a style="padding:2px;" href="?q=' . urlencode($query['q']) . '">';
						echo '<i class="glyphicon glyphicon-check"></i>';				
					}
					else
					{
						//echo '<a style="padding:2px;" href="search/' . urlencode($query['q'] . ' AND year:"' . $year . '"') . '">';
						echo '<a style="padding:2px;" href="?q=' . urlencode($query['q'] . ' AND year:"' . $year . '"') . '">';
						echo '<i class="glyphicon glyphicon-unchecked"></i>';
					}
				
					echo  $year . ' <span class="badge">' . $count . '</span>';
					echo '</a>';
					echo '</li>';
				}
				echo '</ul>';
			}
		}

		if (isset($obj->counts->author))
		{
			echo 	'<h4>Authors</h4>';
			echo '<ul class="nav nav-list">';
			
			if (is_object($obj->counts->author))
			{						
				foreach ($obj->counts->author as $author => $count)
				{
					echo '<li>';
				
					if (isset($query['author']) && ($query['author'] == $author))
					{
						//echo '<a href="search/' . urlencode($query['q']) . '">';
						echo '<a style="padding:2px;" href="?q=' . urlencode($query['q']) . '">';
						echo '<i class="glyphicon glyphicon-check"></i>';				
					}
					else
					{
						//echo '<a style="padding:2px;" href="search/' . urlencode($query['q'] . ' AND author:"' . $author . '"') . '">';
						echo '<a style="padding:2px;" href="?q=' . urlencode($query['q'] . ' AND author:"' . $author . '"') . '">';
						echo '<i class="glyphicon glyphicon-unchecked"></i>';
					}				
					echo  $author . ' <span class="badge">' . $count . '</span>';
					echo '</a>';
					echo '</li>';
				}
			}			
			echo '</ul>';			
		}
		
		/*
		if (isset($obj->counts->container))
		{
			echo 	'<h4>Container</h4>';
			echo '<ul class="nav nav-list">';
			
			if (is_object($obj->counts->container))
			{						
				foreach ($obj->counts->container as $container => $count)
				{
					echo '<li>';
				
					if (isset($query['container']) && ($query['container'] == $author))
					{
						//echo '<a href="search/' . urlencode($query['q']) . '">';
						echo '<a style="padding:2px;" href="?q=' . urlencode($query['q']) . '">';
						echo '<i class="glyphicon glyphicon-check"></i>';				
					}
					else
					{
						//echo '<a style="padding:2px;" href="search/' . urlencode($query['q'] . ' AND container:"' . $container . '"') . '">';
						echo '<a style="padding:2px;" href="?q=' . urlencode($query['q'] . ' AND container:"' . $container . '"') . '">';
						echo '<i class="glyphicon glyphicon-unchecked"></i>';
					}
					echo  $container . ' <span class="badge">' . $count . '</span>';
					echo '</a>';
					echo '</li>';
				}
			}			
			echo '</ul>';
		}
		*/
	}

	echo '   </div>';	
	echo '  </div>';
	echo '</div>';

	display_html_end();	
}

//----------------------------------------------------------------------------------------
function default_display($error_msg = '')
{
	global $config;
	
	display_html_start('CSL Playground');
	display_navbar();
	
	echo  '<div class="container-fluid">';
	
	if ($error_msg != '')
	{
		echo '<div class="alert alert-danger" role="alert"><strong>Error!</strong> ' . $error_msg . '</div>';
	}
	
	echo '<div class="alert alert-warning" role="alert"><strong>Heads up!</strong> This site is very experimental.</div>';
	
	echo '<div class="jumbotron" style="text-align:center">
        <h1>CSL Playground</h1>
        <p>All the articles, yes, all of them...</p>
      </div>';
      
    echo '<div class="row">';     
    echo '</div>';
      	
    echo '</div>';

	display_html_end();
	
}


//----------------------------------------------------------------------------------------
function main()
{
	$query = '';
	$bookmark = '';
		
	// If no query parameters 
	if (count($_GET) == 0)
	{
		default_display();
		exit(0);
	}
		
	// Error message
	if (isset($_GET['error']))
	{	
		$error_msg = $_GET['error'];
		
		default_display($error_msg);
		exit(0);			
	}
	
	// Show a single record
	if (isset($_GET['id']))
	{	
		$id = $_GET['id'];
		
		$full = false;
		if (isset($_GET['full']))
		{
			$full = true;
		}
				
		display_record($id, $full);
		exit(0);
	}
		
	// Show containers
	if (isset($_GET['titles']))
	{	
		$letter = 'A';
		if (isset($_GET['letter']))
		{
			$letter = $_GET['letter'];
			// sanity check
			if (!in_array($letter, range('A', 'Z')))
			{
				$letter = 'A';
			}			
		}
				
		display_containers($letter);
		exit(0);
	}
	
	// Show one container
	if (isset($_GET['container']))
	{	
		$title = $_GET['container'];
		
		if (isset($_GET['year']))
		{
			$year = $_GET['year'];
			
			display_one_container($title, $year);
			exit(0);
		}
		
		display_one_container($title);
		exit(0);
		
	}
		
	// Show search (text, author)
	if (isset($_GET['q']))
	{	
		$query = $_GET['q'];
		$bookmark = '';
		if (isset($_GET['bookmark']))
		{
			$bookmark = $_GET['bookmark'];
		}
		display_search($query, $bookmark);
		exit(0);
	}	
	
}


main();

?>