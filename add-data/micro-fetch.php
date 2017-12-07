<?php

// Add references in CSL from microcitation database

require_once (dirname(dirname(__FILE__)) . '/lib.php');
require_once (dirname(dirname(__FILE__)) . '/couchsimple.php');


$force = false;
$force = true;

$ids=array('10.6165/tai.2004.49(1).44');
$ids=array('http://www.jstor.org/stable/41739027');

$ids=array(
'http://www.jstor.org/stable/41739050',
'http://www.jstor.org/stable/41739049',
'http://www.jstor.org/stable/41739048',
'http://www.jstor.org/stable/41739047',
'http://www.jstor.org/stable/41739046',
'http://www.jstor.org/stable/41739045',
'http://www.jstor.org/stable/41739044',
'http://www.jstor.org/stable/41739043',
'http://www.jstor.org/stable/41739042',
'http://www.jstor.org/stable/41739041',
'http://www.jstor.org/stable/41739040',
'http://www.jstor.org/stable/41739039',
'http://www.jstor.org/stable/41739038',
'http://www.jstor.org/stable/41739037',
'http://www.jstor.org/stable/41739036',
'http://www.jstor.org/stable/41739035',
'http://www.jstor.org/stable/41739034',
'http://www.jstor.org/stable/41739033',
'http://www.jstor.org/stable/41739032',
'http://www.jstor.org/stable/41739031',
'http://www.jstor.org/stable/41739030',
'http://www.jstor.org/stable/41739029',
'http://www.jstor.org/stable/41739028',
'http://www.jstor.org/stable/41739027',
'http://www.jstor.org/stable/41739026',
'http://www.jstor.org/stable/41739025',
'http://www.jstor.org/stable/41739024',
'http://www.jstor.org/stable/41739023',
'http://www.jstor.org/stable/41739022',
'http://www.jstor.org/stable/41739021',
'http://www.jstor.org/stable/41739020',
'http://www.jstor.org/stable/41739019',
'http://www.jstor.org/stable/41739018',
'http://www.jstor.org/stable/41739017',
'http://www.jstor.org/stable/41739016',
'http://www.jstor.org/stable/41739015',
'http://www.jstor.org/stable/41739014',
'http://www.jstor.org/stable/41739013',
'http://www.jstor.org/stable/41739012',
'http://www.jstor.org/stable/41739011',
'http://www.jstor.org/stable/41739010',
'http://www.jstor.org/stable/41739009',
'http://www.jstor.org/stable/41739008',
'http://www.jstor.org/stable/41739082',
'http://www.jstor.org/stable/41739083',
'http://www.jstor.org/stable/41739084',
'http://www.jstor.org/stable/41739085',
'http://www.jstor.org/stable/41739086',
'http://www.jstor.org/stable/41739087',
'http://www.jstor.org/stable/41739088',
'http://www.jstor.org/stable/41739089',
'http://www.jstor.org/stable/41739090',
'http://www.jstor.org/stable/41739091',
'http://www.jstor.org/stable/41739092',
'http://www.jstor.org/stable/41739093',
'http://www.jstor.org/stable/41739094',
'http://www.jstor.org/stable/41739095',
'http://www.jstor.org/stable/41739096',
'http://www.jstor.org/stable/41739097',
'http://www.jstor.org/stable/41739098',
'http://www.jstor.org/stable/41739099',
'http://www.jstor.org/stable/41739100',
'http://www.jstor.org/stable/41739051',
'http://www.jstor.org/stable/41739052',
'http://www.jstor.org/stable/41739053',
'http://www.jstor.org/stable/41739054',
'http://www.jstor.org/stable/41739055',
'http://www.jstor.org/stable/41739056',
'http://www.jstor.org/stable/41739057',
'http://www.jstor.org/stable/41739058',
'http://www.jstor.org/stable/41739059'
);

$ids=array(
'http://www.jstor.org/stable/42906136',
'http://www.jstor.org/stable/23641389',
'http://www.jstor.org/stable/981675',
'http://www.jstor.org/stable/41762153',
'http://www.jstor.org/stable/41941447',
'http://www.jstor.org/stable/42906135',
'http://www.jstor.org/stable/42906183'



);

