<?php

// Cluster references that we think are the "same" based on string similary
// Useful for record that lack identifiers

error_reporting(E_ALL);

require_once (dirname(dirname(__FILE__)) . '/couchsimple.php');
require_once (dirname(__FILE__) . '/merge_records.php');
	

$q = 'International Code of Nomenclature for algae, fungi and plants (Melbourne Code)';

$q = preg_replace('/[:|\(|\)]/u', '', $q);

$parameters = array(
		'q'					=> $q,
		'include_docs' 		=> 'true',
		'limit' 			=> 50
	);

$url = '_design/search/_search/metadata?' . http_build_query($parameters);

$resp = $couch->send("GET", "/" . $config['couchdb_options']['database'] . "/" . $url);

$response_obj = json_decode($resp);

$records = array();

foreach ($response_obj->rows as $row)
{
	$records[] = $row->doc;
}

merge_records($records, true);
		
?>