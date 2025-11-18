<?php
session_start();
require_once "db.php";

if (!isset($_SESSION["voter"])) {
    header("Location: voter_login.php");
    exit;
}

$voter = $_SESSION["voter"];
$message = "";
$error = "";

if ($_SERVER["REQUEST_METHOD"] == "POST") {

    if ($voter["voted"]) {
        $error = "You already voted.";
    } else {

        $conn->begin_transaction();

        try {
            // loop all positions
            $posRes = $conn->query("SELECT * FROM positions WHERE status = 1");
            while ($pos = $posRes->fetch_assoc()) {
                $pid = $pos["id"];
                $max = (int)$pos["max_seats"];
                $fieldName = "position_" . $pid;

                if (!isset($_POST[$fieldName])) {
                    continue;
                }

                $selectedCandidates = $_POST[$fieldName];

                if (!is_array($selectedCandidates)) {
                    $selectedCandidates = [$selectedCandidates];
                }

                if (count($selectedCandidates) > $max) {
                    throw new Exception("Too many selected candidates for " . $pos["name"]);
                }

                foreach ($selectedCandidates as $cid) {
                    $cid = (int)$cid;
                    $stmt = $conn->prepare(
                        "INSERT INTO votes (voter_id, candidate_id, position_id) VALUES (?, ?, ?)"
                    );
                    $stmt->bind_param("iii", $voter["id"], $cid, $pid);
                    $stmt->execute();
                }
            }

            // mark voter as voted
            $conn->query("UPDATE voters SET voted = 1 WHERE id = " . (int)$voter["id"]);

            $conn->commit();

            // update session copy
            $_SESSION["voter"]["voted"] = 1;
            $voter["voted"] = 1;

            $message = "Thank you. Your vote has been recorded.";
        } catch (Exception $e) {
            $conn->rollback();
            $error = "Error: " . $e->getMessage();
        }
    }
}

// fetch positions and candidates for display
$positions = [];
$posRes = $conn->query("SELECT * FROM positions WHERE status = 1 ORDER BY id");
while ($p = $posRes->fetch_assoc()) {
    $positions[$p["id"]] = $p;
}

$candidatesByPos = [];
if ($positions) {
    $posIds = implode(",", array_keys($positions));
    $res = $conn->query(
        "SELECT c.*, p.name AS position_name 
         FROM candidates c
         JOIN positions p ON c.position_id = p.id
         WHERE c.status = 1 AND c.position_id IN ($posIds)
         ORDER BY p.id, c.full_name"
    );
    while ($row = $res->fetch_assoc()) {
        $pid = $row["position_id"];
        if (!isset($candidatesByPos[$pid])) {
            $candidatesByPos[$pid] = [];
        }
        $candidatesByPos[$pid][] = $row;
    }
}
?>
<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>Voting Page</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            background: #f4f6f9;
            margin: 0;
        }
        .topbar {
            background: #2c3e50;
            color: white;
            padding: 10px 20px;
        }
        .topbar span { font-size: 14px; }
        .container {
            max-width: 900px;
            margin: 20px auto;
            background: white;
            padding: 20px;
            box-shadow: 0 0 5px rgba(0,0,0,0.15);
            border-radius: 4px;
        }
        h2 { margin-top: 0; }
        fieldset {
            margin-bottom: 15px;
            border: 1px solid #ccc;
        }
        legend {
            font-weight: bold;
        }
        .msg { padding: 8px; margin-bottom: 10px; border-radius: 3px; }
        .msg-success { background: #d4efdf; color: #145a32; }
        .msg-error { background: #f5b7b1; color: #7b241c; }
        button {
            padding: 8px 15px;
            background: #3498db;
            border: none;
            color: white;
            border-radius: 3px;
            cursor: pointer;
        }
        .logout {
            float: right;
            color: #ecf0f1;
            text-decoration: none;
            font-size: 14px;
        }
    </style>
</head>
<body>
<div class="topbar">
    <strong>Voter: <?php echo htmlspecialchars($voter["full_name"]); ?></strong>
    <span> [ID: <?php echo htmlspecialchars($voter["voter_id"]); ?>]</span>
    <a href="voter_logout.php" class="logout">Logout</a>
</div>

<div class="container">
    <h2>Election Voting</h2>

    <?php if ($message): ?>
        <div class="msg msg-success"><?php echo htmlspecialchars($message); ?></div>
    <?php endif; ?>

    <?php if ($error): ?>
        <div class="msg msg-error"><?php echo htmlspecialchars($error); ?></div>
    <?php endif; ?>

    <?php if ($voter["voted"]): ?>
        <p>You already voted. Thank you.</p>
    <?php else: ?>
        <form method="post">
            <?php foreach ($positions as $pid => $pos): ?>
                <?php
                $cands = isset($candidatesByPos[$pid]) ? $candidatesByPos[$pid] : [];
                if (!$cands) continue;
                ?>
                <fieldset>
                    <legend>
                        <?php echo htmlspecialchars($pos["name"]); ?>
                        (select up to <?php echo (int)$pos["max_seats"]; ?>)
                    </legend>
                    <?php foreach ($cands as $c): ?>
                        <div>
                            <label>
                                <input type="checkbox"
                                       name="position_<?php echo $pid; ?>[]"
                                       value="<?php echo $c["id"]; ?>">
                                <?php echo htmlspecialchars($c["full_name"]); ?>
                                <?php if ($c["party"]): ?>
                                    [<?php echo htmlspecialchars($c["party"]); ?>]
                                <?php endif; ?>
                            </label>
                        </div>
                    <?php endforeach; ?>
                </fieldset>
            <?php endforeach; ?>
            <button type="submit">Submit Vote</button>
        </form>
    <?php endif; ?>
</div>
</body>
</html>
