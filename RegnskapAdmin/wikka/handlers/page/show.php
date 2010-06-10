<?php
/**
 * Display a page if the user has read access or is an admin.
 * 
 * This is the default page handler used by Wikka when no other handler is specified.
 * Depending on user privileges, it displays the page body or an error message. It also
 * displays footer comments and a form to post comments, depending on ACL and general 
 * config settings.
 * 
 * @package		Handlers
 * @subpackage	Page
 * @version		$Id$
 * @license http://www.gnu.org/copyleft/gpl.html GNU General Public License
 * @filesource
 * 
 * @uses		Wakka::Format()
 * @uses		Wakka::FormClose()
 * @uses		Wakka::FormOpen()
 * @uses		Wakka::GetConfigValue()
 * @uses		Wakka::GetPageTag()
 * @uses		Wakka::GetUser()
 * @uses		Wakka::GetUserName()
 * @uses		Wakka::HasAccess()
 * @uses		Wakka::Href()
 * @uses		Wakka::htmlspecialchars_ent()
 * @uses		Wakka::LoadComments()
 * @uses		Wakka::LoadPage()
 * @uses		Wakka::LoadUser()
 * @uses		Wakka::UserIsOwner()
 * @uses		Config::$anony_delete_own_comments
 * @uses		Config::$hide_comments
 * 
 * @todo		move <div> to template;
 * 			necessary) - #312  
 */

//constants
define('SHOW_OLD_REVISION_SOURCE', 0); # if set to 1 shows by default the source of an old revision instead of the rendered version

//validate URL parameters
$raw = (!empty($_GET['raw']))? (int) $this->GetSafeVar('raw', 'get') : SHOW_OLD_REVISION_SOURCE;

?>
<!-- BEGIN PAGE CONTENT -->
<?php
echo '<div id="content"';
echo (($user = $this->GetUser()) && ($user['doubleclickedit'] == 'N') || !$this->HasAccess('write')) ? '' : ' ondblclick="document.location=\''.$this->Href('edit', '', 'id='.$this->page['id']).'\';" '; #268
echo '>'."\n"; //TODO: move to templating class

