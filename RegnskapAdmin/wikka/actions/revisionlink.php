<?php
/**
 * Revision link menulet
 */
//i18n
if (!defined('REVISIONLINK_TITLE')) define('REVISIONLINK_TITLE', 'Klikk for &aring; se nylige revisjoner av denne siden');
if (!defined('REVISIONFEEDLINK_TITLE')) define('REVISIONFEEDLINK_TITLE', 'Klikk for &aring; f&aring; en str&oslash;m av siste revisjoner av denne siden');

echo $this->GetPageTime() ? '<a class="datetime" href="'.$this->Href('revisions').'" title="'.REVISIONLINK_TITLE.'">'.$this->GetPageTime().'</a> <a href="'.$this->Href('revisions.xml').'" title="'.REVISIONFEEDLINK_TITLE.'"><img src="images/feed.png" class="icon" width="14" height="14" alt="feed icon" /></a>' : '';
?>