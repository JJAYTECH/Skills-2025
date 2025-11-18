<?php
require "db.php";

/* SAVE OR UPDATE */
if (isset($_POST["save_candidate"])) {
    $id = $_POST["id"] ?? 0;
    $name = $_POST["full_name"];
    $pos = $_POST["position_id"];
    $party = $_POST["party"];

    if ($id > 0) {
        $conn->query("UPDATE candidates SET full_name='$name', position_id=$pos, party='$party' WHERE id=$id");
    } else {
        $conn->query("INSERT INTO candidates(full_name, position_id, party) VALUES('$name',$pos,'$party')");
    }
    header("Location:candidates.php");
    exit;
}

/* ACTIONS */
if (isset($_GET["cand_action"])) {
    $id = $_GET["id"];
    if ($_GET["cand_action"] == "deactivate") $conn->query("UPDATE candidates SET status=0 WHERE id=$id");
    if ($_GET["cand_action"] == "activate") $conn->query("UPDATE candidates SET status=1 WHERE id=$id");
    if ($_GET["cand_action"] == "delete") $conn->query("DELETE FROM candidates WHERE id=$id");
    header("Location:candidates.php");
    exit;
}

/* EDIT LOAD */
$edit = null;
if (isset($_GET["edit_id"])) {
    $id = $_GET["edit_id"];
    $edit = $conn->query("SELECT * FROM candidates WHERE id=$id")->fetch_assoc();
}
?>
<!DOCTYPE html>
<html>
<head>
<title>Candidates</title>
<style>
body { font-family: Arial; text-align: center; }
table { margin: auto; border-collapse: collapse; }
td, th { border: 1px solid black; padding: 5px; }
</style>
</head>
<body>

<a href="index.php"><b>Back to Dashboard</b></a>
<h2><b>Candidates</b></h2>

<form method="post">
<input type="hidden" name="id" value="<?php echo $edit['id'] ?? ''; ?>">

<table>
<tr><td><b>Full Name</b></td>
<td><input type="text" name="full_name" required value="<?php echo $edit['full_name'] ?? ''; ?>"></td></tr>

<tr><td><b>Position</b></td>
<td>
<select name="position_id" required>
<option value="">Select</option>
<?php
$p = $conn->query("SELECT id, name FROM positions WHERE status=1 ORDER BY name");
while ($row = $p->fetch_assoc()):
$sel = ($edit && $edit["position_id"] == $row["id"]) ? "selected" : "";
?>
<option value="<?php echo $row['id']; ?>" <?php echo $sel; ?>>
<?php echo $row['name']; ?>
</option>
<?php endwhile; ?>
</select>
</td></tr>

<tr><td><b>Party</b></td>
<td><input type="text" name="party" value="<?php echo $edit['party'] ?? ''; ?>"></td></tr>

<tr><td colspan="2">
<input type="submit" name="save_candidate" value="<?php echo $edit ? 'Update' : 'Add'; ?>">
</td></tr>
</table>
</form>

<br>

<?php
$list = $conn->query("
SELECT c.*, p.name AS pos 
FROM candidates c 
LEFT JOIN positions p ON c.position_id = p.id 
ORDER BY c.id DESC
");
?>

<table>
<tr>
<th>ID</th>
<th>Name</th>
<th>Position</th>
<th>Party</th>
<th>Status</th>
<th>Actions</th>
</tr>

<?php while ($r = $list->fetch_assoc()): ?>
<tr>
<td><?php echo $r['id']; ?></td>
<td><?php echo $r['full_name']; ?></td>
<td><?php echo $r['pos']; ?></td>
<td><?php echo $r['party']; ?></td>
<td><?php echo $r['status'] ? 'Active' : 'Inactive'; ?></td>
<td>
<a href="candidates.php?edit_id=<?php echo $r['id']; ?>"><b>Edit</b></a> |
<a href="candidates.php?cand_action=<?php echo $r['status'] ? 'deactivate' : 'activate'; ?>&id=<?php echo $r['id']; ?>">
<b><?php echo $r['status'] ? 'Deactivate' : 'Activate'; ?></b></a> |
<a onclick="return confirm('Delete candidate?')" href="candidates.php?cand_action=delete&id=<?php echo $r['id']; ?>"><b>Delete</b></a>
</td>
</tr>
<?php endwhile; ?>
</table>

</body>
</html>
