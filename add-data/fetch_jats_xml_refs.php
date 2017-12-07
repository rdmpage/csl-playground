<?php

// Fetch lists of CSL format references from glitch

require_once (dirname(dirname(__FILE__)) . '/lib.php');
require_once (dirname(dirname(__FILE__)) . '/couchsimple.php');


$queue = array('https://zookeys.pensoft.net/article/13535/download/xml/');

$queue = array('https://phytokeys.pensoft.net/article/11345/download/xml/');

// scorpions
$queue = array('https://zookeys.pensoft.net/article/12206/download/xml/');

// An illustrated checklist of the genus Elymnias Hübner, 1818 (Nymphalidae, Satyrinae)
$queue = array('https://zookeys.pensoft.net/article/12579/download/xml/');

// The collection of Bathynellacea specimens of MNCN (CSIC) Madrid: microscope slices and DNA extract
$queue = array('https://zookeys.pensoft.net/article/11543/download/xml/');

// A checklist of land snails from the west coast islands of Sabah, Borneo (Mollusca, Gastropoda)
$queue = array('https://zookeys.pensoft.net/article/12422/download/xml/');

// A preliminary checklist of the freshwater snails of Sabah (Malaysian Borneo) deposited in the BORNEENSIS collection, Universiti Malaysia Sabah
$queue = array('https://zookeys.pensoft.net/article/12544/download/xml/');

// Annotated type catalogue of the Bulimulidae (Mollusca, Gastropoda, Orthalicoidea) in the Natural History Museum, London
$queue = array('https://zookeys.pensoft.net/article/3584/download/xml/');


// Classification of weevils as a data-driven science: leaving opinion behind
$queue = array('https://zookeys.pensoft.net/article/4032/download/xml/');

// Classification of weevils as a data-driven science: leaving opinion behind
$queue = array('https://zookeys.pensoft.net/article/4032/download/xml/');

// A new species of Mongolodiaptomus Kiefer, 1938 from northeast Thailand and a key to the species (Crustacea, Copepoda, Calanoida, Diaptomidae)
$queue = array('https://zookeys.pensoft.net/article/13941/download/xml/');

// Roa rumsfeldi, a new butterflyfish (Teleostei, Chaetodontidae) from mesophotic coral ecosystems of the Philippines
$queue = array('https://zookeys.pensoft.net/article/20404/download/xml/');

// Three new cavernicolous species of the millipede genus Trichopeltis Pocock, 1894 from southern China (Diplopoda, Polydesmida, Cryptodesmidae)
$queue = array('https://zookeys.pensoft.net/article/20025/download/xml/');

// Revision of the family Carabodidae (Acari, Oribatida) XII. Yoshiobodes camerunensis sp. n. and Rugocepheus costaricensis sp. n.
$queue = array('https://zookeys.pensoft.net/article/14807/download/xml/');

// A species checklist of the subgenus Culicoides (Avaritia) in China, with a description of a new species (Diptera, Ceratopogonidae)
$queue = array('https://zookeys.pensoft.net/article/13535/download/xml/');

// 10.3897/phytokeys.20.3948
$queue = array('https://phytokeys.pensoft.net/article/1454/download/xml/');

$queue = array('https://phytokeys.pensoft.net/article/1454/download/xml/');

// An online photographic catalog of primary types of Platygastroidea (Hymenoptera) in the National Museum of Natural History, Smithsonian Institution
$queue = array('https://jhr.pensoft.net/article/10774/download/xml/');

// Subgeneric classification and biology of the leafcutter and dauber bees (genus Megachile Latreille) of the western Palearctic (Hymenoptera, Apoidea, Megachilidae)
$queue = array('https://jhr.pensoft.net/article/11255/download/xml/');

// Insect species described by Karl-Johan Hedqvist
$queue = array('https://jhr.pensoft.net/article/9296/download/xml/');

// A review of the land snail genus Alycaeus (Gastropoda, Alycaeidae) in Peninsular Malaysia
$queue = array('https://zookeys.pensoft.net/article/14706/download/xml/');


// Checklist of the Clubiona japonica-group spiders, with the description of a new species from China (Araneae, Clubionidae)
$queue = array('https://zookeys.pensoft.net/article/14645/download/xml/');

// Hyboptera Chaudoir, 1872 of the Cryptobatida group of subtribe Agrina: A taxonomic revision with notes on their ways of life (Insecta, Coleoptera, Carabidae, Lebiini)
$queue = array('https://zookeys.pensoft.net/article/15113/download/xml/');

// Salangathelphusa peractio, a new species of lowland freshwater crab from Pulau Langkawi, Peninsular Malaysia (Crustacea, Brachyura, Gecarcinucidae)
$queue = array('https://zookeys.pensoft.net/article/20621/download/xml/');

// Checklist of the freshwater fishes of Colombia: a Darwin Core alternative to the updating problem
$queue = array('https://zookeys.pensoft.net/article/13897/download/xml/');

// A new species of Physoctonus Mello-Leitão, 1934 from the ‘Campos formations’ of southern Amazonia (Scorpiones, Buthidae)
$queue = array('https://zookeys.pensoft.net/article/20187/download/xml/');

// Diversity and biogeography of land snails (Mollusca, Gastropoda) in the limestone hills of Perak, Peninsular Malaysia
$queue = array('https://zookeys.pensoft.net/article/12999/download/xml/');


// Ridleyandra merohmerea (Gesneriaceae), a new species from Kelantan, Peninsular Malaysia
$queue = array('https://phytokeys.pensoft.net/article/20344/download/xml/');

// Semiaquilegia quelpaertensis (Ranunculaceae), a new species from the Republic of Korea
$queue = array('https://phytokeys.pensoft.net/article/21004/download/xml/');

// Solanum jobsonii, a novel andromonoecious bush tomato species from a new Australian national park
$queue = array('https://phytokeys.pensoft.net/article/12106/download/xml/');

// Five new species of Syzygium (Myrtaceae) from Sulawesi, Indonesia
$queue = array('https://phytokeys.pensoft.net/article/13488/download/xml/');

// Epitypification with an emended description of Tropidia connata (Orchidaceae, Epidendroideae, Tropidieae)
//$queue = array('https://phytokeys.pensoft.net/article/12304/download/xml/');

// Annotated Checklist of the Terrestrial Gastropods of Nepal
$queue = array('https://zookeys.pensoft.net/article/4985/download/xml/');


$queue = array('http://mycokeys.pensoft.net/lib/ajax_srv/article_elements_srv.php?action=download_xml&item_id=12666');

$force = true;

while (count($queue) > 0)
{
	$url = array_pop($queue);
	
	// to do, could add DOIs to queue
	
	// service to retrieve references from XML
	$url = 'https://glow-pajama.glitch.me/feed?q=' . urlencode($url);
	
	$json = get($url);

	if ($json != '')
	{
		$obj = json_decode($json);
	
		print_r($obj);
		
		/*
		// Add any reference template links to queue
		if (isset($obj->links))
		{		
			foreach ($obj->links as $link)
			{
				if (!in_array($link, $queue)) 
				{
					$queue[] = $link;
				}
			}
		}	
		*/
		
		foreach ($obj->cites as $citation)
		{
		
			// generate an identifier (need rules for this)
			
			// SHA1 of JSON string
			$json_string = json_encode($citation);			
			$identifier = sha1($json_string);	
			
			// SHA1 of identifier
			$identifier = sha1($citation->id);
			
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
			
				$resp = $couch->send("PUT", "/" . $config['couchdb_options']['database'] . "/" . urlencode($doc->_id), json_encode($doc));
				var_dump($resp);					
			}	
		}		
	}
}

						

