<?php
session_start();
if (!isset($_SESSION['user_name'])) {
    header('location: login.php');
    exit();
}
include 'conn.php';
include 'header.php';

header('Cache-Control: no-cache, no-store, must-revalidate');
header('Pragma: no-cache');
header('Expires: 0');

function excerpt($title) {
        $new = substr($title, 0, 27);

        if (strlen($title) > 30) {
            return $new.'...';
        } else {
            return $title;
        }
    }
    
if (isset($_POST['update_category'])) {
   
    header('Location: view_post.php');
    exit();
}

$sql = "SELECT posts.*, GROUP_CONCAT(categories.name) AS category_names FROM posts
        LEFT JOIN posts_categories ON posts.id = posts_categories.post_id
        LEFT JOIN categories ON posts_categories.category_id = categories.id
        GROUP BY posts.id"; 
        
$data = mysqli_query($GLOBALS['conn'], $sql);
if (!$data) {
    echo "Error: " . mysqli_error($GLOBALS['conn']);
    exit();
}

?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Display Post</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.0.2/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-EVSTQN3/azprG1Anm3QDgpJLIm9Nao0Yz1ztcQTwFspd3yD65VohhpuuCOmLASjC" crossorigin="anonymous">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.0.2/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-EVSTQN3/azprG1Anm3QDgpJLIm9Nao0Yz1ztcQTwFspd3yD65VohhpuuCOmLASjC" crossorigin="anonymous">
    <link rel="stylesheet" href="style.css">
    <link rel="stylesheet" href="view_post.css">
    <link rel="icon" type="image/x-icon" href="download.png">
</head>
<body>
  
    <div class="border container pb-5 pt-3 " style="background-color:white; margin-bottom:30px; text-align: center;">
      <h3>All Post</h3>
      <div class="" style="margin-top: 25px;">
          <a class="ancor" href="post.php"><input  type="button" value="create post" name="" style="color: white;background-color: #0d6efd;font-weight: 400;line-height: 1.5;text-align: center;text-decoration: none;border: 1px solid transparent;padding: .375rem .75rem;font-size: 1rem;border-radius: .25rem;"></a>
      </div>
    </div>
    <div class=" container">
        <table class="table">
            <tr style="background-color: white; text-align:center;">
                <td>ID</td>
                <td>Title</td>
                <td>Description</td>
                <td>Categories</td>
                <td>Status</td>
                <td>Image</td>
                <td colspan="2";>Option</td>
                <td>Display post</td>
            </tr>
            <?php
            while ($result = mysqli_fetch_assoc($data)) {
                echo "<tr>
                    <td>$result[id]</td>
                    <td>$result[title]</td>
                    <td>" . excerpt($result['description']) . "</td>
                    <td>$result[category_names]</td>
                    <td>$result[status]</td>
                    <td><img src='uploads/$result[image]' style='max-width: 100px; max-height: 100px;' alt='Post Image'></td>
                    <td><a href='update_post.php?id=$result[id]'>Edit</a></td>
                    <td><a href='post_delete.php?id=$result[id]' onclick='return confirmDelete();'>DELETE</a></td>
                    <td><a href='display_posts.php?id=$result[id]'>View</a></td>

                </tr>";
            }
            ?>
        </table>
    </div>

    <script>
    function confirmDelete() {
        return confirm("Are you sure you want to delete this post?");
    }
    </script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.0.2/dist/js/bootstrap.bundle.min.js" integrity="sha384-MrcW6ZMFYlzcLA8Nl+NtUVF0sA7MsXsP1UyJoMp4YLEuNSfAP+JcXn/tWtIaxVXM" crossorigin="anonymous"></script>

</body>
</html>
