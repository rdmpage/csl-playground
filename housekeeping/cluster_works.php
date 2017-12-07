<?php

// Cluster references that we think are the "same"

error_reporting(E_ALL);

require_once (dirname(dirname(__FILE__)) . '/couchsimple.php');
require_once (dirname(__FILE__) . '/merge_records.php');

//----------------------------------------------------------------------------------------
// get list of things to test for match

// what's the best way to do this?

// just take a list of recently added records, generate keys to search for?

// keys to sewarch form such as hashes, could be time-stamped, so those that have changed
// will be re-examined

//----------------------------------------------------------------------------------------


// look up hash, if we get hits then test
$hash = array(2014,12,707); // Trichomycterus venulosus
//$hash = array(1956,32,81); // Notes on Alycaeus
//$hash = array(1902,9,68);

$hash = array(2004,49,44);

// share an identifier - if share a DOI or other guid then we merge

// author year - use as "canopy" for clustering

// container - volume - page - another version of the hash

$url = '_design/match/_view/year-volume-page?key=' . urlencode(json_encode($hash)) . '&reduce=false&include_docs=true';

/*
if ($config['stale'])
{
	$url .= '&stale=ok';
}	
*/
	
$resp = $couch->send("GET", "/" . $config['couchdb_options']['database'] . "/" . $url);
	
$response_obj = json_decode($resp);

print_r($response_obj);

$records = array();

foreach ($response_obj->rows as $row)
{
	$records[] = $row->doc;
}


merge_records($records);



// build a graph?


// components are clusters


// update existing clusters that contain these records

// if cluster is a subset of existing cluster, add
// if new cluster spans > 1 existing cluster

// or, we get all members of any existing cluster for these records, delete them, then rebuild new clusters




// OK, merge these records


// for each cluster create a new cluster object that lists the records (and eventually we merge them),
// but for now simply take data from first one
// need to be able to index single records and clusters

// for each component create a cluster object (array of works)
// need to alter views to handle this.  doc.message is null, 


?>

