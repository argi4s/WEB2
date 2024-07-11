<?php

$productName = $_POST["productName"];
$productCategory = $_POST["productCategory"];
$productQuantity = filter_input(INPUT_POST, "productQuantity", FILTER_VALIDATE_INT);


var_dump($productName, $productCategory, $productQuantity);

$host = "localhost";
$dbname = "WEB2";
$username = "root";
$password = "";

$conn = mysqli_connect( hostname: $host,
                username: $username, 
                password: $password,
                database: $dbname);

if (mysqli_connect_errno()) {
    die("Connection error: " . mysqli_connect_error());
}

$sqli = "INSERT INTO warehouse (productName, productCategory, productQuantity) VALUES (?, ?, ?)";

$stmt = mysqli_stmt_init(($conn));

if ( ! mysqli_stmt_prepare($stmt, $sqli)) {
    die(mysqli_error($conn));
}

mysqli_stmt_bind_param( $stmt, "ssi",
                        $productName,
                        $productCategory,
                        $productQuantity);

mysqli_stmt_execute($stmt);

echo "Record Saved";




