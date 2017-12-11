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
function opengraph_tags ($work)
{
	global $config;
	
	$og = '';
	
	$og .= '<meta name="og:url" content="' . $config['web_server'] . $config['web_root'] . '/work/' . $work->_id . '" />' . "\n";
	
	if (isset($work->message->title))
	{
		//$og .= '<meta name="og:title" content="' . htmlentities($work->message->title, ENT_COMPAT | ENT_HTML5, 'UTF-8') . '" />' . "\n";
		$og .= '<meta name="og:title" content="' . addcslashes($work->message->title, "'") . '" />' . "\n";
	}
	if (isset($work->message->abstract))
	{
		//$og .= '<meta name="og:description" content="' . htmlentities($work->message->abstract, ENT_COMPAT | ENT_HTML5, 'UTF-8') . '" />' . "\n";
		$og .= '<meta name="og:description" content="' . addcslashes($work->message->abstract, "'") . '" />' . "\n";
	}
	
	if (isset($work->message->{'page-images'}))
	{
		$count = 0;
		foreach ($work->message->{'page-images'} as $k => $v)
		{
			$og .= '<meta name="og:image" content="' . $v . '" />' . "\n";
		
			$count++;
			if ($count > 0)
			{
				break;
			}
		}		
	}
	
	return $og;
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
	
	$meta = opengraph_tags($work);
	
	display_html_start($title, $meta, $script);
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
			echo '<div id="map" style="width:100%;height:300px;">
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
              Formats
            </h4>
            <div id="files" lass="btn-group" role="group">';
            
    echo '<a  href="work/' . $id . '.xml"><img src="data:image/svg+xml;utf8;base64,PD94bWwgdmVyc2lvbj0iMS4wIiBlbmNvZGluZz0iaXNvLTg4NTktMSI/Pgo8IS0tIEdlbmVyYXRvcjogQWRvYmUgSWxsdXN0cmF0b3IgMTguMC4wLCBTVkcgRXhwb3J0IFBsdWctSW4gLiBTVkcgVmVyc2lvbjogNi4wMCBCdWlsZCAwKSAgLS0+CjwhRE9DVFlQRSBzdmcgUFVCTElDICItLy9XM0MvL0RURCBTVkcgMS4xLy9FTiIgImh0dHA6Ly93d3cudzMub3JnL0dyYXBoaWNzL1NWRy8xLjEvRFREL3N2ZzExLmR0ZCI+CjxzdmcgeG1sbnM9Imh0dHA6Ly93d3cudzMub3JnLzIwMDAvc3ZnIiB4bWxuczp4bGluaz0iaHR0cDovL3d3dy53My5vcmcvMTk5OS94bGluayIgdmVyc2lvbj0iMS4xIiBpZD0iQ2FwYV8xIiB4PSIwcHgiIHk9IjBweCIgdmlld0JveD0iMCAwIDU4IDU4IiBzdHlsZT0iZW5hYmxlLWJhY2tncm91bmQ6bmV3IDAgMCA1OCA1ODsiIHhtbDpzcGFjZT0icHJlc2VydmUiIHdpZHRoPSIzMnB4IiBoZWlnaHQ9IjMycHgiPgo8Zz4KCTxwYXRoIGQ9Ik01MC45NDksMTIuMTg3bC0xLjM2MS0xLjM2MWwtOS41MDQtOS41MDVjLTAuMDAxLTAuMDAxLTAuMDAxLTAuMDAxLTAuMDAyLTAuMDAxbC0wLjc3LTAuNzcxICAgQzM4Ljk1NywwLjE5NSwzOC40ODYsMCwzNy45ODUsMEg4Ljk2M0M3Ljc3NiwwLDYuNSwwLjkxNiw2LjUsMi45MjZWMzl2MTYuNTM3VjU2YzAsMC44MzcsMC44NDEsMS42NTIsMS44MzYsMS45MDkgICBjMC4wNTEsMC4wMTQsMC4xLDAuMDMzLDAuMTUyLDAuMDQzQzguNjQ0LDU3Ljk4Myw4LjgwMyw1OCw4Ljk2Myw1OGg0MC4wNzRjMC4xNiwwLDAuMzE5LTAuMDE3LDAuNDc1LTAuMDQ4ICAgYzAuMDUyLTAuMDEsMC4xMDEtMC4wMjksMC4xNTItMC4wNDNDNTAuNjU5LDU3LjY1Miw1MS41LDU2LjgzNyw1MS41LDU2di0wLjQ2M1YzOVYxMy45NzhDNTEuNSwxMy4yMTEsNTEuNDA3LDEyLjY0NCw1MC45NDksMTIuMTg3ICAgeiBNMzkuNSwzLjU2NUw0Ny45MzUsMTJIMzkuNVYzLjU2NXogTTguOTYzLDU2Yy0wLjA3MSwwLTAuMTM1LTAuMDI1LTAuMTk4LTAuMDQ5QzguNjEsNTUuODc3LDguNSw1NS43MjEsOC41LDU1LjUzN1Y0MWg0MXYxNC41MzcgICBjMCwwLjE4NC0wLjExLDAuMzQtMC4yNjUsMC40MTRDNDkuMTcyLDU1Ljk3NSw0OS4xMDgsNTYsNDkuMDM3LDU2SDguOTYzeiBNOC41LDM5VjIuOTI2QzguNSwyLjcwOSw4LjUzMywyLDguOTYzLDJoMjguNTk1ICAgQzM3LjUyNSwyLjEyNiwzNy41LDIuMjU2LDM3LjUsMi4zOTFWMTRoMTEuNjA4YzAuMTM1LDAsMC4yNjUtMC4wMjUsMC4zOTEtMC4wNThjMCwwLjAxNSwwLjAwMSwwLjAyMSwwLjAwMSwwLjAzNlYzOUg4LjV6IiBmaWxsPSIjMDAwMDAwIi8+Cgk8cG9seWdvbiBwb2ludHM9IjIxLjIyNyw0My45MjQgMTkuMjk5LDQ4LjAyNSAxOS4xNjIsNDguMDI1IDE3LjM4NSw0My45MjQgMTUuNTEyLDQzLjkyNCAxOC4yMzIsNDkuMTA1IDE1LjY3Niw1NCAxNy41NzYsNTQgICAgMTkuMjk5LDUwLjE5OSAxOS40MzYsNTAuMTk5IDIxLjAzNSw1NCAyMi45MzYsNTQgMjAuMzc5LDQ5LjEwNSAyMy4xLDQzLjkyNCAgIiBmaWxsPSIjMDAwMDAwIi8+Cgk8cG9seWdvbiBwb2ludHM9IjMwLjAxOCw1MC44MTQgMjcuMDIzLDQzLjkyNCAyNS4zNTUsNDMuOTI0IDI1LjM1NSw1NCAyNy4wMjMsNTQgMjcuMDIzLDQ3LjA2OCAyOS4yOTMsNTIuNjc0IDMwLjc0Miw1Mi42NzQgICAgMzIuOTk4LDQ3LjA2OCAzMi45OTgsNTQgMzQuNjY2LDU0IDM0LjY2Niw0My45MjQgMzIuOTk4LDQzLjkyNCAgIiBmaWxsPSIjMDAwMDAwIi8+Cgk8cG9seWdvbiBwb2ludHM9IjM4Ljg2Myw0My45MjQgMzcuMTk1LDQzLjkyNCAzNy4xOTUsNTQgNDMuNDk4LDU0IDQzLjQ5OCw1Mi43NTYgMzguODYzLDUyLjc1NiAgIiBmaWxsPSIjMDAwMDAwIi8+Cgk8cGF0aCBkPSJNMjMuMjA3LDE3LjI5M2MtMC4zOTEtMC4zOTEtMS4wMjMtMC4zOTEtMS40MTQsMGwtNiw2Yy0wLjM5MSwwLjM5MS0wLjM5MSwxLjAyMywwLDEuNDE0bDYsNiAgIEMyMS45ODgsMzAuOTAyLDIyLjI0NCwzMSwyMi41LDMxczAuNTEyLTAuMDk4LDAuNzA3LTAuMjkzYzAuMzkxLTAuMzkxLDAuMzkxLTEuMDIzLDAtMS40MTRMMTcuOTE0LDI0bDUuMjkzLTUuMjkzICAgQzIzLjU5OCwxOC4zMTYsMjMuNTk4LDE3LjY4NCwyMy4yMDcsMTcuMjkzeiIgZmlsbD0iIzAwMDAwMCIvPgoJPHBhdGggZD0iTTM1LjIwNywxNy4yOTNjLTAuMzkxLTAuMzkxLTEuMDIzLTAuMzkxLTEuNDE0LDBzLTAuMzkxLDEuMDIzLDAsMS40MTRMMzkuMDg2LDI0bC01LjI5Myw1LjI5MyAgIGMtMC4zOTEsMC4zOTEtMC4zOTEsMS4wMjMsMCwxLjQxNEMzMy45ODgsMzAuOTAyLDM0LjI0NCwzMSwzNC41LDMxczAuNTEyLTAuMDk4LDAuNzA3LTAuMjkzbDYtNmMwLjM5MS0wLjM5MSwwLjM5MS0xLjAyMywwLTEuNDE0ICAgTDM1LjIwNywxNy4yOTN6IiBmaWxsPSIjMDAwMDAwIi8+Cgk8cGF0aCBkPSJNMzEuODMzLDE0LjA1N2MtMC41MjMtMC4xODUtMS4wOTIsMC4wODktMS4yNzYsMC42MWwtNiwxN2MtMC4xODQsMC41MjEsMC4wOSwxLjA5MiwwLjYxLDEuMjc2ICAgQzI1LjI3NywzMi45ODIsMjUuMzksMzMsMjUuNSwzM2MwLjQxMiwwLDAuNzk4LTAuMjU3LDAuOTQzLTAuNjY3bDYtMTdDMzIuNjI3LDE0LjgxMiwzMi4zNTQsMTQuMjQxLDMxLjgzMywxNC4wNTd6IiBmaWxsPSIjMDAwMDAwIi8+CjwvZz4KPGc+CjwvZz4KPGc+CjwvZz4KPGc+CjwvZz4KPGc+CjwvZz4KPGc+CjwvZz4KPGc+CjwvZz4KPGc+CjwvZz4KPGc+CjwvZz4KPGc+CjwvZz4KPGc+CjwvZz4KPGc+CjwvZz4KPGc+CjwvZz4KPGc+CjwvZz4KPGc+CjwvZz4KPGc+CjwvZz4KPC9zdmc+Cg==" /></a>';
    echo '<a  href="work/' . $id . '.json"><img src="data:image/svg+xml;utf8;base64,PD94bWwgdmVyc2lvbj0iMS4wIiBlbmNvZGluZz0iaXNvLTg4NTktMSI/Pgo8IS0tIEdlbmVyYXRvcjogQWRvYmUgSWxsdXN0cmF0b3IgMTguMC4wLCBTVkcgRXhwb3J0IFBsdWctSW4gLiBTVkcgVmVyc2lvbjogNi4wMCBCdWlsZCAwKSAgLS0+CjwhRE9DVFlQRSBzdmcgUFVCTElDICItLy9XM0MvL0RURCBTVkcgMS4xLy9FTiIgImh0dHA6Ly93d3cudzMub3JnL0dyYXBoaWNzL1NWRy8xLjEvRFREL3N2ZzExLmR0ZCI+CjxzdmcgeG1sbnM9Imh0dHA6Ly93d3cudzMub3JnLzIwMDAvc3ZnIiB4bWxuczp4bGluaz0iaHR0cDovL3d3dy53My5vcmcvMTk5OS94bGluayIgdmVyc2lvbj0iMS4xIiBpZD0iQ2FwYV8xIiB4PSIwcHgiIHk9IjBweCIgdmlld0JveD0iMCAwIDU4IDU4IiBzdHlsZT0iZW5hYmxlLWJhY2tncm91bmQ6bmV3IDAgMCA1OCA1ODsiIHhtbDpzcGFjZT0icHJlc2VydmUiIHdpZHRoPSIzMnB4IiBoZWlnaHQ9IjMycHgiPgo8Zz4KCTxwYXRoIGQ9Ik01MC45NDksMTIuMTg3bC0xLjM2MS0xLjM2MWwtOS41MDQtOS41MDVjLTAuMDAxLTAuMDAxLTAuMDAxLTAuMDAxLTAuMDAyLTAuMDAxbC0wLjc3LTAuNzcxICAgQzM4Ljk1NywwLjE5NSwzOC40ODYsMCwzNy45ODUsMEg4Ljk2M0M3Ljc3NiwwLDYuNSwwLjkxNiw2LjUsMi45MjZWMzl2MTYuNTM3VjU2YzAsMC44MzcsMC44NDEsMS42NTIsMS44MzYsMS45MDkgICBjMC4wNTEsMC4wMTQsMC4xLDAuMDMzLDAuMTUyLDAuMDQzQzguNjQ0LDU3Ljk4Myw4LjgwMyw1OCw4Ljk2Myw1OGg0MC4wNzRjMC4xNiwwLDAuMzE5LTAuMDE3LDAuNDc1LTAuMDQ4ICAgYzAuMDUyLTAuMDEsMC4xMDEtMC4wMjksMC4xNTItMC4wNDNDNTAuNjU5LDU3LjY1Miw1MS41LDU2LjgzNyw1MS41LDU2di0wLjQ2M1YzOVYxMy45NzhDNTEuNSwxMy4yMTEsNTEuNDA3LDEyLjY0NCw1MC45NDksMTIuMTg3ICAgeiBNMzkuNSwzLjU2NUw0Ny45MzUsMTJIMzkuNVYzLjU2NXogTTguOTYzLDU2Yy0wLjA3MSwwLTAuMTM1LTAuMDI1LTAuMTk4LTAuMDQ5QzguNjEsNTUuODc3LDguNSw1NS43MjEsOC41LDU1LjUzN1Y0MWg0MXYxNC41MzcgICBjMCwwLjE4NC0wLjExLDAuMzQtMC4yNjUsMC40MTRDNDkuMTcyLDU1Ljk3NSw0OS4xMDgsNTYsNDkuMDM3LDU2SDguOTYzeiBNOC41LDM5VjIuOTI2QzguNSwyLjcwOSw4LjUzMywyLDguOTYzLDJoMjguNTk1ICAgQzM3LjUyNSwyLjEyNiwzNy41LDIuMjU2LDM3LjUsMi4zOTFWMTMuNzhjLTAuNTMyLTAuNDgtMS4yMjktMC43OC0yLTAuNzhjLTAuNTUzLDAtMSwwLjQ0OC0xLDFzMC40NDcsMSwxLDFjMC41NTIsMCwxLDAuNDQ5LDEsMXY0ICAgYzAsMS4yLDAuNTQyLDIuMjY2LDEuMzgyLDNjLTAuODQsMC43MzQtMS4zODIsMS44LTEuMzgyLDN2NGMwLDAuNTUxLTAuNDQ4LDEtMSwxYy0wLjU1MywwLTEsMC40NDgtMSwxczAuNDQ3LDEsMSwxICAgYzEuNjU0LDAsMy0xLjM0NiwzLTN2LTRjMC0xLjEwMywwLjg5Ny0yLDItMmMwLjU1MywwLDEtMC40NDgsMS0xcy0wLjQ0Ny0xLTEtMWMtMS4xMDMsMC0yLTAuODk3LTItMnYtNCAgIGMwLTAuNzcxLTAuMzAxLTEuNDY4LTAuNzgtMmgxMS4zODljMC4xMzUsMCwwLjI2NS0wLjAyNSwwLjM5MS0wLjA1OGMwLDAuMDE1LDAuMDAxLDAuMDIxLDAuMDAxLDAuMDM2VjM5SDguNXoiIGZpbGw9IiMwMDAwMDAiLz4KCTxwYXRoIGQ9Ik0xNi4zNTQsNTEuNDNjLTAuMDE5LDAuNDQ2LTAuMTcxLDAuNzY0LTAuNDU4LDAuOTVzLTAuNjcyLDAuMjgtMS4xNTUsMC4yOGMtMC4xOTEsMC0wLjM5Ni0wLjAyMi0wLjYxNS0wLjA2OCAgIHMtMC40MjktMC4wOTgtMC42MjktMC4xNTdzLTAuMzg1LTAuMTIzLTAuNTU0LTAuMTkxcy0wLjI5OS0wLjEzNS0wLjM5LTAuMTk4bC0wLjY5NywxLjEwN2MwLjE4MywwLjEzNywwLjQwNSwwLjI2LDAuNjcsMC4zNjkgICBzMC41NCwwLjIwNywwLjgyNywwLjI5NHMwLjU2NSwwLjE1LDAuODM0LDAuMTkxczAuNTA0LDAuMDYyLDAuNzA0LDAuMDYyYzAuNDAxLDAsMC43OTEtMC4wMzksMS4xNjktMC4xMTYgICBjMC4zNzgtMC4wNzcsMC43MTMtMC4yMTQsMS4wMDUtMC40MXMwLjUyNC0wLjQ1NiwwLjY5Ny0wLjc3OXMwLjI2LTAuNzIzLDAuMjYtMS4xOTZ2LTcuODQ4aC0xLjY2OFY1MS40M3oiIGZpbGw9IiMwMDAwMDAiLz4KCTxwYXRoIGQ9Ik0yNS4wODMsNDkuMDY0Yy0wLjMxNC0wLjIyOC0wLjY1NC0wLjQyMi0xLjAxOS0wLjU4MXMtMC43MDItMC4zMjMtMS4wMTItMC40OTJzLTAuNTY5LTAuMzY0LTAuNzc5LTAuNTg4ICAgcy0wLjMxNC0wLjUxOC0wLjMxNC0wLjg4MmMwLTAuMTQ2LDAuMDM2LTAuMjk5LDAuMTA5LTAuNDU4czAuMTczLTAuMzAzLDAuMzAxLTAuNDMxczAuMjczLTAuMjM0LDAuNDM4LTAuMzIxICAgczAuMzM3LTAuMTM5LDAuNTItMC4xNTdjMC4zMjgtMC4wMjcsMC41OTctMC4wMzIsMC44MDctMC4wMTRzMC4zNzgsMC4wNSwwLjUwNiwwLjA5NnMwLjIyNiwwLjA5MSwwLjI5NCwwLjEzNyAgIHMwLjEzLDAuMDgyLDAuMTg1LDAuMTA5YzAuMDA5LTAuMDA5LDAuMDM2LTAuMDU1LDAuMDgyLTAuMTM3czAuMTAxLTAuMTg1LDAuMTY0LTAuMzA4czAuMTMyLTAuMjU1LDAuMjA1LTAuMzk2ICAgczAuMTM3LTAuMjcxLDAuMTkxLTAuMzljLTAuMjY1LTAuMTczLTAuNjEtMC4yOTktMS4wMzktMC4zNzZzLTAuODUzLTAuMTE2LTEuMjcxLTAuMTE2Yy0wLjQxLDAtMC44LDAuMDYzLTEuMTY5LDAuMTkxICAgcy0wLjY5MiwwLjMxMy0wLjk3MSwwLjU1NHMtMC40OTksMC41MzUtMC42NjMsMC44ODJTMjAuNCw0Ni4xMywyMC40LDQ2LjU3NmMwLDAuNDkyLDAuMTA0LDAuOTAyLDAuMzE0LDEuMjMgICBzMC40NzQsMC42MTMsMC43OTMsMC44NTRzMC42NjEsMC40NTEsMS4wMjUsMC42MjlzMC43MDQsMC4zNTUsMS4wMTksMC41MzNzMC41NzYsMC4zNzYsMC43ODYsMC41OTVzMC4zMTQsMC40ODMsMC4zMTQsMC43OTMgICBjMCwwLjUxMS0wLjE0OCwwLjg5Ni0wLjQ0NCwxLjE1NXMtMC43MjMsMC4zOS0xLjI3OCwwLjM5Yy0wLjE4MywwLTAuMzc4LTAuMDE5LTAuNTg4LTAuMDU1cy0wLjQxOS0wLjA4NC0wLjYyOS0wLjE0NCAgIHMtMC40MTItMC4xMjMtMC42MDgtMC4xOTFzLTAuMzU3LTAuMTM5LTAuNDg1LTAuMjEybC0wLjI4NywxLjE3NmMwLjE1NSwwLjEzNywwLjM0LDAuMjUzLDAuNTU0LDAuMzQ5czAuNDM5LDAuMTcxLDAuNjc3LDAuMjI2ICAgYzAuMjM3LDAuMDU1LDAuNDcyLDAuMDk0LDAuNzA0LDAuMTE2czAuNDU4LDAuMDM0LDAuNjc3LDAuMDM0YzAuNTExLDAsMC45NjYtMC4wNzcsMS4zNjctMC4yMzJzMC43MzgtMC4zNjIsMS4wMTItMC42MjIgICBzMC40ODUtMC41NjEsMC42MzYtMC45MDJzMC4yMjYtMC42OTUsMC4yMjYtMS4wNmMwLTAuNTM4LTAuMTA0LTAuOTc4LTAuMzE0LTEuMzE5UzI1LjM5Nyw0OS4yOTIsMjUuMDgzLDQ5LjA2NHoiIGZpbGw9IiMwMDAwMDAiLz4KCTxwYXRoIGQ9Ik0zNC44NzIsNDUuMDcyYy0wLjM3OC0wLjQyOS0wLjgyLTAuNzU0LTEuMzI2LTAuOTc4cy0xLjA2LTAuMzM1LTEuNjYxLTAuMzM1cy0xLjE1NSwwLjExMS0xLjY2MSwwLjMzNSAgIHMtMC45NDgsMC41NDktMS4zMjYsMC45NzhzLTAuNjc1LDAuOTY0LTAuODg5LDEuNjA2cy0wLjMyMSwxLjM4OC0wLjMyMSwyLjIzNXMwLjEwNywxLjU5NSwwLjMyMSwyLjI0MnMwLjUxMSwxLjE4NSwwLjg4OSwxLjYxMyAgIHMwLjgyLDAuNzUyLDEuMzI2LDAuOTcxczEuMDYsMC4zMjgsMS42NjEsMC4zMjhzMS4xNTUtMC4xMDksMS42NjEtMC4zMjhzMC45NDgtMC41NDIsMS4zMjYtMC45NzFzMC42NzUtMC45NjYsMC44ODktMS42MTMgICBzMC4zMjEtMS4zOTUsMC4zMjEtMi4yNDJzLTAuMTA3LTEuNTkzLTAuMzIxLTIuMjM1UzM1LjI1LDQ1LjUwMSwzNC44NzIsNDUuMDcyeiBNMzQuMTk1LDUwLjY5OCAgIGMtMC4xMzcsMC40ODctMC4zMjYsMC44ODItMC41NjcsMS4xODNzLTAuNTE1LDAuNTE4LTAuODIsMC42NDlzLTAuNjI3LDAuMTk4LTAuOTY0LDAuMTk4Yy0wLjMyOCwwLTAuNjQxLTAuMDctMC45MzctMC4yMTIgICBzLTAuNTYxLTAuMzY0LTAuNzkzLTAuNjdzLTAuNDE1LTAuNjk5LTAuNTQ3LTEuMTgzcy0wLjIwMy0xLjA2Ni0wLjIxMi0xLjc1YzAuMDA5LTAuNzAyLDAuMDgyLTEuMjk0LDAuMjE5LTEuNzc3ICAgYzAuMTM3LTAuNDgzLDAuMzI2LTAuODc3LDAuNTY3LTEuMTgzczAuNTE1LTAuNTIxLDAuODItMC42NDlzMC42MjctMC4xOTEsMC45NjQtMC4xOTFjMC4zMjgsMCwwLjY0MSwwLjA2OCwwLjkzNywwLjIwNSAgIHMwLjU2MSwwLjM2LDAuNzkzLDAuNjdzMC40MTUsMC43MDQsMC41NDcsMS4xODNzMC4yMDMsMS4wNiwwLjIxMiwxLjc0M0MzNC40MDUsNDkuNjE2LDM0LjMzMiw1MC4yMTEsMzQuMTk1LDUwLjY5OHoiIGZpbGw9IiMwMDAwMDAiLz4KCTxwb2x5Z29uIHBvaW50cz0iNDQuMDEyLDUwLjg2OSA0MC4wNjEsNDMuOTI0IDM4LjM5Myw0My45MjQgMzguMzkzLDU0IDQwLjA2MSw1NCA0MC4wNjEsNDcuMDU1IDQ0LjAxMiw1NCA0NS42OCw1NCA0NS42OCw0My45MjQgICAgNDQuMDEyLDQzLjkyNCAgIiBmaWxsPSIjMDAwMDAwIi8+Cgk8cGF0aCBkPSJNMjAuNSwyMHYtNGMwLTAuNTUxLDAuNDQ4LTEsMS0xYzAuNTUzLDAsMS0wLjQ0OCwxLTFzLTAuNDQ3LTEtMS0xYy0xLjY1NCwwLTMsMS4zNDYtMywzdjRjMCwxLjEwMy0wLjg5NywyLTIsMiAgIGMtMC41NTMsMC0xLDAuNDQ4LTEsMXMwLjQ0NywxLDEsMWMxLjEwMywwLDIsMC44OTcsMiwydjRjMCwxLjY1NCwxLjM0NiwzLDMsM2MwLjU1MywwLDEtMC40NDgsMS0xcy0wLjQ0Ny0xLTEtMSAgIGMtMC41NTIsMC0xLTAuNDQ5LTEtMXYtNGMwLTEuMi0wLjU0Mi0yLjI2Ni0xLjM4Mi0zQzE5Ljk1OCwyMi4yNjYsMjAuNSwyMS4yLDIwLjUsMjB6IiBmaWxsPSIjMDAwMDAwIi8+Cgk8Y2lyY2xlIGN4PSIyOC41IiBjeT0iMTkuNSIgcj0iMS41IiBmaWxsPSIjMDAwMDAwIi8+Cgk8cGF0aCBkPSJNMjguNSwyNWMtMC41NTMsMC0xLDAuNDQ4LTEsMXYzYzAsMC41NTIsMC40NDcsMSwxLDFzMS0wLjQ0OCwxLTF2LTNDMjkuNSwyNS40NDgsMjkuMDUzLDI1LDI4LjUsMjV6IiBmaWxsPSIjMDAwMDAwIi8+CjwvZz4KPGc+CjwvZz4KPGc+CjwvZz4KPGc+CjwvZz4KPGc+CjwvZz4KPGc+CjwvZz4KPGc+CjwvZz4KPGc+CjwvZz4KPGc+CjwvZz4KPGc+CjwvZz4KPGc+CjwvZz4KPGc+CjwvZz4KPGc+CjwvZz4KPGc+CjwvZz4KPGc+CjwvZz4KPGc+CjwvZz4KPC9zdmc+Cg==" /></a>';

	$pdf = '';
	if (isset($work->message->link))
	{
		foreach ($work->message->link as $link)
		{
			if ($link->{'content-type'} == 'application/pdf')
			{
				$pdf = $link ->URL;
			}
		}
	}
	if ($pdf != '')
	{
      echo '<a href="' . $pdf . '" target="_new"><img src="data:image/svg+xml;utf8;base64,PD94bWwgdmVyc2lvbj0iMS4wIiBlbmNvZGluZz0iaXNvLTg4NTktMSI/Pgo8IS0tIEdlbmVyYXRvcjogQWRvYmUgSWxsdXN0cmF0b3IgMTguMC4wLCBTVkcgRXhwb3J0IFBsdWctSW4gLiBTVkcgVmVyc2lvbjogNi4wMCBCdWlsZCAwKSAgLS0+CjwhRE9DVFlQRSBzdmcgUFVCTElDICItLy9XM0MvL0RURCBTVkcgMS4xLy9FTiIgImh0dHA6Ly93d3cudzMub3JnL0dyYXBoaWNzL1NWRy8xLjEvRFREL3N2ZzExLmR0ZCI+CjxzdmcgeG1sbnM9Imh0dHA6Ly93d3cudzMub3JnLzIwMDAvc3ZnIiB4bWxuczp4bGluaz0iaHR0cDovL3d3dy53My5vcmcvMTk5OS94bGluayIgdmVyc2lvbj0iMS4xIiBpZD0iQ2FwYV8xIiB4PSIwcHgiIHk9IjBweCIgdmlld0JveD0iMCAwIDU4IDU4IiBzdHlsZT0iZW5hYmxlLWJhY2tncm91bmQ6bmV3IDAgMCA1OCA1ODsiIHhtbDpzcGFjZT0icHJlc2VydmUiIHdpZHRoPSIzMnB4IiBoZWlnaHQ9IjMycHgiPgo8Zz4KCTxwYXRoIGQ9Ik01MC45NSwxMi4xODdsLTAuNzcxLTAuNzcxTDQwLjA4NCwxLjMyMUwzOS4zMTMsMC41NUMzOC45NjQsMC4yMDEsMzguNDgsMCwzNy45ODUsMEg4Ljk2M0M3Ljc3NywwLDYuNSwwLjkxNiw2LjUsMi45MjZWMzkgICB2MTYuNTM3VjU2YzAsMC44MzcsMC44NDIsMS42NTMsMS44MzgsMS45MWMwLjA1LDAuMDEzLDAuMDk4LDAuMDMyLDAuMTUsMC4wNDJDOC42NDQsNTcuOTgzLDguODAzLDU4LDguOTYzLDU4aDQwLjA3NCAgIGMwLjE2LDAsMC4zMTktMC4wMTcsMC40NzUtMC4wNDhjMC4wNTItMC4wMSwwLjEtMC4wMjksMC4xNS0wLjA0MkM1MC42NTgsNTcuNjUzLDUxLjUsNTYuODM3LDUxLjUsNTZ2LTAuNDYzVjM5VjEzLjk3OCAgIEM1MS41LDEzLjIxMSw1MS40MDgsMTIuNjQ1LDUwLjk1LDEyLjE4N3ogTTQ3LjkzNSwxMkgzOS41VjMuNTY1TDQ3LjkzNSwxMnogTTguOTYzLDU2Yy0wLjA3MSwwLTAuMTM1LTAuMDI2LTAuMTk4LTAuMDQ5ICAgQzguNjA5LDU1Ljg3Nyw4LjUsNTUuNzIxLDguNSw1NS41MzdWNDFoNDF2MTQuNTM3YzAsMC4xODQtMC4xMDksMC4zMzktMC4yNjUsMC40MTRDNDkuMTcyLDU1Ljk3NCw0OS4xMDgsNTYsNDkuMDM3LDU2SDguOTYzeiAgICBNOC41LDM5VjIuOTI2QzguNSwyLjcwOSw4LjUzMywyLDguOTYzLDJoMjguNTk1QzM3LjUyNSwyLjEyNiwzNy41LDIuMjU2LDM3LjUsMi4zOTFWMTRoMTEuNjA5YzAuMTM1LDAsMC4yNjQtMC4wMjUsMC4zOS0wLjA1OCAgIGMwLDAuMDE1LDAuMDAxLDAuMDIxLDAuMDAxLDAuMDM2VjM5SDguNXoiIGZpbGw9IiMwMDAwMDAiLz4KCTxwYXRoIGQ9Ik0yMi4wNDIsNDQuNzQ0Yy0wLjMzMy0wLjI3My0wLjcwOS0wLjQ3OS0xLjEyOC0wLjYxNWMtMC40MTktMC4xMzctMC44NDMtMC4yMDUtMS4yNzEtMC4yMDVoLTIuODk4VjU0aDEuNjQxdi0zLjYzN2gxLjIxNyAgIGMwLjUyOCwwLDEuMDEyLTAuMDc3LDEuNDQ5LTAuMjMyczAuODExLTAuMzc0LDEuMTIxLTAuNjU2YzAuMzEtMC4yODIsMC41NTEtMC42MzEsMC43MjUtMS4wNDZjMC4xNzMtMC40MTUsMC4yNi0wLjg3NywwLjI2LTEuMzg4ICAgYzAtMC40ODMtMC4xMDMtMC45MTgtMC4zMDgtMS4zMDZTMjIuMzc1LDQ1LjAxOCwyMi4wNDIsNDQuNzQ0eiBNMjEuNDIsNDguMDczYy0wLjEwMSwwLjI3OC0wLjIzMiwwLjQ5NC0wLjM5NiwwLjY0OSAgIGMtMC4xNjQsMC4xNTUtMC4zNDQsMC4yNjctMC41NCwwLjMzNWMtMC4xOTYsMC4wNjgtMC4zOTUsMC4xMDMtMC41OTUsMC4xMDNoLTEuNTA0di0zLjk5MmgxLjIzYzAuNDE5LDAsMC43NTYsMC4wNjYsMS4wMTIsMC4xOTggICBjMC4yNTUsMC4xMzIsMC40NTMsMC4yOTYsMC41OTUsMC40OTJjMC4xNDEsMC4xOTYsMC4yMzQsMC40MDEsMC4yOCwwLjYxNWMwLjA0NSwwLjIxNCwwLjA2OCwwLjQwMywwLjA2OCwwLjU2NyAgIEMyMS41Nyw0Ny40NTEsMjEuNTIsNDcuNzk1LDIxLjQyLDQ4LjA3M3oiIGZpbGw9IiMwMDAwMDAiLz4KCTxwYXRoIGQ9Ik0zMS45NTQsNDUuNGMtMC40MjQtMC40NDYtMC45NTctMC44MDUtMS42LTEuMDczcy0xLjM4OC0wLjQwMy0yLjIzNS0wLjQwM2gtMy4wMzVWNTRoMy44MTQgICBjMC4xMjcsMCwwLjMyMy0wLjAxNiwwLjU4OC0wLjA0OGMwLjI2NC0wLjAzMiwwLjU1Ni0wLjEwNCwwLjg3NS0wLjIxOWMwLjMxOS0wLjExNCwwLjY0OS0wLjI4NSwwLjk5MS0wLjUxMyAgIHMwLjY0OS0wLjU0LDAuOTIzLTAuOTM3czAuNDk5LTAuODg5LDAuNjc3LTEuNDc3czAuMjY3LTEuMjk3LDAuMjY3LTIuMTI2YzAtMC42MDItMC4xMDUtMS4xODgtMC4zMTQtMS43NTcgICBDMzIuNjk0LDQ2LjM1NSwzMi4zNzgsNDUuODQ3LDMxLjk1NCw0NS40eiBNMzAuNzU4LDUxLjczYy0wLjQ5MiwwLjcxMS0xLjI5NCwxLjA2Ni0yLjQwNiwxLjA2NmgtMS42Mjd2LTcuNjI5aDAuOTU3ICAgYzAuNzg0LDAsMS40MjIsMC4xMDMsMS45MTQsMC4zMDhzMC44ODIsMC40NzQsMS4xNjksMC44MDdzMC40OCwwLjcwNCwwLjU4MSwxLjExNGMwLjEsMC40MSwwLjE1LDAuODI1LDAuMTUsMS4yNDQgICBDMzEuNDk2LDQ5Ljk4OSwzMS4yNSw1MS4wMiwzMC43NTgsNTEuNzN6IiBmaWxsPSIjMDAwMDAwIi8+Cgk8cG9seWdvbiBwb2ludHM9IjM1LjU5OCw1NCAzNy4yNjYsNTQgMzcuMjY2LDQ5LjQ2MSA0MS40NzcsNDkuNDYxIDQxLjQ3Nyw0OC4zNCAzNy4yNjYsNDguMzQgMzcuMjY2LDQ1LjE2OCA0MS45LDQ1LjE2OCAgICA0MS45LDQzLjkyNCAzNS41OTgsNDMuOTI0ICAiIGZpbGw9IiMwMDAwMDAiLz4KCTxwYXRoIGQ9Ik0zOC40MjgsMjIuOTYxYy0wLjkxOSwwLTIuMDQ3LDAuMTItMy4zNTgsMC4zNThjLTEuODMtMS45NDItMy43NC00Ljc3OC01LjA4OC03LjU2MmMxLjMzNy01LjYyOSwwLjY2OC02LjQyNiwwLjM3My02LjgwMiAgIGMtMC4zMTQtMC40LTAuNzU3LTEuMDQ5LTEuMjYxLTEuMDQ5Yy0wLjIxMSwwLTAuNzg3LDAuMDk2LTEuMDE2LDAuMTcyYy0wLjU3NiwwLjE5Mi0wLjg4NiwwLjYzNi0xLjEzNCwxLjIxNSAgIGMtMC43MDcsMS42NTMsMC4yNjMsNC40NzEsMS4yNjEsNi42NDNjLTAuODUzLDMuMzkzLTIuMjg0LDcuNDU0LTMuNzg4LDEwLjc1Yy0zLjc5LDEuNzM2LTUuODAzLDMuNDQxLTUuOTg1LDUuMDY4ICAgYy0wLjA2NiwwLjU5MiwwLjA3NCwxLjQ2MSwxLjExNSwyLjI0MmMwLjI4NSwwLjIxMywwLjYxOSwwLjMyNiwwLjk2NywwLjMyNmgwYzAuODc1LDAsMS43NTktMC42NywyLjc4Mi0yLjEwNyAgIGMwLjc0Ni0xLjA0OCwxLjU0Ny0yLjQ3NywyLjM4My00LjI1MWMyLjY3OC0xLjE3MSw1Ljk5MS0yLjIyOSw4LjgyOC0yLjgyMmMxLjU4LDEuNTE3LDIuOTk1LDIuMjg1LDQuMjExLDIuMjg1ICAgYzAuODk2LDAsMS42NjQtMC40MTIsMi4yMi0xLjE5MWMwLjU3OS0wLjgxMSwwLjcxMS0xLjUzNywwLjM5LTIuMTZDNDAuOTQzLDIzLjMyNywzOS45OTQsMjIuOTYxLDM4LjQyOCwyMi45NjF6IE0yMC41MzYsMzIuNjM0ICAgYy0wLjQ2OC0wLjM1OS0wLjQ0MS0wLjYwMS0wLjQzMS0wLjY5MmMwLjA2Mi0wLjU1NiwwLjkzMy0xLjU0MywzLjA3LTIuNzQ0QzIxLjU1NSwzMi4xOSwyMC42ODUsMzIuNTg3LDIwLjUzNiwzMi42MzR6ICAgIE0yOC43MzYsOS43MTJjMC4wNDMtMC4wMTQsMS4wNDUsMS4xMDEsMC4wOTYsMy4yMTZDMjcuNDA2LDExLjQ2OSwyOC42MzgsOS43NDUsMjguNzM2LDkuNzEyeiBNMjYuNjY5LDI1LjczOCAgIGMxLjAxNS0yLjQxOSwxLjk1OS01LjA5LDIuNjc0LTcuNTY0YzEuMTIzLDIuMDE4LDIuNDcyLDMuOTc2LDMuODIyLDUuNTQ0QzMxLjAzMSwyNC4yMTksMjguNzU5LDI0LjkyNiwyNi42NjksMjUuNzM4eiAgICBNMzkuNTcsMjUuMjU5QzM5LjI2MiwyNS42OSwzOC41OTQsMjUuNywzOC4zNiwyNS43Yy0wLjUzMywwLTAuNzMyLTAuMzE3LTEuNTQ3LTAuOTQ0YzAuNjcyLTAuMDg2LDEuMzA2LTAuMTA4LDEuODExLTAuMTA4ICAgYzAuODg5LDAsMS4wNTIsMC4xMzEsMS4xNzUsMC4xOTdDMzkuNzc3LDI0LjkxNiwzOS43MTksMjUuMDUsMzkuNTcsMjUuMjU5eiIgZmlsbD0iIzAwMDAwMCIvPgo8L2c+CjxnPgo8L2c+CjxnPgo8L2c+CjxnPgo8L2c+CjxnPgo8L2c+CjxnPgo8L2c+CjxnPgo8L2c+CjxnPgo8L2c+CjxnPgo8L2c+CjxnPgo8L2c+CjxnPgo8L2c+CjxnPgo8L2c+CjxnPgo8L2c+CjxnPgo8L2c+CjxnPgo8L2c+CjxnPgo8L2c+Cjwvc3ZnPgo=" /></a>';
	}

	if (isset($work->message->{'page-text'}))
	{
    	echo '<a  href="work/' . $id . '.txt"><img src="data:image/svg+xml;utf8;base64,PD94bWwgdmVyc2lvbj0iMS4wIiBlbmNvZGluZz0iaXNvLTg4NTktMSI/Pgo8IS0tIEdlbmVyYXRvcjogQWRvYmUgSWxsdXN0cmF0b3IgMTguMC4wLCBTVkcgRXhwb3J0IFBsdWctSW4gLiBTVkcgVmVyc2lvbjogNi4wMCBCdWlsZCAwKSAgLS0+CjwhRE9DVFlQRSBzdmcgUFVCTElDICItLy9XM0MvL0RURCBTVkcgMS4xLy9FTiIgImh0dHA6Ly93d3cudzMub3JnL0dyYXBoaWNzL1NWRy8xLjEvRFREL3N2ZzExLmR0ZCI+CjxzdmcgeG1sbnM9Imh0dHA6Ly93d3cudzMub3JnLzIwMDAvc3ZnIiB4bWxuczp4bGluaz0iaHR0cDovL3d3dy53My5vcmcvMTk5OS94bGluayIgdmVyc2lvbj0iMS4xIiBpZD0iQ2FwYV8xIiB4PSIwcHgiIHk9IjBweCIgdmlld0JveD0iMCAwIDU4IDU4IiBzdHlsZT0iZW5hYmxlLWJhY2tncm91bmQ6bmV3IDAgMCA1OCA1ODsiIHhtbDpzcGFjZT0icHJlc2VydmUiIHdpZHRoPSIzMnB4IiBoZWlnaHQ9IjMycHgiPgo8Zz4KCTxwYXRoIGQ9Ik01MC45NDksMTIuMTg3bC0xLjM2MS0xLjM2MWwtOS41MDQtOS41MDVjLTAuMDAxLTAuMDAxLTAuMDAxLTAuMDAxLTAuMDAyLTAuMDAxbC0wLjc3LTAuNzcxICAgQzM4Ljk1NywwLjE5NSwzOC40ODYsMCwzNy45ODUsMEg4Ljk2M0M3Ljc3NiwwLDYuNSwwLjkxNiw2LjUsMi45MjZWMzl2MTYuNTM3VjU2YzAsMC44MzcsMC44NDEsMS42NTIsMS44MzYsMS45MDkgICBjMC4wNTEsMC4wMTQsMC4xLDAuMDMzLDAuMTUyLDAuMDQzQzguNjQ0LDU3Ljk4Myw4LjgwMyw1OCw4Ljk2Myw1OGg0MC4wNzRjMC4xNiwwLDAuMzE5LTAuMDE3LDAuNDc1LTAuMDQ4ICAgYzAuMDUyLTAuMDEsMC4xMDEtMC4wMjksMC4xNTItMC4wNDNDNTAuNjU5LDU3LjY1Miw1MS41LDU2LjgzNyw1MS41LDU2di0wLjQ2M1YzOVYxMy45NzhDNTEuNSwxMy4yMTEsNTEuNDA3LDEyLjY0NCw1MC45NDksMTIuMTg3ICAgeiBNMzkuNSwzLjU2NUw0Ny45MzUsMTJIMzkuNVYzLjU2NXogTTguOTYzLDU2Yy0wLjA3MSwwLTAuMTM1LTAuMDI1LTAuMTk4LTAuMDQ5QzguNjEsNTUuODc3LDguNSw1NS43MjEsOC41LDU1LjUzN1Y0MWg0MXYxNC41MzcgICBjMCwwLjE4NC0wLjExLDAuMzQtMC4yNjUsMC40MTRDNDkuMTcyLDU1Ljk3NSw0OS4xMDgsNTYsNDkuMDM3LDU2SDguOTYzeiBNOC41LDM5VjIuOTI2QzguNSwyLjcwOSw4LjUzMywyLDguOTYzLDJoMjguNTk1ICAgQzM3LjUyNSwyLjEyNiwzNy41LDIuMjU2LDM3LjUsMi4zOTFWMTRoMTEuNjA4YzAuMTM1LDAsMC4yNjUtMC4wMjUsMC4zOTEtMC4wNThjMCwwLjAxNSwwLjAwMSwwLjAyMSwwLjAwMSwwLjAzNlYzOUg4LjV6IiBmaWxsPSIjMDAwMDAwIi8+Cgk8cG9seWdvbiBwb2ludHM9IjE1LjE5Nyw0NS4wNDUgMTguMjA1LDQ1LjA0NSAxOC4yMDUsNTQgMTkuODU5LDU0IDE5Ljg1OSw0NS4wNDUgMjIuODY3LDQ1LjA0NSAyMi44NjcsNDMuOTI0IDE1LjE5Nyw0My45MjQgICIgZmlsbD0iIzAwMDAwMCIvPgoJPHBvbHlnb24gcG9pbnRzPSIzMC4yOTEsNDMuOTI0IDI4LjM2Myw0OC4wMjUgMjguMjI3LDQ4LjAyNSAyNi40NDksNDMuOTI0IDI0LjU3Niw0My45MjQgMjcuMjk3LDQ5LjEwNSAyNC43NCw1NCAyNi42NDEsNTQgICAgMjguMzYzLDUwLjE5OSAyOC41LDUwLjE5OSAzMC4xLDU0IDMyLDU0IDI5LjQ0Myw0OS4xMDUgMzIuMTY0LDQzLjkyNCAgIiBmaWxsPSIjMDAwMDAwIi8+Cgk8cG9seWdvbiBwb2ludHM9IjMzLjg1OSw0NS4wNDUgMzYuODY3LDQ1LjA0NSAzNi44NjcsNTQgMzguNTIxLDU0IDM4LjUyMSw0NS4wNDUgNDEuNTI5LDQ1LjA0NSA0MS41MjksNDMuOTI0IDMzLjg1OSw0My45MjQgICIgZmlsbD0iIzAwMDAwMCIvPgoJPHBhdGggZD0iTTEzLjUsMTRoNmMwLjU1MywwLDEtMC40NDgsMS0xcy0wLjQ0Ny0xLTEtMWgtNmMtMC41NTMsMC0xLDAuNDQ4LTEsMVMxMi45NDcsMTQsMTMuNSwxNHoiIGZpbGw9IiMwMDAwMDAiLz4KCTxwYXRoIGQ9Ik0xMy41LDE5aDljMC41NTMsMCwxLTAuNDQ4LDEtMXMtMC40NDctMS0xLTFoLTljLTAuNTUzLDAtMSwwLjQ0OC0xLDFTMTIuOTQ3LDE5LDEzLjUsMTl6IiBmaWxsPSIjMDAwMDAwIi8+Cgk8cGF0aCBkPSJNMjYuNSwxOWMwLjI2LDAsMC41Mi0wLjExLDAuNzEtMC4yOWMwLjE4LTAuMTksMC4yOS0wLjQ1LDAuMjktMC43MWMwLTAuMjYtMC4xMS0wLjUyLTAuMjktMC43MSAgIGMtMC4zNy0wLjM3LTEuMDUtMC4zNy0xLjQxLDBjLTAuMTksMC4xOS0wLjMsMC40NC0wLjMsMC43MXMwLjEwOSwwLjUyLDAuMjksMC43MUMyNS45NzksMTguODksMjYuMjQsMTksMjYuNSwxOXoiIGZpbGw9IiMwMDAwMDAiLz4KCTxwYXRoIGQ9Ik0zMC41LDE5aDhjMC41NTMsMCwxLTAuNDQ4LDEtMXMtMC40NDctMS0xLTFoLThjLTAuNTUzLDAtMSwwLjQ0OC0xLDFTMjkuOTQ3LDE5LDMwLjUsMTl6IiBmaWxsPSIjMDAwMDAwIi8+Cgk8cGF0aCBkPSJNMTIuNzksMzIuMjljLTAuMTgxLDAuMTktMC4yOSwwLjQ1LTAuMjksMC43MWMwLDAuMjYsMC4xMDksMC41MiwwLjI5LDAuNzFDMTIuOTc5LDMzLjg5LDEzLjI0LDM0LDEzLjUsMzQgICBzMC41Mi0wLjExLDAuNzEtMC4yOWMwLjE4LTAuMTksMC4yOS0wLjQ1LDAuMjktMC43MWMwLTAuMjYtMC4xMS0wLjUyLTAuMjktMC43QzEzLjg0LDMxLjkyLDEzLjE2LDMxLjkyLDEyLjc5LDMyLjI5eiIgZmlsbD0iIzAwMDAwMCIvPgoJPHBhdGggZD0iTTI1LjUsMzJoLThjLTAuNTUzLDAtMSwwLjQ0OC0xLDFzMC40NDcsMSwxLDFoOGMwLjU1MywwLDEtMC40NDgsMS0xUzI2LjA1MywzMiwyNS41LDMyeiIgZmlsbD0iIzAwMDAwMCIvPgoJPHBhdGggZD0iTTQ0LjUsMTdoLTJjLTAuNTUzLDAtMSwwLjQ0OC0xLDFzMC40NDcsMSwxLDFoMmMwLjU1MywwLDEtMC40NDgsMS0xUzQ1LjA1MywxNyw0NC41LDE3eiIgZmlsbD0iIzAwMDAwMCIvPgoJPHBhdGggZD0iTTEzLjUsMjRoMjJjMC41NTMsMCwxLTAuNDQ4LDEtMXMtMC40NDctMS0xLTFoLTIyYy0wLjU1MywwLTEsMC40NDgtMSwxUzEyLjk0NywyNCwxMy41LDI0eiIgZmlsbD0iIzAwMDAwMCIvPgoJPHBhdGggZD0iTTQ0LjUsMjJoLTZjLTAuNTUzLDAtMSwwLjQ0OC0xLDFzMC40NDcsMSwxLDFoNmMwLjU1MywwLDEtMC40NDgsMS0xUzQ1LjA1MywyMiw0NC41LDIyeiIgZmlsbD0iIzAwMDAwMCIvPgoJPHBhdGggZD0iTTEzLjUsMjloNGMwLjU1MywwLDEtMC40NDgsMS0xcy0wLjQ0Ny0xLTEtMWgtNGMtMC41NTMsMC0xLDAuNDQ4LTEsMVMxMi45NDcsMjksMTMuNSwyOXoiIGZpbGw9IiMwMDAwMDAiLz4KCTxwYXRoIGQ9Ik0zMS41LDI3aC0xMGMtMC41NTMsMC0xLDAuNDQ4LTEsMXMwLjQ0NywxLDEsMWgxMGMwLjU1MywwLDEtMC40NDgsMS0xUzMyLjA1MywyNywzMS41LDI3eiIgZmlsbD0iIzAwMDAwMCIvPgoJPHBhdGggZD0iTTQ0LjUsMjdoLTljLTAuNTUzLDAtMSwwLjQ0OC0xLDFzMC40NDcsMSwxLDFoOWMwLjU1MywwLDEtMC40NDgsMS0xUzQ1LjA1MywyNyw0NC41LDI3eiIgZmlsbD0iIzAwMDAwMCIvPgo8L2c+CjxnPgo8L2c+CjxnPgo8L2c+CjxnPgo8L2c+CjxnPgo8L2c+CjxnPgo8L2c+CjxnPgo8L2c+CjxnPgo8L2c+CjxnPgo8L2c+CjxnPgo8L2c+CjxnPgo8L2c+CjxnPgo8L2c+CjxnPgo8L2c+CjxnPgo8L2c+CjxnPgo8L2c+CjxnPgo8L2c+Cjwvc3ZnPgo=" /></a>';
    }
            
    echo '
            </div>
          </div>
          
          <!-- Source of data -->
          <div>
            <h4>
              Source
            </h4>
            <div id="source">';
            
     if (isset($work->message->source))
     {
     	echo $work->message->source;
     }
            
     echo '</div>        
          </div>
          
          <div>
            <h4>
              Identifiers
            </h4>';
            
     if (isset($work->message->DOI))
     {
     	echo '<div id="doi">' . '<b>doi:</b> ' . '<a href="https://doi.org/' . $work->message->DOI . '" target="_new">' . $work->message->DOI . '</a></div>';
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
 
    echo '<div id="wikidata"></div>';
            
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
				if (is_array($work->message->URL))
				{
					foreach ($work->message->URL as $url)
					{
						echo '<div>' . '<b style="color:blue;">' . $url . '</b>' . '</div>';			
					}
				
				}
				else
				{			
					echo '<div>' . '<b style="color:blue;">' . $work->message->URL . '</b>' . '</div>';
				}
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
			if (is_array($row->doc->message->URL))
			{
				foreach ($row->doc->message->URL as $url)
				{
					echo '<div>' . '<b style="color:blue;">' . $url . '</b>' . '</div>';			
				}
			
			}
			else
			{			
				echo '<div>' . '<b style="color:blue;">' . $row->doc->message->URL . '</b>' . '</div>';
			}
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