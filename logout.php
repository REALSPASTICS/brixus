<?php
include './site/config.php';

    unset($_SESSION["userId"]);
	exit(header("Location: /"));
?>