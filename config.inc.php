<?php

/**
 * @file config.inc.php
 *
 * Global configuration variables (may be added to by other modules).
 *
 */

global $config;

// Date timezone
date_default_timezone_set('UTC');

//$site = 'local';
$site = 'heroku';

switch ($site)
{
	case 'heroku':
		// Server-------------------------------------------------------------------------
		$config['web_server']	= 'https://csl-playground.herokuapp.com'; 
		$config['site_name']	= 'CSL';

		// Files--------------------------------------------------------------------------
		$config['web_dir']		= dirname(__FILE__);
		$config['web_root']		= '/';		
		break;

	case 'local':
	default:
		// Server-------------------------------------------------------------------------
		$config['web_server']	= 'http://localhost'; 
		$config['site_name']	= 'CSL';

		// Files--------------------------------------------------------------------------
		$config['web_dir']		= dirname(__FILE__);
		$config['web_root']		= '/~rpage/csl-playground/';
}

// Proxy settings for connecting to the web----------------------------------------------- 
// Set these if you access the web through a proxy server. 
$config['proxy_name'] 	= '';
$config['proxy_port'] 	= '';


// CouchDB--------------------------------------------------------------------------------
switch ($site)
{
	case 'heroku':
		// Cloudant
		$config['couchdb_options'] = array(
				'database' => 'cal-playground', // note typo
				'host' => getenv('CLOUDANT_USERNAME') . ':' . getenv('CLOUDANT_PASSWORD')
					. 'rdmpage.cloudant.com',
				'port' => 5984,
				'prefix' => 'https://'
				);	
		break;
		
	case 'local':
	default:
		$config['couchdb_options'] = array(
				'database' => 'cal-playground', // note typo
				'host' => 'localhost',
				'port' => 5984,
				'prefix' => 'http://'
				);	
		break;
}		

// HTTP proxy
if ($config['proxy_name'] != '')
{
	if ($config['couchdb_options']['host'] != 'localhost')
	{
		$config['couchdb_options']['proxy'] = $config['proxy_name'] . ':' . $config['proxy_port'];
	}
}

$config['stale'] = false;
	
?>