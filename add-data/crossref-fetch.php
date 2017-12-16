<?php

require_once (dirname(dirname(__FILE__)) . '/lib.php');
require_once (dirname(dirname(__FILE__)) . '/config.inc.php');
require_once (dirname(dirname(__FILE__)) . '/couchsimple.php');

//require_once (dirname(__FILE__) . '/ncbi.php');


$force = false;
$force = true;

//----------------------------------------------------------------------------------------
// CrossRef API
function get_work($doi, $augment = true)
{
	$doc = null;
	
	$url = 'https://api.crossref.org/v1/works/http://dx.doi.org/' . $doi;
	
	$json = get($url);
	
	if ($json != '')
	{
		$obj = json_decode($json);
		if ($obj)
		{
			$doc = new stdclass;
			
			$doc->{'message-format'} = 'application/vnd.crossref-api-message+json';
			
			$doc->_id = sha1($doi);
			$doc->cluster_id = $doc->_id;		
			
			$doc->{'message-timestamp'} = date("c", time());
			$doc->{'message-modified'} 	= $doc->{'message-timestamp'};
						
			$doc->message = $obj->message;
			
			// always have DOI as an alternative id to help clustering later
			if (!isset($doc->message->{'alternative-id'}))
			{			
				$doc->message->{'alternative-id'} = array();
			}
			if (!in_array('DOI:' . strtolower($doc->message->DOI), $doc->message->{'alternative-id'}))
			{
				$doc->message->{'alternative-id'}[] = 'DOI:' . strtolower($doc->message->DOI);
			}
			
			
			// augment
			
			// do we have XML?
			$xml_url = null;
			if (isset($doc->message->link))
			{
				foreach ($doc->message->link as $link)
				{
					if (isset($link->{'content-type'}))
					{
						if ($link->{'content-type'} == 'application/xml')
						{
							$xml_url = $link->URL;
							$xml_url = str_replace('&amp;', '&', $xml_url);	
						}
					}
				}
			}
			if ($xml_url)
			{
				$xml = get($xml_url);
				if ($xml)
				{
					$doc->message->xml = $xml;
				}
			}
			
				
		}
	}
	return $doc;
}

//----------------------------------------------------------------------------------------

$force = false;
$force = true;

$dois=array('10.3897/zookeys.674.11435'); // lacewing
$dois=array('10.3897/zookeys.682.12999');
$dois=array('10.3897/zookeys.692.14706');
$dois=array('10.1590/1982-0224-20130236'); // abstract, PDF, no link to XML

$dois=array('10.1016/s0254-6299(15)30848-6');

$dois=array('10.1177/194008291600900219');

$dois=array('10.1038/ng.475'); // Cucumber genome

$dois=array('10.1017/s0960428615000177');

$dois=array('10.4039/n04-096');

$dois=array('10.3161/00034541ANZ2016.66.3.010');

// Verrucostoma, a new genus in the Bionectriaceae from the Bonin Islands, Japan
// Taylor & Francis with author affiliations and references cited
$dois=array('10.3852/09-137');

// A 130-Year-Old Specimen Brought Back to Life: A Lost Species of Bee-Mimicking Clearwing Moth, Heterosphecia tawonoides (Lepidoptera: Sesiidae: Osminiini), Rediscovered in Peninsular Malaysia’s Primary Rainforest
// ORCID and refs
$dois=array('10.1177/1940082917739774'); 

foreach ($dois as $doi)
{
	$doi = strtolower($doi);
	$identifier = sha1($doi);

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
		$doc = get_work($doi);
	
		if ($doc)
		{
			print_r($doc);
		
		
			$resp = $couch->send("PUT", "/" . $config['couchdb_options']['database'] . "/" . urlencode($doc->_id), json_encode($doc));
			var_dump($resp);							
		}
		else
		{
			echo "DOI $doi not found\n";
		}
	}	

}

?>
