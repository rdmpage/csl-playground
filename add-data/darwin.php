<?php

// Darwin is a mess, lacks easy way to flag start of ref.

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
	
		//$resp = $couch->send("PUT", "/" . $config['couchdb_options']['database'] . "/" . urlencode($doc->_id), json_encode($doc));
		//var_dump($resp);					
	}	


}


$urls = array(
'http://www.ojs.darwin.edu.ar/index.php/darwiniana/article/view/692'
);

foreach ($urls as $url)
{

	$html = file_get_contents(dirname(__FILE__) . '/6.html');
	//$html = get($url);
	

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
	
	

	print_r($reference);
	exit();

	$citation = null;
	$count = 1;

	$ps = $dom->find('div div p');
	foreach ($ps as $p)
	{
		echo $p->plaintext . "\n";
		
		$citation = new stdclass;
		$citation->id = $reference->_id . '/ref-' . $count++;
		$citation->cited_by[] = $reference->_id;
		$citation->type = 'unknown';
		$citation->unstructured = $p->plaintext;
		$citation->unstructured = str_replace ('&amp;', '&', $citation->unstructured);
		$citation->unstructured = str_replace ("\n", ' ', $citation->unstructured);
		
		$matched = false;
		
		if (!$matched)
		{
			if (preg_match('/(?<authorstring>.*)\s+(?<year>[0-9]{4})[a-z]?\.\s*(?<title>.*)\.\s+(?<journal>.*)\s+(?<volume>\d+)(\((?<issue>.*)\))?:\s+(?<spage>\d+)[–|-](?<epage>\d+)/u', $p->innertext, $m))
			{
				//print_r($m);
				$matched = true;
			}
		}

		// Basic author, year, title
		if (!$matched)
		{
			if (preg_match('/(?<authorstring>.*)\s+(?<year>[0-9]{4})[a-z]?\.\s*(?<title>.*)\./Uu', $p->innertext, $m))
			{
				// print_r($m);
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
						
							/*
							$authorstring = preg_replace('/\.,\s+/u', ".|", $authorstring);
							$authorstring = preg_replace('/\s+&amp;\s+/u', "|", $authorstring);
							$authorstring = preg_replace('/\s+&\s+/u', "|", $authorstring);
							$parts = explode("|", $authorstring);
							foreach ($parts as $part)
							{
								$author = new stdclass;
								
								// add comma to name
								$part = preg_replace('/^(\w+)\s+/u', "$1, ", $part);
								
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
									$author->given = preg_replace('/([A-Z])\.([A-Z])/u', "$1 $2", $author->given);
									$author->given = preg_replace('/\.$/u', "", $author->given);
								}
					
								if (!isset($author->family) || !isset($author->given))
								{
									$author->literal = $contributor->{'credit-name'}->value;
								}

								$citation->author[] = $author;
							}
							*/
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
		}	
	
	
		if (preg_match('/dx.doi.org\/(?<doi>.*)\b/', $p->plaintext, $m))
		{
			$citation->DOI = $m['doi'];
			$citation->{'alternative-id'}[] = 'DOI:' . $citation->DOI;
		}		
		
		
		//print_r($citation);
		
		output($citation);

	}
}


?>