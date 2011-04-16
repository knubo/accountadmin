<?php
header('Content-Type: text/html; charset=ISO-8859-1');
if ($this->HasAccess("read") && $this->page)
{
	echo $this->Format($this->page['body'], 'wakka', 'page');
}
?>