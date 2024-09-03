<?php

use Gatherling\Models\Player;

include 'lib.php';
Player::Logout();
header('location: index.php');
