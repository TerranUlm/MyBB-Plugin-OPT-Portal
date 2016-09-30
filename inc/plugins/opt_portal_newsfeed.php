<?php
/* Exported by Hooks plugin Mon, 18 Nov 2013 20:22:20 GMT */

if (!defined('IN_MYBB'))
{
	die('Direct initialization of this file is not allowed.<br /><br />Please make sure IN_MYBB is defined.');
}

/* --- Plugin API: --- */

function opt_portal_newsfeed_info()
{
	return array(
		'name' => 'OPT Portal News Feed',
		'description' => 'Displays a news feed at the portal page',
		'website' => 'http://opt-community.de/',
		'author' => 'Dieter Gobbers (@Terran_ulm)',
		'authorsite' => 'http://opt-community.de/',
		'version' => '1.0',
		'guid' => '',
		'compatibility' => '16*'
	);
}

// TODO: install/uninstall (table creation/deletion, add/remove template)
 
/**
 * function opt_portal_newsfeed_activate()
 * function opt_portal_newsfeed_deactivate()
 * function opt_portal_newsfeed_is_installed()
 * function opt_portal_newsfeed_install()
 * function opt_portal_newsfeed_uninstall()
 */


/* --- Hooks: --- */

/* --- Hook #26 - Portal Newsfeed --- */

$plugins->add_hook('portal_start', 'opt_portal_newsfeed_portal_start_26', 10);

function opt_portal_newsfeed_portal_start_26()
{
	global $lang, $db, $mybb, $templates, $portal, $portal_content;

	// BATTLEFIELD NEWS STARTSEITE
	Bf3RssNewsClass::insertNews();
	Bf3RssNewsClass::displayNews();
}

class Bf3RssNewsClass
{
	const updatetime = 3600;
	
	function insertNews()
	{
		global $db;
		
		$lastnews = $db->fetch_array($db->query('SELECT id, pubdate, insertdate FROM opt_portal_bf3news ORDER BY pubdate DESC LIMIT 1'));
		
		if ((strtotime($lastnews[ "insertdate" ]) + self::updatetime) > time())
		{
			return false;
		} //(strtotime($lastnews["insertdate"]) + self::updatetime) > time()
		
		$db->write_query('UPDATE opt_portal_bf3news SET insertdate="' . date("Y-m-d H:i:s") . '" WHERE id="' . $lastnews[ "id" ] . '"');
		
		$ch = curl_init();
		//curl_setopt($ch, CURLOPT_URL, "http://www.battlefield.com/de/battlefield3/rss/blog");
		// curl_setopt($ch, CURLOPT_URL, "http://www.battlefield-4.net/rssfeed");
		curl_setopt($ch, CURLOPT_URL, "http://www.battlefieldseries.de/feed/");
		curl_setopt($ch, CURLOPT_HEADER, 0);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
		$data = curl_exec($ch);
		curl_close($ch);
		
		$xml_parser = xml_parser_create();
		xml_parse_into_struct($xml_parser, $data, $vals);
		xml_parser_free($xml_parser);
		
		$a = 0;
		
		for ($i = 0; $i < count($vals); $i++)
		{
			if ($vals[ $i ][ "tag" ] == "ITEM" && $vals[ $i ][ "type" ] == "open")
			{
				$open = true;
			} //$vals[$i]["tag"] == "ITEM" && $vals[$i]["type"] == "open"
			
			if ($open == true)
			{
				if ($vals[ $i ][ "tag" ] == "TITLE")
					$result[ $a ][ "title" ] = $vals[ $i ][ "value" ];
				
				if ($vals[ $i ][ "tag" ] == "LINK")
					$result[ $a ][ "link" ] = $vals[ $i ][ "value" ];
				
				if ($vals[ $i ][ "tag" ] == "PUBDATE")
					$result[ $a ][ "pubdate" ] = date("Y-m-d H:i:s", strtotime($vals[ $i ][ "value" ]));
			} //$open == true
			
			if ($vals[ $i ][ "tag" ] == "ITEM" && $vals[ $i ][ "type" ] == "close")
			{
				if (empty($lastnews[ "pubdate" ]) || strtotime($result[ $a ][ "pubdate" ]) > strtotime($lastnews[ "pubdate" ]))
				{
					$query = 'INSERT INTO ' . 'opt_portal_bf3news ' . 'SET ' . '`title`="' . $db->escape_string($result[ $a ][ "title" ]) . '", ' . '`link`="' . $db->escape_string($result[ $a ][ "link" ]) . '", ' . '`pubdate`="' . $result[ $a ][ "pubdate" ] . '", ' . '`insertdate`="' . date("Y-m-d H:i:s") . '" ';
					$db->write_query($query);
				} //empty($lastnews["pubdate"]) || strtotime($result[$a]["pubdate"]) > strtotime($lastnews["pubdate"])
				
				$a++;
				$open = false;
			} //$vals[$i]["tag"] == "ITEM" && $vals[$i]["type"] == "close"
		} //$i = 0; $i < count($vals); $i++
	}
	
	function displayNews()
	{
		global $db, $templates, $portal_news_entry;
		
		$query = $db->query('SELECT * FROM opt_portal_bf3news ORDER BY pubdate DESC LIMIT 5');
		
		while ($row = $db->fetch_array($query))
		{
			$row[ "pubdate" ] = date("d.m.y H:i", strtotime($row[ "pubdate" ]));
			
			foreach ($row as $key => $value)
			{
				$foo_name  = "nw_" . $key;
				$$foo_name = $value;
			} //$row as $key => $value
			
			eval("\$portal_news_entry .= \"" . $templates->get("portal_news_entry") . "\";");
		} //$row = $db->fetch_array($query)
	}
}


/* Exported by Hooks plugin Mon, 18 Nov 2013 20:22:20 GMT */
?>
