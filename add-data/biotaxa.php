<?php

// Fetch refs from Biotaxa journals (maybe move to a glitch microservice)

require_once(dirname(dirname(__FILE__)) . '/lib.php');
require_once(dirname(dirname(__FILE__)) . '/couchsimple.php');
require_once (dirname(dirname(__FILE__)) . '/nameparse.php');
require_once(dirname(__FILE__) . '/simplehtmldom_1_5/simple_html_dom.php');

$force = true;

//----------------------------------------------------------------------------------------
function output($citation)
{
	global $config;
	global $couch;
	global $force;
	
	// generate an identifier (need rules for this)
	
	// SHA1 of identifier
	$identifier = sha1($citation->id);
	unset($citation->id);
	
	// generate default cluster_id
	// could use global identifier to help build clusters,
	// for now just use identifier
	$cluster_id = $identifier;
			
	$go = true;

	// Check whether this record already exists (i.e., have we done this object already?)
	$exists = $couch->exists($identifier);

	if ($exists)
	{
		echo "$identifier Exists\n";
		$go = false;

		if ($force)
		{
			echo "[forcing]\n";
			$couch->add_update_or_delete_document(null, $identifier, 'delete');
			$go = true;		
		}
	}

	if ($go)
	{
		// couchdb
		$doc = new stdclass;

		$doc->_id = $identifier;
		
		// Add default cluster id
		$doc->cluster_id = $cluster_id;

		// By default message is empty and has timestamp set to "now"
		// This means it will be at the end of the queue of things to add
		$doc->{'message-timestamp'} = date("c", time());
		$doc->{'message-modified'} 	= $doc->{'message-timestamp'};
		$doc->{'message-format'} 	= 'application/vnd.crossref-citation+json';

		$doc->message = $citation;
		
		print_r($doc);
	
		$resp = $couch->send("PUT", "/" . $config['couchdb_options']['database'] . "/" . urlencode($doc->_id), json_encode($doc));
		var_dump($resp);					
	}	


}



/*$html = get('http://biotaxa.org/Phytotaxa/article/view/phytotaxa.174.5.3');
$html = get('http://biotaxa.org/Phytotaxa/article/view/phytotaxa.172.3.2');
$html = get('http://www.biotaxa.org/Zootaxa/article/view/zootaxa.4032.4.1');
$html = get('http://www.biotaxa.org/Zootaxa/article/view/zootaxa.4032.4.6');
$html = get('http://biotaxa.org/Phytotaxa/article/view/phytotaxa.172.3.11');
$html = get('http://biotaxa.org/Phytotaxa/article/view/phytotaxa.170.3.1');
$html = get('http://biotaxa.org/Phytotaxa/article/view/phytotaxa.169.1.1');
$html = get('http://biotaxa.org/Phytotaxa/article/view/phytotaxa.145.1.3');
$html = get('http://biotaxa.org/Phytotaxa/article/view/phytotaxa.126.1.6');
$html = get('http://www.biotaxa.org/Zootaxa/article/view/zootaxa.4032.4.8');
//$html = get('http://biotaxa.org/Phytotaxa/article/view/phytotaxa.267.2.8');
*/

//$html = file_get_contents('zootaxa.4032.4.8.html');
//$html = get('http://biotaxa.org/Phytotaxa/article/view/phytotaxa.267.2.8');
//$html = get('http://biotaxa.org/Zootaxa/article/view/zootaxa.3964.4.3');
//$html = get('http://www.biotaxa.org/Zootaxa/article/view/zootaxa.4032.4.6'); // Moenkhausia (fish)
//$html = get('http://biotaxa.org/Phytotaxa/article/view/phytotaxa.172.3.11');
//$html = get('http://biotaxa.org/Phytotaxa/article/view/phytotaxa.267.2.8');
//$html = get('http://biotaxa.org/Phytotaxa/article/view/phytotaxa.126.1.6');
//$html = get('http://biotaxa.org/Phytotaxa/article/view/phytotaxa.145.1.3');
//$html = get('http://biotaxa.org/Phytotaxa/article/view/phytotaxa.227.3.4');
//$html = get('http://www.biotaxa.org/Zootaxa/article/view/zootaxa.4032.4.1');
//$html = get('http://biotaxa.org/Phytotaxa/article/view/phytotaxa.170.3.1');
//$html = get('http://biotaxa.org/Phytotaxa/article/view/phytotaxa.169.1.1');

// Nasa
//$html = get('http://biotaxa.org/Phytotaxa/article/view/phytotaxa.26.1.1');

//$html = get('http://biotaxa.org/Phytotaxa/article/view/phytotaxa.26.1.3');

