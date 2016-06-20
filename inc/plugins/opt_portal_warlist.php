<?php
/* Exported by Hooks plugin Fri, 25 Oct 2013 21:00:25 GMT */

if (!defined('IN_MYBB'))
{
	die('Direct initialization of this file is not allowed.<br /><br />Please make sure IN_MYBB is defined.');
}

if (!defined("PLUGINLIBRARY"))
{
	define("PLUGINLIBRARY", MYBB_ROOT . "inc/plugins/pluginlibrary.php");
}

/* --- Plugin API: --- */

function opt_portal_warlist_info()
{
	return array(
		'name' => 'OPT Portal Warlist',
		'description' => 'Zeigt die Schlachtenliste auf der Startseite an',
		'website'=>'https://github.com/TerranUlm/',
		'author'=>'Dieter Gobbers (@Terran_ulm)',
		'authorsite' => 'https://opt-community.de/',
		'version' => '1.2',
		'codename'=>'opt_portal_warlist',
		'compatibility' => '18*'
	);
}

 /**
 * function opt_portal_warlist_activate()
 * function opt_portal_warlist_deactivate()
 * function opt_portal_warlist_is_installed()
 * function opt_portal_warlist_install()
 * function opt_portal_warlist_uninstall()
 */
function opt_portal_warlist_is_installed()
{
	
	if (!file_exists(PLUGINLIBRARY))
	{
		flash_message("PluginLibrary is missing.", "error");
		admin_redirect("index.php?module=config-plugins");
	}
	
	global $PL;
	$PL or require_once PLUGINLIBRARY;
	
	if ($PL->version < 12)
	{
		flash_message("PluginLibrary is too old: " . $PL->version, "error");
		admin_redirect("index.php?module=config-plugins");
	}
	
	global $db;
	
	// check if the DB is setup
	$is_installed = false;
	$query=$db->simple_select(
		'templates',
		'count(*) as installed',
		'title="optwarlist_portal_warlist_entry"'
	);
	$is_installed=$db->fetch_field($query, 'installed');
	$db->free_result($query);
	
	// TODO: check for settingsgroup and settings

	return $is_installed;
}

function opt_portal_warlist_install()
{
	if (!file_exists(PLUGINLIBRARY))
	{
		flash_message("PluginLibrary is missing.", "error");
		admin_redirect("index.php?module=config-plugins");
	}
	
	global $PL;
	$PL or require_once PLUGINLIBRARY;
	
	if ($PL->version < 12)
	{
		flash_message("PluginLibrary is too old: " . $PL->version, "error");
		admin_redirect("index.php?module=config-plugins");
	}
	
	global $db, $lang, $cache;
	
	// $lang->load('opt_portal_warlist');
	
	$myplugin = opt_portal_warlist_info();
	
	// create ACP settings
	{
		$PL->settings('opt_portal_warlist', $myplugin[ 'name' ], $myplugin[ 'description' ] . '. Configure the OPT Portal Warlist Settings.', array(
			'response_url' => array(
				'title' => 'Player Response Page URL',
				'description' => 'URL for the player response page, without the match ID, e.g. misc.php?action=match_response&mid=<br/><span style="color: red;">Setting not implemented at the moment</span>',
				'optionscode' => 'text',
				'value' => 'misc.php?action=match_response&mid='
			)
		));
	}
	opt_portal_warlist_setup_templates();
}

function opt_portal_warlist_uninstall()
{
	global $PL;
	$PL or require_once PLUGINLIBRARY;
	
	$myplugin = opt_armies_info();
	$PL->settings_delete('opt_portal_warlist');
	
	global $db, $lang, $cache;
	
	$PL->templates_delete('optwarlist');
	$PL->settings_delete('opt_portal_warlist');
}


/* --- Hooks: --- */

/* --- Hook #24 - Matchliste anzeigen --- */

$plugins->add_hook('portal_start', 'opt_portal_warlist_portal_start_24', 9);

