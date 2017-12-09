<?php

// Support for clustering references

error_reporting(E_ALL);

require_once (dirname(dirname(__FILE__)) . '/couchsimple.php');
require_once (dirname(dirname(__FILE__)) . '/fingerprint.php');
require_once (dirname(dirname(__FILE__)) . '/lcs.php');

//----------------------------------------------------------------------------------------
// Sets
$parents = array();

function makeset($x) {
	global $parents;
	
	$parents[$x] = $x;
}

function find($x) {
	global $parents;
	
	if ($x == $parents[$x]) {
		return $x;
	} else {
		return find($parents[$x]);
	}
}

function union($x, $y) {
	global $parents;
	
	$x_root = find($x);
	$y_root = find($y);
	$parents[$x_root] = $y_root;
	
}

//----------------------------------------------------------------------------------------
// Merge records
function merge_records ($records, $check = false)
{
	global $couch;
	global $parents;
	
	//print_r($records);

	// If we have more than one reference with the same hash, compare and cluster
	$n = count($records);

	if ($n > 1)
	{
	
		for ($i = 0; $i < $n; $i++)
		{
			makeset($i);
		}

		for ($i = 1; $i < $n; $i++)
		{
			for ($j = 0; $j < $i; $j++)
			{
			
				if ($check)
				{
					$v1 = '';
					$v2 = '';
					
					/*
					if ($v1 == '')
					{
						$v1 = $records[$i]->message->unstructured;
					}

					if ($v2 == '')
					{
						$v2 = $records[$j]->message->unstructured;
					}
					*/
					
					if ($v1 == '')
					{
						if (isset($records[$i]->message->title))
						{
							$v1 = $records[$i]->message->title;
						}
					}
					
					if ($v2 == '')
					{
						if (isset($records[$j]->message->title))
						{
							$v2 = $records[$j]->message->title;
						}
					}
					
				
					if (($v1 != '') && ($v2 != ''))
					{
						$v1 = strip_tags($v1);
						$v2 = strip_tags($v2);
					
						$v1 = finger_print($v1);
						$v2 = finger_print($v2);
						
						echo "$i=$v1\n";
						echo "$j=$v2\n";
						
			
						$lcs = new LongestCommonSequence($v1, $v2);
						$d = $lcs->score();
			
						// echo $d;
			
						$score = min($d / strlen($v1), $d / strlen($v2));
			
						if ($score > 0.80)
						{
							echo "MATCH\n";
				
							union($i, $j);
						}
					}
				}
				else
				{
					// Just merge (e.g., if set of record sis based on sharing an identifier)
					union($i, $j);
				}
			}
		}
	
		$blocks = array();
	
		for ($i = 0; $i < $n; $i++)
		{
			$p = $parents[$i];
		
			if (!isset($blocks[$p]))
			{
				$blocks[$p] = array();
			}
			$blocks[$p][] = $i;
		}
		
		print_r($blocks);
	
		// merge things 
		foreach ($blocks as $block)
		{
			if (count($block) > 1)
			{
				$cluster_id = $records[$block[0]]->_id;
			
				foreach ($block as $i)
				{
					$doc = $records[$i];
					$doc->cluster_id = $cluster_id;
				
					echo $doc->_id . ' ' . $doc->cluster_id . "\n";
				
					// update
					$couch->add_update_or_delete_document($doc, $doc->_id, 'update');
				}
			}
		}
	
	}
	
}


?>

