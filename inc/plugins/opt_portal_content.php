<?php
/* Exported by Hooks plugin Mon, 18 Nov 2013 21:13:32 GMT */

if (!defined('IN_MYBB'))
{
	die('Direct initialization of this file is not allowed.<br /><br />Please make sure IN_MYBB is defined.');
}

/* --- Plugin API: --- */

function opt_portal_content_info()
{
	return array(
		'name' => 'OPT Portal Content',
		'description' => 'Displays content and a slideshow at the portal page',
		'website' => 'http://www.opt-community.de',
		'author' => 'Dieter Gobbers (@Terran_ulm)',
		'authorsite' => 'http://www.opt-community.de',
		'version' => '1.1',
		'guid' => '',
		'compatibility' => '18*'
	);
}

// TODO: add/remove templates
// BIG TODO: use something better then posts to build the content...

/**
 * function opt_portal_content_activate()
 * function opt_portal_content_deactivate()
 * function opt_portal_content_is_installed()
 * function opt_portal_content_install()
 * function opt_portal_content_uninstall()
 */


/* --- Hooks: --- */

/* --- Hook #26 - Portal Content and Images --- */

$plugins->add_hook('portal_start', 'opt_portal_content_portal_start_26', 10);

function opt_portal_content_portal_start_26()
{
	global $lang, $db, $mybb, $templates, $portal, $portal_content, $parser, $slider_img_1, $slider_img_2, $slider_img_3;
	
	// PORTAL STARTSEITE CONTENT-POST
	$parser_options[ 'allow_html' ]      = true;
	$parser_options[ 'allow_mycode' ]    = true;
	$parser_options[ 'allow_smilies' ]   = false;
	$parser_options[ 'allow_imgcode' ]   = true;
	$parser_options[ 'allow_videocode' ] = true;
	$parser_options[ 'filter_badwords' ] = 1;
	$portal_foo                          = $db->fetch_array($db->query('SELECT message FROM ' . TABLE_PREFIX . 'posts WHERE pid=178486'));
	$portal_content                      = $parser->parse_message($portal_foo[ 'message' ], $parser_options);
	
	$portal_foo   = $db->fetch_array($db->query('SELECT message FROM ' . TABLE_PREFIX . 'posts WHERE pid=186923'));
	$slider_img_1 = $parser->parse_message($portal_foo[ 'message' ], $parser_options);
	
	$portal_foo   = $db->fetch_array($db->query('SELECT message FROM ' . TABLE_PREFIX . 'posts WHERE pid=186926'));
	$slider_img_2 = $parser->parse_message($portal_foo[ 'message' ], $parser_options);
	
	$portal_foo   = $db->fetch_array($db->query('SELECT message FROM ' . TABLE_PREFIX . 'posts WHERE pid=186929'));
	$slider_img_3 = $parser->parse_message($portal_foo[ 'message' ], $parser_options);
}

/* Exported by Hooks plugin Mon, 18 Nov 2013 21:13:32 GMT */
?>
