<?php
date_default_timezone_set("Europe/Oslo");

include('wikka.config.php');

if (!mysql_pconnect($wakkaConfig['mysql_host'], $wakkaConfig['mysql_user'], $wakkaConfig['mysql_password']))
{
  header('HTTP/1.1 503 Service Unavailable');
  exit;
}

header('Content-Type: text/xml; charset=iso-8859-1');

mysql_select_db($wakkaConfig['mysql_database']);

echo '<?xml version="1.0" encoding="iso-8859-1"?>
<urlset xmlns="http://www.google.com/schemas/sitemap/0.84">
';
$pages = mysql_query('SELECT SQL_NO_CACHE tag, time FROM ' . $wakkaConfig['table_prefix'] . 'pages LEFT JOIN ' . $wakkaConfig['table_prefix'] . "acls ON page_tag = tag WHERE latest = 'Y' AND (read_acl = '*' OR read_acl IS NULL)");
while ($row = mysql_fetch_assoc($pages))
{
  echo " <url>\n";
  echo '  <loc>' . $wakkaConfig['base_url'] . $row['tag'] . "</loc>\n";
  /* PHP4 doesn't support ISO dates, so we get to play games here... */
  $date = date('Y-m-d\TH:i:sO', strtotime($row['time']));
  echo '  <lastmod>' . substr($date, 0, -2) . ':' . substr($date, -2) . "</lastmod>\n";
  echo " </url>\n";
}
echo '</urlset>';
?>
