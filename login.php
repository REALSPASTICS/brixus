<?php
include("./site/config.php");
if($loggedIn) {
	header("Location: /me");
	exit;
}
if($_POST["dologin"] != null){
	$usercheck = mysqli_query($conn,"SELECT * FROM `users` WHERE `literal_username`='".mysqli_escape_string($conn,$_POST["username"])."'");
	$userexist = mysqli_num_rows($usercheck)>0;
	$user = mysqli_fetch_assoc($usercheck);
	if(!$userexist){$errors[]="User does not exist";}
	else {
		if(password_verify($_POST["password"],$user["password"])){
			$_SESSION["userId"] = intval($user["id"]);
			header("Location: /me");
			exit;
		} else {
			$errors[] = "Incorrect password";
		}
	}
}
$title = "Login - Brixus";
include("./site/header.php");
?>
<div class="box" style="padding:100px">
	<h1>Login</h1>
	<div class="sect" style="margin-top:0"></div>
	<form method="POST">
		<h5>Username:</h5>
		<input type="text" name="username">
		<div class="padded"></div>
		<h5>Password:</h5>
		<input type="password" name="password">
		<div class="padded"></div>
		<input type="submit" name="dologin" value="Login">
	</form>
</div>
<script>
$("input[name='username']").keydown(function(event){
	if(event.keyCode == '13'){
		$("form").submit();
	}
});
$("input[name='password']").keydown(function(event){
	if(event.keyCode == '13'){
		$("form").submit();
	}
});
</script>
<?php
include("./site/footer.php");
?>