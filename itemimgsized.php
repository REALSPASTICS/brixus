<?php
include("./site/config.php");
if(!isset($_GET["id"])) {exit;}
$findItemThumb = mysqli_query($conn,"SELECT `thumbnail` FROM `items` WHERE `id`=".intval($_GET["id"]));
$itemThumb = mysqli_fetch_assoc($findItemThumb)["thumbnail"];
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="utf-8">
<title>
	Brixus.net
</title>
</head>
<body style="margin:0;overflow:hidden;">
<img src="<?=$itemThumb?>" width="300" title="">
</body>
</html>