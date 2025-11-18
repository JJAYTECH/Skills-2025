<?php
require "db.php";

/* SAVE OR UPDATE */
if (isset($_POST["save_position"])) {
    $id = $_POST["id"] ?? 0;
    $name = $_POST["name"];
    $desc = $_POST["description"];

    if ($id > 0) {
        $conn->query("UPDATE positions SET name='$name', description='$desc' WHERE id=$id");
    } else {
        $conn->query("INSERT INTO positions(name, description) VALUES('$name','$desc')");
    }
    header("Location: positions.php");
    exit;
}

/* ACTIONS */
if (isset($_GET["pos_action"])) {
    $id = $_GET["id"];
    if ($_GET["pos_action"] == "deactivate") $conn->query("UPDATE positions SET status=0 WHERE id=$id");
    if ($_GET["pos_action"] == "activate")   $conn->query("UPDATE positions SET status=1 WHERE id=$id");
    if ($_GET["pos_action"] == "delete")      $conn->query("DELETE FROM positions WHERE id=$id");
    header("Location: positions.php");
    exit;
}

/* EDIT LOAD */
$edit = null;
if (isset($_GET["edit_id"])) {
    $id = $_GET["edit_id"];
    $edit = $conn->query("SELECT * FROM positions WHERE id=$id")->fetch_assoc();
}
?>
<!DOCTYPE html>
<html>
<head>
<title>Positions</title>
<style>
body { font-family: Arial; text-align: center; }
table { margin: auto; border-collapse: collapse; }
td, th { border: 1px solid black; padding: 5px; }
</style>
</head>
<body>

<p><a href="index.php"><b>Back to Dashboard</b></a></p>

<h2><b>Positions</b></h2>

<form method="post">
<input type="hidden" name="id" value="<?php echo $edit['id'] ?? ''; ?>">

<table>
<tr><td><b>Name</b></td>
<td><input type="text" name="name" required value="<?php echo $edit['name'] ?? ''; ?>"></td></tr>

<tr><td><b>Description</b></td>
<td><input type="text" name="description" value="<?php echo $edit['description'] ?? ''; ?>"></td></tr>

<tr><td colspan="2">
<input type="submit" name="save_position" value="<?php echo $edit ? 'Update' : 'Add'; ?>">
</td></tr>
</table>
</form>

<br>

<?php $res = $conn->query("SELECT * FROM positions ORDER BY id DESC"); ?>

<table>
<tr>
<th>ID</th>
<th>Name</th>
<th>Description</th>
<th>Status</th>
<th>Actions</th>
</tr>

<?php while ($r = $res->fetch_assoc()): ?>
<tr>
<td><?php echo $r["id"]; ?></td>
<td><?php echo $r["name"]; ?></td>
<td><?php echo $r["description"]; ?></td>
<td><?php echo $r["status"] ? "Active" : "Inactive"; ?></td>
<td>
<a href="positions.php?edit_id=<?php echo $r['id']; ?>"><b>Edit</b></a> |
<a href="positions.php?pos_action=<?php echo $r["status"] ? "deactivate":"activate"; ?>&id=<?php echo $r['id']; ?>">
<b><?php echo $r["status"] ? "Deactivate" : "Activate"; ?></b></a> |
<a onclick="return confirm('Delete this?')" href="positions.php?pos_action=delete&id=<?php echo $r['id']; ?>"><b>Delete</b></a>
</td>
</tr>
<?php endwhile; ?>
</table>

</body>
</html>
