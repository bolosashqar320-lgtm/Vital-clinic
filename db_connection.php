<?php
function OpenCon()
{
    $dbhost = "localhost";
    $dbuser = "root";
    $dbpass = "root";
    $db = "H";
    $conn = mysqli_connect($dbhost, $dbuser, $dbpass, $db);
    if (!$conn) {
        die("Connect failed: " . mysqli_connect_error());
    }
    return $conn;
}
$con = OpenCon();
?>