function opt_portal_warlist_portal_start_24()
{
	global $db, $mybb, $templates, $themes, $portal_warlist_entry, $portal, $wl_pos;
	
	$query     = $db->simple_select('myleagues_matchdays', '*');
	$matchdays = array();
	while ($matchday = $db->fetch_array($query))
	{
		$matchdays[$matchday['mid']] = $matchday;
	}
	$db->free_result($query);
	
	$query = $db->simple_select('myleagues_teams', '*');
	$teams = array();
	while ($team = $db->fetch_array($query))
	{
		$teams[$team['tid']] = $team;
	}
	$db->free_result($query);
	
	$next_war = false;
	$i        = 0;
	
	$query = $db->simple_select('myleagues_matches', '*', '', array(
		'order_by' => 'dateline',
		'order_dir' => 'ASC'
	));
	
	$trow=0;
	while ($match = $db->fetch_array($query))
	{
		
		$wl_sch_id              = $match['mid'];
		$war_id                 = $match['mid'];
		$wl_timer               = '';
		$wl_name                = $matchdays[$match['matchday']]['name'];
		$war_value["starttime"] = $match['dateline'];
		$war_value["startdate"] = my_date("d.m.Y H:i", $war_value["starttime"]);
		$wl_startdate           = $war_value["startdate"];
		
		$home_icon  = 'uploads/crests/' . $match['hometeam'] . '.png';
		$guest_icon = 'uploads/crests/' . $match['awayteam'] . '.png';
		
		// $wl_timer=time();
		
		
		if ($war_value["starttime"] > time() && $next_war == false)
		{
			$next_war      = $war_id;
			$next_war_date = my_date($mybb->settings['dateformat'], $war_value["starttime"]);
			// in der nächsten Zeile wird fest 1 Stunde abgezogen, da die Zeitzonen bei MyLeagues nicht korrekt verarbeitet werden (beim Speichern müsste in GMT umgewandelt werden):
			$war_value["starttime"] = my_timestamp($war_value["starttime"]) - 3600;
			$wl_timer      = '';
			$wl_result     = '<td style="width:0px;padding:0;"></td><td style="text-align:center;padding:0;width:140px;font:10px verdana;"><div id="wl_timer">' . $war_value["starttime"] . '000</div></td><td style="padding:0;width:0px;"></td>';
			$wl_pos        = $i * -117;
		} //$war_value["starttime"] > time() && $next_war == false
		else
		{
			if ($war_value["starttime"] < time())
			{
				$wl_result = '<td style="text-align:center;width:70px;padding:0;font:bold 16px verdana;">' . $match['homeresult'] . '</td>
                              <td style="text-align:center;padding:0;font:bold 16px verdana;">:</td>
                              <td style="text-align:center;width:70px;padding:0;font:bold 16px verdana;">' . $match['awayresult'] . '</td>';
			} //$war_value["starttime"] < time()
			else
			{
				$wl_result = '<td style="text-align:center;width:70px;padding:0;font:bold 16px verdana;">-</td>
                              <td style="text-align:center;padding:0;font:bold 16px verdana;">:</td>
                              <td style="text-align:center;width:70px;padding:0;font:bold 16px verdana;">-</td>';
			}
		}
		
		$altbg="trow".($trow+1);
		$trow=1-$trow;
		
		eval("\$portal_warlist_entry .= \"" . $templates->get("optwarlist_portal_warlist_entry") . "\";");
		$i++;
	}
	if ($next_war == false)
		$wl_pos = ($db->num_rows($query) - 2) * -117;
	$db->free_result($query);
	
	if (!empty($next_war))
	{
		$next_war_registration = "<br /><br /><div><a style='text-decoration:blink underline;font: bold 11px verdana;color:#870E0E;' href='http://www.opt-community.de/cc/index.php?page=schlacht_teilnahme&schid=$next_war'>Anmeldung zur Schlacht am $next_war_date</a></div>";
	} //!empty($next_war)
}

function my_timestamp($stamp="", $offset="")
{
	global $mybb, $lang, $mybbadmin, $plugins;

	// If the stamp isn't set, use TIME_NOW
	if(empty($stamp))
	{
		$stamp = TIME_NOW;
	}

	if(!$offset && $offset != '0')
	{
		if(isset($mybb->user['uid']) && $mybb->user['uid'] != 0 && array_key_exists("timezone", $mybb->user))
		{
			$offset = $mybb->user['timezone'];
			$dstcorrection = $mybb->user['dst'];
		}
		else
		{
			$offset = $mybb->settings['timezoneoffset'];
			$dstcorrection = $mybb->settings['dstcorrection'];
		}

		// If DST correction is enabled, add an additional hour to the timezone.
		if($dstcorrection == 1)
		{
			++$offset;
			if(my_substr($offset, 0, 1) != "-")
			{
				$offset = "+".$offset;
			}
		}
	}

	if($offset == "-")
	{
		$offset = 0;
	}
	
	return $stamp + 3600 * $offset;

}

// templates are a big mess so I put it to the end of the file
// TODO: make the response URL a plugin setting
function opt_portal_warlist_setup_templates()
{
	global $PL;
	
	$PL->templates('optwarlist', 'OPT Portal Warlist', array(
		'portal_warlist_entry' => '<li class="{$altbg}">
{$wl_timer}
<table style="border-collapse:collapse;height:100px;color:#555;" class="wl_entry">
<tr>
<td colspan="3" style="font:bold 13px verdana;padding:10px 0 0 0;"><a href="misc.php?action=match_response&mid={$wl_sch_id}">{$wl_name}</a></td>
<td colspan="2" style="font: 12px verdana;padding:0;text-align:right;padding:10px 0 0 0;">{$wl_startdate}</td>
</tr>
<tr>
<td style="width:110px;padding:0;font:bold 16px verdana;text-align:left;"><img style="width:100px;" src="{$home_icon}" /></td>
{$wl_result}
<td style="text-align:right;width:110px;padding:0;font:bold 16px verdana;text-align:right;"><img  style="width:100px;" src="{$guest_icon}" /></td>
</tr>
</table>
</li>'
	));
}

// TODO: create templates

/* Exported by Hooks plugin Fri, 25 Oct 2013 21:00:25 GMT */
?>
