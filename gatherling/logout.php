<?php

use Gatherling\Player;

include 'lib.php';
Player::Logout();
header('location: index.php');
