<?php
include 'site/config.php';

$title = 'Not Found - Brixus';

include 'site/header.php';
?>
        <div class="box" style="margin:auto;padding:25px">
            <center>
                <img src="/assets/Gnome-dialog-warning.svg" title="Error" alt="Error" width="150">
                <div style="margin:15px 0 25px 0">
                    <h4 style="margin:0 0 10px 0">The item you requested does not exist</h4>
                    <span>You may have clicked an invalid link or mistyped the address</span>
                </div>
                <input type="button" value="Return Home" onclick="window.location='/'">
                <input type="button" value="Previous Page" onclick="history.back()">
            </center>
        </div>
<?php
include 'site/footer.php';
?>