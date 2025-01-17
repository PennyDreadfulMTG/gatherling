<?php

declare(strict_types=1);

use Gatherling\Models\Player;

require_once 'lib.php';
Player::logOut();
header('location: index.php');
