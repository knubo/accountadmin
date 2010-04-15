<?php
/**
 * Revert link menulet (admin-only)
 *
 * @todo	Use a new HasRevisions() method instead of loading admin class
 */
//i18n
if (!defined('REVERTLINK_TEXT')) define('REVERTLINK_TEXT', '[Reverser]');
if (!defined('REVERTLINK_TITLE')) define('REVERTLINK_TITLE', 'Klikk for a bytte til tidligere versjon');
if (!defined('REVERTLINK_OLDEST_TITLE')) define('REVERTLINK_OLDEST_TITLE', 'Dette er den eldste kjente versjon av siden');

if ($this->IsAdmin())
{
	include_once('libs/admin.lib.php');
	$res = LoadLastTwoPagesByTag($this, $this->tag);
	if(null !== $res)
	{
		echo '<a href="'.$this->Href('revert').'" title="Klikk for &aring; bytte til forrige versjon av siden">'.REVERTLINK_TEXT.'</a>';
	}
	else
	{
		echo '<span class="disabled" title="'.REVERTLINK_OLDEST_TITLE.'">'.REVERTLINK_TEXT.'</span>';
	}
}
?>
