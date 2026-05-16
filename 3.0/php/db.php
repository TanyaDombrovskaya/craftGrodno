<?php
$server = "localhost";
$username = "global";
$password = "1234";
$database = "grodnoart";

$connection = new mysqli($server, $username, $password, $database);

if (!$connection) {
    die("Connection failed". mysqli_error($connection));
}    