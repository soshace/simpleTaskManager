<?php

require_once(__DIR__ . "/../utils/app_utils.php");
header('Content-Type: application/json');

$user_id = -1;
$moneyAmount = -1;

if (isset($_POST[FIELD_AMOUNT])) {
    if (is_numeric($_POST[FIELD_AMOUNT])) {
        $moneyAmount = floatval($_POST[FIELD_AMOUNT]);
        if ($moneyAmount < 0.01) {
            show_error('It has to be more than $0.01', 403);
        }
        if ($moneyAmount > MAX_AMOUNT_OF_MONEY) {
            show_error('Do you really have so much cash?', 403);
        }
    } else {
        show_error('It must be float', 403);
    }
} else {
    show_error('Amount of money should be not empty', 403);
}

$moneyAmount = (floor($moneyAmount * 100)) / 100;
$user_id = get_user_id();
$user_type = get_user_type();

if ($user_id == -1) {
    show_error('You are not logged in', 401);
}

if ($user_type != USER_CUSTOMER) {
    show_error('You can\'t add money on your account', 403);
}

require_once(__DIR__ . "/../utils/database_util.php");

$add_money_statement = mysqli_stmt_init($db_connection);
$query = "INSERT INTO issues(title, fromUserId, fromUsername, toUserId, price, issueType, ts) VALUE ('', 0, 'Sys', ?, ?, 'A', ?)";

if (mysqli_stmt_prepare($add_money_statement, $query)) {
    mysqli_stmt_bind_param($add_money_statement, 'idi',
        $user_id,
        $moneyAmount,
        get_current_time_in_mills()
        );
    mysqli_stmt_execute($add_money_statement);
    if (mysqli_stmt_affected_rows($add_money_statement) != 1) {
        show_error_stmt(mysqli_stmt_error($add_money_statement), 501, $db_connection, $add_money_statement);
    }
} else {
    show_error_stmt(mysqli_stmt_errno($add_money_statement), 500, $db_connection, $add_money_statement);
}
//echo json_encode(calc_user_wallet($db_connection, $user_id, $user_type));
require_once(__DIR__.'/../utils/wallet_utils.php');
echo json_encode(calc_user_wallet($db_connection, $user_id, $user_type));

mysqli_stmt_close($add_money_statement);
mysqli_close($db_connection);