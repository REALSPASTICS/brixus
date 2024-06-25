<?php

//for testing purposes

ini_set('smtp_port', 1025);

$to = 'yeb0itsme@outlook.com';
$subject = 'abc';
$message = 'abc';
$headers = 'From: System <noreply@brixus.net>';

mail($to, $subject, $message, $headers);
?>