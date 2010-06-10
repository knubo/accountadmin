<?php

// only claim ownership if this page has no owner, and if user is logged in.
if ($this->page && !$this->GetPageOwner() && $this->GetUser())
{
	$this->SetPageOwner($this->GetPageTag(), $this->GetUserName());
	$this->SetRedirectMessage("Du eier n&aring; denne siden.");
}

$this->Redirect($this->Href());

?>