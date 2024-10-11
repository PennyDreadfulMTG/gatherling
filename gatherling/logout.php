<?php

declare(strict_types=1);

use Gatherling\Models\Player;

require_once 'lib.php';
Player::Logout();
header('location: index.php');
