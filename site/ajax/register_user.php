<?php
/*
 * Registering user with username and password pair
 *
 * Gets from POST request username and password
 */

require_once(__DIR__ . "/../utils/app_utils.php");
header('Content-Type: application/json');

$username = null;
$password = null;
$userType = null;

$response = array();

if (isset($_POST[FIELD_USERNAME])) {
    $username = $_POST[FIELD_USERNAME];
    if (strlen($username) < 5) {
        show_error('Username must be at least 5 chars', 403);
    } elseif (!ctype_alnum($username)) {
        show_error('Username can only contain alphanumeric characters', 403);
    }
} else {
    show_error('Username must not be empty', 403);
}
if (isset($_POST[FIELD_PASSWORD])) {
    $password = $_POST['password'];
    if (strlen($password) < 5) {
        show_error('Password must be at least 5 chars', 403);
    }
} else {
    show_error('Password must not be empty', 403);
}
if (isset($_POST[FIELD_USER_TYPE])) {
    $userType = $_POST[FIELD_USER_TYPE];
    if ($userType != USER_CUSTOMER && $userType != USER_PERFORMER) {
        show_error('Incorrect user type', 403);
    }
} else {
    show_error('User type must not be empty', 403);
}

require_once(__DIR__ . "/../utils/database_util.php");

$username = trim($username);
$username = strtolower($username);
$username = mysqli_real_escape_string($db_connection, $username);
$password = password_hash($password, PASSWORD_BCRYPT);

$add_user_statement = mysqli_stmt_init($db_connection);
$query = "INSERT INTO `users` (`username`, `password`, `userType`) VALUES (?, ?, ?);";
if (mysqli_stmt_prepare($add_user_statement, $query)) {

    mysqli_stmt_bind_param($add_user_statement, 'sss', $username, $password, $userType);
    mysqli_stmt_execute($add_user_statement);
    if (mysqli_stmt_affected_rows($add_user_statement) != 1) {
        http_response_code(403);
        $result['reason'] = 'There is another user with same username';
    }
    if (!empty($result)) {
        echo json_encode($result);
    }
}
mysqli_stmt_close($add_user_statement);
mysqli_close($db_connection);