<?php
include("../scripts/format_date.php");
require_once "../scripts/db_connect.php";
session_start();

$user_id = null;
$errors = [];
$update_profile_success = "";

// Redirect unauthorized users
if (!isset($_SESSION["loggedin"]) || $_SESSION["loggedin"] !== true) {
    header("Location: sign-up.php");
    exit;
}

$user_id = $_SESSION["id"];

// Fetch user data
$query = "SELECT * FROM users WHERE id = ?";
$stmt = $link->prepare($query);
$stmt->bind_param("i", $user_id);
$stmt->execute();
$user_data = $stmt->get_result()->fetch_assoc();

// Function to handle file uploads
function uploadImage($file, $directory, &$error)
{
    $allowed_extensions = ['jpg', 'jpeg', 'png'];
    $file_extension = strtolower(pathinfo($file["name"], PATHINFO_EXTENSION));
    
    if (!in_array($file_extension, $allowed_extensions)) {
        $error = "Please upload a valid image (JPG, JPEG, PNG).";
        return null;
    }

    $file_name = uniqid() . "_" . time() . "." . $file_extension;
    $target_path = "../assets/images/user/$directory/" . $file_name;

    if (move_uploaded_file($file["tmp_name"], $target_path)) {
        return $file_name;
    } else {
        $error = "There was an error saving your image.";
        return null;
    }
}

// Handle form submission
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Profile and banner pictures
    $profile_picture = !empty($_FILES['profile_picture']['tmp_name']) ? uploadImage($_FILES['profile_picture'], "profile", $errors['profile_picture']) : $user_data["profile_picture"];
    $banner_picture = !empty($_FILES['banner_picture']['tmp_name']) ? uploadImage($_FILES['banner_picture'], "banner", $errors['banner_picture']) : $user_data["banner_picture"];

    // Validate username
    if (empty(trim($_POST["username"]))) {
        $errors['username'] = "Please enter a username.";
    } elseif (!preg_match('/^[a-zA-Z0-9_]+$/', trim($_POST["username"]))) {
        $errors['username'] = "Username can only contain letters, numbers, and underscores.";
    } else {
        $username = trim($_POST["username"]);
        $query = "SELECT id FROM users WHERE username = ? AND id != ?";
        $stmt = $link->prepare($query);
        $stmt->bind_param("si", $username, $user_id);
        $stmt->execute();
        if ($stmt->get_result()->num_rows > 0) {
            $errors['username'] = "This username is already taken.";
        }
    }

    // Validate first name and last name
    $first_name = !empty(trim($_POST["first_name"])) ? trim($_POST["first_name"]) : $errors['first_name'] = "Please enter your first name.";
    $last_name = !empty(trim($_POST["last_name"])) ? trim($_POST["last_name"]) : $errors['last_name'] = "Please enter your last name.";

    // Validate email
    if (empty(trim($_POST["email"]))) {
        $errors['email'] = "Please enter an email.";
    } elseif (!filter_var(trim($_POST["email"]), FILTER_VALIDATE_EMAIL)) {
        $errors['email'] = "Please enter a valid email.";
    } else {
        $email = trim($_POST["email"]);
        $query = "SELECT id FROM users WHERE email = ? AND id != ?";
        $stmt = $link->prepare($query);
        $stmt->bind_param("si", $email, $user_id);
        $stmt->execute();
        if ($stmt->get_result()->num_rows > 0) {
            $errors['email'] = "This email is already in use.";
        }
    }

    // Validate password
    if (!empty(trim($_POST["new_password"])) || !empty(trim($_POST["confirm_new_password"]))) {
        if (empty(trim($_POST["new_password"]))) {
            $errors['new_password'] = "Please enter a password.";
        } elseif (strlen(trim($_POST["new_password"])) < 6) {
            $errors['new_password'] = "Password must be at least 6 characters.";
        } else {
            $new_password = trim($_POST["new_password"]);
        }

        if (empty(trim($_POST["confirm_new_password"]))) {
            $errors['confirm_new_password'] = "Please confirm your password.";
        } elseif ($new_password !== trim($_POST["confirm_new_password"])) {
            $errors['confirm_new_password'] = "Passwords do not match.";
        }
    }

    // If no errors, update profile
    if (empty($errors)) {
        if (!empty($new_password)) {
            $hashed_password = password_hash($new_password, PASSWORD_DEFAULT);
            $query = "UPDATE users SET username = ?, first_name = ?, last_name = ?, email = ?, password = ?, profile_picture = ?, banner_picture = ? WHERE id=?";
            $stmt = $link->prepare($query);
            $stmt->bind_param("sssssssi", $username, $first_name, $last_name, $email, $hashed_password, $profile_picture, $banner_picture, $user_id);
        } else {
            $query = "UPDATE users SET username = ?, first_name = ?, last_name = ?, email = ?, profile_picture = ?, banner_picture = ? WHERE id=?";
            $stmt = $link->prepare($query);
            $stmt->bind_param("ssssssi", $username, $first_name, $last_name, $email, $profile_picture, $banner_picture, $user_id);
        }

        if ($stmt->execute()) {
            $update_profile_success = "Your profile has been updated successfully.";
        } else {
            $errors['general'] = "Sorry, we couldn't update your profile. Please try again later.";
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <title>Edit Profile</title>
    
    <!-- Tailwind CSS -->
    <script src="https://cdn.tailwindcss.com"></script>
    <script defer src="https://unpkg.com/alpinejs@3.10.2/dist/cdn.min.js"></script>
</head>

<body>
    <?php include("../components/header.php"); ?>
    
    <div class="container mx-auto p-4">
        <?php if (!empty($update_profile_success)) : ?>
            <div class="p-4 mb-4 text-green-700 bg-green-100 border border-green-400 rounded">
                <?= htmlspecialchars($update_profile_success); ?>
            </div>
        <?php endif; ?>
        
        <?php if (!empty($errors)) : ?>
            <div class="p-4 mb-4 text-red-700 bg-red-100 border border-red-400 rounded">
                <ul>
                    <?php foreach ($errors as $error) : ?>
                        <li><?= htmlspecialchars($error); ?></li>
                    <?php endforeach; ?>
                </ul>
            </div>
        <?php endif; ?>

        <?php include("../components/profile/profile-edit-card.php"); ?>
    </div>
</body>

</html>
