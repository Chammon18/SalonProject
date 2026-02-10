<?php
session_start();
require_once(__DIR__ . '/../../public/dp.php');

$staffRes = $mysqli->query("
    SELECT
        st.id,
        st.name,
        GROUP_CONCAT(DISTINCT c.name ORDER BY c.name SEPARATOR ', ') AS skills,
        (
            SELECT s2.name
            FROM appointments a2
            JOIN services s2 ON s2.id = a2.service_id
            WHERE a2.staff_id = st.id AND a2.status = 'completed'
            GROUP BY s2.id, s2.name
            ORDER BY COUNT(*) DESC
            LIMIT 1
        ) AS best_service
    FROM staff st
    LEFT JOIN staff_categories sc ON sc.staff_id = st.id
    LEFT JOIN categories c ON c.id = sc.category_id
    GROUP BY st.id, st.name
    ORDER BY st.name ASC
    LIMIT 5
");

$team = [];
if ($staffRes && $staffRes->num_rows > 0) {
    while ($row = $staffRes->fetch_assoc()) {
        $team[] = $row;
    }
}

$imagePool = [
    "../image/hairservice.jpg",
    "../image/nialservice.jpg",
    "../image/eyelashservice.jpg",
    "../image/thae.jpg",
    "../image/waxing service.jpg",
];
?>
