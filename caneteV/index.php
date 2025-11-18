<?php 
// simple home page
?>
<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>Election Dashboard</title>
    <style>
        body { font-family: Arial; text-align: center; padding-top: 30px; }
        table { margin: auto; border-collapse: collapse; width: 50%; }
        td { border: 1px solid #000; padding: 15px; }
        a { text-decoration: none; color: #000; }
    </style>
</head>
<body>

<h2>Election Management</h2>

<table>
    <tr><td><a href="positions.php">Manage Positions</a></td></tr>
    <tr><td><a href="candidates.php">Manage Candidates</a></td></tr>
    <tr><td><a href="voters.php">Manage Voters</a></td></tr>
    <tr><td><a href="voter_login.php">Voter Login</a></td></tr>
    <tr><td><a href="winners.php">View Winners</a></td></tr>
</table>

</body>
</html>
