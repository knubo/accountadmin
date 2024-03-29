<?php
/**
 * INI language file for Wikka highlighting (configuration file).
 * 
 * @package	Formatters
 * @version	$Id: ini.php 355 2007-02-27 20:52:29Z JavaWoman $
 * @license http://www.gnu.org/copyleft/gpl.html GNU General Public License
 * @filesource
 * 
 * @uses	Wakka::hsc_secure()
 */

$text = $this->hsc_secure($text,ENT_QUOTES);	#427

$text = preg_replace("/([=,\|]+)/m","<span style=\"color:#4400DD\">\\1</span>",$text);
$text = preg_replace("/^([;#].+)$/m","<span style=\"color:#226622\">\\1</span>",$text);
$text = preg_replace("/([^\d\w#;:>])([;#].+)$/m","<span style=\"color:#226622\">\\2</span>",$text);
$text = preg_replace("/^(\[.*\])/m","<strong style=\"color:#AA0000;background:#EEE0CC\">\\1</strong>",$text);
print "<pre>".$text."</pre>";
?>
