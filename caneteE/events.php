<?php
require "db.php";

if(isset($_POST["save_event"])||isset($_POST["update_event"])){
    $name=$_POST["event_name"]; $date=$_POST["event_date"]; $loc=$_POST["location"]; $fee=$_POST["reg_fee"];
    if(isset($_POST["save_event"])){
        $stmt=$conn->prepare("INSERT INTO events (event_name,event_date,location,reg_fee,status) VALUES (?,?,?,?,1)");
        $stmt->bind_param("sssd",$name,$date,$loc,$fee);
    }else{
        $id=$_POST["event_id"];
        $stmt=$conn->prepare("UPDATE events SET event_name=?,event_date=?,location=?,reg_fee=? WHERE id=?");
        $stmt->bind_param("sssdi",$name,$date,$loc,$fee,$id);
    }
    $stmt->execute(); $stmt->close();
    header("Location:index.php?page=events"); exit;
}

if(isset($_GET["delete_id"])){
    $id=$_GET["delete_id"];
    $stmt=$conn->prepare("DELETE FROM events WHERE id=?");
    $stmt->bind_param("i",$id);
    $stmt->execute(); $stmt->close();
    header("Location:index.php?page=events"); exit;
}

$editEvent=null;
if(isset($_GET["edit_id"])){
    $id=$_GET["edit_id"];
    $stmt=$conn->prepare("SELECT * FROM events WHERE id=?");
    $stmt->bind_param("i",$id);
    $stmt->execute();
    $editEvent=$stmt->get_result()->fetch_assoc();
}

$search=isset($_GET["search_event"])?$_GET["search_event"]:"";
$sql="SELECT * FROM events WHERE status=1";
if($search!=""){ $s=$conn->real_escape_string($search); $sql.=" AND event_name LIKE '%$s%'"; }
$sql.=" ORDER BY event_date DESC";
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

<h2><b>Events Management</b></h2>

<?php if($editEvent){ ?>
<b>Edit Event</b>
<form method="post">
<input type="hidden" name="event_id" value="<?php echo $editEvent["id"]; ?>">
<table>
<tr><td><b>Name</b></td><td><input name="event_name" value="<?php echo $editEvent["event_name"]; ?>"></td></tr>
<tr><td><b>Date</b></td><td><input type="date" name="event_date" value="<?php echo $editEvent["event_date"]; ?>"></td></tr>
<tr><td><b>Location</b></td><td><input name="location" value="<?php echo $editEvent["location"]; ?>"></td></tr>
<tr><td><b>Fee</b></td><td><input type="number" step="0.01" name="reg_fee" value="<?php echo $editEvent["reg_fee"]; ?>"></td></tr>
<tr><td></td><td><input type="submit" name="update_event" value="Update"></td></tr>
</table>
</form>
<?php }else{ ?>

<b>Add Event</b>
<form method="post">
<table>
<tr><td><b>Name</b></td><td><input name="event_name"></td></tr>
<tr><td><b>Date</b></td><td><input type="date" name="event_date"></td></tr>
<tr><td><b>Location</b></td><td><input name="location"></td></tr>
<tr><td><b>Fee</b></td><td><input type="number" step="0.01" name="reg_fee"></td></tr>
<tr><td></td><td><input type="submit" name="save_event" value="Save"></td></tr>
</table>
</form>
<?php } ?>

<br>

<form>
<input type="hidden" name="page" value="events">
<table>
<tr>
<td><b>Search</b></td>
<td><input name="search_event" value="<?php echo $search; ?>"></td>
<td><input type="submit" value="Go"></td>
<td><a href="index.php?page=events">Reset</a></td>
</tr>
</table>
</form>

<br>

<table border="1">
<tr>
<th><b>ID</b></th><th><b>Name</b></th><th><b>Date</b></th><th><b>Location</b></th><th><b>Fee</b></th><th><b>Actions</b></th>
</tr>

<?php if($res->num_rows>0){ while($r=$res->fetch_assoc()){ ?>
<tr>
<td><?php echo $r["id"]; ?></td>
<td><?php echo $r["event_name"]; ?></td>
<td><?php echo $r["event_date"]; ?></td>
<td><?php echo $r["location"]; ?></td>
<td><?php echo $r["reg_fee"]; ?></td>
<td>
<a href="index.php?page=events&edit_id=<?php echo $r["id"]; ?>"><b>Edit</b></a> |
<a href="index.php?page=events&delete_id=<?php echo $r["id"]; ?>"><b>Delete</b></a>
</td>
</tr>
<?php }}else{ echo "<tr><td colspan=6>No events</td></tr>"; } ?>
</table>

</center>
</body>
</html>
