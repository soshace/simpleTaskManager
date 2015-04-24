<?php
require_once (__DIR__."/../utils/app_utils.php");

if (!check_authentication()) {
    show_error('You are not logged in', 401);
}

$user_id = get_user_id();
$user_type = get_user_type();

if ($user_id == -1 || $user_type == -1) {
    show_error('You are not logged in', 401);
}

require_once (__DIR__."/../utils/database_util.php");
require_once (__DIR__."/../utils/wallet_utils.php");

echo json_encode(calc_user_wallet($db_connection, $user_id, $user_type));