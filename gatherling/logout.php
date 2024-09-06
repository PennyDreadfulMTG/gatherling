<?php

use Gatherling\Models\Player;

require_once 'lib.php';
Player::Logout();
header('location: index.php');
