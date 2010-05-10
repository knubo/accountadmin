<?php
if($this->GetUser()) {
    $user = $this->GetUser();

    $username = $user["name"];

    $result = $this->LoadAll("select * from installations where wikilogin = '".mysql_real_escape_string($username)."'");

    foreach($result as &$one) {
        $hostname = $one["hostprefix"].".frittregnskap.no";
        $ip = gethostbyname($hostname);
        $one["status"] = strcmp($ip, $hostname) != 0 ? 1 : 0;
        $one["ip"] = $ip;
        $one["hostname"] = $one["hostprefix"].".frittregnskap.no";
    }
    
    echo json_encode($result);
}
?>