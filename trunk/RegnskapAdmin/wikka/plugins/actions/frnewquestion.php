<?php

$detail = array_key_exists("detail", $_REQUEST) ? $_REQUEST["detail"] : "";
$title = array_key_exists("title", $_REQUEST) ? $_REQUEST["title"] : "";

$user = 0;
if($this->GetUser()) {
    $user = $this->GetUser();
    $user = $user["name"];
}

$eol="\r\n";
$sender = "question@frittregnskap.no";
$headers .= "From: ".$sender.$eol;
$headers .= "Content-Type:text/plain;";
$headers .= "Message-ID: <".time()."-".$sender.">".$eol;
$headers .= "X-Mailer: PHP v".phpversion().$eol;          // These two to help avoid spam-filters

$subject = "New question";
$email = "question@frittregnskap.no";

$msg = "Nytt sp¿rsmal: $title\nDetaljer: $detail\nInnlagt av:".json_encode($user)."\nServer:\n".json_encode($_SERVER);

mail($email, $subject, $msg, $headers);


header("Location: http://www.frittregnskap.no/wakka/ThanksQuestionAdded");

?>