<?php
/**
 * Display a form to edit the current page.
 *
 * @package		Handlers
 * @subpackage	Page
 * @version		$Id$
 * @license		http://www.gnu.org/copyleft/gpl.html GNU General Public License
 * @filesource
 *
 * @author		{@link http://wikkawiki.org/JsnX Jason Tourtelotte} (original code)
 * @author		{@link http://wikkawiki.org/Dartar Dario Taraborelli} (preliminary code cleanup, i18n)
 * @author		{@link http://wikkawiki.org/DotMG Mahefa Randimbisoa} (bugfixes)
 *
 * @uses	Config::$edit_buttons_position
 * @uses	Config::$require_edit_note
 * @uses	Config::$gui_editor
 * @uses	Wakka::ClearLinkTable()
 * @uses	Wakka::Footer()
 * @uses	Wakka::Format()
 * @uses	Wakka::FormClose()
 * @uses	Wakka::FormOpen()
 * @uses	Wakka::GetUser()
 * @uses	Wakka::GetUserName()
 * @uses	Wakka::HasAccess()
 * @uses	Wakka::Header()
 * @uses	Wakka::Href()
 * @uses	Wakka::htmlspecialchars_ent()
 * @uses	Wakka::hsc_secure()
 * @uses	Wakka::LoadSingle()
 * @uses	Wakka::Redirect()
 * @uses	Wakka::SavePage()
 * @uses	Wakka::StartLinkTracking()
 * @uses	Wakka::StopLinkTracking()
 * @uses	Wakka::WriteLinkTable()
 *
 * @todo	use central regex library for validation;
 * @todo	replace $_REQUEST with either $_GET or $_POST (or both if really
 * 			necessary) - #312 => NOT CLEAR here what to do; see also #449
 */

/**#@+
 * Default value.
 */
if (!defined('VALID_PAGENAME_PATTERN')) define ('VALID_PAGENAME_PATTERN', '/^[A-Za-z�������]+[A-Za-z0-9�������]*$/s'); //TODO use central regex library
if (!defined('MAX_TAG_LENGTH')) define ('MAX_TAG_LENGTH', 75);
if (!defined('MAX_EDIT_NOTE_LENGTH')) define ('MAX_EDIT_NOTE_LENGTH', 50);
/**
 * Class attribute to enable styling as error.
 */
if (!defined('INPUT_ERROR_STYLE')) define('INPUT_ERROR_STYLE', 'class="highlight"');
/**#@-*/

/**#@+
 * Internationalization (i18n).
 */
if (!defined('PREVIEW_HEADER')) define('PREVIEW_HEADER', 'Forh&aring;ndsvisning');
if (!defined('LABEL_EDIT_NOTE')) define('LABEL_EDIT_NOTE', 'Legg inn en notis om din redigering');
if (!defined('ERROR_INVALID_PAGENAME')) define('ERROR_INVALID_PAGENAME', 'Sidenavnet er ugyldig. Gyldige sider m&aring; starte med stor bokstav og kun inneholde bokstaver og tall. ');
if (!defined('ERROR_OVERWRITE_ALERT')) define('ERROR_OVERWRITE_ALERT', 'Overskrivings advarsel: Denne siden var endret av noen andre mens du redigerte den. Ta kopi av dine endringer og rediger siden p&aring; nytt.');
if (!defined('ERROR_MISSING_EDIT_NOTE')) define('ERROR_MISSING_EDIT_NOTE', 'Mangler redigerings notis: Fyll inn en redigeringsnotis!');
if (!defined('ERROR_TAG_TOO_LONG')) define('ERROR_TAG_TOO_LONG', 'Tag for lang! %d tegn er maks.');
if (!defined('ERROR_NO_WRITE_ACCESS')) define('ERROR_NO_WRITE_ACCESS', 'Du har ikke skrivetilgang til denne siden. Du m&aring; kanskje registrere en konto for &aring; kunne redigere denne siden.');
if (!defined('ERROR_INVALID_PAGEID')) define('ERROR_INVALID_PAGEID', 'Revisjonsidentifikatoren finnes ikke for siden du sp&oslash;rr etter');
if (!defined('MESSAGE_AUTO_RESIZE')) define('MESSAGE_AUTO_RESIZE', 'Trykk p&aring; %s og taggen blir klippet til riktig lengde');
if (!defined('INPUT_SUBMIT_PREVIEW')) define('INPUT_SUBMIT_PREVIEW', 'Sjekk');
if (!defined('INPUT_SUBMIT_STORE')) define('INPUT_SUBMIT_STORE', 'Lagre');
if (!defined('INPUT_SUBMIT_REEDIT')) define('INPUT_SUBMIT_REEDIT', 'Rediger p&aring; nytt');
if (!defined('INPUT_BUTTON_CANCEL')) define('INPUT_BUTTON_CANCEL', 'Avbryt');
if (!defined('INPUT_SUBMIT_RENAME')) define('INPUT_SUBMIT_RENAME', 'Omd&oslash;p');
if (!defined('ACCESSKEY_STORE')) define('ACCESSKEY_STORE', 's');
if (!defined('ACCESSKEY_REEDIT')) define('ACCESSKEY_REEDIT', 'r');
if (!defined('ACCESSKEY_PREVIEW')) define('ACCESSKEY_PREVIEW', 'p');
if (!defined('SHOWCODE_LINK')) define('SHOWCODE_LINK', 'Vis formateringskoder for denne siden');
if (!defined('SHOWCODE_LINK_TITLE')) define('SHOWCODE_LINK_TITLE', 'Klikk for &aring; vise formateringskoder');
/**#@-*/

