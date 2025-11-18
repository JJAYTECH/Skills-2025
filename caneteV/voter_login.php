<?php
session_start();
require_once "db.php";

$error = "";

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $voter_id = $_POST["voter_id"];
    $password = $_POST["password"];

    $stmt = $conn->prepare("SELECT * FROM voters WHERE voter_id = ? AND password = ? AND status = 1");
    $stmt->bind_param("ss", $voter_id, $password);
    $stmt->execute();
    $result = $stmt->get_result();
    $voter = $result->fetch_assoc();

    if ($voter) {
        if ($voter["voted"]) {
            $error = "You already voted.";
        } else {
            $_SESSION["voter"] = $voter;
            header("Location: voter_vote.php");
            exit;
        }
    } else {
        $error = "Invalid credentials or inactive voter.";
    }
}
?>
<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>Voter Login</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            background: #f4f6f9;
            margin: 0;
        }
        .box {
            max-width: 400px;
            margin: 80px auto;
            background: white;
            padding: 20px;
            box-shadow: 0 0 5px rgba(0,0,0,0.15);
            border-radius: 4px;
        }
        h2 { text-align: center; }
        label { display: block; font-weight: bold; margin-bottom: 5px; }
        input[type="text"], input[type="password"] {
            width: 100%;
            padding: 8px;
            margin-bottom: 10px;
        }
        button {
            width: 100%;
            padding: 8px;
            background: #3498db;
            color: white;
            border: none;
            border-radius: 3px;
            cursor: pointer;
        }
        .error { color: red; margin-bottom: 10px; text-align: center; }
    </style>
</head>
<body>
<div class="box">
    <h2>Voter Login</h2>
    <?php if ($error): ?>
        <div class="error"><?php echo htmlspecialchars($error); ?></div>
    <?php endif; ?>
    <form method="post">
        <label>Voter ID</label>
        <input type="text" name="voter_id" required>
        <label>Password</label>
        <input type="password" name="password" required>
        <button type="submit">Login and Vote</button>
    </form>
</div>
</body>
</html>
