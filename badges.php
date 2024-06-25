<?php
include 'site/config.php';

$title = 'Badges - Brixus';

include 'site/header.php';
?>
		<div class="box">
			<h3>Badges</h3>
			<?
			$get_badges = mysqli_query($conn, "SELECT * FROM `badgeinfo`");
			while($badge = mysqli_fetch_assoc($get_badges)) {
				echo '<div style="padding:10px;width:900px;">
						<img title="'.$badge["name"].'" src="data:image/png;base64,'.base64_encode(file_get_contents($badge["image"])).'" style="background:#FFF;border:1px solid #000;width:108px;"><div style="width:758px;display:inline-block;vertical-align:top;padding:0 10px;">
						<h5 style="padding-top:0;padding-left:0;">'.$badge["name"].'</h5>
						<span class="label">'.$badge["description"].'</span>
					</div>
					<div style="clear:both"></div>
				</div>';
			}
			?>
		</div>
<?
include("site/footer.php");
?>