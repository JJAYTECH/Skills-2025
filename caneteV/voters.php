<?php
require "db.php";

if(isset($_POST["save_voter"])) {
    $id   = $_POST["id"] ?? 0;
    $vid  = $_POST["voter_id"];
    $name = $_POST["full_name"];
    $pass = $_POST["password"];

    if($id)
        $conn->query("UPDATE voters SET voter_id='$vid', full_name='$name', password='$pass' WHERE id=$id");
    else
        $conn->query("INSERT INTO voters(voter_id,full_name,password) VALUES('$vid','$name','$pass')");

    header("Location: voters.php"); exit;
}

if(isset($_GET["voter_action"])) {
    $id = $_GET["id"];
    if($_GET["voter_action"]=="deactivate") $conn->query("UPDATE voters SET status=0 WHERE id=$id");
    if($_GET["voter_action"]=="activate")   $conn->query("UPDATE voters SET status=1 WHERE id=$id");
    if($_GET["voter_action"]=="delete")     $conn->query("DELETE FROM voters WHERE id=$id");
    header("Location: voters.php"); exit;
}

$edit = null;
if(isset($_GET["edit_id"])) {
    $edit = $conn->query("SELECT * FROM voters WHERE id=".$_GET["edit_id"])->fetch_assoc();
}
?>
<!DOCTYPE html>
<html>
<head>
<title>Voters Management</title>
<style>
body { font-family: Arial; text-align: center; }
table { margin: auto; border-collapse: collapse; }
td, th { border: 1px solid black; padding: 5px; }
</style>
</head>
<body>

<a href="index.php">Back</a>
<h3>Voters Management</h3>

<form method="post">
<input type="hidden" name="id" value="<?php echo $edit['id'] ?? ''; ?>">
<table>
<tr><td>Voter ID</td><td><input name="voter_id" required value="<?php echo $edit['voter_id'] ?? ''; ?>"></td></tr>
<tr><td>Full Name</td><td><input name="full_name" required value="<?php echo $edit['full_name'] ?? ''; ?>"></td></tr>
<tr><td>Password</td><td><input name="password" required value="<?php echo $edit['password'] ?? ''; ?>"></td></tr>
<tr><td colspan="2"><input type="submit" name="save_voter" value="<?php echo $edit ? 'Update' : 'Add'; ?>"></td></tr>
</table>
</form>

<?php $rows = $conn->query("SELECT * FROM voters ORDER BY id DESC"); ?>
<table>
<tr><th>ID</th><th>Voter ID</th><th>Name</th><th>Status</th><th>Actions</th></tr>
<?php while($r = $rows->fetch_assoc()): ?>
<tr>
<td><?php echo $r["id"]; ?></td>
<td><?php echo $r["voter_id"]; ?></td>
<td><?php echo $r["full_name"]; ?></td>
<td><?php echo $r["status"] ? "Active" : "Inactive"; ?></td>
<td>
<a href="?edit_id=<?php echo $r['id']; ?>">Edit</a> |
<a href="?voter_action=<?php echo $r['status'] ? 'deactivate' : 'activate'; ?>&id=<?php echo $r['id']; ?>">
<?php echo $r['status'] ? 'Deactivate' : 'Activate'; ?>
</a> |
<a onclick="return confirm('Delete?')" href="?voter_action=delete&id=<?php echo $r['id']; ?>">Delete</a>
</td>
</tr>
<?php endwhile; ?>
</table>

</body>
</html>
