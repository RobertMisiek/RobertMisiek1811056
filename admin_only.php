<?php
include("auth.php");
if ($_SESSION["role"] != "Admin") {
    header("Location: user_dashboard.php");
    exit();
}
?>