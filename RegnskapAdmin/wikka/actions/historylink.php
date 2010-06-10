<?php
/**
 * History link menulet
 */
//i18n
if (!defined('HISTORYLINK_TEXT')) define('HISTORYLINK_TEXT', '[Historikk]');
if (!defined('HISTORYLINK_TITLE')) define('HISTORYLINK_TITLE', 'Klikk for &aring; se siste redigeringer av denne siden');

echo '<a href="'.$this->Href('history').'" title="'.HISTORYLINK_TITLE.'">'.HISTORYLINK_TEXT.'</a>'."\n";
?>