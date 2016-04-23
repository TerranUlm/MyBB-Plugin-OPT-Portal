<?php
/* Exported by Hooks plugin Mon, 18 Nov 2013 20:48:12 GMT */

if (!defined('IN_MYBB'))
{
	die('Direct initialization of this file is not allowed.<br /><br />Please make sure IN_MYBB is defined.');
}

/* --- Plugin API: --- */

function opt_portal_welcome_info()
{
	return array(
		'name' => 'OPT Portal Welcome',
		'description' => 'Displays a welcome block at the portal page',
		'website' => 'http://www.opt-community.de',
		'author' => 'Dieter Gobbers (@Terran_ulm)',
		'authorsite' => 'http://www.opt-community.de',
		'version' => '1.0',
		'guid' => '',
		'compatibility' => '16*'
	);
}

// TODO: add/remove templates

/**
 * function opt_portal_welcome_activate()
 * function opt_portal_welcome_deactivate()
 * function opt_portal_welcome_is_installed()
 * function opt_portal_welcome_install()
 * function opt_portal_welcome_uninstall()
 */


/* --- Hooks: --- */

/* --- Hook #26 - Portal Welcome Block --- */

$plugins->add_hook('portal_start', 'opt_portal_welcome_portal_start_26', 10);

function opt_portal_welcome_portal_start_26()
{
	global $lang, $db, $mybb, $templates, $portal, $portal_content, $parser, $portal_welcomeblock;
	
	// MEMBER-LOGIN STARTSEITE
	if ($mybb->user[ 'uid' ] != 0)
	{
		if ($mybb->usergroup[ 'cancp' ] == 1 && $mybb->config[ 'hide_admin_links' ] != 1)
		{
			$admin_dir = $config[ 'admin_dir' ];
			eval("\$admincplink = \"" . $templates->get("portal_welcomeblock_member_admin") . "\";");
		} //$mybb->usergroup['cancp'] == 1 && $mybb->config['hide_admin_links'] != 1
		
		if ($mybb->usergroup[ 'canmodcp' ] == 1)
		{
			eval("\$modcplink = \"" . $templates->get("portal_welcomeblock_member_moderator") . "\";");
		} //$mybb->usergroup['canmodcp'] == 1
		
		preg_match('/.+?href=\"(.+?)\">(.+?)<\/a>.+?Besuch:\ (.+?),\ (.+?)$/', trim($lang->welcome_back), $welcome);
		$new_welcome             = '<div style="padding: 0 0 5px 0;font:10px verdana;">Letzter Besuch: ' . $welcome[ 3 ] . ', ' . $welcome[ 4 ] . '</div><b>Willkommen Soldat, ' . $welcome[ 2 ] . '</b>';
		$lang->welcome_back      = $new_welcome;
		$lang->welcome_pms_usage = $lang->sprintf($lang->welcome_pms_usage, my_number_format($mybb->user[ 'pms_unread' ]), my_number_format($mybb->user[ 'pms_total' ]));
		eval("\$portal_welcomeblock = \"" . $templates->get("portal_welcomeblock_member") . "\";");
	} //$mybb->user['uid'] != 0
	else
	{
		eval("\$portal_welcomeblock = \"" . $templates->get("portal_welcomeblock_guest") . "\";");
	}
}

/* Exported by Hooks plugin Mon, 18 Nov 2013 20:48:12 GMT */
?>
