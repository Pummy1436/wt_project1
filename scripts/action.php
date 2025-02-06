<?php
require_once "./db_connect.php";
session_start();

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    extract($_POST);

    // Logout logic
    if (isset($logout)) {
        session_unset();
        session_destroy();
        header("Location: ../index.php");
        exit;
    }

    // Post Dislike Logic
    if (isset($post_dislike)) {
        $query = "INSERT INTO post_review (post_review_user_id, post_review_post_id, post_review_type) VALUES (?, ?, -1)";
        $stmt = $link->prepare($query);
        
        if ($stmt) {
            $stmt->bind_param("ii", $user_id, $post_id);
            if ($stmt->execute()) {
                header("Location: ../pages/sign-up.php");
                exit;
            } else {
                die("Error executing query: " . $stmt->error);
            }
        } else {
            die("Error preparing query: " . $link->error);
        }
    }
}
?>
