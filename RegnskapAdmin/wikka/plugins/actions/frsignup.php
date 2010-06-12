<?php 

if($this->GetUser()) {
    echo "<script language=\"javascript\"";
	echo "src=\"/frsignup/no.knubo.accounting.admin.SignupGWT.nocache.js\"></script>";
    echo "<div id=\"signupapp\"><!-- In here --></div>";
}

?>
