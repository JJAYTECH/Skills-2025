<?php
require "db.php";

$events=[]; $e=$conn->query("SELECT id,event_name,reg_fee FROM events WHERE status=1 ORDER BY event_name");
while($r=$e->fetch_assoc()){ $events[]=$r; }

$parts=[]; $p=$conn->query("SELECT id,full_name FROM participants WHERE status=1 ORDER BY full_name");
while($r=$p->fetch_assoc()){ $parts[]=$r; }

$reg_msg="";
if(isset($_POST["save_registration"])){
    $eid=$_POST["event_id"];
    $pid=$_POST["participant_id"];
    $dis=$_POST["discount_rate"];

    $stmt=$conn->prepare("SELECT reg_fee FROM events WHERE id=?");
    $stmt->bind_param("i",$eid);
    $stmt->execute();
    $stmt->bind_result($fee);
    if($stmt->fetch()){
        $stmt->close();
        $paid=$fee-($fee*($dis/100));
        $stmt2=$conn->prepare("INSERT INTO registrations (event_id,participant_id,discount_rate,fee_original,fee_paid) VALUES (?,?,?,?,?)");
        $stmt2->bind_param("iiddd",$eid,$pid,$dis,$fee,$paid);
        $stmt2->execute();
        $stmt2->close();
        $reg_msg="Registration saved. Fee ".$fee.", discount ".$dis." percent, paid ".$paid.".";
    }
    header("Location:index.php?page=registration&msg=".urlencode($reg_msg));
    exit;
}

if(isset($_GET["msg"])){ $reg_msg=$_GET["msg"]; }

$list=$conn->query("
SELECT r.id,e.event_name,p.full_name,r.reg_date,r.fee_original,r.discount_rate,r.fee_paid
FROM registrations r
JOIN events e ON r.event_id=e.id
JOIN participants p ON r.participant_id=p.id
ORDER BY r.id DESC");
?>
<html>
<body>
<center>

<table border="1"><tr>
<td><a href="index.php?page=events"><b>Events</b></a></td>
<td><a href="index.php?page=participants"><b>Participants</b></a></td>
<td><a href="index.php?page=registration"><b>Registration</b></a></td>
</tr></table>

<h2><b>Participant Registration</b></h2>

<?php if($reg_msg!="") echo "<b>".$reg_msg."</b><br><br>"; ?>

<b>Add Registration</b>
<form method="post">
<table>
<tr><td><b>Event</b></td><td>
<select name="event_id" required>
<option value="">Select</option>
<?php foreach($events as $e){ ?>
<option value="<?php echo $e["id"]; ?>">
<?php echo $e["event_name"]." (".$e["reg_fee"].")"; ?>
</option>
<?php } ?>
</select>
</td></tr>

<tr><td><b>Participant</b></td><td>
<select name="participant_id" required>
<option value="">Select</option>
<?php foreach($parts as $p){ ?>
<option value="<?php echo $p["id"]; ?>"><?php echo $p["full_name"]; ?></option>
<?php } ?>
</select>
</td></tr>

<tr><td><b>Discount</b></td><td><input type="number" step="0.01" value="0" name="discount_rate"></td></tr>
<tr><td></td><td><input type="submit" name="save_registration" value="Save"></td></tr>
</table>
</form>

<br>

<b>Registration Records</b>
<table border="1">
<tr>
<th><b>ID</b></th><th><b>Event</b></th><th><b>Participant</b></th>
<th><b>Date</b></th><th><b>Original</b></th><th><b>Discount</b></th><th><b>Paid</b></th>
</tr>

<?php if($list->num_rows>0){ while($r=$list->fetch_assoc()){ ?>
<tr>
<td><?php echo $r["id"]; ?></td>
<td><?php echo $r["event_name"]; ?></td>
<td><?php echo $r["full_name"]; ?></td>
<td><?php echo $r["reg_date"]; ?></td>
<td><?php echo $r["fee_original"]; ?></td>
<td><?php echo $r["discount_rate"]; ?></td>
<td><?php echo $r["fee_paid"]; ?></td>
</tr>
<?php }}else{ echo "<tr><td colspan=7>No records</td></tr>"; } ?>
</table>

</center>
</body>
</html>