if (!$this->HasAccess('read'))
{
	echo '<p><em class="error">Du har ikke tilgang til &aring; lese denne siden.</em></p>';
	echo "\n".'</div><!--closing page content-->'."\n"; //TODO: move to templating class
}
else
{
	if (!$this->page)
	{
		echo '<p>Denne siden finnes ikke enda. Vil du kanskje <a href="'.$this->Href('edit').'">opprette</a> den?</p>';
		echo '</div><!--closing page content-->'."\n"; //TODO: move to templating class
	}
	else
	{
		if ($this->page['latest'] == 'N')
		{
			echo '<div class="revisioninfo">'."\n";
			echo '<h4>Revisjon ['.$this->page['id'].']</h4>'."\n";
			echo 'Dette er en <a href="'.$this->Href('revisions').'">gammel revisjon</a> av <a href="'.$this->Href().'">'.$this->GetPageTag().'</a> skrevet av '.$this->page['user'].' den <span class="datetime">'.$this->page['time'].'</span>.'."\n";
?>
				<br />
				<?php echo $this->FormOpen('show', '', 'GET', '', 'left') ?>
				<input type="hidden" name="time" value="<?php echo $this->GetSafeVar('time', 'get') ?>" />
				<input type="hidden" name="raw" value="<?php echo ($raw == 1)? '0' :'1' ?>" />
				<input type="submit" value="<?php echo ($raw == 1)? 'Show formatted' : 'Show source' ?>" />&nbsp;
				<?php echo $this->FormClose(); ?>
<?php
			// if this is an old revision, display some buttons
			if ($this->HasAccess('write'))
			{
				// added if encapsulation : in case where some pages were brutally deleted from database
				if ($latest = $this->LoadPage($this->tag))
				{
?>
 					<?php echo $this->FormOpen('edit') ?>
 					<input type="hidden" name="previous" value="<?php echo $latest['id'] ?>" />
 					<input type="hidden" name="body" value="<?php echo $this->htmlspecialchars_ent($this->page['body']) ?>" />
 					<input type="submit" value="Re-edit this old revision" />
 					<?php echo $this->FormClose(); ?>
<?php
				}
			}
			echo '<div class="clear"></div></div>'."\n";
		}
		// display page
		if ($raw == 1)
		{
			echo '<div class="wikisource">'.nl2br($this->htmlspecialchars_ent($this->page["body"], ENT_QUOTES)).'</div>';
		}
		else
		{
			echo $this->Format($this->page['body'], 'wakka', 'page');
		}
?>
<div style="clear: both"></div>
</div>
<!-- END PAGE CONTENT -->
<?php		
		if ($this->GetConfigValue('hide_comments') != 1)
		{
			// load comments for this page
			$comments = $this->LoadComments($this->tag);

			// store comments display in session
			$tag = $this->GetPageTag();
			if (!isset($_SESSION['show_comments'][$tag]))
				$_SESSION['show_comments'][$tag] = ($this->UserWantsComments() ? '1' : '0');
			if (isset($_GET['show_comments'])) #312
			{
				switch($_GET['show_comments']) #312
				{
				case "0":
					$_SESSION['show_comments'][$tag] = 0;
					break;
				case "1":
					$_SESSION['show_comments'][$tag] = 1;
					break;
				}
			}
?>
<!-- BEGIN COMMENT BLOCK -->
<div id="comments">
<?php
			if ($_SESSION['show_comments'][$tag])
			{
?>
<div id="commentheader">
Kommentarer [<a href="<?php echo $this->Href('', '', 'show_comments=0') ?>">Skjul kommentarer/skjema</a>]
</div>
<?php
				// display comments themselves
				if ($comments)
				{
					$current_user = $this->GetUserName(); 
					echo '<div id="commentlist">'."\n";
					foreach ($comments as $comment)
					{
						echo '<div class="comment">'."\n".
							'<span id="comment_'.$comment['id'].'"></span>'.$comment['comment']."\n".
							"\t".'<div class="commentinfo">'."\n-- ";
						echo ($this->LoadUser($comment['user']))? $this->Format($comment['user']) : $comment['user']; // #84
						echo ' ('.$comment['time'].')'."\n";
						$current_user = $this->GetUserName(); 
     						if ($this->UserIsOwner() || $current_user == $comment['user'] || ($this->config['anony_delete_own_comments'] && $current_user == $comment['user']) )
						{
							echo $this->FormOpen("delcomment");
?>
<input type="hidden" name="comment_id" value="<?php echo $comment['id'] ?>" />
<input type="submit" value="Delete Comment" />
<?php 
							echo $this->FormClose();
						}
						echo "\n\t".'</div>'."\n";
						echo '</div>'."\n";
					}
					echo '</div>'."\n";
				}
				// display comment form
				echo '<div id="commentform">'."\n";
				if ($this->HasAccess('comment'))
				{?>
		    		<?php echo $this->FormOpen('addcomment'); ?>
					<label for="commentbox">Legg til en kommentar til siden:<br />
						<textarea id="commentbox" name="body" rows="6" cols="78"></textarea><br />
						<input type="submit" value="Legg til kommentar" accesskey="s" />
            		</label>
					<?php echo $this->FormClose(); ?>
				<?php
				}
				echo '</div>'."\n";
			}
			else
			{
			?>
<div id="commentheader">
<p>
<?php
				switch (count($comments))
				{
				case 0:
					echo 'Det er ingen kommentarer p&aring; denne siden. ';
					$showcomments_text = 'Legg til kommentar';
					break;
				case 1:
					echo 'Det er en kommentar p&aring; denne siden. ';
					$showcomments_text = 'Vis kommentar';
					break;
				default:
					echo 'Det er  '.count($comments).' kommentarer p&aring; siden. ';
					$showcomments_text = 'Vis kommentarer';
				}
?>
[<a href="<?php echo $this->Href('', '', 'show_comments=1#comments'); ?>"><?php echo $showcomments_text; ?></a>]</p>
</div>
<?php
			}
?>
</div>
<!-- END COMMENT BLOCK -->
<?php
		}
	}
}
?>
