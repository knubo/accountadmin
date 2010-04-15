<?php
/**
 * Delete link menulet
 */
//i18n
if (!defined('DELETELINK_TEXT')) define('DELETELINK_TEXT', '[Slett]');
if (!defined('DELETELINK_TITLE')) define('DELETELINK_TITLE', 'Slett siden (krever bekreftelse)');

echo '<a href="'.$this->Href('delete').'" title="'.DELETELINK_TITLE.'">'.DELETELINK_TEXT.'</a>'."\n";
?>