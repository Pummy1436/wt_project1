<?php
$user_id = null;
$post_comment_title = $post_comment_image = "";
$post_comment_title_err = $post_comment_image_err = $share_comment_error = $share_comment_success = "";

if (isset($_SESSION["loggedin"]) && $_SESSION["loggedin"] === true) {
    $user_id = $_SESSION["id"];

    if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['share_post_comment'])) {
        if (is_uploaded_file($_FILES['post_comment_image']['tmp_name'])) {
            date_default_timezone_set('Europe/Istanbul');
            $file_extension = strtolower(pathinfo($_FILES["post_comment_image"]["name"], PATHINFO_EXTENSION));
            $uploaded_file_name = $user_id . "_" . date("Y-m-d_H-i-s") . "_" . rand() . '.' . $file_extension;
            $source_path = $_FILES["post_comment_image"]["tmp_name"];
            $target_path = '../assets/images/comments/' . $uploaded_file_name;

            if (move_uploaded_file($source_path, $target_path)) {
                $allowed_extensions = ['jpg', 'png', 'jpeg'];
                if (in_array($file_extension, $allowed_extensions)) {
                    $post_comment_image = $uploaded_file_name;
                } else {
                    $post_comment_image_err = "Please provide a valid file extension.";
                }
            } else {
                $post_comment_image_err = "We encountered an error while saving your image.";
            }
        }

        if (empty(trim($_POST["post_comment_title"]))) {
            $post_comment_title_err = "Please fill in the comment field.";
        } elseif (strlen(trim($_POST["post_comment_title"])) > 280) {
            $post_comment_title_err = "Your comment must not exceed 280 characters.";
        } else {
            $post_comment_title = trim($_POST["post_comment_title"]);
        }

        if (empty($post_comment_title_err) && empty($post_comment_image_err)) {
            $query = "INSERT INTO post_comment (post_comment_title, post_comment_image, post_comment_user_id, post_comment_post_id) VALUES (?, ?, ?, ?)";
            $stmt = $link->prepare($query);
            if ($stmt) {
                $stmt->bind_param("ssii", $post_comment_title, $post_comment_image, $user_id, $_POST['post_id']);
                if ($stmt->execute()) {
                    $share_comment_success = "Your post has been shared successfully.";
                } else {
                    $share_post_error = "Sorry, your post couldn't be shared at this time.";
                }
            } else {
                $share_post_error = "Sorry, your post couldn't be shared at this time.";
            }
        }
    }

    // Handle post likes
    if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['post_like'])) {
        handlePostReview($link, $user_id, $_POST['post_id'], 1);
    }

    // Handle post dislikes
    if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['post_dislike'])) {
        handlePostReview($link, $user_id, $_POST['post_id'], -1);
    }

    // Handle comment likes
    if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['post_comment_like'])) {
        handleCommentReview($link, $user_id, $_POST['post_comment_id'], 1);
    }

    // Handle comment dislikes
    if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['post_comment_dislike'])) {
        handleCommentReview($link, $user_id, $_POST['post_comment_id'], -1);
    }
}

// Helper function to handle post reviews
function handlePostReview($link, $user_id, $post_id, $review_type) {
    $isLikedQuery = "SELECT * FROM post_review WHERE post_review_user_id = ? AND post_review_post_id = ?";
    $stmt = $link->prepare($isLikedQuery);
    $stmt->bind_param("ii", $user_id, $post_id);
    $stmt->execute();
    $result = $stmt->get_result();
    $post_review_data = $result->fetch_assoc();

    if ($result->num_rows < 1) {
        $query = "INSERT INTO post_review (post_review_user_id, post_review_post_id, post_review_type) VALUES (?, ?, ?)";
        $stmt = $link->prepare($query);
        $stmt->bind_param("iii", $user_id, $post_id, $review_type);
        $stmt->execute();
    } elseif ($post_review_data["post_review_type"] == $review_type) {
        $query = "DELETE FROM post_review WHERE post_review_user_id = ? AND post_review_post_id = ?";
        $stmt = $link->prepare($query);
        $stmt->bind_param("ii", $user_id, $post_id);
        $stmt->execute();
    } else {
        $query = "UPDATE post_review SET post_review_type = ? WHERE post_review_user_id = ? AND post_review_post_id = ?";
        $stmt = $link->prepare($query);
        $stmt->bind_param("iii", $review_type, $user_id, $post_id);
        $stmt->execute();
    }
}

// Helper function to handle comment reviews
function handleCommentReview($link, $user_id, $comment_id, $review_type) {
    $isLikedQuery = "SELECT * FROM post_comment_review WHERE post_comment_review_user_id = ? AND post_comment_review_post_comment_id = ?";
    $stmt = $link->prepare($isLikedQuery);
    $stmt->bind_param("ii", $user_id, $comment_id);
    $stmt->execute();
    $result = $stmt->get_result();
    $comment_review_data = $result->fetch_assoc();

    if ($result->num_rows < 1) {
        $query = "INSERT INTO post_comment_review (post_comment_review_user_id, post_comment_review_post_comment_id, post_comment_review_type) VALUES (?, ?, ?)";
        $stmt = $link->prepare($query);
        $stmt->bind_param("iii", $user_id, $comment_id, $review_type);
        $stmt->execute();
    } elseif ($comment_review_data["post_comment_review_type"] == $review_type) {
        $query = "DELETE FROM post_comment_review WHERE post_comment_review_user_id = ? AND post_comment_review_post_comment_id = ?";
        $stmt = $link->prepare($query);
        $stmt->bind_param("ii", $user_id, $comment_id);
        $stmt->execute();
    } else {
        $query = "UPDATE post_comment_review SET post_comment_review_type = ? WHERE post_comment_review_user_id = ? AND post_comment_review_post_comment_id = ?";
        $stmt = $link->prepare($query);
        $stmt->bind_param("iii", $review_type, $user_id, $comment_id);
        $stmt->execute();
    }
}

$post_id = $_GET["post_id"];

$postQuery = "SELECT * FROM post 
              INNER JOIN category ON category.category_id = post.post_category_id 
              INNER JOIN users ON users.id = post.user_id 
              WHERE post.post_id = ?";
$stmt = $link->prepare($postQuery);
$stmt->bind_param("i", $post_id);
$stmt->execute();
$result = $stmt->get_result();
$row = $result->fetch_assoc();

if (empty($row) || empty($post_id)) { ?>
    <div class="pt-16 text-sm text-center">Sorry, the post you're looking for couldn't be found.</div>
<?php } else {
    include($_SERVER['DOCUMENT_ROOT'] . "/wt_project/components/content/components/post-card.php");

    $postQuery = "SELECT * FROM post_comment 
                  INNER JOIN users ON users.id = post_comment.post_comment_user_id 
                  WHERE post_comment_post_id = ? 
                  ORDER BY post_comment_id ASC";
    $stmt = $link->prepare($postQuery);
    $stmt->bind_param("i", $post_id);
    $stmt->execute();
    $commentData = $stmt->get_result();

    if ($commentData->num_rows > 0) {
        while ($comment_row = $commentData->fetch_assoc()) { ?>
            <div class="border-t my-6"></div>
        <?php
            include("components/comment-card.php");
        }
    } else { ?>
        <div class="py-16 text-sm text-center">No comments here yet. Be the first to comment!</div>
<?php }

    if (isset($_SESSION["loggedin"]) && $_SESSION["loggedin"] === true) {
        $user_id = $_SESSION["id"];
        include("components/share-comment.php");
    } else {
        include("components/share-comment-preview.php");
    }
} ?>