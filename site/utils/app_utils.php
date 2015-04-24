<?php

DEFINE("USER_CUSTOMER", '0');
DEFINE("USER_PERFORMER", '1');

DEFINE("TASK_OPENED", 'O');
DEFINE("TASK_DELETED", 'D');
DEFINE("TASK_COMPLETED", 'C');
DEFINE("MONEY_ADDED", 'A');


DEFINE("FIELD_USERNAME", "username");
DEFINE("FIELD_PASSWORD", "password");
DEFINE("FIELD_USER_TYPE", "userType");

DEFINE("FIELD_TITLE", "title");
DEFINE("FIELD_PRICE", "price");
DEFINE("FIELD_AMOUNT", 'amount');

DEFINE("FIELD_USER_ID", 'userId');
DEFINE("FIELD_WALLET", 'wallet');
DEFINE("FIELD_ISSUE_ID", 'taskId');

DEFINE("FIELD_COMMISSION", 'commission');
DEFINE("FIELD_ISSUE_TYPE", 'issueType');

DEFINE("FIELD_FROM_USER_ID", 'fromUserId');
DEFINE("FIELD_TO_USER_ID", 'toUserId');
DEFINE("FIELD_TS", 'ts');
DEFINE("FIELD_TS_EDITED", 'tsEdited');
DEFINE("REASON", 'reason');

DEFINE("SESSION_USER", 'user');

DEFINE("FIELD_OFFSET", 'offset');

DEFINE("GET_TASK_LIMIT", 20);
DEFINE("MAX_AMOUNT_OF_MONEY", 1000000000);
DEFINE("COMMISSION", 0.20);
DEFINE("MINIMAL_PRICE", 1);

function check_authentication()
{
    if (!session_id()) {
        session_start();
    }
    return isset($_SESSION[SESSION_USER]);
}

function get_user_id() {
    if (check_authentication()) {
        return $_SESSION[SESSION_USER][FIELD_USER_ID];
    }
    return -1;
}

function get_username() {
    if (check_authentication()) {
        return $_SESSION[SESSION_USER][FIELD_USERNAME];
    }
    return null;
}

function get_user_type() {
    if (check_authentication()) {
        return $_SESSION[SESSION_USER][FIELD_USER_TYPE];
    }
    return -1;
}

function show_error($error, $http_code) {
    http_response_code($http_code);
    die (json_encode(array(REASON => $error)));
}

function show_error_db($error, $http_code, $db) {
    mysqli_close($db);
    show_error($error, $http_code);
}

function show_error_stmt($error, $http_code, $db, $stmt) {
    mysqli_stmt_close($stmt);
    show_error_db($error, $http_code, $db);
}

function get_current_time_in_mills() {
    return (int) round(microtime(true) * 10000);
}