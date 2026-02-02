<?php

/**
 * Add a notification for a user
 *
 * @param mysqli $mysqli      Database connection
 * @param int    $user_id     ID of the user to notify
 * @param string $message     Notification message
 * @param int    $appointment_id Optional appointment ID (for link)
 */
function selectData($table, $mysqli, $where = '', $select = '*', $order = '')
{
    $query = "SELECT $select FROM `$table`";
    if (!empty($where)) {
        $query .= " WHERE $where";
    }
    if (!empty($order)) {
        $query .= " $order";
    }
    return $mysqli->query($query);
}

function updateData($table, $mysqli, $data, $where)
{
    $set = [];
    foreach ($data as $key => $value) {
        $set[] = "`$key`='" . $mysqli->real_escape_string($value) . "'";
    }

    $setClause = implode(", ", $set);

    $query = "UPDATE `$table` SET $setClause WHERE $where";

    // Debug once
    // echo $query; exit;

    return $mysqli->query($query);
}


function addNotification($mysqli, $user_id, $message, $appointment_id = null)
{
    $user_id = (int)$user_id;
    $appointment_id = $appointment_id ? (int)$appointment_id : "NULL";
    $message = $mysqli->real_escape_string($message);

    $sql = "INSERT INTO notifications (user_id, appointment_id, message, created_at)
            VALUES ($user_id, $appointment_id, '$message', NOW())";
    $mysqli->query($sql);
}
