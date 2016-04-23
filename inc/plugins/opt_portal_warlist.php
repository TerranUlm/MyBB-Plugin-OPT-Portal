<?php
/* Exported by Hooks plugin Fri, 25 Oct 2013 21:00:25 GMT */

if (!defined('IN_MYBB'))
{
	die('Direct initialization of this file is not allowed.<br /><br />Please make sure IN_MYBB is defined.');
}

/* --- Plugin API: --- */

function opt_portal_warlist_info()
{
	return array(
		'name' => 'OPT Portal Warlist',
		'description' => 'Zeigt die Schlachtenliste auf der Startseite an',
		'website' => 'http://opt-community.de/',
		'author' => 'Dieter Gobbers (@Terran_ulm)',
		'authorsite' => 'http://opt-community.de/',
		'version' => '1.0',
		'guid' => '',
		'compatibility' => '16*'
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
	
	// setup some helper functions
	// function opt_armies_settinggroups_defined($settinggroup)
	// {
		// global $db;
		// $query  = $db->simple_select('settinggroups', '*', 'name="' . $db->escape_string($settinggroup) . '"');
		// $result = $db->fetch_array($query);
		// $db->free_result($query);
		// return (!empty($result));
	// }
	
	// function opt_armies_setting_defined($setting)
	// {
		// global $db;
		// $query  = $db->simple_select('settings', '*', 'name="' . $db->escape_string($setting) . '"');
		// $result = $db->fetch_array($query);
		// $db->free_result($query);
		// return (!empty($result));
	// }
	
	// definitions:
	// $settinggroups = array(
		// 'opt_armies'
	// );
	// $settings      = array(
		// 'opt_armies_registration_open',
		// 'opt_armies_random_join_only',
		// 'opt_armies_max_member_difference'
	// );
	// $tables        = array(
		// 'armies',
		// 'armies_structures'
	// );
	
	// now check if the DB is setup
	$is_installed = false;
	$query=$db->simple_select(
		'templates',
		'count(*) as installed',
		'title="optwarlist_portal_warlist_entry"'
	);
	$is_installed=$db->fetch_field($query, 'installed');
	$db->free_result($query);
	// foreach ($settinggroups as $settinggroup)
	// {
		// if (!opt_armies_settinggroups_defined($settinggroup))
		// {
			// $is_installed = false;
		// }
	// }
	// foreach ($settings as $setting)
	// {
		// if (!opt_armies_setting_defined($setting))
		// {
			// $is_installed = false;
		// }
	// }
	// foreach ($tables as $table)
	// {
		// if (!$db->table_exists($table))
		// {
			// $is_installed = false;
		// }
	// }
	
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
	
	$myplugin = opt_armies_info();
	
	// create ACP settings
	// {
		// $PL->settings('opt_armies', $myplugin[ 'name' ], $myplugin[ 'description' ] . '. Configure the Army System Settings.', array(
			// 'registration_open' => array(
				// 'title' => $lang->opt_armies_registration_open_title,
				// 'description' => $lang->opt_armies_registration_open_description,
				// 'optionscode' => 'yesno',
				// 'value' => 1
			// ),
			// 'random_join_only' => array(
				// 'title' => $lang->opt_armies_registration_random_only_title,
				// 'description' => $lang->opt_armies_registration_random_only_description,
				// 'optionscode' => 'yesno',
				// 'value' => 0
			// ),
			// 'max_member_difference' => array(
				// 'title' => $lang->opt_armies_max_member_difference_title,
				// 'description' => $lang->opt_armies_max_member_difference_description,
				// 'optionscode' => 'text',
				// 'value' => 10
			// )
		// ));
	// }
	
	// tables definition statements
	// {
		// $create_table_armies            = "CREATE TABLE IF NOT EXISTS `" . TABLE_PREFIX . "armies` (
			// `aid` int(11) NOT NULL AUTO_INCREMENT COMMENT 'Army ID',
			// `gid` smallint(5) unsigned NOT NULL COMMENT 'usergroup ID',
			// `uugid` smallint(5) unsigned NOT NULL COMMENT 'unassigned users usergroup ID',
			// `HCO_gid` smallint(5) unsigned DEFAULT NULL COMMENT 'High Command Officer Group ID',
			// `CO_gid` smallint(5) unsigned DEFAULT NULL COMMENT 'Commanding Officer Group ID',
			// `shortcut` varchar(5) NOT NULL COMMENT 'shortcut of the army name (aka \"Tag\")',
			// `name` varchar(255) NOT NULL COMMENT 'Name of the Army',
			// `nation` varchar(255) NOT NULL COMMENT 'Nation of the Army',
			// `icon` varchar(255) NOT NULL DEFAULT '' COMMENT 'Army Icon (optional)',
			// `leader_uid` int(10) unsigned NOT NULL COMMENT 'the army leaders'' UID',
			// `displayorder` int(10) unsigned NOT NULL,
			// `is_locked` int(1) unsigned NOT NULL DEFAULT '0' COMMENT '0=users can join the army, 1=users cannot join the army',
			// `is_invite_only` tinyint(1) unsigned NOT NULL DEFAULT '0' COMMENT '0=users can request to join the army, 1=users must be invited to join the army',
			// `welcome_pm` text COMMENT 'templates for the PMs send to new recruits',
			// PRIMARY KEY (`aid`),
			// UNIQUE KEY `name` (`name`),
			// UNIQUE KEY `gid` (`gid`),
			// UNIQUE KEY `uugid` (`uugid`),
			// UNIQUE KEY `HCO_gid` (`HCO_gid`),
			// UNIQUE KEY `CO_gid` (`CO_gid`),
			// KEY `displayorder` (`displayorder`),
			// KEY `leader_uid` (`leader_uid`)
		// ) ENGINE=InnoDB  DEFAULT CHARSET=utf8 COMMENT='Armies'";
		// $create_table_armies_structures = "CREATE TABLE IF NOT EXISTS `" . TABLE_PREFIX . "armies_structures` (
			// `agrid` int(11) unsigned NOT NULL AUTO_INCREMENT COMMENT 'Army to Groups Relations ID',
			// `pagrid` int(11) unsigned DEFAULT NULL COMMENT 'Parent''s agrid',
			// `aid` int(11) NOT NULL COMMENT 'Army ID',
			// `gid` smallint(5) unsigned NOT NULL COMMENT 'usergroup ID',
			// `shortcut` varchar(5) DEFAULT NULL COMMENT 'shortcut of the group name (aka \"Tag\")',
			// `leader_uid` int(10) unsigned NOT NULL COMMENT 'the groups leaders'' UID',
			// `displayorder` int(11) NOT NULL,
			// PRIMARY KEY (`agrid`),
			// UNIQUE KEY `gid` (`gid`),
			// KEY `displayorder` (`displayorder`),
			// KEY `acid` (`aid`),
			// KEY `pagrid` (`pagrid`),
			// KEY `leader_uid` (`leader_uid`)
		// ) ENGINE=InnoDB  DEFAULT CHARSET=utf8 COMMENT='Army and Group Relationships'";
		// $alter_table_armies_structures  = "ALTER TABLE `" . TABLE_PREFIX . "armies_structures`
			// ADD CONSTRAINT `" . TABLE_PREFIX . "armies_structures_ibfk_1` FOREIGN KEY (`aid`) REFERENCES `" . TABLE_PREFIX . "armies` (`aid`) ON DELETE CASCADE ON UPDATE CASCADE";
		
		// // create tables
		// $db->write_query($create_table_armies);
		// $db->write_query($create_table_armies_structures);
		
		// // alter tables
		// // $db->write_query($alter_table_armies_structures);
	// }
	
	// create stylesheet
	// opt_armies_setup_stylessheet();
	
	// create templates
	opt_portal_warlist_setup_templates();
	
	// create task
	// require_once MYBB_ROOT."/inc/functions_task.php";
	
	// $new_task = array(
	// "title" => $db->escape_string($lang->opt_armies_title),
	// "description" => $db->escape_string($lang->opt_armies_task_description),
	// "file" => $db->escape_string('opt_armies'),
	// "minute" => $db->escape_string('27'),
	// "hour" => $db->escape_string('3'),
	// "day" => $db->escape_string('*'),
	// "month" => $db->escape_string('*'),
	// "weekday" => $db->escape_string('*'),
	// "enabled" => intval(0),
	// "logging" => intval(1)
	// );
	
	// $new_task['nextrun'] = fetch_next_run($new_task);
	// $tid = $db->insert_query("tasks", $new_task);
	// $cache->update_tasks();
	
}

function opt_portal_warlist_uninstall()
{
	global $PL;
	$PL or require_once PLUGINLIBRARY;
	
	$myplugin = opt_armies_info();
	$PL->settings_delete('opt_portal_warlist');
	
	global $db, $lang, $cache;
	
	// $lang->load('opt_portal_warlist');
	
	// drop tables
	// $tables = array(
		// 'armies_structures',
		// 'armies'
	// );
	// foreach ($tables as $table)
	// {
		// $db->write_query("DROP TABLE " . TABLE_PREFIX . $table);
	// }
	
	// $PL->stylesheet_delete('opt_portal_warlist');
	$PL->templates_delete('optwarlist');
	
	// $db->delete_query("tasks", "title='{$db->escape_string($lang->opt_armies_title)}'");
	// $cache->update_tasks();
	
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
		
		
		eval("\$portal_warlist_entry .= \"" . $templates->get("optwarlist_portal_warlist_entry") . "\";");
		$i++;
	}
	if ($next_war == false)
		$wl_pos = ($db->num_rows($query) - 3) * -117;
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
function opt_portal_warlist_setup_templates()
{
	global $PL;
	
	$PL->templates('optwarlist', 'OPT Portal Warlist', array(
		'portal_warlist_entry' => '<li>
{$wl_timer}
<table style="border-collapse:collapse;height:100px;color:#555;" class="wl_entry">
<tr>
<td colspan="3" style="font:bold 13px verdana;padding:10px 0 0 0;"><a href="misc.php?page=schlacht_teilnahme&schid={$wl_sch_id}">{$wl_name}</a></td>
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

/* Exported by Hooks plugin Fri, 25 Oct 2013 21:00:25 GMT */
?>
