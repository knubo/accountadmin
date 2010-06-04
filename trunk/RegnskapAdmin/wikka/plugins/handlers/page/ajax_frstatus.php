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

    if(count($result == 0)) {
        $result = $this->LoadSingle("select count(*) as c from installations");

        if($result["c"] > 100) {
            header("HTTP/1.0 513 Validation Error");

            die(json_encode(array($result["c"])));

        }

    }

    echo json_encode($result);
}
?>