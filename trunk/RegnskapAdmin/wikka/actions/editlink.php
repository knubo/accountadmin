<?php
/**
 * Edit link menulet
 */
//i18n
if (!defined('EDITLINK_TEXT')) define('EDITLINK_TEXT', '[Rediger]');
if (!defined('SHOWLINK_TEXT')) define('SHOWLINK_TEXT', '[Vis]');
if (!defined('SHOWCODELINK_TEXT')) define('SHOWCODELINK_TEXT', '[Kilde]');
if (!defined('EDITLINK_TITLE')) define('EDITLINK_TITLE', 'Klikk for &aring; redigere siden');
if (!defined('SHOWLINK_TITLE')) define('SHOWLINK_TITLE', 'Vis formatert versjon av siden');
if (!defined('SHOWCODELINK_TITLE')) define('SHOWCODELINK_TITLE', 'Vis koder for siden');

if ($this->method == 'edit' || $this->method == 'showcode')
{
	echo '<a href="'.$this->Href().'" title="'.SHOWLINK_TITLE.'">'.SHOWLINK_TEXT.'</a>';
}
else
{
	if ($this->HasAccess('write'))
	{
		echo '<a href="'.$this->Href('edit').'" title="'.EDITLINK_TITLE.'">'.EDITLINK_TEXT.'</a>';
	}
	else
	{
		echo '<a href="'.$this->Href('showcode').'" title="'.SHOWCODELINK_TITLE.'">'.SHOWCODELINK_TEXT.'</a>';
	}
}
?>
