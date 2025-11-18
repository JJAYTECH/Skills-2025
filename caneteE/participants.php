<?php
require "db.php";

if(isset($_POST["save_participant"])||isset($_POST["update_participant"])){
    $name=$_POST["full_name"]; $email=$_POST["email"]; $contact=$_POST["contact_no"];
    if(isset($_POST["save_participant"])){
        $stmt=$conn->prepare("INSERT INTO participants (full_name,email,contact_no,status) VALUES (?,?,?,1)");
        $stmt->bind_param("sss",$name,$email,$contact);
    }else{
        $id=$_POST["participant_id"];
        $stmt=$conn->prepare("UPDATE participants SET full_name=?,email=?,contact_no=? WHERE id=?");
        $stmt->bind_param("sssi",$name,$email,$contact,$id);
    }
    $stmt->execute(); $stmt->close();
    header("Location:index.php?page=participants"); exit;
}

if(isset($_GET["delete_pid"])){
    $id=$_GET["delete_pid"];
    $stmt=$conn->prepare("DELETE FROM participants WHERE id=?");
    $stmt->bind_param("i",$id);
    $stmt->execute(); $stmt->close();
    header("Location:index.php?page=participants"); exit;
}

$editP=null;
if(isset($_GET["edit_pid"])){
    $id=$_GET["edit_pid"];
    $stmt=$conn->prepare("SELECT * FROM participants WHERE id=?");
    $stmt->bind_param("i",$id);
    $stmt->execute();
    $editP=$stmt->get_result()->fetch_assoc();
}

$psearch=isset($_GET["search_p"])?$_GET["search_p"]:"";
$sql="SELECT * FROM participants WHERE status=1";
if($psearch!=""){ $ps=$conn->real_escape_string($psearch); $sql.=" AND full_name LIKE '%$ps%'"; }
$sql.=" ORDER BY id DESC";
$res=$conn->query($sql);
?>
<html>
<body>
<center>

<table border="1"><tr>
<td><a href="index.php?page=events"><b>Events</b></a></td>
<td><a href="index.php?page=participants"><b>Participants</b></a></td>
<td><a href="index.php?page=registration"><b>Registration</b></a></td>
</tr></table>

<h2><b>Participants Management</b></h2>

<?php if($editP){ ?>
<b>Edit Participant</b>
<form method="post">
<input type="hidden" name="participant_id" value="<?php echo $editP["id"]; ?>">
<table>
<tr><td><b>Full Name</b></td><td><input name="full_name" value="<?php echo $editP["full_name"]; ?>"></td></tr>
<tr><td><b>Email</b></td><td><input name="email" value="<?php echo $editP["email"]; ?>"></td></tr>
<tr><td><b>Contact</b></td><td><input name="contact_no" value="<?php echo $editP["contact_no"]; ?>"></td></tr>
<tr><td></td><td><input type="submit" name="update_participant" value="Update"></td></tr>
</table>
</form>
<?php }else{ ?>

<b>Add Participant</b>
<form method="post">
<table>
<tr><td><b>Full Name</b></td><td><input name="full_name"></td></tr>
<tr><td><b>Email</b></td><td><input name="email"></td></tr>
<tr><td><b>Contact</b></td><td><input name="contact_no"></td></tr>
<tr><td></td><td><input type="submit" name="save_participant" value="Save"></td></tr>
</table>
</form>
<?php } ?>

<br>

<form>
<input type="hidden" name="page" value="participants">
<table>
<tr>
<td><b>Search</b></td>
<td><input name="search_p" value="<?php echo $psearch; ?>"></td>
<td><input type="submit" value="Go"></td>
<td><a href="index.php?page=participants">Reset</a></td>
</tr>
</table>
</form>

<br>

<table border="1">
<tr>
<th><b>ID</b></th><th><b>Full Name</b></th><th><b>Email</b></th><th><b>Contact</b></th><th><b>Actions</b></th>
</tr>

<?php if($res->num_rows>0){ while($p=$res->fetch_assoc()){ ?>
<tr>
<td><?php echo $p["id"]; ?></td>
<td><?php echo $p["full_name"]; ?></td>
<td><?php echo $p["email"]; ?></td>
<td><?php echo $p["contact_no"]; ?></td>
<td>
<a href="index.php?page=participants&edit_pid=<?php echo $p["id"]; ?>"><b>Edit</b></a> |
<a href="index.php?page=participants&delete_pid=<?php echo $p["id"]; ?>"><b>Delete</b></a>
</td>
</tr>
<?php }}else{ echo "<tr><td colspan=5>No participants</td></tr>"; } ?>
</table>

</center>
</body>
</html>