//initialization
$error = '';
$highlight_note = '';
$note = '';
$ondblclick = ''; //#123

// cancel operation and return to page
if(isset($_POST['cancel']) && ($_POST['cancel'] == INPUT_BUTTON_CANCEL))
{
	$this->Redirect($this->Href());
}

if (isset($_POST['submit']) && ($_POST['submit'] == INPUT_SUBMIT_PREVIEW) && ($user = $this->GetUser()) && ($user['doubleclickedit'] != 'N'))
{
	$ondblclick = ' ondblclick=\'document.getElementById("reedit_id").click();\'';
}
?>
<div id="content"<?php echo $ondblclick;?>>
<?php
if (!(preg_match(VALID_PAGENAME_PATTERN, $this->tag))) { //TODO use central regex library
	echo '<em class="error">'.ERROR_INVALID_PAGENAME.'</em>';
}
elseif ($this->HasAccess("write") && $this->HasAccess("read"))
{
	$newtag = $output = '';
	// rename action
	if (isset($_POST['newtag']))
	{
		$newtag = $_POST['newtag'];
		if ($newtag !== '') $this->Redirect($this->Href('edit', $newtag));
	}
	$body = '';
	$id = $this->page['id'];
	if(isset($_GET['id']))
	{
		$page = $this->LoadPageById(mysql_real_escape_string($_GET['id']));
		if($page['tag'] != $this->page['tag'])
		{
			$this->Redirect($this->Href(), ERROR_INVALID_PAGEID);
		}
		else
		{
			$body = $page['body'];
			$id = $page['id'];
		}
	}
	if (isset($_POST['form_id']))
	{
		// strip CRLF line endings down to LF to achieve consistency ... plus it saves database space.
		// Note: these codes must remain enclosed in double-quotes to work! -- JsnX
		$body = str_replace("\r\n", "\n", $_POST['body']);
		// replace each 4 consecutive spaces at the start of a line with a tab
		#$body = preg_replace("/\n[ ]{4}/", "\n\t", $body);						# @@@ FIXME: misses first line and multiple sets of four spaces - JW 2005-01-16
		# JW FIXED 2005-07-12
		$pattern = '/^(\t*) {4}/m';					# m modifier: match ^ at start of line *and* at start of string;
		$replace = "$1\t";
		while (preg_match($pattern,$body))
		{
			$body = preg_replace($pattern,$replace,$body);
		}
		// we don't need to escape here, we do that just before display (i.e., treat note just like body!)
		if (isset($_POST['note']))
		{
			$note = trim($_POST['note']);
		}
		// only if saving:
		if (isset($_POST['submit']) && $_POST['submit'] == INPUT_SUBMIT_STORE)
		{
			if (FALSE != ($aKey = $this->getSessionKey($_POST['form_id'])))	# check if form key was stored in session
			{
				if (TRUE != ($rc = $this->hasValidSessionKey($aKey)))	# check if correct name,key pair was passed
				{
					$error = 'Something went wrong with your credentials. Page was not saved';
				}
			}
			
			// check for overwriting
			if ($this->page)
			{
				if ($this->page['id'] != $_POST['previous'])
				{
					$error = ERROR_OVERWRITE_ALERT;
				}
			}
			// check for edit note if required
			if (($this->config['require_edit_note'] == 1) && $_POST['note'] == '')
			{
				$error .= ERROR_MISSING_EDIT_NOTE;
				$highlight_note = INPUT_ERROR_STYLE;
			}
			// store
			if (!$error)
			{
				// only save if new body differs from old body
				if ($body != $this->page['body']) {

					// add page (revisions)
					$this->SavePage($this->tag, $body, $note);

					$this->SaveACL($this->GetPageTag(), 'read', '*');
				    $this->SaveACL($this->GetPageTag(), 'write', '+');
				    $this->SaveACL($this->GetPageTag(), 'comment', '+');
					
					
					// now we render it internally so we can write the updated link table.
					$this->ClearLinkTable();
					$dummy = $this->Header();
					$this->StartLinkTracking();
					$dummy .= $this->Format($body);
					$this->StopLinkTracking();
					$dummy .= $this->Footer();
					$this->WriteLinkTable();
					$this->ClearLinkTable();
				}

				// forward
				$this->Redirect($this->Href());
			}
		}
	}

	// fetch fields
	$previous = $this->page['id'];
	if (isset($_POST['previous'])) $previous = $_POST['previous'];
	if (empty($body)) $body = $this->page['body'];
	// replace each 4 consecutive spaces at the start of a line with a tab
	#$body = preg_replace("/\n[ ]{4}/", "\n\t", $body);						# @@@ FIXME: misses first line and multiple sets of four spaces - JW 2005-01-16
	# JW FIXED 2005-07-12
	$pattern = '/^(\t*) {4}/m';					# m modifier: match ^ at start of line *and* at start of string;
	$replace = "$1\t";
	while (preg_match($pattern,$body))
	{
		$body = preg_replace($pattern,$replace,$body);
	}

	// derive maximum length for a page name from the table structure if possible
	if ($result = mysql_query("describe ".$this->config['table_prefix']."pages tag")) {
		$field = mysql_fetch_assoc($result);
		if (preg_match("/varchar\((\d+)\)/", $field['Type'], $matches)) $maxtaglen = $matches[1];
	}
	else
	{
		$maxtaglen = MAX_TAG_LENGTH;
	}

	// PREVIEW screen
	if (isset($_POST['submit']) && $_POST['submit'] == INPUT_SUBMIT_PREVIEW) # preview output
	{
		$preview_buttons = '<hr />'."\n";
		// We need to escape ALL entity refs before display so we display them _as_ entities instead of interpreting them
		// so we use hsc_secure() on the edit note (as on the body)
		if ($this->config['require_edit_note'] != 2) //check if edit_notes are enabled
		{
			$preview_buttons .= '<input size="'.MAX_EDIT_NOTE_LENGTH.'" maxlength="'.MAX_EDIT_NOTE_LENGTH.'" type="text" name="note" value="'.$this->hsc_secure($note).'" '.$highlight_note.'/>'.LABEL_EDIT_NOTE.'<br />'."\n";
		}
		$preview_buttons .= '<input name="submit" type="submit" value="'.INPUT_SUBMIT_STORE.'" accesskey="'.ACCESSKEY_STORE.'" />'."\n".
			'<input name="submit" type="submit" value="'.INPUT_SUBMIT_REEDIT.'" accesskey="'.ACCESSKEY_REEDIT.'" id="reedit_id" />'."\n".
			'<input type="submit" value="'.INPUT_BUTTON_CANCEL.'" name="cancel"/>'."\n";

		$output .= '<div class="previewhead">'.PREVIEW_HEADER.'</div>'."\n";

		$output .= $this->Format($body);

		$output .=
			'<div class="clear">'."\n".	#683
			$this->FormOpen('edit')."\n".
			'<input type="hidden" name="previous" value="'.$previous.'" />'."\n".
			// We need to escape ALL entity refs before display so we display them _as_ entities instead of interpreting them
			// hence hsc_secure() instead of htmlspecialchars_ent() which UNescapes entities!
			// JW/2007-02-20: why is this? wouldn't it be  easier for the person editing to show actual characters instead of entities?
			'<input type="hidden" name="body" value="'.$this->hsc_secure($body).'" />'."\n";	#427
		$output .= '</div>'."\n";	#683

		$output .= "<br />\n".$preview_buttons.$this->FormClose()."\n";
	}
	// RENAME screen
	elseif (!$this->page && strlen($this->tag) > $maxtaglen)
	{
		// truncate tag to feed a backlinks-handler with the correct value. may be omited. it only works if the link to a backlinks-handler is built in the footer.
		$this->tag = substr($this->tag, 0, $maxtaglen);
		$output  = '<em class="error">'.sprintf(ERROR_TAG_TOO_LONG, $maxtaglen).'</em><br />'."\n";
		$output .= sprintf(MESSAGE_AUTO_RESIZE, INPUT_SUBMIT_RENAME).'<br /><br />'."\n";
		$output .= $this->FormOpen('edit');
		$output .= '<input name="newtag" size="'.MAX_TAG_LENGTH.'" value="'.$this->htmlspecialchars_ent($this->tag).'" />';
		$output .= '<input name="submit" type="submit" value="'.INPUT_SUBMIT_RENAME.'" />'."\n";
		$output .= $this->FormClose();
	}
	// EDIT Screen
	else
	{
		// display form
		if (!empty($error))
		{
			$output .= '<em class="error">'.$error.'</em>'."\n";
		}

		// append a comment?
		// TODO not clear if this is/was intended as a URL parameter (GET), or a check box on the edito form (POST) ....
		// would be nice as a checkbox, provided it is acted upon only when user is actually submitting - NOT on preview or re-edit
		if (isset($_REQUEST['appendcomment'])) #312, #449
		{
			$body = trim($body)."\n\n----\n\n--".$this->GetUserName().' ('.strftime("%c").')';
		}

		$output .=
			$this->FormOpen('edit').
			'<input type="hidden" name="previous" value="'.$previous.'" />'."\n".
			// We need to escape ALL entity refs before display so we display them _as_ entities instead of interpreting them
			// hence hsc_secure() instead of htmlspecialchars_ent() which UNescapes entities!
			// JW/2007-02-20: why is this? wouldn't it be  easier for the person editing to show actual characters instead of entities?
			'<div id="textarea_container">'."\n".
			'<textarea id="body" name="body" rows="100" cols="20">'.$this->hsc_secure($body).'</textarea>'."\n".	#427
			'</div>'."\n";
		// add Edit note
		// We need to escape ALL entity refs before display so we display them _as_ entities instead of interpreting them
		// so we use hsc_secure on the edit note (as on the body)
		if ($this->config['require_edit_note'] != 2) //check if edit_notes are enabled
		{
			$output .= '<input size="'.MAX_EDIT_NOTE_LENGTH.'" maxlength="'.MAX_EDIT_NOTE_LENGTH.'" type="text" name="note" value="'.$this->hsc_secure($note).'" '.$highlight_note.'/> '.LABEL_EDIT_NOTE.'<br />'."\n";
		}
		//finish
		$output .=	'<input name="submit" type="submit" value="'.INPUT_SUBMIT_STORE.'" accesskey="'.ACCESSKEY_STORE.'" /> <input name="submit" type="submit" value="'.INPUT_SUBMIT_PREVIEW.'" accesskey="'.ACCESSKEY_PREVIEW.'" /> <input type="submit" value="'.INPUT_BUTTON_CANCEL.'" name="cancel" />'."\n".
			$this->FormClose();

		if ($this->config['gui_editor'] == 1)
		{
			$output .= '<script type="text/javascript" src="3rdparty/plugins/wikkaedit/wikkaedit_data.js"></script>'."\n";
			$output .= '<script type="text/javascript" src="3rdparty/plugins/wikkaedit/wikkaedit_search.js"></script>'."\n";
			$output .= '<script type="text/javascript" src="3rdparty/plugins/wikkaedit/wikkaedit.js"></script>'."\n";
		}
	}

	echo $output;
}
else
{
	$message = '<em class="error">'.ERROR_NO_WRITE_ACCESS.'</em><br />'."\n".
			"<br />\n".
			'<a href="'.$this->Href('showcode').'" title="'.SHOWCODE_LINK_TITLE.'">'.SHOWCODE_LINK.'</a>'.
			"<br />\n";
	echo $message;
}
?>
</div>
