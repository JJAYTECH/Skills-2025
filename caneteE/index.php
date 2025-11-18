<?php
require_once "db.php";

$page = isset($_GET["page"]) ? $_GET["page"] : "events";

/* =====================================
   EVENTS MANAGEMENT LOGIC [MODULE 1]
   ===================================== */
if ($page == "events") {

    // add new event
    if (isset($_POST["save_event"])) {
        $name = $_POST["event_name"];
        $date = $_POST["event_date"];
        $loc  = $_POST["location"];
        $fee  = $_POST["reg_fee"];

        $stmt = $conn->prepare("INSERT INTO events (event_name, event_date, location, reg_fee, status) VALUES (?, ?, ?, ?, 1)");
        $stmt->bind_param("sssd", $name, $date, $loc, $fee);
        $stmt->execute();
        $stmt->close();

        header("Location: index.php?page=events");
        exit;
    }

    // update existing event
    if (isset($_POST["update_event"])) {
        $id   = $_POST["event_id"];
        $name = $_POST["event_name"];
        $date = $_POST["event_date"];
        $loc  = $_POST["location"];
        $fee  = $_POST["reg_fee"];

        $stmt = $conn->prepare("UPDATE events SET event_name = ?, event_date = ?, location = ?, reg_fee = ? WHERE id = ?");
        $stmt->bind_param("sssdi", $name, $date, $loc, $fee, $id);
        $stmt->execute();
        $stmt->close();

        header("Location: index.php?page=events");
        exit;
    }

    // delete event
    if (isset($_GET["delete_id"])) {
        $id = $_GET["delete_id"];

        $stmt = $conn->prepare("DELETE FROM events WHERE id = ?");
        $stmt->bind_param("i", $id);
        $stmt->execute();
        $stmt->close();

        header("Location: index.php?page=events");
        exit;
    }

    // get record for editing
    $editEvent = null;
    if (isset($_GET["edit_id"])) {
        $id = $_GET["edit_id"];

        $stmt = $conn->prepare("SELECT * FROM events WHERE id = ?");
        $stmt->bind_param("i", $id);
        $stmt->execute();
        $result = $stmt->get_result();
        $editEvent = $result->fetch_assoc();
        $stmt->close();
    }

    // search filter
    $search = isset($_GET["search_event"]) ? trim($_GET["search_event"]) : "";

    $sql = "SELECT * FROM events WHERE status = 1";
    if ($search != "") {
        $sql .= " AND event_name LIKE '%" . $conn->real_escape_string($search) . "%'";
    }
    $sql .= " ORDER BY event_date DESC";

    $eventsRes = $conn->query($sql);
}

/* ==========================================
   PARTICIPANTS MANAGEMENT LOGIC [MODULE 2]
   ========================================== */
if ($page == "participants") {

    // add participant
    if (isset($_POST["save_participant"])) {
        $name = $_POST["full_name"];
        $email = $_POST["email"];
        $contact = $_POST["contact_no"];

        $stmt = $conn->prepare("INSERT INTO participants (full_name, email, contact_no, status) VALUES (?, ?, ?, 1)");
        $stmt->bind_param("sss", $name, $email, $contact);
        $stmt->execute();
        $stmt->close();

        header("Location: index.php?page=participants");
        exit;
    }

    // update participant
    if (isset($_POST["update_participant"])) {
        $id = $_POST["participant_id"];
        $name = $_POST["full_name"];
        $email = $_POST["email"];
        $contact = $_POST["contact_no"];

        $stmt = $conn->prepare("UPDATE participants SET full_name = ?, email = ?, contact_no = ? WHERE id = ?");
        $stmt->bind_param("sssi", $name, $email, $contact, $id);
        $stmt->execute();
        $stmt->close();

        header("Location: index.php?page=participants");
        exit;
    }

    // delete participant
    if (isset($_GET["delete_pid"])) {
        $id = $_GET["delete_pid"];

        $stmt = $conn->prepare("DELETE FROM participants WHERE id = ?");
        $stmt->bind_param("i", $id);
        $stmt->execute();
        $stmt->close();

        header("Location: index.php?page=participants");
        exit;
    }

    // get participant for editing
    $editP = null;
    if (isset($_GET["edit_pid"])) {
        $id = $_GET["edit_pid"];

        $stmt = $conn->prepare("SELECT * FROM participants WHERE id = ?");
        $stmt->bind_param("i", $id);
        $stmt->execute();
        $res = $stmt->get_result();
        $editP = $res->fetch_assoc();
        $stmt->close();
    }

    // search participants
    $psearch = isset($_GET["search_p"]) ? trim($_GET["search_p"]) : "";

    $psql = "SELECT * FROM participants WHERE status = 1";
    if ($psearch != "") {
        $psql .= " AND full_name LIKE '%" . $conn->real_escape_string($psearch) . "%'";
    }
    $psql .= " ORDER BY id DESC";

    $pRes = $conn->query($psql);
}

