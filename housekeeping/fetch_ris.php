<?php

error_reporting(E_ALL);

// Dump RIS for a journal

require_once (dirname(dirname(__FILE__)) . '/couchsimple.php');

$rows_per_page = 10;
$skip = 0;

$done = false;

$journal = 'Malakozoologische Blätter';

while (!$done)
{
	$resp = $couch->send("GET", "/" . $config['couchdb_options']['database']
		. "/_design/export/_view/ris-by-container" 
		. '?key=' . urlencode('"' . addcslashes($journal, '"') . '"')
		. "&skip=$skip&limit=$rows_per_page"
		);	
	
	$articles = json_decode($resp);
	
	foreach ($articles->rows as $row)
	{
		echo $row->value . "\n\n";
	}
	
	$skip += $rows_per_page;

	$done = (count($articles->rows) == 0);
}
	
?>
