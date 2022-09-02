<?php
include 'lib.php';
session_start();
print_header('Login');

?>
<div class="grid_10 suffix_1 prefix_1">
    <div id="gatherling_main" class="box">
        <div class="uppertitle"> Login to Gatherling </div>
            <center><h3>Resetting your Gatherling password</h3>
            <!-- Chat '<code>!reset <?php echo $CONFIG['infobot_prefix'] ?></code>' to pdbot to get a replacement <br />
            or</br> -->
            Message a Gatherling Administrator over MTGO or Discord <br />
    </div> <!-- gatherling_main -->
</div> <!-- grid 10 pre 1 suff 1 -->

<?php print_footer(); ?>