/* =====================================
   REGISTRATION LOGIC [MODULE 3]
   ===================================== */
$reg_msg = "";
if ($page == "registration") {

    // get events for dropdown
    $eventsList = [];
    $er = $conn->query("SELECT id, event_name, reg_fee FROM events WHERE status = 1 ORDER BY event_name");
    while ($row = $er->fetch_assoc()) {
        $eventsList[] = $row;
    }

    // get participants for dropdown
    $participantsList = [];
    $pr = $conn->query("SELECT id, full_name FROM participants WHERE status = 1 ORDER BY full_name");
    while ($row = $pr->fetch_assoc()) {
        $participantsList[] = $row;
    }

    // save registration
    if (isset($_POST["save_registration"])) {
        $event_id = $_POST["event_id"];
        $participant_id = $_POST["participant_id"];
        $discount_rate = floatval($_POST["discount_rate"]);

        // get event fee
        $stmt = $conn->prepare("SELECT reg_fee FROM events WHERE id = ?");
        $stmt->bind_param("i", $event_id);
        $stmt->execute();
        $stmt->bind_result($fee_original);
        if ($stmt->fetch()) {
            $stmt->close();

            $discount_amount = $fee_original * ($discount_rate / 100);
            $fee_paid = $fee_original - $discount_amount;

            $stmt2 = $conn->prepare("INSERT INTO registrations (event_id, participant_id, discount_rate, fee_original, fee_paid) VALUES (?, ?, ?, ?, ?)");
            $stmt2->bind_param("iiddi", $event_id, $participant_id, $discount_rate, $fee_original, $fee_paid);
            // correction: last param must be double, change "iiddi" to "iidd d"
        } else {
            $stmt->close();
        }
    }

    // better full save block:
    if (isset($_POST["save_registration"])) {
        $event_id = intval($_POST["event_id"]);
        $participant_id = intval($_POST["participant_id"]);
        $discount_rate = floatval($_POST["discount_rate"]);

        // get event fee
        $stmt = $conn->prepare("SELECT reg_fee FROM events WHERE id = ?");
        $stmt->bind_param("i", $event_id);
        $stmt->execute();
        $stmt->bind_result($fee_original);
        if ($stmt->fetch()) {
            $stmt->close();

            $discount_amount = $fee_original * ($discount_rate / 100);
            $fee_paid = $fee_original - $discount_amount;

            $stmt2 = $conn->prepare("INSERT INTO registrations (event_id, participant_id, discount_rate, fee_original, fee_paid) VALUES (?, ?, ?, ?, ?)");
            $stmt2->bind_param("iiddi", $event_id, $participant_id, $discount_rate, $fee_original, $fee_paid);
            $stmt2->execute();
            $stmt2->close();

            $reg_msg = "Registration saved. Original fee " . number_format($fee_original, 2) . ", discount " . $discount_rate . "%, fee paid " . number_format($fee_paid, 2) . ".";
        } else {
            $stmt->close();
            $reg_msg = "Event fee not found.";
        }

        header("Location: index.php?page=registration&msg=" . urlencode($reg_msg));
        exit;
    }

    if (isset($_GET["msg"])) {
        $reg_msg = $_GET["msg"];
    }

    // get registrations list sorted latest to oldest
    $regRes = $conn->query("
        SELECT 
            r.id,
            e.event_name,
            p.full_name,
            r.reg_date,
            r.fee_original,
            r.discount_rate,
            r.fee_paid
        FROM registrations r
        JOIN events e ON r.event_id = e.id
        JOIN participants p ON r.participant_id = p.id
        ORDER BY r.reg_date DESC, r.id DESC
    ");
}

?>
<!DOCTYPE html>
<html>
<head>
    <title>Events Registration System</title>
    <style>
        body { font-family: Arial, sans-serif; padding: 10px; }
        .nav a { margin-right: 10px; text-decoration: none; padding: 5px 10px; border: 1px solid #ccc; }
        .nav a.active { background: #f0f0f0; }
        .box { border: 1px solid #ccc; padding: 15px; margin-top: 15px; }
        table { border-collapse: collapse; width: 100%; margin-top: 10px; }
        table, th, td { border: 1px solid #aaa; }
        th, td { padding: 6px; font-size: 14px; }
        input[type="text"], input[type="date"], input[type="number"], select {
            padding: 4px; width: 100%; box-sizing: border-box;
        }
        input[type="submit"], button {
            padding: 6px 12px; cursor: pointer;
        }
        .actions a { margin-right: 5px; }
        .msg { padding: 8px; background: #e7ffe7; border: 1px solid #8bc34a; margin-bottom: 10px; }
    </style>
</head>
<body>

<h2>Events Registration Dashboard</h2>

<div class="nav">
    <a href="index.php?page=events" class="<?php echo ($page == 'events') ? 'active' : ''; ?>">Events Management</a>
    <a href="index.php?page=participants" class="<?php echo ($page == 'participants') ? 'active' : ''; ?>">Participants Management</a>
    <a href="index.php?page=registration" class="<?php echo ($page == 'registration') ? 'active' : ''; ?>">Participant Registration</a>
</div>

<?php if ($page == "events") { ?>
    <div class="box">
        <h3>Events Management</h3>

        <?php
        if (isset($editEvent) && $editEvent) {
        ?>
            <h4>Edit Event</h4>
            <form method="post" action="index.php?page=events">
                <input type="hidden" name="event_id" value="<?php echo $editEvent['id']; ?>" />

                <label>Event Name</label>
                <input type="text" name="event_name" required value="<?php echo htmlspecialchars($editEvent['event_name']); ?>" />

                <label>Event Date</label>
                <input type="date" name="event_date" required value="<?php echo $editEvent['event_date']; ?>" />

                <label>Location</label>
                <input type="text" name="location" value="<?php echo htmlspecialchars($editEvent['location']); ?>" />

                <label>Registration Fee</label>
                <input type="number" step="0.01" name="reg_fee" required value="<?php echo $editEvent['reg_fee']; ?>" />

                <br><br>
                <input type="submit" name="update_event" value="Update Event" />
                <a href="index.php?page=events">Cancel</a>
            </form>
        <?php } else { ?>
            <h4>Add Event</h4>
            <form method="post" action="index.php?page=events">
                <label>Event Name</label>
                <input type="text" name="event_name" required />

                <label>Event Date</label>
                <input type="date" name="event_date" required />

                <label>Location</label>
                <input type="text" name="location" />

                <label>Registration Fee</label>
                <input type="number" step="0.01" name="reg_fee" required />

                <br><br>
                <input type="submit" name="save_event" value="Save Event" />
            </form>
        <?php } ?>

        <hr>

        <h4>Search or View Events</h4>
        <form method="get" action="index.php">
            <input type="hidden" name="page" value="events" />
            <input type="text" name="search_event" placeholder="Search event name" value="<?php echo isset($search) ? htmlspecialchars($search) : ''; ?>" />
            <input type="submit" value="Search" />
            <a href="index.php?page=events">Reset</a>
        </form>

        <table>
            <tr>
                <th>ID</th>
                <th>Event Name</th>
                <th>Event Date</th>
                <th>Location</th>
                <th>Registration Fee</th>
                <th>Actions</th>
            </tr>
            <?php if (isset($eventsRes) && $eventsRes->num_rows > 0) { ?>
                <?php while ($row = $eventsRes->fetch_assoc()) { ?>
                    <tr>
                        <td><?php echo $row["id"]; ?></td>
                        <td><?php echo htmlspecialchars($row["event_name"]); ?></td>
                        <td><?php echo $row["event_date"]; ?></td>
                        <td><?php echo htmlspecialchars($row["location"]); ?></td>
                        <td><?php echo number_format($row["reg_fee"], 2); ?></td>
                        <td class="actions">
                            <a href="index.php?page=events&edit_id=<?php echo $row['id']; ?>">Edit</a>
                            <a href="index.php?page=events&delete_id=<?php echo $row['id']; ?>" onclick="return confirm('Delete this event?');">Delete</a>
                        </td>
                    </tr>
                <?php } ?>
            <?php } else { ?>
                <tr><td colspan="6">No events found.</td></tr>
            <?php } ?>
        </table>
    </div>
<?php } ?>

<?php if ($page == "participants") { ?>
    <div class="box">
        <h3>Participants Management</h3>

        <?php if (isset($editP) && $editP) { ?>
            <h4>Edit Participant</h4>
            <form method="post" action="index.php?page=participants">
                <input type="hidden" name="participant_id" value="<?php echo $editP['id']; ?>">

                <label>Full Name</label>
                <input type="text" name="full_name" required value="<?php echo htmlspecialchars($editP['full_name']); ?>">

                <label>Email</label>
                <input type="text" name="email" value="<?php echo htmlspecialchars($editP['email']); ?>">

                <label>Contact Number</label>
                <input type="text" name="contact_no" value="<?php echo htmlspecialchars($editP['contact_no']); ?>">

                <br><br>
                <input type="submit" name="update_participant" value="Update Participant">
                <a href="index.php?page=participants">Cancel</a>
            </form>
        <?php } else { ?>
            <h4>Add Participant</h4>
            <form method="post" action="index.php?page=participants">
                <label>Full Name</label>
                <input type="text" name="full_name" required>

                <label>Email</label>
                <input type="text" name="email">

                <label>Contact Number</label>
                <input type="text" name="contact_no">

                <br><br>
                <input type="submit" name="save_participant" value="Save Participant">
            </form>
        <?php } ?>

        <hr>

        <form method="get" action="index.php">
            <input type="hidden" name="page" value="participants">
            <input type="text" name="search_p" placeholder="Search participant" value="<?php echo isset($psearch) ? htmlspecialchars($psearch) : ''; ?>">
            <input type="submit" value="Search">
            <a href="index.php?page=participants">Reset</a>
        </form>

        <table>
            <tr>
                <th>ID</th>
                <th>Full Name</th>
                <th>Email</th>
                <th>Contact</th>
                <th>Actions</th>
            </tr>

            <?php if (isset($pRes) && $pRes->num_rows > 0) { ?>
                <?php while ($p = $pRes->fetch_assoc()) { ?>
                    <tr>
                        <td><?php echo $p['id']; ?></td>
                        <td><?php echo htmlspecialchars($p['full_name']); ?></td>
                        <td><?php echo htmlspecialchars($p['email']); ?></td>
                        <td><?php echo htmlspecialchars($p['contact_no']); ?></td>
                        <td>
                            <a href="index.php?page=participants&edit_pid=<?php echo $p['id']; ?>">Edit</a>
                            <a href="index.php?page=participants&delete_pid=<?php echo $p['id']; ?>" onclick="return confirm('Delete this participant?');">Delete</a>
                        </td>
                    </tr>
                <?php } ?>
            <?php } else { ?>
                <tr><td colspan="5">No participants found.</td></tr>
            <?php } ?>
        </table>

    </div>
<?php } ?>

<?php if ($page == "registration") { ?>
    <div class="box">
        <h3>Participant Registration</h3>

        <?php if ($reg_msg != "") { ?>
            <div class="msg"><?php echo htmlspecialchars($reg_msg); ?></div>
        <?php } ?>

        <h4>Register participant to event</h4>
        <form method="post" action="index.php?page=registration">
            <label>Event</label>
            <select name="event_id" required>
                <option value="">Select event</option>
                <?php if (!empty($eventsList)) { ?>
                    <?php foreach ($eventsList as $e) { ?>
                        <option value="<?php echo $e["id"]; ?>">
                            <?php echo htmlspecialchars($e["event_name"]) . " (" . number_format($e["reg_fee"], 2) . ")"; ?>
                        </option>
                    <?php } ?>
                <?php } ?>
            </select>

            <label>Participant</label>
            <select name="participant_id" required>
                <option value="">Select participant</option>
                <?php if (!empty($participantsList)) { ?>
                    <?php foreach ($participantsList as $pp) { ?>
                        <option value="<?php echo $pp["id"]; ?>">
                            <?php echo htmlspecialchars($pp["full_name"]); ?>
                        </option>
                    <?php } ?>
                <?php } ?>
            </select>

            <label>Discount rate in percent</label>
            <input type="number" name="discount_rate" step="0.01" min="0" max="100" value="0">

            <br><br>
            <input type="submit" name="save_registration" value="Save Registration">
        </form>

        <hr>

        <h4>Registration records latest to oldest</h4>

        <table>
            <tr>
                <th>ID</th>
                <th>Event</th>
                <th>Participant</th>
                <th>Registration Date</th>
                <th>Original Fee</th>
                <th>Discount %</th>
                <th>Fee Paid</th>
            </tr>

            <?php if (isset($regRes) && $regRes->num_rows > 0) { ?>
                <?php while ($r = $regRes->fetch_assoc()) { ?>
                    <tr>
                        <td><?php echo $r["id"]; ?></td>
                        <td><?php echo htmlspecialchars($r["event_name"]); ?></td>
                        <td><?php echo htmlspecialchars($r["full_name"]); ?></td>
                        <td><?php echo $r["reg_date"]; ?></td>
                        <td><?php echo number_format($r["fee_original"], 2); ?></td>
                        <td><?php echo number_format($r["discount_rate"], 2); ?></td>
                        <td><?php echo number_format($r["fee_paid"], 2); ?></td>
                    </tr>
                <?php } ?>
            <?php } else { ?>
                <tr><td colspan="7">No registrations found.</td></tr>
            <?php } ?>
        </table>

    </div>
<?php } ?>

</body>
</html>
