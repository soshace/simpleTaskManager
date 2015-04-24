<?php
require_once(__DIR__ . "/../utils/app_utils.php");
require_once(__DIR__ . "/../utils/database_util.php");
header('Content-Type: application/json');

$startingFrom = -1;

if (isset($_GET[FIELD_OFFSET])) {
    if (is_numeric($_GET[FIELD_OFFSET])) {
        $startingFrom = (int)$_GET[FIELD_OFFSET];
    }
}

$queryOffset = '';

if ($startingFrom != -1) {
    $queryOffset = " AND issues.id < ?";
}

$query = "SELECT id, title, fromUserId, fromUsername, price"
    . " FROM issues WHERE blocked='F' AND issueType='O'"
    . $queryOffset
    . " ORDER BY id DESC LIMIT ?;";

$get_tasks_statement = mysqli_stmt_init($db_connection);

if (mysqli_stmt_prepare($get_tasks_statement, $query)) {
    if ($startingFrom == -1) {
        mysqli_stmt_bind_param($get_tasks_statement, 'i', intval(GET_TASK_LIMIT));
    } else {
        mysqli_stmt_bind_param($get_tasks_statement, 'ii', $startingFrom, intval(GET_TASK_LIMIT));
    }
    mysqli_stmt_execute($get_tasks_statement);
    mysqli_stmt_bind_result($get_tasks_statement, $taskId, $title, $fromUserId, $fromUsername, $price);
    $response = array();
    while (mysqli_stmt_fetch($get_tasks_statement)) {
        array_push($response, array(
            FIELD_ISSUE_ID => $taskId,
            FIELD_TITLE => $title,
            FIELD_USER_ID => $fromUserId,
            FIELD_USERNAME => $fromUsername,
            FIELD_PRICE => $price
        ));
    }
    echo(json_encode($response));
} else {
    show_error_stmt('', 500, $db_connection, $get_tasks_statement);
}

mysqli_stmt_close($get_tasks_statement);
mysqli_close($db_connection);