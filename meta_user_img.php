<?
include("site/config.php");
$get_avatar_thumb = mysqli_query($conn, "SELECT `avatar` FROM `users` WHERE `id` = ".intval($_GET["userID"]));
$avatar_thumb = mysqli_fetch_assoc($get_avatar_thumb)["avatar"];
header("Content-Type: image/png");
exit ( file_get_contents($avatar_thumb) ) ;
?>