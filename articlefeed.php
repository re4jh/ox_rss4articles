<?php
/* OXID RSS4Articles
   Author: jonas.hess@revier.de
   Licence: GPLv3
*/

//send an RSS-header
header("Content-Type: application/rss+xml; charset=utf-8");

//include OXID-config
class OXID_Config {
	public function __construct() {
		include 'config.inc.php';
	}
}

//function to determine my current URL
function curPageURL() {
	$pageURL = 'http';
	if ($_SERVER["HTTPS"] == "on") {$pageURL .= "s";
	}
	$pageURL .= "://";
	if ($_SERVER["SERVER_PORT"] != "80") {
		$pageURL .= $_SERVER["SERVER_NAME"] . ":" . $_SERVER["SERVER_PORT"] . $_SERVER["REQUEST_URI"];
	} else {
		$pageURL .= $_SERVER["SERVER_NAME"] . $_SERVER["REQUEST_URI"];
	}
	return $pageURL;
}

//initialize shop config
$ox_shop = new OXID_Config();

//fill feed with data
$rssfeed = '<?xml version="1.0" encoding="UTF-8" ?>' . "\n";
$rssfeed .= '<rss version="2.0"  xmlns:atom="http://www.w3.org/2005/Atom">' . "\n\n";
$rssfeed .= '<channel>' . "\n";
$rssfeed .= '  <title>Artikel RSS Feed</title>' . "\n";
$rssfeed .= '  <link>' . $ox_shop->sShopURL . '</link>' . "\n";
$rssfeed .= '<atom:link href="' . curPageURL() . '" rel="self" type="application/rss+xml" />' . "\n";
$rssfeed .= '  <description>Artikelfeed ' . $_SERVER['HTTP_HOST'] . '</description>' . "\n";
$rssfeed .= '  <language>de-de</language>' . "\n\n";

//get articles data from database
$connection = @mysql_connect($ox_shop -> dbHost, $ox_shop -> dbUser, $ox_shop -> dbPwd) or die('Could not connect to database');
mysql_select_db($ox_shop->dbName) or die('Could not select database');
$query = 'SELECT * FROM `' . $ox_shop -> dbName . '`.`oxarticles` WHERE oxactive=1 ORDER BY `OXTIMESTAMP` DESC, `OXTIMESTAMP` DESC LIMIT 25;';
$result = mysql_query($query) or die("Could not execute query");

while ($row = mysql_fetch_array($result)) {
	$rssfeed .= '<item>' . "\n";
	$rssfeed .= '<title>' . utf8_encode($row['OXTITLE']) . '</title>' . "\n";
	$rssfeed .= '<link>' . $ox_shop->sShopURL . '/index.php?lang=0&amp;cl=search&amp;listtype=search&amp;searchparam=' . urlencode(utf8_encode($row['OXARTNUM'])) . '</link>' . "\n";
	$rssfeed .= '<description>' . utf8_encode($row['OXSHORTDESC']) . '</description>' . "\n";
	$rssfeed .= '<guid>http://' . $_SERVER['HTTP_HOST'] . '/' . utf8_encode($row['OXID']) . '</guid>' . "\n";
	$rssfeed .= '<pubDate>' . date("D, d M Y H:i:s O", strtotime($row['OXTIMESTAMP'])) . '</pubDate>' . "\n";
	$rssfeed .= '</item>' . "\n\n";
}

$rssfeed .= '</channel>' . "\n";
$rssfeed .= "\n";
$rssfeed .= '</rss> ' . "\n";

//display feed
echo $rssfeed;

//close db_connection
mysql_close($connection);
?>