$ids=array(
'http://www.jstor.org/stable/4103256'
);
$ids=array(
'http://www.jstor.org/stable/4110914',
'http://www.jstor.org/stable/4129959',
'http://www.jstor.org/stable/4110426'

);

$ids=array(
'http://www.jstor.org/stable/23726861',
'10.2307/3667655',
'10.5252/a2011n1a4',
'10.6165/tai.2014.59.4.368',
'http://ejournal.sinica.edu.tw/bbas/content/2008/1/Bot491-12/',
'http://www.jstor.org/stable/20443326'
);

// Kew 
$ids=array(
"http://www.jstor.org/stable/20443315",
"http://www.jstor.org/stable/20443316",
"http://www.jstor.org/stable/20443317",
"http://www.jstor.org/stable/20443318",
"http://www.jstor.org/stable/20443319",
"http://www.jstor.org/stable/20443320",
"http://www.jstor.org/stable/20443321",
"http://www.jstor.org/stable/20443322",
"http://www.jstor.org/stable/20443323",
"http://www.jstor.org/stable/20443324",
"http://www.jstor.org/stable/20443325",
"http://www.jstor.org/stable/20443326",
"http://www.jstor.org/stable/20443327",
"http://www.jstor.org/stable/20443328",
"http://www.jstor.org/stable/20443329",
"http://www.jstor.org/stable/20443330",
"http://www.jstor.org/stable/20443331",
"http://www.jstor.org/stable/20443332",
"http://www.jstor.org/stable/20443333",
"http://www.jstor.org/stable/20443334",
"http://www.jstor.org/stable/20443335",
"http://www.jstor.org/stable/20443336",
"http://www.jstor.org/stable/20443337",
"http://www.jstor.org/stable/20443338",
"http://www.jstor.org/stable/20443339",
"http://www.jstor.org/stable/20443340",
"http://www.jstor.org/stable/20443341",
"http://www.jstor.org/stable/20443342",
"http://www.jstor.org/stable/20443343",
"http://www.jstor.org/stable/20443346",
"http://www.jstor.org/stable/20443347",
"http://www.jstor.org/stable/20443348",
"http://www.jstor.org/stable/20443349",
"http://www.jstor.org/stable/20443350",
"http://www.jstor.org/stable/20443351",
"http://www.jstor.org/stable/20443352",
"http://www.jstor.org/stable/20443353",
"http://www.jstor.org/stable/20443354",
"http://www.jstor.org/stable/20443355",
"http://www.jstor.org/stable/20443356",
"http://www.jstor.org/stable/20443357",
"http://www.jstor.org/stable/20443358",
"http://www.jstor.org/stable/20443359",
"http://www.jstor.org/stable/20443360",
"http://www.jstor.org/stable/20443363",
"http://www.jstor.org/stable/20443364",
"http://www.jstor.org/stable/20443365",
"http://www.jstor.org/stable/20443366",
"http://www.jstor.org/stable/20443367",
"http://www.jstor.org/stable/20443368",
"http://www.jstor.org/stable/20443369",
"http://www.jstor.org/stable/20443370",
"http://www.jstor.org/stable/20443371",
"http://www.jstor.org/stable/20443372",
"http://www.jstor.org/stable/20443373",
"http://www.jstor.org/stable/20443374",
"http://www.jstor.org/stable/20443375",
"http://www.jstor.org/stable/20443376",
"http://www.jstor.org/stable/20443377",
"http://www.jstor.org/stable/20443378",
"http://www.jstor.org/stable/20443379",
"http://www.jstor.org/stable/20443380",
"http://www.jstor.org/stable/20443381",
"http://www.jstor.org/stable/20443382",
"http://www.jstor.org/stable/20443383",
"http://www.jstor.org/stable/20443384",
"http://www.jstor.org/stable/20443388",
"http://www.jstor.org/stable/20443389",
"http://www.jstor.org/stable/20443390",
"http://www.jstor.org/stable/20443391",
"http://www.jstor.org/stable/20443392",
"http://www.jstor.org/stable/20443393",
"http://www.jstor.org/stable/20443394",
"http://www.jstor.org/stable/20443395",
"http://www.jstor.org/stable/20443396",
"http://www.jstor.org/stable/20443397",
"http://www.jstor.org/stable/20443398",
"http://www.jstor.org/stable/20443399",
"http://www.jstor.org/stable/20443400",
"http://www.jstor.org/stable/20443401",
"http://www.jstor.org/stable/20443402",
"http://www.jstor.org/stable/20443403"
);