//$html = get('https://biotaxa.org/Phytotaxa/article/view/phytotaxa.222.1.7');


$urls = array('http://biotaxa.org/Phytotaxa/article/view/phytotaxa.26.1.3');

$urls = array('http://biotaxa.org/Phytotaxa/article/view/phytotaxa.26.1.1',
'http://biotaxa.org/Phytotaxa/article/view/phytotaxa.172.3.11',
'http://biotaxa.org/Phytotaxa/article/view/phytotaxa.126.1.6',
'http://www.biotaxa.org/Zootaxa/article/view/zootaxa.4032.4.6'
);

$urls = array(
'https://biotaxa.org/Phytotaxa/article/view/phytotaxa.328.2.7',
'https://biotaxa.org/Phytotaxa/article/view/phytotaxa.328.2.1',
'https://biotaxa.org/Phytotaxa/article/view/phytotaxa.328.3.5',
'https://biotaxa.org/Phytotaxa/article/view/phytotaxa.328.3.1',
'https://www.biotaxa.org/Phytotaxa/article/view/phytotaxa.298.2.1'
);

$urls = array('https://biotaxa.org/Phytotaxa/article/view/phytotaxa.265.3.12');

$urls = array(
"http://biotaxa.org/Phytotaxa/article/view/phytotaxa.210.1.2",
"http://biotaxa.org/Phytotaxa/article/view/phytotaxa.241.1.1",
"http://biotaxa.org/Phytotaxa/article/view/phytotaxa.240.1.1",
"http://biotaxa.org/Phytotaxa/article/view/phytotaxa.242.1.1",
"http://biotaxa.org/Phytotaxa/article/view/phytotaxa.243.1.1",
"http://biotaxa.org/Phytotaxa/article/view/phytotaxa.243.1.2",
"http://biotaxa.org/Phytotaxa/article/view/phytotaxa.243.1.3",
"http://biotaxa.org/Phytotaxa/article/view/phytotaxa.243.1.4",
"http://biotaxa.org/Phytotaxa/article/view/phytotaxa.243.1.5",
"http://biotaxa.org/Phytotaxa/article/view/phytotaxa.243.1.6",
"http://biotaxa.org/Phytotaxa/article/view/phytotaxa.243.1.7",
"http://biotaxa.org/Phytotaxa/article/view/phytotaxa.245.2.1",
"http://biotaxa.org/Phytotaxa/article/view/phytotaxa.245.2.2",
"http://biotaxa.org/Phytotaxa/article/view/phytotaxa.245.2.3",
"http://biotaxa.org/Phytotaxa/article/view/phytotaxa.245.2.4",
"http://biotaxa.org/Phytotaxa/article/view/phytotaxa.245.2.5",
"http://biotaxa.org/Phytotaxa/article/view/phytotaxa.245.2.6",
"http://biotaxa.org/Phytotaxa/article/view/phytotaxa.245.2.7",
"http://biotaxa.org/Phytotaxa/article/view/phytotaxa.245.2.8",
"http://biotaxa.org/Phytotaxa/article/view/phytotaxa.245.2.9",
"http://biotaxa.org/Phytotaxa/article/view/phytotaxa.245.2.10",
"http://biotaxa.org/Phytotaxa/article/view/phytotaxa.245.3.1",
"http://biotaxa.org/Phytotaxa/article/view/phytotaxa.245.3.2",
"http://biotaxa.org/Phytotaxa/article/view/phytotaxa.245.3.3",
"http://biotaxa.org/Phytotaxa/article/view/phytotaxa.245.3.4",
"http://biotaxa.org/Phytotaxa/article/view/phytotaxa.245.3.5",
"http://biotaxa.org/Phytotaxa/article/view/phytotaxa.245.3.6",
"http://biotaxa.org/Phytotaxa/article/view/phytotaxa.245.3.7",
"http://biotaxa.org/Phytotaxa/article/view/phytotaxa.244.2.1",
"http://biotaxa.org/Phytotaxa/article/view/phytotaxa.244.2.2",
"http://biotaxa.org/Phytotaxa/article/view/phytotaxa.244.2.3",
"http://biotaxa.org/Phytotaxa/article/view/phytotaxa.244.2.4",
"http://biotaxa.org/Phytotaxa/article/view/phytotaxa.244.2.5",
"http://biotaxa.org/Phytotaxa/article/view/phytotaxa.244.2.6",
"http://biotaxa.org/Phytotaxa/article/view/phytotaxa.244.2.7",
"http://biotaxa.org/Phytotaxa/article/view/phytotaxa.244.2.8",
"http://biotaxa.org/Phytotaxa/article/view/phytotaxa.244.3.1",
"http://biotaxa.org/Phytotaxa/article/view/phytotaxa.244.3.2",
"http://biotaxa.org/Phytotaxa/article/view/phytotaxa.244.3.3",
"http://biotaxa.org/Phytotaxa/article/view/phytotaxa.244.3.4",
"http://biotaxa.org/Phytotaxa/article/view/phytotaxa.244.3.5",
"http://biotaxa.org/Phytotaxa/article/view/phytotaxa.244.3.6",
"http://biotaxa.org/Phytotaxa/article/view/phytotaxa.245.1.1",
"http://biotaxa.org/Phytotaxa/article/view/phytotaxa.245.1.2",
"http://biotaxa.org/Phytotaxa/article/view/phytotaxa.245.1.3",
"http://biotaxa.org/Phytotaxa/article/view/phytotaxa.245.1.4",
"http://biotaxa.org/Phytotaxa/article/view/phytotaxa.245.1.5",
"http://biotaxa.org/Phytotaxa/article/view/phytotaxa.245.1.6",
"http://biotaxa.org/Phytotaxa/article/view/phytotaxa.245.1.7",
"http://biotaxa.org/Phytotaxa/article/view/phytotaxa.245.1.8",
"http://biotaxa.org/Phytotaxa/article/view/phytotaxa.245.1.9",
"http://biotaxa.org/Phytotaxa/article/view/phytotaxa.245.1.10",
"http://biotaxa.org/Phytotaxa/article/view/phytotaxa.245.1.11",
"http://biotaxa.org/Phytotaxa/article/view/phytotaxa.243.2.1",
"http://biotaxa.org/Phytotaxa/article/view/phytotaxa.243.2.2",
"http://biotaxa.org/Phytotaxa/article/view/phytotaxa.243.2.3",
"http://biotaxa.org/Phytotaxa/article/view/phytotaxa.243.2.4",
"http://biotaxa.org/Phytotaxa/article/view/phytotaxa.243.2.5",
"http://biotaxa.org/Phytotaxa/article/view/phytotaxa.243.2.6",
"http://biotaxa.org/Phytotaxa/article/view/phytotaxa.243.2.7",
"http://biotaxa.org/Phytotaxa/article/view/phytotaxa.243.2.8",
"http://biotaxa.org/Phytotaxa/article/view/phytotaxa.243.2.9",
"http://biotaxa.org/Phytotaxa/article/view/phytotaxa.243.2.10",
"http://biotaxa.org/Phytotaxa/article/view/phytotaxa.243.2.11",
"http://biotaxa.org/Phytotaxa/article/view/phytotaxa.243.2.12",
"http://biotaxa.org/Phytotaxa/article/view/phytotaxa.243.2.13",
"http://biotaxa.org/Phytotaxa/article/view/phytotaxa.243.2.14",
"http://biotaxa.org/Phytotaxa/article/view/phytotaxa.243.3.1",
"http://biotaxa.org/Phytotaxa/article/view/phytotaxa.243.3.2",
"http://biotaxa.org/Phytotaxa/article/view/phytotaxa.243.3.3",
"http://biotaxa.org/Phytotaxa/article/view/phytotaxa.243.3.4",
"http://biotaxa.org/Phytotaxa/article/view/phytotaxa.243.3.5",
"http://biotaxa.org/Phytotaxa/article/view/phytotaxa.243.3.6",
"http://biotaxa.org/Phytotaxa/article/view/phytotaxa.243.3.7",
"http://biotaxa.org/Phytotaxa/article/view/phytotaxa.243.3.8",
"http://biotaxa.org/Phytotaxa/article/view/phytotaxa.244.1.1",
"http://biotaxa.org/Phytotaxa/article/view/phytotaxa.244.1.2",
"http://biotaxa.org/Phytotaxa/article/view/phytotaxa.244.1.3",
"http://biotaxa.org/Phytotaxa/article/view/phytotaxa.244.1.4",
"http://biotaxa.org/Phytotaxa/article/view/phytotaxa.244.1.5",
"http://biotaxa.org/Phytotaxa/article/view/phytotaxa.244.1.6",
"http://biotaxa.org/Phytotaxa/article/view/phytotaxa.244.1.7",
"http://biotaxa.org/Phytotaxa/article/view/phytotaxa.244.1.8",
"http://biotaxa.org/Phytotaxa/article/view/phytotaxa.252.2.10",
"http://biotaxa.org/Phytotaxa/article/view/phytotaxa.252.2.8",
"http://biotaxa.org/Phytotaxa/article/view/phytotaxa.252.1.1",
"http://biotaxa.org/Phytotaxa/article/view/phytotaxa.252.1.2",
"http://biotaxa.org/Phytotaxa/article/view/phytotaxa.252.1.3",
"http://biotaxa.org/Phytotaxa/article/view/phytotaxa.252.1.4",
"http://biotaxa.org/Phytotaxa/article/view/phytotaxa.252.1.5",
"http://biotaxa.org/Phytotaxa/article/view/phytotaxa.252.1.6",
"http://biotaxa.org/Phytotaxa/article/view/phytotaxa.252.1.7",
"http://biotaxa.org/Phytotaxa/article/view/phytotaxa.252.1.8",
"http://biotaxa.org/Phytotaxa/article/view/phytotaxa.252.1.9",
"http://biotaxa.org/Phytotaxa/article/view/phytotaxa.252.1.10",
"http://biotaxa.org/Phytotaxa/article/view/phytotaxa.252.2.4",
"http://biotaxa.org/Phytotaxa/article/view/phytotaxa.252.2.9",
"http://biotaxa.org/Phytotaxa/article/view/phytotaxa.252.2.1",
"http://biotaxa.org/Phytotaxa/article/view/phytotaxa.252.2.6",
"http://biotaxa.org/Phytotaxa/article/view/phytotaxa.252.3.3",
"http://biotaxa.org/Phytotaxa/article/view/phytotaxa.252.3.1",
"http://biotaxa.org/Phytotaxa/article/view/phytotaxa.252.2.5",
"http://biotaxa.org/Phytotaxa/article/view/phytotaxa.252.2.2",
"http://biotaxa.org/Phytotaxa/article/view/phytotaxa.252.2.7",
"http://biotaxa.org/Phytotaxa/article/view/phytotaxa.252.2.3",
"http://biotaxa.org/Phytotaxa/article/view/phytotaxa.252.3.5",
"http://biotaxa.org/Phytotaxa/article/view/phytotaxa.252.3.6",
"http://biotaxa.org/Phytotaxa/article/view/phytotaxa.252.3.2",
"http://biotaxa.org/Phytotaxa/article/view/phytotaxa.252.3.4",
"http://biotaxa.org/Phytotaxa/article/view/phytotaxa.252.4.7",
"http://biotaxa.org/Phytotaxa/article/view/phytotaxa.252.4.1",
"http://biotaxa.org/Phytotaxa/article/view/phytotaxa.252.4.8",
"http://biotaxa.org/Phytotaxa/article/view/phytotaxa.252.4.3",
"http://biotaxa.org/Phytotaxa/article/view/phytotaxa.252.4.5",
"http://biotaxa.org/Phytotaxa/article/view/phytotaxa.252.4.4",
"http://biotaxa.org/Phytotaxa/article/view/phytotaxa.252.4.6",
"http://biotaxa.org/Phytotaxa/article/view/phytotaxa.252.4.2",
"http://biotaxa.org/Phytotaxa/article/view/phytotaxa.253.1.1",
"http://biotaxa.org/Phytotaxa/article/view/phytotaxa.253.1.2",
"http://biotaxa.org/Phytotaxa/article/view/phytotaxa.253.1.3",
"http://biotaxa.org/Phytotaxa/article/view/phytotaxa.253.1.4",
"http://biotaxa.org/Phytotaxa/article/view/phytotaxa.253.1.5",
"http://biotaxa.org/Phytotaxa/article/view/phytotaxa.253.1.6",
"http://biotaxa.org/Phytotaxa/article/view/phytotaxa.253.1.7",
"http://biotaxa.org/Phytotaxa/article/view/phytotaxa.253.2.1",
"http://biotaxa.org/Phytotaxa/article/view/phytotaxa.253.2.2",
"http://biotaxa.org/Phytotaxa/article/view/phytotaxa.253.2.3",
"http://biotaxa.org/Phytotaxa/article/view/phytotaxa.253.2.4",
"http://biotaxa.org/Phytotaxa/article/view/phytotaxa.253.2.5",
"http://biotaxa.org/Phytotaxa/article/view/phytotaxa.253.2.6",
"http://biotaxa.org/Phytotaxa/article/view/phytotaxa.253.2.7",
"http://biotaxa.org/Phytotaxa/article/view/phytotaxa.253.2.8",
"http://biotaxa.org/Phytotaxa/article/view/phytotaxa.253.2.9",
"http://biotaxa.org/Phytotaxa/article/view/phytotaxa.246.1.1",
"http://biotaxa.org/Phytotaxa/article/view/phytotaxa.246.1.2",
"http://biotaxa.org/Phytotaxa/article/view/phytotaxa.246.1.3",
"http://biotaxa.org/Phytotaxa/article/view/phytotaxa.246.1.4",
"http://biotaxa.org/Phytotaxa/article/view/phytotaxa.246.1.5",
"http://biotaxa.org/Phytotaxa/article/view/phytotaxa.246.1.6",
"http://biotaxa.org/Phytotaxa/article/view/phytotaxa.246.1.7",
"http://biotaxa.org/Phytotaxa/article/view/phytotaxa.246.1.8",
"http://biotaxa.org/Phytotaxa/article/view/phytotaxa.245.4.1",
"http://biotaxa.org/Phytotaxa/article/view/phytotaxa.245.4.2",
"http://biotaxa.org/Phytotaxa/article/view/phytotaxa.245.4.3",
"http://biotaxa.org/Phytotaxa/article/view/phytotaxa.245.4.4",
"http://biotaxa.org/Phytotaxa/article/view/phytotaxa.245.4.5",
"http://biotaxa.org/Phytotaxa/article/view/phytotaxa.245.4.6",
"http://biotaxa.org/Phytotaxa/article/view/phytotaxa.245.4.7",
"http://biotaxa.org/Phytotaxa/article/view/phytotaxa.246.2.1",
"http://biotaxa.org/Phytotaxa/article/view/phytotaxa.246.2.2",
"http://biotaxa.org/Phytotaxa/article/view/phytotaxa.246.2.3",
"http://biotaxa.org/Phytotaxa/article/view/phytotaxa.246.2.4",
"http://biotaxa.org/Phytotaxa/article/view/phytotaxa.246.2.5",
"http://biotaxa.org/Phytotaxa/article/view/phytotaxa.246.2.6",
"http://biotaxa.org/Phytotaxa/article/view/phytotaxa.246.2.7",
"http://biotaxa.org/Phytotaxa/article/view/phytotaxa.246.2.8",
"http://biotaxa.org/Phytotaxa/article/view/phytotaxa.246.2.9",
"http://biotaxa.org/Phytotaxa/article/view/phytotaxa.246.3.1",
"http://biotaxa.org/Phytotaxa/article/view/phytotaxa.246.3.2",
"http://biotaxa.org/Phytotaxa/article/view/phytotaxa.246.3.3",
"http://biotaxa.org/Phytotaxa/article/view/phytotaxa.246.3.4",
"http://biotaxa.org/Phytotaxa/article/view/phytotaxa.246.4.1",
"http://biotaxa.org/Phytotaxa/article/view/phytotaxa.246.4.2",
"http://biotaxa.org/Phytotaxa/article/view/phytotaxa.246.4.3",
"http://biotaxa.org/Phytotaxa/article/view/phytotaxa.246.4.4",
"http://biotaxa.org/Phytotaxa/article/view/phytotaxa.246.4.5",
"http://biotaxa.org/Phytotaxa/article/view/phytotaxa.246.4.6",
"http://biotaxa.org/Phytotaxa/article/view/phytotaxa.247.1.1",
"http://biotaxa.org/Phytotaxa/article/view/phytotaxa.247.1.2",
"http://biotaxa.org/Phytotaxa/article/view/phytotaxa.247.1.3",
"http://biotaxa.org/Phytotaxa/article/view/phytotaxa.247.1.4",
"http://biotaxa.org/Phytotaxa/article/view/phytotaxa.247.1.5",
"http://biotaxa.org/Phytotaxa/article/view/phytotaxa.247.1.6",
"http://biotaxa.org/Phytotaxa/article/view/phytotaxa.247.1.7",
"http://biotaxa.org/Phytotaxa/article/view/phytotaxa.247.1.8",
"http://biotaxa.org/Phytotaxa/article/view/phytotaxa.247.3.4",
"http://biotaxa.org/Phytotaxa/article/view/phytotaxa.247.3.2",
"http://biotaxa.org/Phytotaxa/article/view/phytotaxa.247.3.1",
"http://biotaxa.org/Phytotaxa/article/view/phytotaxa.247.3.5",
"http://biotaxa.org/Phytotaxa/article/view/phytotaxa.247.3.3",
"http://biotaxa.org/Phytotaxa/article/view/phytotaxa.247.3.7",
"http://biotaxa.org/Phytotaxa/article/view/phytotaxa.247.3.6",
"http://biotaxa.org/Phytotaxa/article/view/phytotaxa.247.2.1",
"http://biotaxa.org/Phytotaxa/article/view/phytotaxa.247.2.2",
"http://biotaxa.org/Phytotaxa/article/view/phytotaxa.247.2.3",
"http://biotaxa.org/Phytotaxa/article/view/phytotaxa.247.2.4",
"http://biotaxa.org/Phytotaxa/article/view/phytotaxa.247.2.5",
"http://biotaxa.org/Phytotaxa/article/view/phytotaxa.247.2.6",
"http://biotaxa.org/Phytotaxa/article/view/phytotaxa.247.2.7",
"http://biotaxa.org/Phytotaxa/article/view/phytotaxa.247.2.8",
"http://biotaxa.org/Phytotaxa/article/view/phytotaxa.247.2.9",
"http://biotaxa.org/Phytotaxa/article/view/phytotaxa.249.1.1",
"http://biotaxa.org/Phytotaxa/article/view/phytotaxa.249.1.2",
"http://biotaxa.org/Phytotaxa/article/view/phytotaxa.249.1.3",
"http://biotaxa.org/Phytotaxa/article/view/phytotaxa.249.1.4",
"http://biotaxa.org/Phytotaxa/article/view/phytotaxa.249.1.5",
"http://biotaxa.org/Phytotaxa/article/view/phytotaxa.249.1.6",
"http://biotaxa.org/Phytotaxa/article/view/phytotaxa.249.1.7",
"http://biotaxa.org/Phytotaxa/article/view/phytotaxa.248.1.1",
"http://biotaxa.org/Phytotaxa/article/view/phytotaxa.247.4.4",
"http://biotaxa.org/Phytotaxa/article/view/phytotaxa.247.4.1",
"http://biotaxa.org/Phytotaxa/article/view/phytotaxa.247.4.8",
"http://biotaxa.org/Phytotaxa/article/view/phytotaxa.247.4.9",
"http://biotaxa.org/Phytotaxa/article/view/phytotaxa.247.4.10",
"http://biotaxa.org/Phytotaxa/article/view/phytotaxa.247.4.3",
"http://biotaxa.org/Phytotaxa/article/view/phytotaxa.247.4.2",
"http://biotaxa.org/Phytotaxa/article/view/phytotaxa.247.4.5",
"http://biotaxa.org/Phytotaxa/article/view/phytotaxa.247.4.6",
"http://biotaxa.org/Phytotaxa/article/view/phytotaxa.247.4.7",
"http://biotaxa.org/Phytotaxa/article/view/phytotaxa.250.1.1",
"http://biotaxa.org/Phytotaxa/article/view/phytotaxa.251.1.1",
"http://biotaxa.org/Phytotaxa/article/view/phytotaxa.253.3.1",
"http://biotaxa.org/Phytotaxa/article/view/phytotaxa.253.3.2",
"http://biotaxa.org/Phytotaxa/article/view/phytotaxa.253.3.3",
"http://biotaxa.org/Phytotaxa/article/view/phytotaxa.253.3.4",
"http://biotaxa.org/Phytotaxa/article/view/phytotaxa.253.3.5",
"http://biotaxa.org/Phytotaxa/article/view/phytotaxa.253.3.6",
"http://biotaxa.org/Phytotaxa/article/view/phytotaxa.253.3.7",
"http://biotaxa.org/Phytotaxa/article/view/phytotaxa.253.3.8",
"http://biotaxa.org/Phytotaxa/article/view/phytotaxa.253.3.9",
"http://biotaxa.org/Phytotaxa/article/view/phytotaxa.253.3.10",
"http://biotaxa.org/Phytotaxa/article/view/phytotaxa.253.4.1",
"http://biotaxa.org/Phytotaxa/article/view/phytotaxa.253.4.2"
);


