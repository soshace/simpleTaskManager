<?php

require_once(__DIR__ . "/../utils/app_utils.php");
require_once(__DIR__ . "/../utils/wallet_utils.php");
header('Content-Type: application/json');


if (!check_authentication()) {
    show_error('You must be logged in', 401);
}
$taskId = null;
$username = get_username();
$userId = get_user_id();
$user_type = get_user_type();

if ($userId == -1 || $username == null || $user_type == -1) {
    show_error('You must be logged in', 401);
}

if (isset($_POST[FIELD_ISSUE_ID])) {
    if (is_numeric($_POST[FIELD_ISSUE_ID])) {
        $taskId = intval($_POST[FIELD_ISSUE_ID]);
    } else {
        show_error('Task id must be int', 403);
    }
} else {
    show_error('Task id shouldn\'t be empty', 403);
}
require_once(__DIR__. "/../utils/database_util.php");

$query = "SELECT fromUserId FROM issues WHERE id = ?;";
$get_user_id_statement = mysqli_stmt_init($db_connection);

if (mysqli_stmt_prepare($get_user_id_statement, $query)) {
    mysqli_stmt_bind_param($get_user_id_statement, 'i', $taskId);
    mysqli_stmt_execute($get_user_id_statement);
    mysqli_stmt_bind_result($get_user_id_statement, $fromUserId);
    if (!mysqli_stmt_fetch($get_user_id_statement)) {
        show_error_stmt(mysqli_stmt_error($get_user_id_statement), 500, $db_connection, $get_user_id_statement);
    }
} else {
    show_error_stmt(mysqli_stmt_error($get_user_id_statement), 500, $db_connection, $get_user_id_statement);
}

mysqli_stmt_close($get_user_id_statement);

calc_user_wallet($db_connection, $fromUserId, USER_CUSTOMER);

$query = "UPDATE issues SET toUserId = ?, toUsername = ?, issueType = 'C', tsEdited = ? WHERE id = ? AND issueType = 'O' AND blocked = 'F';";
$complete_tasks_statement = mysqli_stmt_init($db_connection);
if (mysqli_stmt_prepare($complete_tasks_statement, $query)) {
    mysqli_stmt_bind_param($complete_tasks_statement, 'isii', $userId, $username, get_current_time_in_mills(), $taskId);
    mysqli_stmt_execute($complete_tasks_statement);
    if (mysqli_stmt_affected_rows($complete_tasks_statement) != 1) {
        show_error_stmt('TaskDeleted', 403, $db_connection, $complete_tasks_statement);
    }
} else {
    show_error_stmt(mysqli_stmt_error($complete_tasks_statement), 500, $db_connection, $complete_tasks_statement);
}

echo json_encode(calc_user_wallet($db_connection, $userId, $user_type));

mysqli_stmt_close($complete_tasks_statement);
mysqli_close($db_connection);