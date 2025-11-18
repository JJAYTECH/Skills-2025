<?php
require "db.php";

$positions = [];
$pos = $conn->query("SELECT * FROM positions WHERE status=1 ORDER BY id");
while($p = $pos->fetch_assoc()) $positions[$p["id"]] = $p;

$res = $conn->query("
SELECT c.id, c.full_name, c.position_id,
p.name AS position_name, p.max_seats,
COUNT(v.id) AS votes
FROM candidates c
JOIN positions p ON c.position_id=p.id
LEFT JOIN votes v ON v.candidate_id=c.id
WHERE c.status=1
GROUP BY c.id
ORDER BY p.id, votes DESC
");

$grouped = [];
while($r = $res->fetch_assoc()){
    $grouped[$r["position_id"]][] = $r;
}
?>
<!DOCTYPE html>
<html>
<head>
<title>Winners</title>
<style>
body{font-family:Arial;text-align:center}
table{margin:auto;border-collapse:collapse}
td,th{border:1px solid black;padding:5px}
</style>
</head>
<body>

<h3>Election Winners</h3>

<table>
<tr>
<th>Position</th>
<th>Winner</th>
<th>Votes</th>
</tr>

<?php foreach($grouped as $pid=>$list): ?>
    <?php
    $max = $positions[$pid]["max_seats"];
    $win = array_slice($list,0,$max);
    ?>
    <?php foreach($win as $w): ?>
    <tr>
        <td><?php echo $positions[$pid]["name"]; ?></td>
        <td><?php echo $w["full_name"]; ?></td>
        <td><?php echo $w["votes"]; ?></td>
    </tr>
    <?php endforeach; ?>
<?php endforeach; ?>

</table>

<br>
<a href="index.php">Back</a>

</body>
</html>
