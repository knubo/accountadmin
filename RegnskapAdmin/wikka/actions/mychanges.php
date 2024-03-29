<?php
/**
 * Display a list of pages edited by the current user.
 *
 * If the current user is logged-in and has edited at least one page, a list of pages edited by the current user
 * is displayed, ordered alphabetically or by date and time (last edit first).
 *
 * @package		Actions
 * @version		$Id$
 * @license		http://www.gnu.org/copyleft/gpl.html GNU General Public License
 * @filesource
 *
 * @author	{@link http://web.archive.org/web/20040616194824/http://www.wakkawiki.com/CarloZottmann Carlo Zottmann}
 * @author	{@link http://wikkawiki.org/NilsLindenberg Nils Lindenberg} (rewrite, i18n)
 *
 * @uses	Wakka::GetConfig()
 * @uses	Wakka::GetPageTag()
 * @uses	Wakka::GetUser() 
 * @uses	Wakka::GetUserName()
 * @uses	Wakka::Href()
 * @uses	Wakka::htmlspecialchars_ent()
 * @uses	Wakka::IsAdmin()
 * @uses	Wakka::LoadAll()
 * @uses	Wakka::Link()
 * @todo	fix RE (#104 etc.); also lose the comma in there!
 */
date_default_timezone_set("Europe/Oslo");
setlocale(LC_TIME, 'no_NO');

if (!defined('NO_PAGES_EDITED')) define('NO_PAGES_EDITED', 'Du har ikke redigert noen sider enda.');
if (!defined('MYCHANGES_ALPHA_LIST')) define('MYCHANGES_ALPHA_LIST', "Dette er en liste av sider redigert av %s, sammen med tidspunktet for dette.");
if (!defined('MYCHANGES_DATE_LIST')) define('MYCHANGES_DATE_LIST', "Dette er en liste av sider redigert av %s, sortert etter tidspunktet for dette.");
if (!defined('ORDER_DATE_LINK_DESC')) define('ORDER_DATE_LINK_DESC', 'sorter etter dato');
if (!defined('ORDER_ALPHA_LINK_DESC')) define('ORDER_ALPHA_LINK_DESC', 'sorter alfabetisk');
if (!defined('MYCHANGES_NOT_LOGGED_IN')) define('MYCHANGES_NOT_LOGGED_IN', "Du er ikke logget inn, alts&aring; har du ingen sider som du har redigert.");
if(!defined('REVISION_DATE_FORMAT')) define('REVISION_DATE_FORMAT', '%A, %e %B %Y');
if(!defined('REVISION_TIME_FORMAT')) define('REVISION_TIME_FORMAT', 'H:i T');
if (!defined('TITLE_REVISION_LINK')) define('TITLE_REVISION_LINK', 'Vis siste revisjoner for  %s');
if (!defined('WIKKA_NO_PAGES_FOUND')) define('WIKKA_NO_PAGES_FOUND', "Ingen redigerte sider funner for %s.");

// order alphabetically or by time?
$alpha = FALSE;
if (isset($_GET["alphabetically"]) && $_GET["alphabetically"] == 1) $alpha = TRUE;

$tag = $this->GetPageTag();
$output = '';
$time_output = '';

$params = '';
$username = '';
if(isset($_GET['user']))
{
	$username = $this->htmlspecialchars_ent($_GET['user']);
	$params .= "user=$username&";
}

$action = '';
if(isset($_GET['action']))
{
	$action = $this->htmlspecialchars_ent($_GET['action']);
	$params .= "action=$action&";
}
$params = substr($params, 0, -1);

if (($this->IsAdmin() && !empty($username)) ||
	($this->GetUser() &&  $username = $this->GetUserName()))
{
	$my_edits_count = 0;

	// get the pages
	$order = ($alpha) ? "tag ASC, time DESC" : "time DESC, tag ASC";
	$query = "
		SELECT id, tag, time
		FROM ".$this->GetConfigValue('table_prefix')."pages
		WHERE user = '".mysql_real_escape_string($username)."'
		AND latest = 'Y'
		ORDER BY ".$order;

	if ($pages = $this->LoadAll($query))
	{
		// header
		$output .= '<div class="floatl">';
		if ($alpha)
		{
			$output .= sprintf(MYCHANGES_ALPHA_LIST, $username).' (<a href="'.$this->Href("", $tag, $params).'">'.ORDER_DATE_LINK_DESC;
		}
		else
		{
			if(!empty($params))
			{
				$params .= "&alphabetically=1";
			}
			else
			{
				$params = "alphabetically=1";
			}

			$output .= sprintf(MYCHANGES_DATE_LIST, $username).' (<a href="'.$this->href("", $tag, $params).'">'.ORDER_ALPHA_LINK_DESC;
		}
		$output .= '</a>)</div><div class="clear">&nbsp;</div>'."\n";

		$current = '';

		// build the list of pages
		foreach ($pages as $page)
		{
			list($day, $time) = explode(" ", $page["time"]);
			// order alphabetically
			if ($alpha)
			{
				$firstChar = strtoupper($page["tag"][0]);
				if (!preg_match("/[A-Z,a-z]/", $firstChar)) //TODO: (#104 #340, #34) Internationalization (allow other starting chars, make consistent with Formatter REs)
				{
					$firstChar = "#";
				}

				if ($firstChar != $current)
				{
					if ($current) $output .= "<br />\n";
					$output .= '<h5>'.$firstChar."</h5>\n";
					$current = $firstChar;
				}
				$time_output = $page["time"];		
				$output .= "&nbsp;&nbsp;&nbsp;".$this->Link($page["tag"], "", "", 0)." ".$this->Link($page["tag"], 'revisions', "[".$page['id']."]", 0).' <a class="datetime" href="'.$this->Href('revisions', $page['tag']).'" title="'.sprintf(TITLE_REVISION_LINK, $page['tag']).'">'.$time_output."</a><br />\n";
			}
			// order by time
			else
			{
				// day header
				if ($day != $current)
				{
					if ($current) $output .= "<br />\n";
					$current = $day;
					$output .= '<h5>'.ucfirst(strftime(REVISION_DATE_FORMAT, strtotime($day))).'</h5>'."\n";
				}
				$time_output = ucfirst(strftime(REVISION_TIME_FORMAT, strtotime($time)));
				//$time_output = $time;
				$output .= '&nbsp;&nbsp;&nbsp;<a class="datetime" href="'.$this->Href('revisions', $page['tag']).'" title="'.sprintf(TITLE_REVISION_LINK, $page['tag']).'">'.$time_output.'</a> '.$this->Link($page["tag"], 'revisions', "[".$page['id']."]", 0)." ".$this->Link($page["tag"], "", "", 0)."<br />\n";
			}
			$my_edits_count++;
		}

		if ($my_edits_count == 0)
		{
			$output .= '<em class="error">'.STATUS_NO_PAGES_EDITED.'</em>';
		}
	}
	else
	{
		$output .= '<em class="error">'.sprintf(WIKKA_NO_PAGES_FOUND, $username).'</em>';
	}
}
else
{
	$output .= '<em class="error">'.MYCHANGES_NOT_LOGGED_IN.'</em>';
}

// *** output section ***
print $output;