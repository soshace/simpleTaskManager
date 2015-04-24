<?php
require_once(__DIR__ . "/../utils/app_utils.php");
require_once(__DIR__ . "/../utils/database_util.php");
require_once(__DIR__ . "/../utils/wallet_utils.php");
header('Content-Type: application/json');

if (check_authentication()) {
    $result = array();
    $result[FIELD_USERNAME] = $_SESSION[SESSION_USER][FIELD_USERNAME];
    $result[FIELD_USER_TYPE] = $_SESSION[SESSION_USER][FIELD_USER_TYPE];
    $result[FIELD_USER_ID] = $_SESSION[SESSION_USER][FIELD_USER_ID];
    $result[FIELD_WALLET] = calc_user_wallet($db_connection, $result[FIELD_USER_ID], $result[FIELD_USER_TYPE]);
    echo json_encode($result);
} else {
    http_response_code(401);
}