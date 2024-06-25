<?php
	die();
	include("./Site/Config.php");
?>
<?php
	if(!$loggedIn) {
		header("Location: /Login.php");
		exit;
	}
?>
<?php
	$ShowTimeError = false;
	
	if(isset($_POST["CreateKey"])) {
		$LatestKey = mysqli_query($conn,"SELECT * FROM `BetaKeys` WHERE `UserID`=$_SESSION[userId] ORDER BY `ID` DESC LIMIT 1");
		$LatestKeyRow = mysqli_fetch_assoc($LatestKey);
			
		$NOWDT = new DateTime();
		$LCRDT = new DateTime($LatestKeyRow["Created"]);
		
		if(mysqli_num_rows($LatestKey) == 0 || $NOWDT->getTimestamp() - $LCRDT->getTimestamp() > 86400) {
			function randtoken()
				{
					$chars = "qwertyuiopasdfghjklzxcvbnmQWERTYUIOPASDFGHJKLZXCVBNM1234567890";
					
					
					
					$resulting = "";
					
					for($i = 0; $i < 20; $i++) {$resulting .= $chars[rand(0, strlen($chars)-1)];}
					
				return $resulting	;
				}
				
			do {
				$RandomKey = randtoken();
				$FindMatchingKeys = mysqli_query($conn,"SELECT 1 FROM `BetaKeys` WHERE `Key`='$RandomKey'");
			} while(mysqli_num_rows($FindMatchingKeys) > 0);
			
			mysqli_query($conn,"INSERT INTO `BetaKeys` VALUES (NULL,'$RandomKey','yes',$_SESSION[userID],CURRENT_TIMESTAMP())");
			
			header("Location: /invite");
			exit;
		} else {
			$ShowTimeError = true;
		}
	}
?>
<?php
	include("./Site/Header.php");
?>
<h1>Invite</h1>
<h3 style="width:50%">Signup Keys</h3>
<div class="sect"></div>
<table width="100%" cellpadding="0" cellspacing="1" style="background:#587AB5;" class="table">
	<tr>
		<th width="10%">#</th>
		<th width="40%">Key</th>
		<th width="40%">Created</th>
		<th width="10%">Is Valid?</th>
	</tr>
	<?php
		$FindKeysSQL = "SELECT * FROM `BetaKeys` WHERE `UserID`=$_SESSION[userID]";
		$FindKeys = mysqli_query($conn,$FindKeysSQL);
		while($KeyRow = mysqli_fetch_assoc($FindKeys)) {
			?>
			<tr style="color:#444;text-align:center;">
				<td><?php echo (int) $KeyRow["ID"]; ?></td>
				<td><?php echo $KeyRow["Key"]; ?></td>
				<td><?php echo $KeyRow["Created"]; ?></td>
				<td><?php echo $KeyRow["IsValid"]; ?></td>
			</tr>
			<?php
		}
	?>
</table>
<div class="sect"></div>
<form method="POST">
<?php
	if($ShowTimeError) {
		?>
		<div style="color:#C00000;font-weight:bold;font-size:12px;">Please wait 24 hours before creating another key</div>
		<script>
		setTimeout(function() {
			window.location = "invite";
		}, 3000);
		</script>
		<?php
	} else {
		?>
	<input type="hidden" name="CreateKey" value="CreateKey">
	<input type="submit" value="Create">
	<?php
	}
	?>
</form>
<?php
	include("./Site/Footer.php");
?>