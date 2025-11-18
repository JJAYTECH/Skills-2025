<?php
session_start();
require "db.php";

if (!isset($_SESSION["voter"])) {
    header("Location: voter_login.php");
    exit;
}

$voter = $_SESSION["voter"];
$message = "";
$error = "";

/* SUBMIT VOTE */
if ($_SERVER["REQUEST_METHOD"] == "POST") {

    if ($voter["voted"]) {
        $error = "You already voted.";
    } else {

        $conn->begin_transaction();

        try {
            $posRes = $conn->query("SELECT * FROM positions WHERE status=1");
            while ($p = $posRes->fetch_assoc()) {

                $pid = $p["id"];
                $max = (int)$p["max_seats"];
                $name = "position_" . $pid;

                if (!isset($_POST[$name])) continue;

                $selected = $_POST[$name];
                if (!is_array($selected)) $selected = [$selected];

                if (count($selected) > $max)
                    throw new Exception("Too many selections for " . $p["name"]);

                foreach ($selected as $cid) {
                    $cid = (int)$cid;
                    $conn->query("INSERT INTO votes (voter_id, candidate_id, position_id)
                                  VALUES (" . $voter["id"] . ", $cid, $pid)");
                }
            }

            $conn->query("UPDATE voters SET voted=1 WHERE id=" . $voter["id"]);
            $conn->commit();

            $_SESSION["voter"]["voted"] = 1;
            $voter["voted"] = 1;
            $message = "Thank you. Your vote has been recorded.";

        } catch (Exception $e) {
            $conn->rollback();
            $error = "Error: " . $e->getMessage();
        }
    }
}

/* LOAD POSITIONS AND CANDIDATES */
$positions = [];
$res = $conn->query("SELECT * FROM positions WHERE status=1 ORDER BY id");
while ($r = $res->fetch_assoc()) $positions[$r["id"]] = $r;

$candidates = [];
if ($positions) {
    $ids = implode(",", array_keys($positions));
    $q = $conn->query("
        SELECT c.*, p.name AS pos
        FROM candidates c
        JOIN positions p ON c.position_id=p.id
        WHERE c.status=1 AND c.position_id IN ($ids)
        ORDER BY p.id, c.full_name
    ");

    while ($c = $q->fetch_assoc()) {
        $pid = $c["position_id"];
        if (!isset($candidates[$pid])) $candidates[$pid] = [];
        $candidates[$pid][] = $c;
    }
}
?>
<!DOCTYPE html>
<html>
<head>
<title>Vote</title>
<style>
body { font-family: Arial; text-align: center; }
fieldset { margin: 10px auto; width: 60%; }
legend { font-weight: bold; }
.box { border: 1px solid black; padding: 8px; width: 60%; margin: auto; }
</style>
</head>
<body>

<p><b>Voter:</b> <?php echo $voter["full_name"]; ?>
 (ID: <?php echo $voter["voter_id"]; ?>)
 <a href="voter_logout.php">Logout</a>
</p>

<h3><b>Election Voting</b></h3>

<?php if ($message): ?>
    <div class="box"><?php echo $message; ?></div>
<?php endif; ?>

<?php if ($error): ?>
    <div class="box"><?php echo $error; ?></div>
<?php endif; ?>

<?php if ($voter["voted"]): ?>

<p>You already voted. Thank you.</p>

<?php else: ?>

<form method="post">

<?php foreach ($positions as $pid => $pos): ?>
    <?php if (!isset($candidates[$pid])) continue; ?>

    <fieldset>
        <legend>
            <?php echo $pos["name"]; ?> (max <?php echo $pos["max_seats"]; ?>)
        </legend>

        <?php foreach ($candidates[$pid] as $c): ?>
            <p>
                <label>
                    <input type="checkbox"
                           name="position_<?php echo $pid; ?>[]"
                           value="<?php echo $c["id"]; ?>">
                    <?php echo $c["full_name"]; ?>
                    <?php if ($c["party"]): ?>
                        [<?php echo $c["party"]; ?>]
                    <?php endif; ?>
                </label>
            </p>
        <?php endforeach; ?>
    </fieldset>

<?php endforeach; ?>

<p><button type="submit">Submit Vote</button></p>

</form>

<?php endif; ?>

<p><a href="index.php">Back to Dashboard</a></p>

</body>
</html>
