<?php
session_start();
require "db.php";

$error = "";

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $id = $_POST["voter_id"];
    $pw = $_POST["password"];

    $stmt = $conn->prepare("SELECT * FROM voters WHERE voter_id=? AND password=? AND status=1");
    $stmt->bind_param("ss", $id, $pw);
    $stmt->execute();
    $v = $stmt->get_result()->fetch_assoc();

    if ($v) {
        if ($v["voted"]) $error = "You already voted.";
        else {
            $_SESSION["voter"] = $v;
            header("Location: voter_vote.php");
            exit;
        }
    } else {
        $error = "Invalid login.";
    }
}
?>
<!DOCTYPE html>
<html>
<head>
<title>Voter Login</title>
<style>
body { font-family: Arial; text-align: center; padding-top: 40px; }
table { margin: auto; border-collapse: collapse; }
td { border: 1px solid black; padding: 5px; }
input { width: 150px; }
a, button { margin-top: 10px; display: inline-block; border: 1px solid black; padding: 5px 10px; background: white; cursor: pointer; text-decoration: none; }
</style>
</head>
<body>

<h3>Voter Login</h3>

<?php if ($error != "") echo "<p><b>".$error."</b></p>"; ?>

<form method="post">
<table>
<tr>
    <td><b>Voter ID</b></td>
    <td><input type="text" name="voter_id" required></td>
</tr>
<tr>
    <td><b>Password</b></td>
    <td><input type="password" name="password" required></td>
</tr>
</table>
<br>
<button type="submit">Login</button>
</form>

<br>
<a href="index.php">Back to Dashboard</a>

</body>
</html>
