<?php
//
require 'functions/functions.php';
session_start();
// Check whether user is logged on or not
if (!isset($_SESSION['user_id'])) {
    header("location:index.php");
}
// Establish Database Connection
$conn = connect();

?>

<!DOCTYPE html>
<html>
<head>
    <title>The Forest</title>
    <link rel="stylesheet" type="text/css" href="resources/css/main.css">
    <link rel="preconnect" href="https://fonts.gstatic.com">
    <link href="https://fonts.googleapis.com/css2?family=Abel&display=swap" rel="stylesheet">
</head>
<style>
    *{
        font-family: 'Abel', sans-serif;
    }
</style>
<body>
    <div class="container">
        <?php include 'includes/navbar.php'; ?>
        <h1>Search Results</h1>
        <?php
             $key = $_GET['query'];
                $name = explode(' ', $key, 2);  //break string into array
                if(empty($name[1])) {
                    $sql = "SELECT * FROM users WHERE users.user_firstname = '$name[0]' OR users.user_lastname= '$name[0]'";
                } else {
                    $sql = "SELECT * FROM users WHERE users.user_firstname = '$name[0]' AND users.user_lastname= '$name[1]'";
                }
                include 'includes/userquery.php';
                
                echo "<br><center><hr></center><br>";

                $sql = "SELECT posts.post_caption, posts.post_time, posts.post_public, users.user_firstname,
                                users.user_lastname, users.user_id, users.user_gender, posts.post_id
                        FROM posts
                        JOIN users
                        ON posts.post_by = users.user_id
                        WHERE (posts.post_public = 'Y' OR users.user_id = {$_SESSION['user_id']}) AND posts.post_caption LIKE '%$key%'
                        UNION
                        SELECT posts.post_caption, posts.post_time, posts.post_public, users.user_firstname,
                                users.user_lastname, users.user_id, users.user_gender, posts.post_id
                        FROM posts
                        JOIN users
                        ON posts.post_by = users.user_id
                        JOIN (
                            SELECT friendship.user1_id AS user_id
                            FROM friendship
                            WHERE friendship.user2_id = {$_SESSION['user_id']} AND friendship.friendship_status = 1
                            UNION
                            SELECT friendship.user2_id AS user_id
                            FROM friendship
                            WHERE friendship.user1_id = {$_SESSION['user_id']} AND friendship.friendship_status = 1
                        ) userfriends
                        ON userfriends.user_id = posts.post_by
                        WHERE posts.post_public = 'N' AND posts.post_caption LIKE '%$key%'
                        ORDER BY post_time DESC";
                $query = mysqli_query($conn, $sql);
                $width = '40px';
                $height = '40px';
                if(!$query){
                    echo mysqli_error($conn);
                }
                if(mysqli_num_rows($query) == 0){
                    echo '<div class="post">';
                    echo 'There are no posts associated with the given the keyword, try to widen your search query.';
                    echo '</div>';
                    echo '<br>';
                }
                while($row = mysqli_fetch_assoc($query)){
                    include 'includes/post.php';
                    echo '<br>';
                }
           
        
    
        ?>
    </div>
</body>
</html>
