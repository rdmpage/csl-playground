<?php

// ORCID API

// Fetch references from ORCID API, convert to CSL and add to CouchDB

require_once (dirname(dirname(__FILE__)) . '/lib.php');
require_once (dirname(dirname(__FILE__)) . '/nameparse.php');
require_once (dirname(dirname(__FILE__)) . '/fingerprint.php');
//require_once (dirname(dirname(__FILE__)) . '/shared/crossref.php');

require_once (dirname(dirname(__FILE__)) . '/couchsimple.php');


$force = false;
$force = true;


/*
  Problems with ORCID:
  
  1. Only the person whose profile this is has is identified by an ORCID. Coauthors
     with ORCIDs don't have them (sigh).
     
  2. Many works lack DOIs in the ORCID profile, even if they actually have them. Need to
     think about whether we go hunting for these.
*/

//----------------------------------------------------------------------------------------
function orcid_works($obj, $lookup_works)
{
	global $config;	
	global $couch;	
	global $force;
	
	// ORCID
	$orcid = $obj->{'orcid-profile'}->{'orcid-identifier'}->uri;	
	
	// Extract works and create CSL JSON objects for each one
	if (isset($obj->{'orcid-profile'}->{'orcid-activities'}))
	{
		$works = $obj->{'orcid-profile'}->{'orcid-activities'}->{'orcid-works'}->{'orcid-work'};
		
		if ($works)
		{
			foreach ($works as $work)
			{
				$doc = new stdclass;
				
				// Use put-code as bnode identifier for this work
				$doc->_id = $orcid . '-' . $work->{'put-code'};
				$doc->{'message-format'} = 'application/vnd.crossref-api-message+json';		
			
				// Get reference details--------------------------------------------------
				$reference = new stdclass;
				$reference->title = $work->{'work-title'}->{'title'}->value;
				
				$reference->title = strip_tags($reference->title);
	
				// Journal?
				if (isset($work->{'journal-title'}->value))
				{
					$reference->{'container-title'}[] = $work->{'journal-title'}->value;
					$reference->type = 'article-journal';
				}		
		
				if (isset($work->{'publication-date'}))
				{
					$reference->issued = new stdclass;
					$reference->issued->{'date-parts'} = array();
					if (isset($work->{'publication-date'}->{'year'}->value))
					{
						$reference->issued->{'date-parts'}[] = array((Integer)$work->{'publication-date'}->{'year'}->value);
					}
				}		

				// Parse BibTex-----------------------------------------------------------
				if (isset($work->{'work-citation'}->citation))
				{
					$bibtext = $work->{'work-citation'}->citation;
		
					if (!isset($work->{'journal-title'}->value))
					{
						if (preg_match('/journal = \{(?<journal>.*)\}/Uu', $bibtext, $m))
						{
							$reference->{'container-title'}[] = $m['journal'];
							$reference->type = 'journal-article';
						}
					}
	
					if (!isset($reference->issued))
					{
						$reference->issued = new stdclass;
						$reference->issued->{'date-parts'} = array();
						if (preg_match('/year = \{(?<year>[0-9]{4})\}/', $bibtext, $m))
						{
							$reference->issued->{'date-parts'}[] = array($m['year']);
						}
					}
			
					if (preg_match('/volume = \{(?<volume>.*)\}/Uu', $bibtext, $m))
					{
						$reference->volume = $m['volume'];
					}

					if (preg_match('/number = \{(?<issue>.*)\}/Uu', $bibtext, $m))
					{
						$reference->issue = $m['issue'];
					}

					// pages = {41-68}
					if (preg_match('/pages = \{(?<pages>.*)\}/Uu', $bibtext, $m))
					{
						$reference->page = $m['pages'];
						$reference->page = str_replace('--', '-', $reference->page);
						
						if (preg_match('/(?<spage>.*)-(?<epage>.*)/', $reference->page, $m))
						{
							$reference->{'page-first'} = $m['spage'];
						}
						else
						{
							$reference->{'page-first'} = $reference->page;
						}
						
						
					}
				}
				
				//------------------------------------------------------------------------
				if (isset($work->{'work-contributors'}))
				{		
					$reference->author = array();
				
					// OK, since this person is an author, find the best matching name amongst
					// and use the ORCID for that person. The others will be blank nodes.
					// ORCID has a field "contributor-orcid" but this always seems to be null :(

					foreach ($work->{'work-contributors'}->{'contributor'} as $contributor)
					{
						$author = new stdclass;
											
						// Parse the name
						$parts = parse_name($contributor->{'credit-name'}->value);
		
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
						}
						
						if (!isset($author->family) || !isset($author->given))
						{
							$author->literal = $contributor->{'credit-name'}->value;
						}
					
						$reference->author[] = $author;
					}
				}
				
				// match to ORCID
				// code from recon-15
				if (isset($reference->author))
				{
				
					$target = new stdclass;
					$target->name = '';

					$personal_details = $obj->{'orcid-profile'}->{'orcid-bio'}->{'personal-details'};

					if (isset($personal_details->{'given-names'}))
					{
						$target->name = $personal_details->{'given-names'}->{'value'};
					}

					if (isset($personal_details->{'family-name'}))
					{
						$target->name .= $personal_details->{'family-name'}->{'value'};
					}

					if (isset($personal_details->{'credit-name'}))
					{
						$target->credit_name = $personal_details->{'credit-name'}->{'value'};
					}

					if (isset($personal_details->{'other-names'}))
					{
						foreach ($personal_details->{'other-names'}->{'other-name'} as $other_name)
						{
							$target->other_names[] = $other_name->value;
						}
					}				
					$min_d = 100;
					$hit = -1;

					$n = count($reference->author);
					for ($i = 0; $i < $n; $i++)
					{
						$name = '';
						if (isset($reference->author[$i]->literal))
						{
							$name = $reference->author[$i]->literal;
						}
						else
						{
						 $name = $reference->author[$i]->given . ' ' . $reference->author[$i]->family;
						}
										
						if (isset($target->name))
						{
							$d = levenshtein(finger_print($name), finger_print($target->name));

							if ($d < $min_d)
							{
								$min_d = $d;
								$hit = $i;
							}
						}

						if (isset($target->credit_name))
						{
							$d = levenshtein(finger_print($name), finger_print($target->credit_name));

							if ($d < $min_d)
							{
								$min_d = $d;
								$hit = $i;
							}
						}

					}
					
					if ($hit != -1)
					{
						$reference->author[$hit]->ORCID = str_replace('http://orcid.org/', '', $orcid);
					}						
				}
		
				// Identifiers------------------------------------------------------------
				if (isset($work->{'work-external-identifiers'}))
				{
					$reference->{'alternative-id'} = array();
				
					foreach ($work->{'work-external-identifiers'}->{'work-external-identifier'} as $identifier)
					{
						switch ($identifier->{'work-external-identifier-type'})
						{
							case 'DOI':
								$value = $identifier->{'work-external-identifier-id'}->value;
								// clean
								$value = preg_replace('/^doi:/', '', $value);
								$value = preg_replace('/\.$/', '', $value);
								$value = preg_replace('/\s+/', '', $value);
					
								// DOI
								$reference->DOI = strtolower($value);
								
								$reference->{'alternative-id'}[] = 'DOI:' . $reference->DOI;								
								break;
						
							case 'ISBN':
								$value = $identifier->{'work-external-identifier-id'}->value;
						
								if ($work_type == 'BOOK')
								{
									$reference->isbn[] = $value;
								}		
								
								$reference->{'alternative-id'}[] = 'ISBN:' . $value;										
								break;

							case 'ISSN':
								$value = $identifier->{'work-external-identifier-id'}->value;
								$parts = explode(";", $value);
						
								$reference->ISSN[] = $parts;
								break;

							case 'PMC':
								$value = $identifier->{'work-external-identifier-id'}->value;
								$reference->PMC = $value;
								
								$reference->{'alternative-id'}[] = 'PMC:' . $value;								
								break;

							case 'PMID':
								$value = $identifier->{'work-external-identifier-id'}->value;
								$reference->PMID = $value;
								
								$reference->{'alternative-id'}[] = 'PMID:' . $value;								
								break;

							case 'WOSUID':
								$value = $identifier->{'work-external-identifier-id'}->value;
								$reference->WOSUID = $value;
								
								$reference->{'alternative-id'}[] = 'WOS:' . $value;								
								break;
					
							default:
								break;
						}
					}
					
					if (count($reference->{'alternative-id'}) == 0)
					{
						unset($reference->{'alternative-id'});
					}
				}
	
				// URL
				// These seem to be mostly WOS URLs so ignore
				/*
				if (isset($work->{'url'}))
				{
					if (isset($work->{'url'}->{'value'}))
					{
						$urls = explode(",", $work->{'url'}->{'value'});
						$reference->URL = $urls[0];
					}
				}
				*/
				
				$doc->message = $reference;
				
				$doc->{'message-timestamp'} = date("c", time());
				$doc->{'message-modified'} 	= $doc->{'message-timestamp'};
				
				//print_r($doc);
				//exit();
				
				// add to database--------------------------------------------------------


				$identifier = sha1($doc->_id);
				$doc->_id = $identifier;
				$doc->cluster_id = $doc->_id;

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
					$resp = $couch->send("PUT", "/" . $config['couchdb_options']['database'] . "/" . urlencode($doc->_id), json_encode($doc));
					var_dump($resp);							
				}	
	
			}
		}
	}	
	
}


//----------------------------------------------------------------------------------------
function orcid_fetch($orcid, $lookup_works = false)
{
	$data = null;
	
	if (1)
	{
		$url = 'http://pub.orcid.org/v1.2/' . $orcid . '/orcid-profile';
		$json = get($url, '', 'application/orcid+json');
	}
	else
	{
		$json = file_get_contents(dirname(__FILE__) . '/' . $orcid . '.json');
	}	
	
	
	if ($json != '')
	{
		$data = new stdclass;
		
		$data->{'message-format'} = 'application/vnd.orcid+json';		
		$data->message = json_decode($json);
		
		orcid_works($data->message, $lookup_works);
		
		// for now (debugging)
		//unset($data->content);

		//print_r($data);
	}
	
	return $data;
}


if (1)
{
	//orcid_fetch('0000-0002-7573-096X'); // Cameron D. Siler

	//orcid_fetch('0000-0002-7941-346X'); // Christopher Barker (no public data)

	//orcid_fetch('0000-0001-8916-5570'); // Nick Golding

	//orcid_fetch('0000-0003-0566-372X'); // J. J. Wieringa
	
	orcid_fetch('0000-0001-7698-3945'); // Sandy
}


?>
