<?php
require_once(__DIR__ . "/app_utils.php");

// Function adds wallet if it's not exists
function add_wallet($user_id, $db_connection)
{
    $query_add = "INSERT IGNORE INTO wallets(userId, money, blocked, paid, ts) VALUE (?, 0, 0, 0, 0);";
    $add_wallet_statement = mysqli_stmt_init($db_connection);
    if (mysqli_stmt_prepare($add_wallet_statement, $query_add)) {
        mysqli_stmt_bind_param($add_wallet_statement, 'i', $user_id);
        mysqli_stmt_execute($add_wallet_statement);
        if (mysqli_stmt_affected_rows($add_wallet_statement) != 1) {
            show_error_stmt(mysqli_stmt_error($add_wallet_statement), 500, $db_connection, $add_wallet_statement);
        }
        mysqli_stmt_close($add_wallet_statement);
    } else {
        show_error_stmt(mysqli_stmt_error($add_wallet_statement), 500, $db_connection, $add_wallet_statement);
    }
}

function calc_user_wallet($db_connection, $user_id, $user_type)
{
    $get_wallet_statement = mysqli_stmt_init($db_connection);
    $balance = 0;
    $blocked = 0;
    $paid = 0;
    $wallet_timestamp = 0;
    $new_timestamp = 0;

    // Try to get wallet from DB
    $query = "SELECT money, blocked, paid, ts FROM wallets WHERE userId = ?;";
    if (mysqli_stmt_prepare($get_wallet_statement, $query)) {
        mysqli_stmt_bind_param($get_wallet_statement, 'i', $user_id);
        mysqli_stmt_execute($get_wallet_statement);
        mysqli_stmt_store_result($get_wallet_statement);
        if (mysqli_stmt_num_rows($get_wallet_statement) != 1) {
            mysqli_stmt_close($get_wallet_statement);
            // If there is no user's wallet in wallets db yet than lets try to create one
            add_wallet($user_id, $db_connection);
        } else {
            // Otherwise lets sync info from wallet
            mysqli_stmt_bind_result($get_wallet_statement, $balance, $blocked, $paid, $wallet_timestamp);
            mysqli_stmt_fetch($get_wallet_statement);
        }

        mysqli_stmt_close($get_wallet_statement);

        // So right now we have information about
        // user's account and so let's try to update this data

        // Set new timestamp of user's wallet to current
        $new_timestamp = $wallet_timestamp;

//        $wallet_timestamp = 0;
        $issues = array();

        $query = "SELECT id, fromUserId, toUserId, price, commission, issueType, ts, tsEdited FROM issues WHERE (fromUserId = ? OR toUserId = ?) AND (ts > ? OR tsEdited > ?);";
        $get_issues_statement = mysqli_stmt_init($db_connection);
        if (mysqli_stmt_prepare($get_issues_statement, $query)) {
            mysqli_stmt_bind_param($get_issues_statement, 'iiii', $user_id, $user_id, $wallet_timestamp, $wallet_timestamp);
            mysqli_stmt_execute($get_issues_statement);
            mysqli_stmt_bind_result($get_issues_statement,
                $task_id,
                $from_user_id,
                $to_user_id,
                $price,
                $commission,
                $issue_type,
                $task_timestamp,
                $task_timestamp_edited
            );
            while (mysqli_stmt_fetch($get_issues_statement)) {
                array_push($issues, array(
                    FIELD_ISSUE_ID => $task_id,
                    FIELD_FROM_USER_ID => $from_user_id,
                    FIELD_TO_USER_ID => $to_user_id,
                    FIELD_PRICE => $price,
                    FIELD_COMMISSION => $commission,
                    FIELD_ISSUE_TYPE => $issue_type,
                    FIELD_TS => $task_timestamp,
                    FIELD_TS_EDITED => $task_timestamp_edited
                ));
                $new_timestamp = max($new_timestamp, $task_timestamp, $task_timestamp_edited);
            }
        } else {
            show_error_stmt(mysqli_stmt_error($get_issues_statement), 500, $db_connection, $get_issues_statement);
        }
//        echo json_encode($issues);

        if ($user_type == USER_CUSTOMER) {
            // At first we have to calc adding money to user's balance and deleted tasks

            foreach ($issues as $issue) {
                if ($issue[FIELD_ISSUE_TYPE] == 'A') {
                    $balance += $issue[FIELD_PRICE];
                }
            }

            foreach ($issues as $issue) {
                if ($issue[FIELD_ISSUE_TYPE] == 'D') {
                    // if we calced this task as opened, than we have to revert everything
                    $sum = $issue[FIELD_PRICE] + $issue[FIELD_COMMISSION];
                    $balance += $sum;
                    $blocked -= $sum;
                }
            }

            $tasks_to_block = array();

            foreach ($issues as $issue) {
                if ($issue[FIELD_ISSUE_TYPE] == 'O') {
                    $sum = $issue[FIELD_PRICE] + $issue[FIELD_COMMISSION];
                    if ($balance - $sum < 0) {
                        array_push($tasks_to_block, $issue[FIELD_ISSUE_ID]);
                    } else {
                        $balance -= $sum;
                        $blocked += $sum;
                    }
                }
                if ($issue[FIELD_ISSUE_TYPE] == 'C' || $issue[FIELD_ISSUE_TYPE] == 'D') {
                    if ($issue[FIELD_TS] > $wallet_timestamp) {
                        $sum = $issue[FIELD_PRICE] + $issue[FIELD_COMMISSION];
                        $balance -= $sum;
                        $blocked += $sum;

                    }
                }
            }


            foreach ($issues as $issue) {
                if ($issue[FIELD_ISSUE_TYPE] == 'C') {
                    $sum = $issue[FIELD_PRICE] + $issue[FIELD_COMMISSION];
                    $blocked -= $sum;
                    $paid += $sum;
                }
            }



            // TODO do in background
            $query = "UPDATE issues SET issueType = 'D', tsEdited = ? WHERE id = ? AND issueType = 'O';";
            $set_deleted_tasks_statement = mysqli_stmt_init($db_connection);
            if (mysqli_stmt_prepare($set_deleted_tasks_statement, $query)) {
                foreach ($tasks_to_block as $id) {
                    mysqli_stmt_bind_param($set_deleted_tasks_statement, 'ii',
                        get_current_time_in_mills(),
                        $id
                    );
                    mysqli_stmt_execute($set_deleted_tasks_statement);
                }
            } else {
                show_error_stmt(mysqli_stmt_error($set_deleted_tasks_statement), 500, $db_connection, $set_deleted_tasks_statement);
            }

        }
        if ($user_type == USER_PERFORMER) {
            foreach ($issues as $issue) {
                if ($issue[FIELD_ISSUE_TYPE] == 'C') {
                    $balance += $issue[FIELD_PRICE];
                }
            }
        }

    } else {
        show_error_stmt(mysqli_stmt_error($get_wallet_statement), 500, $db_connection, $get_wallet_statement);
    }
//    $balance = round($balance * 100) / 100;
//    $blocked = round($blocked * 100) / 100;
//    $paid = round($paid * 100) / 100;

    $query = "UPDATE wallets SET money = ?, blocked = ?, paid = ?, ts = ? WHERE userId = ?";
    $update_wallet_statement = mysqli_stmt_init($db_connection);
//    echo json_encode(array($balance, $blocked, $paid, $user_id, $new_timestamp));
    if (mysqli_stmt_prepare($update_wallet_statement, $query)) {
        mysqli_stmt_bind_param($update_wallet_statement, 'dddii', $balance, $blocked, $paid, $new_timestamp, $user_id);
        mysqli_stmt_execute($update_wallet_statement);
    } else {
        show_error_stmt(mysqli_stmt_error($update_wallet_statement), 500, $db_connection, $update_wallet_statement);
    }
    mysqli_stmt_close($update_wallet_statement);
    return array(
        'balance' => $balance,
        'blocked' => $blocked,
        'paid' => $paid);
}
