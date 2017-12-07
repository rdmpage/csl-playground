<?php

// Add references from BioStor

require_once (dirname(dirname(__FILE__)) . '/lib.php');
require_once (dirname(dirname(__FILE__)) . '/couchsimple.php');


$force = false;
$force = true;

$ids = array(164556,164402);

foreach ($ids as $id)
{
	$url = 'http://biostor.org/api.php?id=biostor/' . $id . '&format=citeproc';
	
	$identifier = sha1('biostor/' . $id);
	
	// Do we have this already?
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
		$json = get($url);
	
		if ($json != '') {
			$obj = json_decode($json);
		
		
			// clean
			unset($obj->id);
			
			//print_r($obj);
			
			if (isset($obj->bhl_pages))
			{
				$obj->{'page-images'} = new stdclass;
				$obj->{'page-thumbnails'} = new stdclass;
				
				$count = 1;
				foreach ($obj->bhl_pages as $k => $v)
				{
					$obj->{'page-images'}->{$k} = 'http://biostor.org/documentcloud/biostor/' . $id . '/pages/' . $count . '-large';
					$obj->{'page-thumbnails'}->{$k} = 'http://biostor.org/documentcloud/biostor/' . $id . '/pages/' . $count . '-small';
					
					$count++;
				}
				
				unset($obj->bhl_pages);
				
				// thumbnail
				$url = 'http://biostor.org/documentcloud/biostor/' . $id . '/pages/1-small';
				$image = get($url);
		
				if ($image != '')
				{				
					$mime_type = 'image/png';
					$base64 = chunk_split(base64_encode($image));
					$obj->thumbnail = 'data:' . $mime_type . ';base64,' . $base64;		
				}	

			}
						
			
			if (isset($obj->text_pages))
			{
				$obj->{'page-text'} = $obj->text_pages;
				unset($obj->text_pages);
			}

			// couchdb
			$doc = new stdclass;
			
			$doc->_id = $identifier;
			$doc->cluster_id = $doc->_id;		
			
			// By default message is empty and has timestamp set to "now"
			// This means it will be at the end of the queue of things to add
			$doc->{'message-timestamp'} = date("c", time());
			$doc->{'message-modified'} 	= $doc->{'message-timestamp'};
			
			// Set source of this record
			$doc->{'message-source'} = $url;
			
			// $doc->{'message-format'} 	= 'application/vnd.crossref-citation+json';
			// MIME type is CrossRef API
			$doc->{'message-format'} = 'application/vnd.crossref-api-message+json';
			$doc->message = $obj;
			
			$resp = $couch->send("PUT", "/" . $config['couchdb_options']['database'] . "/" . urlencode($doc->_id), json_encode($doc));
			var_dump($resp);				
					
		}				
	}
}	



?>
