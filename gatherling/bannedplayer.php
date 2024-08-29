<?php

use Gatherling\Player;

require_once 'lib.php';
$player = Player::getSessionPlayer();

print_header('You have been banned');
?>
<div class="grid_10 suffix_1 prefix_1">
<div id="gatherling_main" class="box">
<div class="uppertitle"> You have been banned! </div>
<?php
echo '<center>You have been banned from this Series and cannot participate in any of its events.</center>';
?>
</div> <!-- gatherling_main box -->
</div> <!-- grid 10 suff 1 pre 1 -->
<?php print_footer(); ?>
