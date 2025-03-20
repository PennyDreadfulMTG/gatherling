<?php

declare(strict_types=1);

use Gatherling\Models\Player;

require_once __DIR__ . '/bootstrap.php';
Player::logOut();
header('location: index.php');
