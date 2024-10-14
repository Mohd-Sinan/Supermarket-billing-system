<?php

$sname= "127.0.0.1";
$unmae= "sinan";
$password = "kpmyran";

$db_name = "db_test";

$conn = mysqli_connect($sname, $unmae, $password, $db_name);

if (!$conn) {
	echo "Connection failed!";
}
