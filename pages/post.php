<?php
include("../scripts/format_date.php");
require_once "../scripts/db_connect.php";
session_start();

// Redirect unauthorized users trying to access via POST request
if ($_SERVER["REQUEST_METHOD"] == "POST" && !isset($_SESSION["loggedin"])) {
    header("Location: sign-in.php");
    exit;
}
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <title>Post Details</title>
    
    <!-- Tailwind CSS -->
    <script src="https://cdn.tailwindcss.com"></script>

    <!-- Alpine.js -->
    <script defer src="https://unpkg.com/alpinejs@3.10.2/dist/cdn.min.js"></script>
</head>

<body class="bg-gray-50">
    <?php include("../components/header.php"); ?>

    <div class="p-2 container mx-auto grid grid-cols-12 gap-4">
        <aside class="col-span-3">
            <?php include("../components/left-bar/index.php"); ?>
        </aside>
        
        <main class="col-span-6">
            <?php include("../components/post-detail/index.php"); ?>
        </main>
        
        <aside class="col-span-3">
            <?php include("../components/right-bar/index.php"); ?>
        </aside>
    </div>
</body>

</html>
