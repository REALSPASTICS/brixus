<?
include "site/config.php";

mysqli_query($conn,"UPDATE `users` SET `password`='".password_hash("gathaloaskedsincethe11th",PASSWORD_DEFAULT)."' WHERE `id`=13");