<?php

function getDbConnection()
{
    $host = "localhost";
    $user = "root";
    $pass = "";
    $dbname = "Talib_1stLab";
    $port = 3307;

    try {
        $conn = mysqli_connect($host, $user, $pass, null, $port);
        if (!$conn) {
            throw new mysqli_sql_exception(mysqli_connect_error());
        }

        mysqli_query($conn, "CREATE DATABASE IF NOT EXISTS `$dbname`");
        mysqli_select_db($conn, $dbname);
    } catch (mysqli_sql_exception $e) {
        die("Database connection failed: " . $e->getMessage());
    }

    if (!$conn) {
        die("Database connection failed: " . mysqli_connect_error());
    }

    return $conn;
}
