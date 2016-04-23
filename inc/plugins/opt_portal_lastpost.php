<?php
/* Exported by Hooks plugin Mon, 28 Oct 2013 20:58:39 GMT */

if (!defined('IN_MYBB'))
{
	die('Direct initialization of this file is not allowed.<br /><br />Please make sure IN_MYBB is defined.');
}

/* --- Plugin API: --- */

function opt_portal_lastpost_info()
{
	return array(
		'name' => 'OPT Portal Lastposts',
		'description' => 'Zeigt die aktuellsten Threads auf der Portalseite an',
		'website' => 'http://opt-community.de/',
		'author' => 'Dieter Gobbers (@Terran_ulm)',
		'authorsite' => 'http://opt-community.de/',
		'version' => '1.0',
		'guid' => '',
		'compatibility' => '16*'
	);
}

function opt_portal_lastpost_is_installed()
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
	$query        = $db->simple_select('templates', 'count(*) as installed', 'title="optlastpost_portal_lastpost_entry"');
	$is_installed = $db->fetch_field($query, 'installed');
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

function opt_portal_lastpost_install()
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
	
	// $lang->load('opt_portal_lastpost');
	
	$myplugin = opt_portal_lastpost_info();
	
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
	opt_portal_lastpost_setup_templates();
	
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

function opt_portal_lastpost_uninstall()
{
	global $PL;
	$PL or require_once PLUGINLIBRARY;
	
	$myplugin = opt_armies_info();
	$PL->settings_delete('opt_portal_lastpost');
	
	global $db, $lang, $cache;
	
	// $lang->load('opt_portal_lastpost');
	
	// drop tables
	// $tables = array(
	// 'armies_structures',
	// 'armies'
	// );
	// foreach ($tables as $table)
	// {
	// $db->write_query("DROP TABLE " . TABLE_PREFIX . $table);
	// }
	
	// $PL->stylesheet_delete('opt_portal_lastpost');
	$PL->templates_delete('optlastpost');
	
	// $db->delete_query("tasks", "title='{$db->escape_string($lang->opt_armies_title)}'");
	// $cache->update_tasks();
	
}



/* --- Hooks: --- */

/* --- Hook #25 - Aktuelle Posts auf der Startseite anzeigen --- */

$plugins->add_hook('portal_start', 'opt_portal_lastpost_portal_start_25', 9);

