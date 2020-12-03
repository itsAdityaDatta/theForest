<?php 
require 'functions/functions.php';
    session_start();
    if (!isset($_SESSION['user_id'])) {
        header("location:index.php");
    }
    $temp = $_SESSION['user_id'];
    session_destroy();
    session_start();
    $_SESSION['user_id'] = $temp;
    ob_start(); 
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
    *,h1,h2,h3{
        font-family: 'Abel', sans-serif;
    }
</style>
<body>
    <div class="container">
        <?php include 'includes/navbar.php'; ?>
        <br>
        <div class="createpost">
            <form method="post" action="" onsubmit="return validatePost()" enctype="multipart/form-data">
                <span style="float:right; color:black">
                <input type="checkbox" id="public" name="public">
                <label for="public">Private</label>
                </span>
                Wassup? <span class="required" style="display:none;"> &nbsp Error: Input cannot be empty</span><br>
                <textarea rows="6" name="caption" style="margin-bottom:5px; padding:5px"></textarea>
                <center><img src="" id="preview" style="max-width:580px; display:none;"></center>
                <div class="createpostbuttons">
                    <label>
                        <img src="images/photo.png">
                        <input type="file" name="fileUpload" id="imagefile">
                    </label>
                    <input type="submit" value="Post" name="post">
                </div>
            </form>
        </div>
       <h1> Your Feed </h1>
        <?php 
        
        $sql = "SELECT posts.post_caption, posts.post_time, posts.post_public, users.user_firstname,users.user_lastname, users.user_id, users.user_gender, posts.post_id
                FROM posts
                JOIN users                     
                ON posts.post_by = users.user_id  
                WHERE posts.post_public = 'Y' OR users.user_id = {$_SESSION['user_id']}   -- public posts ya khudke posts--
                UNION
                SELECT posts.post_caption, posts.post_time, posts.post_public, users.user_firstname,
                        users.user_lastname, users.user_id, users.user_gender, posts.post_id
                FROM posts
                JOIN users
                ON posts.post_by = users.user_id
                JOIN (                              -- friends ke post --
                    SELECT friendship.user1_id AS user_id
                    FROM friendship
                    WHERE friendship.user2_id = {$_SESSION['user_id']} AND friendship.friendship_status = 1
                    UNION
                    SELECT friendship.user2_id AS user_id
                    FROM friendship
                    WHERE friendship.user1_id = {$_SESSION['user_id']} AND friendship.friendship_status = 1
                ) userfriends
                ON userfriends.user_id = posts.post_by
                WHERE posts.post_public = 'N'           -- sirf wohi posts jo private ho warna toh public mein pehle hi aa jaayenge --
                ORDER BY post_time DESC";
                
        $query = mysqli_query($conn, $sql);
        if(!$query){
            echo mysqli_error($conn);
        }
        if(mysqli_num_rows($query) == 0){
            echo '<div class="post">';
            echo 'Your feed is empty. There are no posts to show.';
            echo '</div>';
        }
        else{
            // Profile Image Dimensions
            $width = '40px'; 
            $height = '40px';
            while($row = mysqli_fetch_assoc($query)){
                include 'includes/post.php';
                echo '<br>';
            }
        }
        ?>
        <br><br><br>
    </div>
    <script src="resources/js/jquery.js"></script>
    <script>
        // Invoke preview when an image file is choosen.
        $(document).ready(function(){
            $('#imagefile').change(function(){
                preview(this);
            });
        });
        
        function preview(input){
            if (input.files && input.files[0]) {
                var reader = new FileReader();
                reader.onload = function (event){
                    $('#preview').attr('src', event.target.result);
                    $('#preview').css('display', 'initial');
                }
                reader.readAsDataURL(input.files[0]);
            }
        }
        
        function validatePost(){
            var required = document.getElementsByClassName("required");
            var caption = document.getElementsByTagName("textarea")[0].value;
            required[0].style.display = "none";
            if(caption == ""){
                required[0].style.display = "initial";
                return false;
            }
            return true;
        }
    </script>
</body>
</html>

<?php
if($_SERVER['REQUEST_METHOD'] == 'POST') { // Form is Posted
    // Assign Variables
    $caption = $_POST['caption'];
    if(isset($_POST['public'])) {
        $public = "N";
    } else {
        $public = "Y";
    }
    $poster = $_SESSION['user_id'];
    
    $sql = "INSERT INTO posts (post_caption, post_public, post_time, post_by)
            VALUES ('$caption', '$public', NOW(), $poster)";
    $query = mysqli_query($conn, $sql);

    if($query){
        // Upload Post Image If a file was choosen
        if (!empty($_FILES['fileUpload']['name'])) {
            // Retrieve Post ID
            $last_id = mysqli_insert_id($conn);
            include 'functions/upload.php';
        }
        header("location: home.php");
    }
}
?>