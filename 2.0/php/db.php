<?php
$server = "localhost";
$username = "global";
$password = "1234";
$database = "grodnoCraft";

$connection = new mysqli($server, $username, $password, $database);

if (!$connection) {
    die("Connection failed". mysqli_error($connection));
}    