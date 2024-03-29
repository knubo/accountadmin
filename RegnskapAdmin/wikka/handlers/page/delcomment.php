<?php
/**
 * Delete a comment if the user is an admin, page owner or has posted the comment.
 * 
 * @package		Handlers
 * @subpackage	Page
 * @version		$Id$
 * @license		http://www.gnu.org/copyleft/gpl.html GNU General Public License
 * @filesource
 * 
 * @uses	Config::table_prefix
 * @uses	Wakka::UserIsOwner()
 * @uses	Wakka::LoadSingle()
 * @uses	Wakka::GetUserName()
 * @uses	Wakka::getSessionKey()
 * @uses	Wakka::hasValidSessionKey()
 * @uses	Wakka::Query()
 * @uses	Href()
 * 
 */

if(isset($_POST['form_id']) && isset($_POST["comment_id"])) 
{
	//select comment
	$comment_id = intval(trim($_POST["comment_id"]));
	$comment = $this->LoadSingle("select user from ".$this->config["table_prefix"]."comments where id = '".$comment_id."' limit 1");
	$current_user = $this->GetUserName();	
	
	$delete = FALSE;
    if (FALSE != ($aKey = $this->getSessionKey($_POST['form_id'])))	# check if form key was stored in session
	{
		if (TRUE == ($rc = $this->hasValidSessionKey($aKey)))	# check if correct name,key pair was passed
		{
			
			if ($this->UserIsOwner() || $comment["user"]==$current_user)
			{
				$delete = TRUE;
			}
			
		}
	}

	// delete comment
	if (TRUE === $delete)
	{
		$this->Query("DELETE FROM ".$this->config["table_prefix"]."comments WHERE id = '".$comment_id."' LIMIT 1");
		// redirect to page
		$this->redirect($this->Href(), 'Kommentar slettet.');
	}
	else
	{
		echo '<div id="content"><em class="error">Du har ikke lov til &aring; slette denne kommentaren!</em></div>'."\n";
	}
}
?>
