<?php
include("site/config.php");
$userID = max(0,intval($_GET["id"]));
if($userID == 0) {exit("invalid user id");}
$findUserSQL = "SELECT * FROM `users` WHERE `id`=$userID";
$findUser = mysqli_query($conn, $findUserSQL);
$user = (object) mysqli_fetch_assoc($findUser);
if(!$user) {exit("requested user does not exist");}
header("Content-Type: image/png");
echo file_get_contents($user->{"avatar"});
exit;
?>