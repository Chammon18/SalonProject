<?php
// Run via cron to auto-complete services after end time + break
require_once("dp.php");

$breakMinutes = 15;

$sql = "
    UPDATE appointments a
    JOIN services s ON s.id = a.service_id
    SET a.status = 'completed'
    WHERE a.status IN ('pending','approved')
      AND TIMESTAMPADD(
            MINUTE,
            (CASE WHEN s.duration > 1000 THEN ROUND(s.duration / 60) ELSE s.duration END) + $breakMinutes,
            TIMESTAMP(a.appointment_date, a.appointment_time)
          ) <= NOW()
";

$mysqli->query($sql);

if ($mysqli->error) {
    echo 'Error: ' . $mysqli->error;
    exit;
}

echo 'Auto-complete updated: ' . $mysqli->affected_rows . ' rows';
