<?php
session_start();
session_unset();
session_destroy();
header("Location: voter_login.php");
exit;
