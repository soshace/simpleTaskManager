<?php
$option_db_username = "webapp";
$option_db_password = "password";
$option_db_url = "dbUsers";
$option_db_database = "db";

$db_connection = mysqli_connect($option_db_url, $option_db_username, $option_db_password, $option_db_database);
if (!$db_connection) {
    echo "Connection error";
}
mysqli_select_db($db_connection, 'db');