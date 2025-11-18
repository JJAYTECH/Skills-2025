<?php
require_once "db.php";

$section = isset($_GET["section"]) ? $_GET["section"] : "positions";

/* ------------- POSITIONS HANDLERS ------------- */
if (isset($_POST["save_position"])) {
    $id   = isset($_POST["id"]) ? (int)$_POST["id"] : 0;
    $name = $_POST["name"];
    $desc = $_POST["description"];

    if ($id > 0) {
        $sql = "UPDATE positions SET name='$name', description='$desc' WHERE id=$id";
    } else {
        $sql = "INSERT INTO positions (name, description) VALUES ('$name', '$desc')";
    }
    $conn->query($sql);
    header("Location: index.php?section=positions");
    exit;
}

if (isset($_GET["pos_action"])) {
    $id = (int)$_GET["id"];
    if ($_GET["pos_action"] == "deactivate") {
        $conn->query("UPDATE positions SET status=0 WHERE id=$id");
    } elseif ($_GET["pos_action"] == "activate") {
        $conn->query("UPDATE positions SET status=1 WHERE id=$id");
    } elseif ($_GET["pos_action"] == "delete") {
        $conn->query("DELETE FROM positions WHERE id=$id");
    }
    header("Location: index.php?section=positions");
    exit;
}

/* ------------- CANDIDATES HANDLERS ------------- */
if (isset($_POST["save_candidate"])) {
    $id          = isset($_POST["id"]) ? (int)$_POST["id"] : 0;
    $full_name   = $_POST["full_name"];
    $position_id = (int)$_POST["position_id"];
    $party       = $_POST["party"];

    if ($id > 0) {
        $sql = "UPDATE candidates 
                SET full_name='$full_name', position_id=$position_id, party='$party'
                WHERE id=$id";
    } else {
        $sql = "INSERT INTO candidates (full_name, position_id, party) 
                VALUES ('$full_name', $position_id, '$party')";
    }
    $conn->query($sql);
    header("Location: index.php?section=candidates");
    exit;
}

if (isset($_GET["cand_action"])) {
    $id = (int)$_GET["id"];
    if ($_GET["cand_action"] == "deactivate") {
        $conn->query("UPDATE candidates SET status=0 WHERE id=$id");
    } elseif ($_GET["cand_action"] == "activate") {
        $conn->query("UPDATE candidates SET status=1 WHERE id=$id");
    } elseif ($_GET["cand_action"] == "delete") {
        $conn->query("DELETE FROM candidates WHERE id=$id");
    }
    header("Location: index.php?section=candidates");
    exit;
}

/* ------------- VOTERS HANDLERS ------------- */
if (isset($_POST["save_voter"])) {
    $id        = isset($_POST["id"]) ? (int)$_POST["id"] : 0;
    $voter_id  = $_POST["voter_id"];
    $full_name = $_POST["full_name"];
    $password  = $_POST["password"]; // for demo only, walay hashing

    if ($id > 0) {
        $sql = "UPDATE voters 
                SET voter_id='$voter_id', full_name='$full_name', password='$password'
                WHERE id=$id";
    } else {
        $sql = "INSERT INTO voters (voter_id, full_name, password) 
                VALUES ('$voter_id', '$full_name', '$password')";
    }
    $conn->query($sql);
    header("Location: index.php?section=voters");
    exit;
}

if (isset($_GET["voter_action"])) {
    $id = (int)$_GET["id"];
    if ($_GET["voter_action"] == "deactivate") {
        $conn->query("UPDATE voters SET status=0 WHERE id=$id");
    } elseif ($_GET["voter_action"] == "activate") {
        $conn->query("UPDATE voters SET status=1 WHERE id=$id");
    } elseif ($_GET["voter_action"] == "delete") {
        $conn->query("DELETE FROM voters WHERE id=$id");
    }
    header("Location: index.php?section=voters");
    exit;
}

/* ------------- FETCH DATA FOR FORMS ------------- */
$editPosition = null;
if ($section == "positions" && isset($_GET["edit_id"])) {
    $id = (int)$_GET["edit_id"];
    $res = $conn->query("SELECT * FROM positions WHERE id=$id");
    $editPosition = $res->fetch_assoc();
}

$editCandidate = null;
if ($section == "candidates" && isset($_GET["edit_id"])) {
    $id = (int)$_GET["edit_id"];
    $res = $conn->query("SELECT * FROM candidates WHERE id=$id");
    $editCandidate = $res->fetch_assoc();
}