$urls = array(
'https://biotaxa.org/Zootaxa/article/view/zootaxa.3938.1.1'
);

foreach ($urls as $url)
{

	//$html = file_get_contents('phytotaxa.222.1.7.html');
	$html = get($url);

	$reference = new stdclass;

	$dom = str_get_html($html);

	$metas = $dom->find('meta');
	foreach ($metas as $meta)
	{
		switch ($meta->name)
		{
			case 'citation_author':
				$reference->authors[] =  mb_convert_case($meta->content, MB_CASE_TITLE);
				break;
	
			case 'citation_title':
				$reference->title = trim($meta->content);
				$reference->title = preg_replace('/\s\s+/u', ' ', $reference->title);
				break;

			case 'citation_doi':
				$reference->doi =  $meta->content;
				$reference->_id = $reference->doi;
				$reference->cluster_id = $reference->doi;
				$reference->source = $reference->doi;
				break;

			case 'citation_journal_title':
				$reference->journal =  $meta->content;
				$reference->genre = 'article';
				break;

			case 'citation_issn':
				$reference->issn =  $meta->content;
				break;

			case 'citation_volume':
				$reference->volume =  $meta->content;
				break;

			case 'citation_issue':
				$reference->issue =  $meta->content;
				break;

			case 'citation_firstpage':
				$reference->spage =  $meta->content;
				break;

			case 'citation_lastpage':
				$reference->epage =  $meta->content;
				break;

			case 'citation_abstract_html_url':
				$reference->url =  $meta->content;
				break;

			case 'citation_pdf_url':
				$reference->pdf =  $meta->content;
				break;

			case 'citation_date':
				if (preg_match('/^[0-9]{4}$/', $meta->content))
				{
					$reference->year = $meta->content;
				}
			
				if (preg_match('/^(?<year>[0-9]{4})\//', $meta->content, $m))
				{
					$reference->year = $m['year'];
				}
				break;
			
			default:
				break;
		}
	}


	//print_r($reference);

	$citation = null;
	$count = 1;

	$ps = $dom->find('div[id=articleCitations] div p p');
	foreach ($ps as $p)
	{
	
		// Zhang, B. &amp; Li, Y. (2013 ‘2012’) Myxomycetes from China 16: 
		if (preg_match('/\([0-9]{4}[a-z]?(\s*‘[0-9]{4}’)?\)/u', $p->plaintext))
		{
			if ($citation)
			{
				$citation->id = $reference->_id . '/ref-' . $count++;
				$citation->cited_by[] = $reference->_id;
				output($citation);		
			}
			$citation = new stdclass;
			$citation->type = 'unknown';
			$citation->unstructured = $p->plaintext;
			$citation->unstructured = str_replace ('&amp;', '&', $citation->unstructured);
			$citation->unstructured = str_replace ("\n", ' ', $citation->unstructured);
		
			//echo "------------------------------------------\n";
			//echo $p->innertext . "\n";
		
			$m = array();
			$matched = false;
		
			// Phytotaxa-style
		
			// <p>Brunner von Wattenwyl, C. (1888) Monographie der Stenopelmatiden und Gryllacriden. <em>Verhandlungen zoologische-botanische Gesellschaft, Wien</em>, 38, 247–-394.</p>
			if (!$matched)
			{
				if (preg_match('/(?<authorstring>.*)\s+\((?<year>[0-9]{4})[a-z]?\)(?<title>.*)\.\s+<em>(?<journal>.*)<\/em>,\s+(?<volume>\d+),\s+(?<spage>\d+)–-(?<epage>\d+)/u', $p->innertext, $m))
				{
					//print_r($m);
					$matched = true;
				}
			}
		
		
		
			// León, B. (2006) Malesherbiaceae endémicas del Perú<em>. Revista. Peruana de Biología </em>13 (2): 407–408.

			if (!$matched)
			{
				if (preg_match('/(?<authorstring>.*)\s+\((?<year>[0-9]{4})[a-z]?\)(?<title>.*)<em>\.\s+(?<journal>.*)<\/em>\s*(?<volume>\d+)(\s*\((?<issue>.*)\))?:(\s+t.)?\s*(?<spage>\d+)–(?<epage>\d+)[\.|,]/u', $p->innertext, $m))
				{
					//print_r($m);
					$matched = true;
				}
			}
		
		
			if (!$matched)
			{
				if (preg_match('/(?<authorstring>.*)\s+\((?<year>[0-9]{4})(\s*‘[0-9]{4}’)?[a-z]?\)(?<title>.*)\s+<em>(?<journal>.*)<\/em>\s*(?<volume>\d+)(\s*\((?<issue>.*)\))?:(\s+t.)?\s*(?<spage>\d+)–(?<epage>\d+)[\.|,]/u', $p->innertext, $m))
				{
					//print_r($m);
					$matched = true;
				}
			}
		
			// Zootaxa-style
			if (!$matched)
			{
				if (preg_match('/(?<authorstring>.*)\s+\((?<year>[0-9]{4})[a-z]?\)(?<title>.*)[\.|?]\s+<em>(?<journal>.*)(<\/em>,|,\s*<\/em>)\s*(?<volume>\d+)(\s*\((?<issue>\d+([-|–]\d+)?)\))?,(<em> <\/em>)?\s*(?<spage>\d+)(<em>)?–(-)?(<\/em>)?(?<epage>\d+)[\.|,]?/u', $p->innertext, $m))
				{
					//print_r($m);
					$matched = true;
				}
			}
		
			// Book
			// Walker, F. (1848) List of the specimens of dipterous insects in the collection of the British Museum. Part I (4). British Museum, London, 229 pp.
			if (!$matched)
			{
				if (preg_match('/(?<authorstring>.*)\s+\((?<year>[0-9]{4})[a-z]?\)(?<title>.*)\.\s+(?<publisher>\w+(\s+\w+)?),\s+(?<publoc>.*),\s+(?<pages>\d+)\s+pp./u', $p->innertext, $m))
				{
					//print_r($m);
					$matched = true;
				}
			}
		
			// Chapter
			if (!$matched)
			{
				if (preg_match('/(?<authorstring>.*)\s+\((?<year>[0-9]{4})[a-z]?\)(?<title>.*) (<em>)?In(<\/em>)?:/u', $p->innertext, $m))
				{
					//print_r($m);
					$matched = true;
				}
			}
		
		
		
			// Last ditch capture author, year, and title
			if (!$matched)
			{
				if (preg_match('/(?<authorstring>.*)\s+\((?<year>[0-9]{4})[a-z]?\)(?<title>.*)\./Uu', $p->innertext, $m))
				{
					//print_r($m);
					$matched = true;
				}
			}
		
		
		
			if ($matched)
			{
		
		
				//reference_from_matches($m, $citation);
			
				$keys = array('authorstring', 'title', 'journal', 'year', 'volume', 'issue', 'spage', 'epage', 'publisher', 'publoc');
			
				foreach ($keys as $key)
				{
					if (isset($m[$key]) && ($m[$key] != ''))
					{
						switch ($key)
						{
							case 'authorstring':						
								$authorstring = $m[$key];
							
								$authorstring = preg_replace('/\.,\s+/u', ".|", $authorstring);
								$authorstring = preg_replace('/\s+&amp;\s+/u', "|", $authorstring);
								$authorstring = preg_replace('/\s+&\s+/u', "|", $authorstring);
								$parts = explode("|", $authorstring);
								foreach ($parts as $part)
								{
									$author = new stdclass;
									
									// Parse the name
									$parts = parse_name($part);
		
									if (isset($parts['last']))
									{
										$author->family = $parts['last'];
									}
									if (isset($parts['first']))
									{
										$author->given = $parts['first'];
										
										
			
										if (array_key_exists('middle', $parts))
										{
											$author->given .= ' ' . $parts['middle'];
										}
										
										$author->given = preg_replace('/([A-Z])\.([A-Z])/u', "$1 $2", $author->given);
										$author->given = preg_replace('/\.$/u', "", $author->given);
									}
						
									if (!isset($author->family) || !isset($author->given))
									{
										$author->literal = $contributor->{'credit-name'}->value;
									}
	
									$citation->author[] = $author;
								}
								break;					
					
							case 'title':
								$citation->title = $m[$key];
								$citation->title = preg_replace('/\.$/', '', $citation->title);
								break;

							case 'journal':
								$citation->{'container-title'} = $m[$key];
								$citation->type = 'article-journal';
								break;

							case 'year':
								$citation->issued = new stdclass;
								$citation->issued->{'date-parts'} = array();
								$citation->issued->{'date-parts'}[0] = array((Integer)$m[$key]);						
								break;

							case 'volume':
							case 'issue':
								$citation->{$key} = $m[$key];
								break;
							
							case 'spage':
								$citation->page = $m[$key];
								$citation->{'page-first'} = $m[$key];
								break;

							case 'epage':
								$citation->page .= '-' . $m[$key];
								break;

							case 'publisher':
								$citation->publisher = $m[$key];
								$citation->type = 'book';
								break;

							case 'publoc':
								$citation->{'publisher-place'} = $m[$key];
								break;
							
							
							default:
							
								break;
						}
					}
				}
				
				// cleanup
				if (isset($citation->title))
				{
					if (preg_match('/\. Acta Botanica/', $citation->title))
					{
						$citation->title = str_replace('. Acta Botanica', '',  $citation->title);
						$citation->{'container-title'} = 'Acta Botanica ' . $citation->{'container-title'} ;
					}
				}
			}
		
			// URL
			if (preg_match('/Available from: (?<url>http:\/\/(.*))\s+\(accessed/Uu', $p->innertext, $m))
			{
				//print_r($m);
				$citation->URL = $m['url'];
			}
			//echo "------------------------------------------\n";
		
		
		}
		if (preg_match('/dx.doi.org\/(?<doi>.*)\b/', $p->plaintext, $m))
		{
			$citation->DOI = strtolower($m['doi']);
			$citation->{'alternative-id'} = 'DOI:' . $citation->DOI;
			//print_r($citation);
			//echo join("\t", reference_to_tsv($citation)) . "\n";
			$citation->id = $reference->_id . '/ref-' . $count++;
			$citation->cited_by[] = $reference->_id;
			output($citation);
			$citation = null;
		}
		
	
	
	}
	if ($citation)
	{
		//print_r($citation);
		//echo join("\t", reference_to_tsv($citation)) . "\n";
		$citation->id = $reference->_id . '/ref-' . $count++;
		$citation->cited_by[] = $reference->_id;
		output($citation);

	}
}


?>