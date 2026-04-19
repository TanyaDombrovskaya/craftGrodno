<?php
$server = "localhost";
$username = "global";
$password = "1234";
$database = "grodnoCraft3.0";

$connection = new mysqli($server, $username, $password, $database);

if (!$connection) {
    die("Connection failed". mysqli_error($connection));
}    