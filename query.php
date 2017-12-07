<?php

// Parse a user-supplied query string to extracxt any facets, and also clean the string
function parse_query($q)
{
	// Parse query to extract special parts
	$query = array();
	$query['q'] = $q;
	
	$matched = false;
	
	if (!$matched)
	{
		if (preg_match('/(?<q>.*)\s+AND\s+author:"(?<author>.*)"\s+AND\s+year:"(?<year>[0-9]{4})"/u', $q, $m))
		{
			$query['q'] = $m['q'];
			$query['author'] = urldecode($m['author']);
			$query['year'] = $m['year'];
			$matched = true;
		}
	}
	
	// filter by author
	if (!$matched)
	{
		if (preg_match('/(?<q>.*)\s+AND\s+author:"(?<author>.*)"/u', $q, $m))
		{
			$query['q'] = $m['q'];
			$query['author'] = urldecode($m['author']);
			$matched = true;
		}
	}
	
	// filering on year	
	if (!$matched)
	{
		if (preg_match('/(?<q>.*)\s+AND\s+year:"(?<year>[0-9]{4})"/u', $q, $m))
		{
			$query['q'] = $m['q'];
			$query['year'] = $m['year'];
			$matched = true;
		}
	}
	
	// query for just author
	if (!$matched)
	{
		if (preg_match('/(?<q>author:"(?<author>.*)")/u', $q, $m))
		{
			$query['q'] = $m['q'];
			$query['author'] = urldecode($m['author']);
			$matched = true;
		}
	}
	
	// clean the remaining string of characters that may break the search
	$query['q'] = preg_replace('/[:|\(|\)]/u', '', $query['q']);
	
	// create complete string we send to search engine
	$query['string'] = $query['q'];
	
	if (isset($query['author']))
	{
		$query['string'] .= ' AND author:"' . $query['author'] . '"';
	}
	if (isset($query['year']))
	{
		$query['string'] .= ' AND year:"' . $query['year'] . '"';
	}	
	
	
	return $query;
		
	
}