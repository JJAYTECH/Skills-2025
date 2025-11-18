<?php
// simple router
$page = isset($_GET["page"]) ? $_GET["page"] : "events";

if ($page == "events") {
    include "events.php";
} elseif ($page == "participants") {
    include "participants.php";
} elseif ($page == "registration") {
    include "register.php";
} else {
    echo "Page not found.";
}
?>
