<?php
/**
 * Display a list of recently commented pages.
 *
 * Usage: {{recentlycommented}}
 *
 * Optionally, setting the "user=" GET param will display recently
 * commented pages only for the specified user.
 *
 * @package		Actions
 * @version		$Id: recentlycommented.php 1096 2008-05-26 04:40:18Z BrianKoontz $
 * @license http://www.gnu.org/copyleft/gpl.html GNU General Public License
 * @filesource
 * 
 * @author		{@link http://wikkawiki.org/DarTar Dario Taraborelli} (preliminary code cleanup, ACL check)
 *
 * @uses		Wakka::Format()
 * @uses		Wakka::LoadRecentlyCommented()
 * @uses		Wakka::HasAccess()
 * @uses		Wakka::LoadUser()
 */
/**
 * defaults
 */


date_default_timezone_set("Europe/Oslo");
setlocale(LC_TIME, 'no_NO');

if(!defined('COMMENT_DATE_FORMAT')) define('COMMENT_DATE_FORMAT', '%A, %e %B %Y');
if(!defined('COMMENT_TIME_FORMAT')) define('COMMENT_TIME_FORMAT', 'H:i T');
if(!defined('COMMENT_SNIPPET_LENGTH')) define('COMMENT_SNIPPET_LENGTH', 120);
/**
 * i18n
 */
if (!defined('RECENTLY_COMMENTED_HEADING')) define('RECENTLY_COMMENTED_HEADING', '=====Nylig kommenterte sider=====');
if(!defined('ANONYMOUS_COMMENT_AUTHOR')) define('ANONYMOUS_COMMENT_AUTHOR', '(uregistrert bruker)');
if (!defined('COMMENT_AUTHOR_DIVIDER')) define ('COMMENT_AUTHOR_DIVIDER', ', kommentert av ');
if (!defined('NO_RECENTLY_COMMENTED')) define ('NO_RECENTLY_COMMENTED', 'Det er ingen sider med kommentarer%s');
if (!defined('NO_READABLE_RECENTLY_COMMENTED')) define ('NO_READABLE_RECENTLY_COMMENTED', 'Det er ingen sider med kommentarer som du kan lese..');
$readable = 0;

$username = '';
if(isset($_GET['user']))
{
	$username = $this->htmlspecialchars_ent($_GET['user']);
}

echo $this->Format(RECENTLY_COMMENTED_HEADING.' --- ');
if ($comments = $this->LoadRecentlyCommented(50, $username))
{
	$curday = '';
	foreach ($comments as $comment)
	{
		if ($this->HasAccess('comment', $comment['page_tag']))
		{
			$readable++;
			// day header
			list($day, $time) = explode(' ', $comment['time']);
			if ($day != $curday)
			{
				$dateformatted = ucfirst(strftime(COMMENT_DATE_FORMAT, strtotime($day)));
	
				if ($curday)
				{
					echo "<br />\n";
				}
				echo '<strong>'.$dateformatted.':</strong><br />'."\n";
				$curday = $day;
			}
			$timeformatted = ucfirst(strftime(COMMENT_TIME_FORMAT, strtotime($comment['time'])));
			$comment_preview = str_replace('<br />', '', $comment['comment']);
			if (strlen($comment_preview) > COMMENT_SNIPPET_LENGTH)
			{
				$comment_spillover_link = '<a href="'.$this->href('', $comment['page_tag'], 'show_comments=1').'#comment_'.$comment['id'].'" title="View comment">[&#8230;]</a>'; # i18n
				$comment_preview = substr($comment_preview, 0, COMMENT_SNIPPET_LENGTH).$comment_spillover_link;
			}
			$commentlink = '<a href="'.$this->href('', $comment['page_tag'], 'show_comments=1').'#comment_'.$comment['id'].'" title="View comment">'.$comment['page_tag'].'</a>'; # i18n
			$comment_by = $comment['user'];
			if (!$this->LoadUser($comment_by))
			{
				$comment_by .= ANONYMOUS_COMMENT_AUTHOR;
			}
			// print entry
			echo '&nbsp;&nbsp;&nbsp;'.$commentlink.COMMENT_AUTHOR_DIVIDER.$comment_by.'<blockquote>'.$comment_preview.'</blockquote>'."\n";
		}
	}
	if ($readable == 0)
	{
		echo '<em class="error">'.NO_READABLE_RECENTLY_COMMENTED.'</em>';
	}
}
else
{
	if(!empty($username))
	{
		echo '<em class="error">'.sprintf(NO_RECENTLY_COMMENTED, " by $username.").'</em>';
	}
	else
	{
		echo '<em class="error">'.sprintf(NO_RECENTLY_COMMENTED, ".").'</em>';
	}
}

?>
