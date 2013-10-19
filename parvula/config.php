<?php

// ----------------------------- //
// # Core config
// ----------------------------- //

return array(
	// Show errors if debug is enabled
	'debug' => true,

	// Class aliases
	'aliases' => array(
		'HTML' => 'Parvula\Core\HTML',
		'Asset' => 'Parvula\Core\Config',
		'Asset' => 'Parvula\Core\Asset'
	),

	// You can force this option with a boolean
	'URLRewriting' => file_exists('.htaccess'),

	// Home page
	'homePage' => 'home',

	// Error page
	'errorPage' => '_404',

	// Extension for files in ./data
	'fileExtension' => 'md',

	// How to sort pages (see http://www.php.net/manual/en/array.sorting.php)
	'typeOfSort' => 'natcasesort',

	// Config file to read
	'userConfig' => 'site.conf',

	// Class to (un)serialize pages (must implements PageSerializerInterface)
	'defaultPageSerializer' => 'Parvula\Core\ParvulaPageSerializer'
);