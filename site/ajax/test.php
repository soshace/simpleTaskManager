<?php
require_once (__DIR__ ."/../utils/app_utils.php");
require_once (__DIR__ ."/../utils/database_util.php");
require_once (__DIR__ ."/../utils/wallet_utils.php");

echo json_encode(calc_user_wallet($db_connection, 1, USER_CUSTOMER));