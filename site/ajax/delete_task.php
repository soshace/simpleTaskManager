<?php

require_once(__DIR__ . "/../utils/app_utils.php");
header('Content-Type: application/json');


if (!check_authentication()) {
    show_error('You must be logged in', 401);
}


$taskId = null;
$user_id = get_user_id();
$user_type = get_user_type();
if ($user_id == -1 || $user_type == -1) {
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

$query = "UPDATE issues SET issueType = 'D', tsEdited = ? WHERE id = ? AND fromUserId = ? AND issueType = 'O' AND blocked = 'F';";
$delete_tasks_statement = mysqli_stmt_init($db_connection);
if (mysqli_stmt_prepare($delete_tasks_statement, $query)) {
    mysqli_stmt_bind_param($delete_tasks_statement, 'iii', get_current_time_in_mills(), $taskId, $user_id);
    mysqli_stmt_execute($delete_tasks_statement);
    if (mysqli_stmt_affected_rows($delete_tasks_statement) != 1) {
        show_error_stmt('', 403, $db_connection, $delete_tasks_statement);
    }
} else {
    show_error_stmt(mysqli_stmt_error($delete_tasks_statement), 500, $db_connection, $delete_tasks_statement);
}

require_once (__DIR__ . '/../utils/wallet_utils.php');
echo json_encode(calc_user_wallet($db_connection, $user_id, $user_type));

mysqli_stmt_close($delete_tasks_statement);
mysqli_close($db_connection);