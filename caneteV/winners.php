<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

require_once "db.php";

// get all active positions
$positions = [];
$posRes = $conn->query("SELECT * FROM positions WHERE status = 1 ORDER BY id");
while ($p = $posRes->fetch_assoc()) {
    $positions[$p["id"]] = $p;
}

// compute vote counts
$res = $conn->query("
    SELECT 
        c.id AS candidate_id,
        c.full_name,
        c.position_id,
        p.name AS position_name,
        p.max_seats,
        COUNT(v.id) AS total_votes
    FROM candidates c
    JOIN positions p ON c.position_id = p.id
    LEFT JOIN votes v ON v.candidate_id = c.id
    WHERE c.status = 1
    GROUP BY c.id, c.full_name, c.position_id, p.name, p.max_seats
    ORDER BY p.id, total_votes DESC
");

$grouped = [];

// group candidates per position
while ($row = $res->fetch_assoc()) {
    $pid = $row["position_id"];
    if (!isset($grouped[$pid])) {
        $grouped[$pid] = [];
    }
    $grouped[$pid][] = $row;
}
?>
<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>Election Winners</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            background: #f4f6f9;
            margin: 0;
        }
        .topbar {
            background: #2c3e50;
            color: white;
            padding: 15px;
            text-align: center;
        }
        .container {
            max-width: 900px;
            margin: 20px auto;
            background: white;
            padding: 20px;
            box-shadow: 0 0 5px rgba(0,0,0,0.15);
        }
        table {
            width: 100%;
            border-collapse: collapse;
            font-size: 14px;
        }
        th, td {
            padding: 8px;
            border: 1px solid #ddd;
        }
        th { background: #ecf0f1; }
    </style>
</head>
<body>

<div class="topbar">
    <h2>Election Winners</h2>
</div>

<div class="container">

<table>
    <tr>
        <th>Elective Position</th>
        <th>Winner</th>
        <th>Total Votes</th>
    </tr>

    <?php foreach ($grouped as $pid => $candidates): ?>
        <?php
        $pos = $positions[$pid];
        $maxSeats = (int)$pos["max_seats"];

        // get only the top N winners
        $winners = array_slice($candidates, 0, $maxSeats);
        ?>
        
        <?php foreach ($winners as $winner): ?>
        <tr>
            <td><?php echo htmlspecialchars($pos["name"]); ?></td>
            <td><?php echo htmlspecialchars($winner["full_name"]); ?></td>
            <td><?php echo (int)$winner["total_votes"]; ?></td>
        </tr>
        <?php endforeach; ?>
    <?php endforeach; ?>

</table>

</div>
</body>
</html>
