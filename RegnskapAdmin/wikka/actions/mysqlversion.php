<?php
/**
 * Print current MySQL version.
 *
 * By default this only displays for Wikka admins. This option can be
 * changed by setting 'public_sysinfo' to '1' in the Wikka
 * configuration file.
 *
 * Syntax: {{mysqlversion}}
 *
 * @package		Actions
 * @name		MySQL Version	
 *
 * @author		{@link http://wikkawiki.org/Jsnx Jason Tourtelotte}
 * @author		{@link http://wikkawiki.org/DarTar Dario Taraborelli}
 * @author		{@link http://wikkawiki.org/JavaWoman JavaWoman}
 * @author		{@link http://wikkawiki.org/BrianKoontz Brian Koontz}
 */

// i18n
if (!defined('NOT_AVAILABLE')) define('NOT_AVAILABLE', 'n/a');
if (!defined('WIKKA_ADMIN_ONLY_TITLE')) define('WIKKA_ADMIN_ONLY_TITLE', 'Sorry, only wiki administrators can display this information');

// defaults
$out = '<abbr title="'.WIKKA_ADMIN_ONLY_TITLE.'">'.NOT_AVAILABLE.'</abbr>'."\n";

//check privs
if ($this->config['public_sysinfo'] == '1' || $this->IsAdmin())
{
	$out = mysql_get_server_info();
}
echo $out;
?>
