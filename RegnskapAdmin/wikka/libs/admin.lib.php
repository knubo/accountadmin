<?php

/**
 * Admin routines 
 *
 * Various admin-related routines
 *
 * @name	    admin.lib.php 
 *
 * @package	    Lib	
 * @version		$Id$
 * @license		http://www.gnu.org/copyleft/gpl.html GNU General Public License
 * @since		Wikka 1.1.6.4
 * @filesource
 *
 * @author		{@link http://wikkawiki.org/BrianKoontz Brian Koontz}
 *
 */

//i18n
// Reversion routine strings
if(!defined('REVERT_DEFAULT_COMMENT')) define ('REVERT_DEFAULT_COMMENT', 'Reverting last edit by %s [%d] to previous version [%d]');
if(!defined('REVERT_MESSAGE_SUCCESS')) define ('REVERT_MESSAGE_SUCCESS', 'Reverted to previous version');
if(!defined('REVERT_MESSAGE_FAILURE')) define ('REVERT_MESSAGE_FAILURE', 'Reversion to previous version FAILED!');

// User deletion strings
if(!defined('USERDELETE_MESSAGE_SUCCESS')) define('USERDELETE_MESSAGE_SUCCESS', 'User deletion successful');
if(!defined('USERDELETE_MESSAGE_FAILURE')) define('USERDELETE_MESSAGE_FAILURE', 'User deletion error');

/**
 * LoadLastTwoPagesByTag
 *
 * Returns an object containing the most recent page and the page
 * immediately preceding. Array element 0 is most recent page; element
 * 1 is the previous page.
 *
 * @param object $wakka Wakka class instantiation
 * @param string $tag Page tag
 * @return object Page records or null if only single revision exists
 *
 */
function LoadLastTwoPagesByTag($wakka, $tag)
{
	$tag = mysql_real_escape_string($tag);
	$res = $wakka->LoadAll("SELECT * FROM ".$wakka->config['table_prefix']."pages WHERE tag='".$tag."' ORDER BY time DESC LIMIT 2");
	if(count($res) != 2)
	{
		return null;
	}
	return $res;
}

/**
 * RevertPageToPreviousByTag
 *
 * Reverts a page to the version immediately preceding the "latest"
 * version. New page is created with previous version's metadata.
 *
 * @param object $wakka Wakka class instantiation
 * @param string $tag Page tag
 * @param string $comment Page comment (defaults to REVERT_DEFAULT_COMMENT)
 * @return string REVERT_MESSAGE_SUCCESS or REVERT_MESSAGE_FAILURE
 * 
 */
function RevertPageToPreviousByTag($wakka, $tag, $comment='')
{
	$message = REVERT_MESSAGE_FAILURE;
	$tag = mysql_real_escape_string($tag);
	$comment = mysql_real_escape_string($comment);
	if(TRUE===$wakka->IsAdmin())
	{
		// Select current version of this page and version immediately preceding
		$res = LoadLastTwoPagesByTag($wakka, $tag);
		if($res)
		{
			// $res[0] is current page, $res[1] is page we're reverting to

			// Set default comment
			if(TRUE===empty($comment))
			{
				$comment = sprintf(REVERT_DEFAULT_COMMENT, $res[0]['user'], $res[0]['id'], $res[1]['id']);
			}

			// Save reverted page
			$wakka->SavePage($tag, $res[1]['body'], $comment, $res[1]['owner']);
			$message = REVERT_MESSAGE_SUCCESS;
		}
		else
		{
			$message = REVERT_MESSAGE_FAILURE;
		}
	}
	return $message;
}

/**
 * RevertPageToPreviousById
 *
 * Reverts a page to the version immediately preceding the "latest"
 * version. New page is created with previous version's metadata.
 *
 * @param object $wakka Wakka class instantiation
 * @param string $id Page id (converted to page tag) 
 * @param string $comment Page comment (defaults to REVERT_DEFAULT_COMMENT)
 * @return string REVERT_MESSAGE_SUCCESS or REVERT_MESSAGE_FAILURE
 * 
 */
function RevertPageToPreviousById($wakka, $id, $comment='')
{
	$message = REVERT_MESSAGE_FAILURE;
	$id = mysql_real_escape_string($id);
	if(TRUE===$wakka->IsAdmin())
	{
		$res = $wakka->LoadPageById($id);
		if(TRUE===isset($res))
		{
			$tag = $res['tag'];
			if(TRUE===isset($tag))
			{
				return RevertPageToPreviousByTag($wakka, $tag, $comment);
			}
		}
	}
	return $message;
}

/**
 * DeleteUser
 *
 * Mark a user as deleted, and set password hash to a value that can
 * never be generated by the md5 hash function
 *
 * @param object $wakka Wakka class instantiation
 * @param string $user User name
 * @return string USERDELETE_MESSAGE_SUCCESS or * USERDELETE_MESSAGE_FAILURE
 *
 */
function DeleteUser($wakka, $user)
{
	$status = true;
	if(is_array($user))
	{
		$user = mysql_real_escape_string($user['name']);
	}
	else
	{
		$user = mysql_real_escape_string($user);
	}
	if(TRUE===$wakka->IsAdmin())
	{
		// Don't permit deletion of admin accounts!
		if(TRUE===$wakka->IsAdmin($user))
		{
			return false;
		}		

		// Reset password
		$res = $wakka->LoadSingle("SELECT * FROM ".$wakka->config['table_prefix']."users WHERE name='".$user."'");
		if(FALSE===empty($res))
		{
			$wakka->Query("UPDATE ".$wakka->config['table_prefix']."users SET status='deleted', password='!' WHERE name='".$user."'");
		}
		else
		{
			$status = false;
		}

		// Remove sessions
		$res = $wakka->LoadAll("SELECT * FROM ".$wakka->config['table_prefix']."sessions WHERE userid='".$user."'");	
		if(FALSE===empty($res))
		{
			foreach($res as $session)
			{
				$session_file = session_save_path().DIRECTORY_SEPARATOR."sess_".$session['sessionid'];
				$status = $status && unlink($session_file);
			}
		}
		$wakka->Query("DELETE FROM ".$wakka->config['table_prefix']."sessions WHERE userid='".$user."'");

		return $status;
	}
}

?>
