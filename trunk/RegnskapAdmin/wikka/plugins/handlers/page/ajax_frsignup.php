<?php 
if($this->GetUser()) {
    $user = $this->GetUser();
    
    $username = $user["name"];

    $secret = "";
    for ($i=0; $i<80; $i++) {
       $secret.= chr(mt_rand(97, 122));
    }
    
    $this->Query("insert into to_install (wikilogin, secret) values ('".mysql_real_escape_string($username)."','".mysql_real_escape_string($secret)."')");
    
    echo json_encode(array("secret"=>$secret));
    
}

?>