<?php
/*
 * Sign in user with username and password pair
 *
 * Gets from POST request username and password
 */

require_once(__DIR__ . "/../utils/app_utils.php");

header('Content-Type: application/json');

// Initializing values to login user
$username = null;
$password = null;

// Array for response
$response = array();

// Check username
if (isset($_POST[FIELD_USERNAME])) {
    $username = $_POST[FIELD_USERNAME];
} else {
    show_error('Username must not be empty', 403);
}

// Check password
if (isset($_POST[FIELD_PASSWORD])) {
    $password = $_POST[FIELD_PASSWORD];
} else {
    show_error('Password must not be empty', 403);
}

require_once(__DIR__ . "/../utils/database_util.php");
require_once(__DIR__ . "/../utils/wallet_utils.php");

$query = "SELECT id, userType, password FROM `users` WHERE username = ? LIMIT 1;";
$select_user_statement = mysqli_stmt_init($db_connection);
if (mysqli_stmt_prepare($select_user_statement, $query)) {
    // Trying to find user in database with username which we got in POST request
    mysqli_stmt_bind_param($select_user_statement, 's', $username);
    mysqli_stmt_execute($select_user_statement);
    mysqli_stmt_store_result($select_user_statement);

    // Check if we found some user
    if (mysqli_stmt_num_rows($select_user_statement) != 1) {
        // If we didn't find than response with 401 Auth fail
        show_error_stmt('Wrong username or password', 401, $db_connection, $select_user_statement);
    }
    // In other way if we found user lets check his password
    mysqli_stmt_bind_result($select_user_statement, $user_id, $user_type, $password_database);
    mysqli_stmt_fetch($select_user_statement);

    if (!password_verify($password, $password_database)) {
        // If it's not equal than response with 401 Auth fail
        show_error_stmt('Wrong username or password', 401, $db_connection, $select_user_statement);
    } else {
        // Otherwise lets store credentials in session
        session_start();

        $_SESSION[SESSION_USER] = array(
            FIELD_USER_ID => $user_id,
            FIELD_USERNAME => $username,
            FIELD_USER_TYPE => $user_type
        );

        $response[FIELD_USERNAME] = $username;
        $response[FIELD_USER_ID] = $user_id;
        $response[FIELD_USER_TYPE] = $user_type;
        $response[FIELD_WALLET] = calc_user_wallet($db_connection, $user_id, $user_type);
        die (json_encode($response));
    }
} else {
    show_error_stmt(mysqli_stmt_error($select_user_statement), 500, $db_connection, $select_user_statement);
}

mysqli_stmt_close($select_user_statement);
mysqli_close($db_connection);