$ids=array(
"http://www.jstor.org/stable/23725536"
);

// zootaxa with PDF thumbnail
$ids=array('http://www.mapress.com/zootaxa/2012/f/z03161p066f.pdf');

$ids=array('http://ejournal.sinica.edu.tw/bbas/content/2008/1/Bot491-12/');

$ids=array('10.6165/tai.2014.59.4.368');

$ids=array('http://natuurtijdschriften.nl/record/591719');
$ids=array('http://natuurtijdschriften.nl/record/591676');
$ids=array('http://www.repository.naturalis.nl/record/525488');
$ids=array('10.3767/000651908x608106');
$ids=array('http://www.biodiversitylibrary.org/part/127339');
$ids=array('http://natuurtijdschriften.nl/record/597326');

$ids=array('http://www.insect.org.cn/EN/abstract/abstract10989.shtml');
$ids=array('http://www.tci-thaijo.org/index.php/ThaiForestBulletin/article/view/24345');

$ids=array('http://fbc.pionier.net.pl/id/oai:rcin.org.pl:58521');

$ids=array('http://www.repository.naturalis.nl/record/311947');
$ids=array('http://www.repository.naturalis.nl/record/315884');

$ids=array('http://www.mapress.com/zootaxa/2010/f/z02496p037f.pdf');

$ids=array('10.11646/zootaxa.1422.1.1');
$ids=array('http://old.ssbg.asu.ru/turcz/turcz198-7-21.pdf');
$ids=array('http://lkcnhm.nus.edu.sg/nus/pdf/PUBLICATION/Raffles%20Bulletin%20of%20Zoology/Past%20Volumes/RBZ%2054(1)/54rbz011-020.pdf');
$ids=array('http://lkcnhm.nus.edu.sg/nus/pdf/PUBLICATION/Raffles%20Bulletin%20of%20Zoology/Past%20Volumes/RBZ%2061(2)/61rbz561-569.pdf');
$ids=array('http://lkcnhm.nus.edu.sg/nus/pdf/PUBLICATION/Raffles%20Bulletin%20of%20Zoology/Past%20Volumes/RBZ%2061(2)/61rbz641-649.pdf');

$ids=array('http://lkcnhm.nus.edu.sg/nus/pdf/PUBLICATION/Raffles%20Bulletin%20of%20Zoology/Past%20Volumes/RBZ%2061(2)/61rbz705-725.pdf');

$ids = array(
'http://lkcnhm.nus.edu.sg/nus/pdf/PUBLICATION/Raffles%20Bulletin%20of%20Zoology/Past%20Volumes/RBZ%2060(2)/60rbz279-287.pdf',
'http://lkcnhm.nus.edu.sg/nus/pdf/PUBLICATION/Raffles%20Bulletin%20of%20Zoology/Past%20Volumes/RBZ%2060(2)/60rbz289-311.pdf',
'http://lkcnhm.nus.edu.sg/nus/pdf/PUBLICATION/Raffles%20Bulletin%20of%20Zoology/Past%20Volumes/RBZ%2060(2)/60rbz361-397.pdf'
);

$ids = array(
'http://lkcnhm.nus.edu.sg/nus/pdf/PUBLICATION/Raffles%20Bulletin%20of%20Zoology/Past%20Volumes/RBZ%2061(2)/61rbz749-761.pdf'
);

foreach ($ids as $id)
{
	$go = true;
	
	$url = 'http://localhost/~rpage/microcitation/www/citeproc-api.php?guid=' . urlencode($id);
	
	// echo $url . "\n";
	// exit();
	
	$identifier = sha1($id);

	
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
		
			// post process

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