function opt_portal_lastpost_portal_start_25()
{
	global $db, $mybb, $lang, $themes, $templates, $portal_lastpost_entry, $portal, $lastpost_setting, $lp_setting_url, $cache;
	
	require_once MYBB_ROOT . "inc/functions_search.php";
	require_once MYBB_ROOT . "inc/class_parser.php";
	$parser = new postParser;
	
	$lp = $mybb->input['lastpost'];
	if (!empty($lp))
	{
		if ($lp == 5 || $lp == 10 || $lp == 15 || $lp == 20)
		{
			my_setcookie("lastpost", $lp);
		}
	}
	if (empty($mybb->cookies['lastpost']))
	{
		my_setcookie("lastpost", 5);
	}
	$lastpost = $mybb->cookies['lastpost'];
	
	$lp_setting_url = '';
	for ($i = 5; $i <= 20; $i = $i + 5)
	{
		$lp_style = ($mybb->cookies['lastpost'] == $i) ? "underline" : "none";
		$lp_setting_url .= '<a style="text-decoration:' . $lp_style . ';" href="?lastpost=' . $i . '">' . $i . '</a>&nbsp;&nbsp;|&nbsp;&nbsp;';
	}
	$lp_setting_url = substr($lp_setting_url, 0, -13);
	
	$lastpost_setting = 'Anzeige:&nbsp;&nbsp;&nbsp;&nbsp;' . $lp_setting_url;
	
	$where_sql = '1';
	
	$unsearchforums = get_unsearchable_forums();
	if ($unsearchforums)
	{
		$where_sql .= " AND t.fid NOT IN ($unsearchforums)";
	}
	$inactiveforums = get_inactive_forums();
	if ($inactiveforums)
	{
		$where_sql .= " AND t.fid NOT IN ($inactiveforums)";
	}
	
	$permsql    = "";
	$onlyusfids = array();
	
	// Check group permissions if we can't view threads not started by us
	$group_permissions = forum_permissions();
	foreach ($group_permissions as $fid => $forum_permissions)
	{
		if ($forum_permissions['canonlyviewownthreads'] == 1)
		{
			$onlyusfids[] = $fid;
		}
	}
	if (!empty($onlyusfids))
	{
		$where_sql .= " AND ((fid IN(" . implode(',', $onlyusfids) . ") AND uid='{$mybb->user['uid']}') OR fid NOT IN(" . implode(',', $onlyusfids) . "))";
	}
	
	$forumcache = $cache->read("forums");
	$threads    = array();
	
	if ($mybb->user['uid'] == 0)
	{
		// Build a forum cache.
		$query = $db->query("
			SELECT fid
			FROM " . TABLE_PREFIX . "forums
			WHERE active != 0
			ORDER BY pid, disporder
		");
		
		$forumsread = my_unserialize($mybb->cookies['mybb']['forumread']);
	}
	else
	{
		// Build a forum cache.
		$query = $db->query("
			SELECT f.fid, fr.dateline AS lastread
			FROM " . TABLE_PREFIX . "forums f
			LEFT JOIN " . TABLE_PREFIX . "forumsread fr ON (fr.fid=f.fid AND fr.uid='{$mybb->user['uid']}')
			WHERE f.active != 0
			ORDER BY pid, disporder
		");
	}
	
	while ($forum = $db->fetch_array($query))
	{
		if ($mybb->user['uid'] == 0)
		{
			if ($forumsread[$forum['fid']])
			{
				$forum['lastread'] = $forumsread[$forum['fid']];
			}
		}
		$readforums[$forum['fid']] = $forum['lastread'];
	}
	$fpermissions = forum_permissions();
	
	$threadcount = 0;
	
	$unapproved_where = 't.visible>0';
	
	$search = array(
		"uid" => $mybb->user['uid'],
		"dateline" => TIME_NOW,
		"ipaddress" => '',
		"threads" => '',
		"posts" => '',
		"resulttype" => "threads",
		"querycache" => $where_sql,
		"keywords" => ''
		
	);
	
	if ($search['querycache'] != "")
	{
		$where_conditions = $search['querycache'];
		$query            = $db->simple_select("threads t", "t.tid", $where_conditions . " AND {$unapproved_where} AND t.closed NOT LIKE 'moved|%' ORDER BY t.lastpost DESC {$limitsql}");
		while ($thread = $db->fetch_array($query))
		{
			$threads[$thread['tid']] = $thread['tid'];
			$threadcount++;
		}
		// Build our list of threads.
		if ($threadcount > 0)
		{
			$search['threads'] = implode(",", $threads);
		}
		// No results.
		else
		{
			error($lang->error_nosearchresults);
		}
		$where_conditions = "t.tid IN (" . $search['threads'] . ")";
	}
	// This search doesn't use a query cache, results stored in search table.
	else
	{
		$where_conditions = "t.tid IN (" . $search['threads'] . ")";
		$query            = $db->simple_select("threads t", "COUNT(t.tid) AS resultcount", $where_conditions . " AND {$unapproved_where} AND t.closed NOT LIKE 'moved|%' {$limitsql}");
		$count            = $db->fetch_array($query);
		
		if (!$count['resultcount'])
		{
			error($lang->error_nosearchresults);
		}
		$threadcount = $count['resultcount'];
	}
	
	$permsql    = "";
	$onlyusfids = array();
	
	// Check group permissions if we can't view threads not started by us
	$group_permissions = forum_permissions();
	foreach ($group_permissions as $fid => $forum_permissions)
	{
		if ($forum_permissions['canonlyviewownthreads'] == 1)
		{
			$onlyusfids[] = $fid;
		}
	}
	if (!empty($onlyusfids))
	{
		$permsql .= "AND ((t.fid IN(" . implode(',', $onlyusfids) . ") AND t.uid='{$mybb->user['uid']}') OR t.fid NOT IN(" . implode(',', $onlyusfids) . "))";
	}
	
	$unsearchforums = get_unsearchable_forums();
	if ($unsearchforums)
	{
		$permsql .= " AND t.fid NOT IN ($unsearchforums)";
	}
	$inactiveforums = get_inactive_forums();
	if ($inactiveforums)
	{
		$permsql .= " AND t.fid NOT IN ($inactiveforums)";
	}
	
	
	$order       = "desc";
	$oppsortnext = "asc";
	$oppsort     = $lang->asc;
	
	$sortfield = "t.lastpost";
	$sortby    = "lastpost";
	
	$sqlarray = array(
		'order_by' => $sortfield,
		'order_dir' => $order,
		'limit_start' => 1,
		'limit' => $perpage
	);
	
	
	$querystring = "
			SELECT t.*, u.username AS userusername, p.displaystyle AS threadprefix
			FROM " . TABLE_PREFIX . "threads t
			LEFT JOIN " . TABLE_PREFIX . "users u ON (u.uid=t.uid)
			LEFT JOIN " . TABLE_PREFIX . "threadprefixes p ON (p.pid=t.prefix)
			WHERE $where_conditions AND {$unapproved_where} {$permsql} AND t.closed NOT LIKE 'moved|%'
			ORDER BY $sortfield $order
			LIMIT 1, $lastpost
		";
	
	
	$query        = $db->query($querystring);
	// die($querystring);
	$thread_cache = array();
	
	while ($thread = $db->fetch_array($query))
	{
		$thread_cache[$thread['tid']] = $thread;
	}
	$thread_ids = implode(",", array_keys($thread_cache));
	
	
	// Fetch the read threads.
	if ($mybb->user['uid'] && $mybb->settings['threadreadcut'] > 0)
	{
		$query = $db->simple_select("threadsread", "tid,dateline", "uid='" . $mybb->user['uid'] . "' AND tid IN(" . $thread_ids . ")");
		while ($readthread = $db->fetch_array($query))
		{
			$thread_cache[$readthread['tid']]['lastread'] = $readthread['dateline'];
		}
	}
	
	foreach ($thread_cache as $thread)
	{
		$bgcolor = alt_trow();
		$folder  = '';
		$prefix  = '';
		
		if ($thread['userusername'])
		{
			$thread['username'] = $thread['userusername'];
		}
		$thread['profilelink'] = build_profile_link($thread['username'], $thread['uid']);
		
		// If this thread has a prefix, insert a space between prefix and subject
		if ($thread['prefix'] != 0)
		{
			$thread['threadprefix'] .= '&nbsp;';
		}
		
		$thread['subject'] = $parser->parse_badwords($thread['subject']);
		$thread['subject'] = htmlspecialchars_uni($thread['subject']);
		
		if ($thread['poll'])
		{
			$prefix = $lang->poll_prefix;
		}
		
		// Determine the folder
		$folder       = '';
		$folder_label = '';
		
		$gotounread = '';
		$isnew      = 0;
		$donenew    = 0;
		$last_read  = 0;
		
		if ($mybb->settings['threadreadcut'] > 0 && $mybb->user['uid'])
		{
			$forum_read = $readforums[$thread['fid']];
			
			$read_cutoff = TIME_NOW - $mybb->settings['threadreadcut'] * 60 * 60 * 24;
			if ($forum_read == 0 || $forum_read < $read_cutoff)
			{
				$forum_read = $read_cutoff;
			}
		}
		else
		{
			$forum_read = $forumsread[$thread['fid']];
		}
		
		if ($mybb->settings['threadreadcut'] > 0 && $mybb->user['uid'] && $thread['lastpost'] > $forum_read)
		{
			if ($thread['lastread'])
			{
				$last_read = $thread['lastread'];
			}
			else
			{
				$last_read = $read_cutoff;
			}
		}
		else
		{
			$last_read = my_get_array_cookie("threadread", $thread['tid']);
		}
		
		if ($forum_read > $last_read)
		{
			$last_read = $forum_read;
		}
		
		if ($thread['lastpost'] > $last_read && $last_read)
		{
			$folder .= "new";
			$new_class = "subject_new";
			$folder_label .= $lang->icon_new;
			$thread['newpostlink'] = get_thread_link($thread['tid'], 0, "newpost") . $highlight;
			eval("\$gotounread = \"" . $templates->get("forumdisplay_thread_gotounread") . "\";");
			$unreadpost = 1;
		}
		else
		{
			$new_class = 'subject_old';
			$folder_label .= $lang->icon_no_new;
		}
		
		if ($thread['replies'] >= $mybb->settings['hottopic'] || $thread['views'] >= $mybb->settings['hottopicviews'])
		{
			$folder .= "hot";
			$folder_label .= $lang->icon_hot;
		}
		if ($thread['closed'] == 1)
		{
			$folder .= "lock";
			$folder_label .= $lang->icon_lock;
		}
		$folder .= "folder";
		
		if (!$mybb->settings['postsperpage'])
		{
			$mybb->settings['postperpage'] = 20;
		}
		
		$thread['pages']     = 0;
		$thread['multipage'] = '';
		$threadpages         = '';
		$morelink            = '';
		$thread['posts']     = $thread['replies'] + 1;
		
		if ($thread['posts'] > $mybb->settings['postsperpage'])
		{
			$thread['pages'] = $thread['posts'] / $mybb->settings['postsperpage'];
			$thread['pages'] = ceil($thread['pages']);
			if ($thread['pages'] > $mybb->settings['maxmultipagelinks'])
			{
				$pagesstop = $mybb->settings['maxmultipagelinks'] - 1;
				$page_link = get_thread_link($thread['tid'], $thread['pages']) . $highlight;
				eval("\$morelink = \"" . $templates->get("forumdisplay_thread_multipage_more") . "\";");
			}
			else
			{
				$pagesstop = $thread['pages'];
			}
			for ($i = 1; $i <= $pagesstop; ++$i)
			{
				$page_link = get_thread_link($thread['tid'], $i) . $highlight;
				eval("\$threadpages .= \"" . $templates->get("forumdisplay_thread_multipage_page") . "\";");
			}
			eval("\$thread['multipage'] = \"" . $templates->get("forumdisplay_thread_multipage") . "\";");
		}
		else
		{
			$threadpages         = '';
			$morelink            = '';
			$thread['multipage'] = '';
		}
		
		$lastpostdate           = my_date($mybb->settings['dateformat'], $thread['lastpost']);
		$lastposttime           = my_date($mybb->settings['timeformat'], $thread['lastpost']);
		$lastposter             = $thread['lastposter'];
		$thread['lastpostlink'] = get_thread_link($thread['tid'], 0, "lastpost");
		$lastposteruid          = $thread['lastposteruid'];
		$thread_link            = get_thread_link($thread['tid']);
		
		// Don't link to guest's profiles (they have no profile).
		if ($lastposteruid == 0)
		{
			$lastposterlink = $lastposter;
		}
		else
		{
			$lastposterlink = build_profile_link($lastposter, $lastposteruid);
		}
		
		$thread['replies'] = my_number_format($thread['replies']);
		$thread['views']   = my_number_format($thread['views']);
		
		if ($forumcache[$thread['fid']])
		{
			$thread['forumlink'] = "<a href=\"" . get_forum_link($thread['fid']) . "\">" . $forumcache[$thread['fid']]['name'] . "</a>";
		}
		else
		{
			$thread['forumlink'] = "";
		}
		
		$inline_edit_class = "";
		
		$load_inline_edit_js = 0;
		
		// If this thread has 1 or more attachments show the papperclip
		if ($thread['attachmentcount'] > 0)
		{
			if ($thread['attachmentcount'] > 1)
			{
				$attachment_count = $lang->sprintf($lang->attachment_count_multiple, $thread['attachmentcount']);
			}
			else
			{
				$attachment_count = $lang->attachment_count;
			}
			
			eval("\$attachment_count = \"" . $templates->get("forumdisplay_thread_attachment_count") . "\";");
		}
		else
		{
			$attachment_count = '';
		}
		
		$inline_edit_tid = $thread['tid'];
		
		
		eval("\$portal_lastpost_entry .= \"" . $templates->get("optlastpost_portal_lastpost_entry") . "\";");
	}
}

// templates are a big mess so I put it to the end of the file
function opt_portal_lastpost_setup_templates()
{
	global $PL;
	
	$PL->templates('optlastpost', 'OPT Portal Lastpost', array(
		'portal_lastpost_entry' => '<div class="lastpost_entry">
 <span class="date">User: {$lastposterlink}</span> <span class="author">{$lastpostdate} {$lastposttime}</span><br/>
<b>Forum: {$thread[\'forumlink\']}</b><br/>
<a href="{$thread[\'lastpostlink\']}">{$thread[\'subject\']}</a><br/>
</div>'
	));
}

/* Exported by Hooks plugin Mon, 28 Oct 2013 20:58:39 GMT */
?>
