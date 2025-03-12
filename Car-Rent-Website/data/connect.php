<?php

$host="127.0.0.1"; // Changed from 'localhost' to IP address
$user="root";
$pass="";
$db="car_rent";
$conn=new mysqli($host,$user,$pass,$db);
if($conn->connect_error){
    echo "Failed to connect DB".$conn->connect_error;
}
?>