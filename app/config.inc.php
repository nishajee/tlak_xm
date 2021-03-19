<?php
define("SITE_ROOT_PATH", $_SERVER['DOCUMENT_ROOT'] . "/adboek_admin");

$con=mysqli_connect("localhost","root","H3lpd0c0828","adboek_admin");
if (!$con) {
    die("Database connection failed");
}


?>