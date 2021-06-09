<?php

use Gatherling\Player;

include 'lib.php';
session_start();
Player::Logout();
header('location: index.php');
