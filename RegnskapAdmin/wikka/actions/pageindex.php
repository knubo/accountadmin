<?php
/**
 * Display an alphabetical list of pages of the wiki.
 *
 * This action checks user read privileges and displays an index of read-accessible pages.
 *
 * @package    Actions
 * @version	$Id$
 * @license http://www.gnu.org/copyleft/gpl.html GNU General Public License
 * @filesource
 *
 * @author    {@link http://wikkawiki.org/GiorgosKontopoulos GiorgosKontopoulos} (added ACL check, first code cleanup)
 * @author    {@link http://wikkawiki.org/DarTar DarTar} (adding doc header, minor code and layout refinements, i18n)
 * 
 * @uses		Wakka::LoadPageTitles()
 * @uses		Wakka::GetUserName()
 * @uses		Wakka::HasAccess()
 * @uses		Wakka::Link()
 * @uses		Wakka::Format()
 *
 * @output		a list of pages accessible to the current user
 * @todo		add filtering options
 * @todo		fix RE (#104 etc.)
 */

// i18n strings
if(!defined('PAGE_HEADING')) define('PAGE_HEADING',"Sideindeks");
if(!defined('INDEX_CAPTION')) define('INDEX_CAPTION',"Dette er en alfabetisk liste over sider du kan lese p&aring; denne serveren.");
if(!defined('ALL_PAGES')) define('ALL_PAGES',"Alle");
if(!defined('PAGE_OWNER')) define('PAGE_OWNER'," . . . . Eier: %s");
if(!defined('OWNED_PAGES_CAPTION')) define('OWNED_PAGES_CAPTION',"Elementer markert med * indikerer sider du eier.");
if(!defined('ERROR_NO_PAGES_FOUND')) define('ERROR_NO_PAGES_FOUND', "Ingen sider funnet.");

if ($pages = $this->LoadAllPages())
{
	// filter by letter
	$requested_letter = (isset($_GET['letter'])) ? $_GET['letter'] : ''; #312 
	if (!$requested_letter && isset($letter)) $requested_letter = strtoupper($letter); // TODO action parameter (letter) needs to be validated 

	// get things started
	$cached_username = $this->GetUserName();
	$user_owns_pages = false;
	$link = $this->href('', '', 'letter=');
	$alpha_bar = '<a href="'.$link.'">'.ALL_PAGES.'</a>&nbsp;'."\n";
	$index_header = INDEX_CAPTION;
	$index_output = '';
	$current_character = '';
	$character_changed = false;

	// get page list
	foreach ($pages as $page)
	{
		// check user read privileges
		if (!$this->HasAccess('read', $page['tag'])) continue;

		$page_owner = $page['owner'];
		// $this->CachePage($page);

		$firstChar = strtoupper($page['tag'][0]);
		if (!preg_match('/[A-Za-z]/', $firstChar)) $firstChar = '#'; //TODO: Internationalization
		if ($firstChar != $current_character) {
			$alpha_bar .= '<a href="'.$link.$firstChar.'">'.$firstChar.'</a>&nbsp;'."\n";
			$current_character = $firstChar;
			$character_changed = true;
		}
		if ($requested_letter == '' || $firstChar == $requested_letter) {
			if ($character_changed) {
				$index_output .= "<br />\n<strong>$firstChar</strong><br />\n";
				$character_changed = false;
			}
			$index_output .= $this->Link($page['tag']);

			if ($cached_username == $page_owner) {                       
				$index_output .= '*';
				$user_owns_pages = true;
			} elseif ($page_owner != '(Public)' && $page_owner != '') {
				$index_output .= sprintf(PAGE_OWNER, $page_owner);
			}
		     	$index_output .= "<br />\n";    
		}
	}
	// generate page
	$index_header .= ($user_owns_pages) ? '---'.OWNED_PAGES_CAPTION : '';
	echo $this->Format('===='.PAGE_HEADING.'==== --- <<'.$index_header.'<< ::c:: ---'); 
	echo "\n<strong>".$alpha_bar."</strong><br />\n".$index_output;
} else {
	echo ERROR_NO_PAGES_FOUND;
}
?>