$editVoter = null;
if ($section == "voters" && isset($_GET["edit_id"])) {
    $id = (int)$_GET["edit_id"];
    $res = $conn->query("SELECT * FROM voters WHERE id=$id");
    $editVoter = $res->fetch_assoc();
}
?>
<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>Election Dashboard</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            background: #f4f6f9;
            margin: 0;
            padding: 0;
        }
        .topbar {
            background: #2c3e50;
            color: white;
            padding: 15px;
            text-align: center;
        }
        .nav {
            display: flex;
            justify-content: center;
            background: #34495e;
        }
        .nav a {
            color: white;
            padding: 10px 20px;
            text-decoration: none;
        }
        .nav a.active {
            background: #1abc9c;
        }
        .container {
            max-width: 1100px;
            margin: 20px auto;
            background: white;
            padding: 20px;
            box-shadow: 0 0 5px rgba(0,0,0,0.15);
            border-radius: 4px;
        }
        h2 {
            margin-top: 0;
        }
        form {
            margin-bottom: 20px;
            background: #ecf0f1;
            padding: 15px;
            border-radius: 4px;
        }
        label {
            display: block;
            margin-bottom: 5px;
            font-size: 14px;
            font-weight: bold;
        }
        input[type="text"],
        input[type="password"],
        select {
            width: 100%;
            padding: 8px;
            margin-bottom: 10px;
            border: 1px solid #bdc3c7;
            border-radius: 3px;
        }
        button {
            padding: 8px 15px;
            border: none;
            border-radius: 3px;
            cursor: pointer;
        }
        .btn-primary { background: #3498db; color: white; }
        .btn-warning { background: #f1c40f; color: black; }
        .btn-danger  { background: #e74c3c; color: white; }
        .btn-success { background: #2ecc71; color: white; }
        table {
            width: 100%;
            border-collapse: collapse;
            font-size: 14px;
        }
        th, td {
            border: 1px solid #ddd;
            padding: 6px 8px;
            text-align: left;
        }
        th {
            background: #f9fafb;
        }
        .status-badge {
            padding: 3px 8px;
            border-radius: 10px;
            font-size: 12px;
        }
        .status-active { background: #2ecc71; color: white; }
        .status-inactive { background: #e74c3c; color: white; }
    </style>
</head>
<body>

<div class="topbar">
    <h1>Election Management Dashboard</h1>
</div>

<div class="nav">
    <a href="index.php?section=positions" class="<?php echo $section=='positions'?'active':''; ?>">Positions</a>
    <a href="index.php?section=candidates" class="<?php echo $section=='candidates'?'active':''; ?>">Candidates</a>
    <a href="index.php?section=voters" class="<?php echo $section=='voters'?'active':''; ?>">Voters</a>
</div>

<div class="container">
<?php if ($section == "positions"): ?>

    <h2>Positions Management</h2>
    <form method="post">
        <input type="hidden" name="id" value="<?php echo $editPosition ? $editPosition['id'] : ''; ?>">
        <label>Position Name</label>
        <input type="text" name="name" required
               value="<?php echo $editPosition ? htmlspecialchars($editPosition['name']) : ''; ?>">
        <label>Description</label>
        <input type="text" name="description"
               value="<?php echo $editPosition ? htmlspecialchars($editPosition['description']) : ''; ?>">
        <button type="submit" name="save_position" class="btn-primary">
            <?php echo $editPosition ? 'Update Position' : 'Add Position'; ?>
        </button>
    </form>

    <?php
    $res = $conn->query("SELECT * FROM positions ORDER BY id DESC");
    ?>
    <table>
        <tr>
            <th>ID</th>
            <th>Position</th>
            <th>Description</th>
            <th>Status</th>
            <th>Actions</th>
        </tr>
        <?php while ($row = $res->fetch_assoc()): ?>
        <tr>
            <td><?php echo $row["id"]; ?></td>
            <td><?php echo htmlspecialchars($row["name"]); ?></td>
            <td><?php echo htmlspecialchars($row["description"]); ?></td>
            <td>
                <span class="status-badge <?php echo $row["status"] ? 'status-active':'status-inactive'; ?>">
                    <?php echo $row["status"] ? 'Active' : 'Inactive'; ?>
                </span>
            </td>
            <td>
                <a href="index.php?section=positions&edit_id=<?php echo $row['id']; ?>" class="btn-warning">Edit</a>
                <?php if ($row["status"]): ?>
                    <a href="index.php?section=positions&pos_action=deactivate&id=<?php echo $row['id']; ?>" class="btn-danger">Deactivate</a>
                <?php else: ?>
                    <a href="index.php?section=positions&pos_action=activate&id=<?php echo $row['id']; ?>" class="btn-success">Activate</a>
                <?php endif; ?>
                <a href="index.php?section=positions&pos_action=delete&id=<?php echo $row['id']; ?>" class="btn-danger" onclick="return confirm('Delete this position?');">Delete</a>
            </td>
        </tr>
        <?php endwhile; ?>
    </table>

<?php elseif ($section == "candidates"): ?>

    <h2>Candidates Management</h2>
    <form method="post">
        <input type="hidden" name="id" value="<?php echo $editCandidate ? $editCandidate['id'] : ''; ?>">
        <label>Full Name</label>
        <input type="text" name="full_name" required
               value="<?php echo $editCandidate ? htmlspecialchars($editCandidate['full_name']) : ''; ?>">

        <label>Position</label>
        <select name="position_id" required>
            <option value="">Select position</option>
            <?php
            $posRes = $conn->query("SELECT id, name FROM positions WHERE status=1 ORDER BY name");
            while ($p = $posRes->fetch_assoc()):
                $selected = $editCandidate && $editCandidate["position_id"] == $p["id"] ? "selected" : "";
            ?>
                <option value="<?php echo $p["id"]; ?>" <?php echo $selected; ?>>
                    <?php echo htmlspecialchars($p["name"]); ?>
                </option>
            <?php endwhile; ?>
        </select>

        <label>Party</label>
        <input type="text" name="party"
               value="<?php echo $editCandidate ? htmlspecialchars($editCandidate['party']) : ''; ?>">

        <button type="submit" name="save_candidate" class="btn-primary">
            <?php echo $editCandidate ? 'Update Candidate' : 'Add Candidate'; ?>
        </button>
    </form>

    <?php
    $res = $conn->query("SELECT c.*, p.name AS position_name 
                         FROM candidates c
                         LEFT JOIN positions p ON c.position_id = p.id
                         ORDER BY c.id DESC");
    ?>
    <table>
        <tr>
            <th>ID</th>
            <th>Full Name</th>
            <th>Position</th>
            <th>Party</th>
            <th>Status</th>
            <th>Actions</th>
        </tr>
        <?php while ($row = $res->fetch_assoc()): ?>
        <tr>
            <td><?php echo $row["id"]; ?></td>
            <td><?php echo htmlspecialchars($row["full_name"]); ?></td>
            <td><?php echo htmlspecialchars($row["position_name"]); ?></td>
            <td><?php echo htmlspecialchars($row["party"]); ?></td>
            <td>
                <span class="status-badge <?php echo $row["status"] ? 'status-active':'status-inactive'; ?>">
                    <?php echo $row["status"] ? 'Active' : 'Inactive'; ?>
                </span>
            </td>
            <td>
                <a href="index.php?section=candidates&edit_id=<?php echo $row['id']; ?>" class="btn-warning">Edit</a>
                <?php if ($row["status"]): ?>
                    <a href="index.php?section=candidates&cand_action=deactivate&id=<?php echo $row['id']; ?>" class="btn-danger">Deactivate</a>
                <?php else: ?>
                    <a href="index.php?section=candidates&cand_action=activate&id=<?php echo $row['id']; ?>" class="btn-success">Activate</a>
                <?php endif; ?>
                <a href="index.php?section=candidates&cand_action=delete&id=<?php echo $row['id']; ?>" class="btn-danger" onclick="return confirm('Delete this candidate?');">Delete</a>
            </td>
        </tr>
        <?php endwhile; ?>
    </table>

<?php elseif ($section == "voters"): ?>

    <h2>Voters Management</h2>
    <form method="post">
        <input type="hidden" name="id" value="<?php echo $editVoter ? $editVoter['id'] : ''; ?>">
        <label>Voter ID</label>
        <input type="text" name="voter_id" required
               value="<?php echo $editVoter ? htmlspecialchars($editVoter['voter_id']) : ''; ?>">

        <label>Full Name</label>
        <input type="text" name="full_name" required
               value="<?php echo $editVoter ? htmlspecialchars($editVoter['full_name']) : ''; ?>">

        <label>Password</label>
        <input type="password" name="password" required
               value="<?php echo $editVoter ? htmlspecialchars($editVoter['password']) : ''; ?>">

        <button type="submit" name="save_voter" class="btn-primary">
            <?php echo $editVoter ? 'Update Voter' : 'Add Voter'; ?>
        </button>
    </form>

    <?php
    $res = $conn->query("SELECT * FROM voters ORDER BY id DESC");
    ?>
    <table>
        <tr>
            <th>ID</th>
            <th>Voter ID</th>
            <th>Full Name</th>
            <th>Status</th>
            <th>Actions</th>
        </tr>
        <?php while ($row = $res->fetch_assoc()): ?>
        <tr>
            <td><?php echo $row["id"]; ?></td>
            <td><?php echo htmlspecialchars($row["voter_id"]); ?></td>
            <td><?php echo htmlspecialchars($row["full_name"]); ?></td>
            <td>
                <span class="status-badge <?php echo $row["status"] ? 'status-active':'status-inactive'; ?>">
                    <?php echo $row["status"] ? 'Active' : 'Inactive'; ?>
                </span>
            </td>
            <td>
                <a href="index.php?section=voters&edit_id=<?php echo $row['id']; ?>" class="btn-warning">Edit</a>
                <?php if ($row["status"]): ?>
                    <a href="index.php?section=voters&voter_action=deactivate&id=<?php echo $row['id']; ?>" class="btn-danger">Deactivate</a>
                <?php else: ?>
                    <a href="index.php?section=voters&voter_action=activate&id=<?php echo $row['id']; ?>" class="btn-success">Activate</a>
                <?php endif; ?>
                <a href="index.php?section=voters&voter_action=delete&id=<?php echo $row['id']; ?>" class="btn-danger" onclick="return confirm('Delete this voter?');">Delete</a>
            </td>
        </tr>
        <?php endwhile; ?>
    </table>

<?php endif; ?>
</div>

</body>
</html>
