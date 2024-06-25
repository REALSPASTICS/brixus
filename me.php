<?php
include ( $_SERVER["DOCUMENT_ROOT"] . "/site/config.php" );
if ( !$loggedIn ) {
	header ( "Location: /login/" );
	exit;
}
header ( "Location: /user?id=" . intval ( $_SESSION["userId"] ) . "&personal_view" );
?>