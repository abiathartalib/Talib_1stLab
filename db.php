<?php

function getDbConnection()
{
    $host = "localhost";
    $user = "root";
    $pass = "";
    $dbname = "talib_1stlab";

    try {
        $conn = @mysqli_connect($host, $user, $pass);
        if (!$conn) {
            $conn = @mysqli_connect($host, $user, $pass, null);
        }
        if (!$conn) {
            throw new mysqli_sql_exception(mysqli_connect_error());
        }

        mysqli_query($conn, "CREATE DATABASE IF NOT EXISTS `$dbname`");
        mysqli_select_db($conn, $dbname);
        mysqli_query(
            $conn,
            'CREATE TABLE IF NOT EXISTS users (
                id INT AUTO_INCREMENT PRIMARY KEY,
                username VARCHAR(50) NOT NULL UNIQUE,
                password_hash VARCHAR(255) NOT NULL,
                created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
            )'
        );
        mysqli_query(
            $conn,
            'CREATE TABLE IF NOT EXISTS students (
                id INT AUTO_INCREMENT PRIMARY KEY,
                student_number VARCHAR(50) NOT NULL,
                name VARCHAR(100) NOT NULL,
                email VARCHAR(100) NOT NULL,
                course VARCHAR(100) NOT NULL
            )'
        );
    } catch (mysqli_sql_exception $e) {
        die("Database connection failed: " . $e->getMessage());
    }

    if (!$conn) {
        die("Database connection failed: " . mysqli_connect_error());
    }

    return $conn;
}
