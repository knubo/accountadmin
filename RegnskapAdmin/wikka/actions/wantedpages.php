<?php
if (isset($_GET["linking_to"]))
{
	$linking_to = $_GET["linking_to"];
	if ($pages = $this->LoadPagesLinkingTo($linking_to))
	{
		print("Pages linking to ".$this->Link($linking_to).":<br />\n");
		foreach ($pages as $page)
		{
			print($this->Link($page["tag"])."<br />\n");
		}
	}
	else
	{
		print('<em class="error">Ingen side linker til '.$this->Link($linking_to).".</em>");
	}
}
else
{
	if ($pages = $this->LoadWantedPages())
	{
		foreach ($pages as $page)
		{
			print($this->Link($page["tag"])." (<a href=\"".$this->href("", "", "linking_to=".$page["tag"])."\">".$page["count"]."</a>)<br />\n");
		}
	}
	else
	{
		print('<em class="error">Ingen sider er &oslash;nsket - godt!</em>');
	}
}
?>