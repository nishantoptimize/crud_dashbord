<?php
session_start();
if (!isset($_SESSION['user_name'])) {
    header('location: login.php');
    exit();
}
include 'header.php';
include 'conn.php';
include 'connection.php';

$id = $_GET['id'];

$query = "SELECT * FROM posts WHERE id='$id'";
$data = mysqli_query($conn, $query);
$result = mysqli_fetch_assoc($data);

$sql = "SELECT * FROM categories WHERE parent_id = '0'";
$query1 = mysqli_query($conn, $sql);
$categories = array();
$subcategories = array();
if ($query1) {
    while ($row = mysqli_fetch_assoc($query1)) {
        $categories[$row['id']] = $row['name'];

        $subQuery = "SELECT * FROM categories WHERE parent_id = '{$row['id']}'";
        $subResult = mysqli_query($conn, $subQuery);
        if ($subResult) {
            while ($subRow = mysqli_fetch_assoc($subResult)) {
                $subcategories[$row['id']][$subRow['id']] = $subRow['name'];
            }
        }
    }
}

if (isset($_POST['update'])) {
    $title = $_POST['title'];
    $desc = $_POST['description'];
    $status = $_POST['status'];
    $categories = $_POST['categories']; 

    if (!empty($_FILES['image']['name'])) {
        $image_name = $_FILES['image']['name'];
        $image_tmp = $_FILES['image']['tmp_name'];
        $image_size = $_FILES['image']['size'];

        $upload_directory = "uploads/";

        if ($image_size > 5000000) { 
            $error['image'] = "Image size must be less than 5MB";
        } else {
            $target_file = $upload_directory . basename($image_name);
            if (!move_uploaded_file($image_tmp, $target_file)) {
                $error['image'] = "Error uploading image";
            }
        }
    } else {
        $image_name = $result['image'];
    }

    $sql = "UPDATE posts SET title='$title', description='$desc', status='$status', image='$image_name', updated_at=NOW() WHERE id='$id'";
    $query = mysqli_query($conn, $sql);

    $deleteQuery = "DELETE FROM posts_categories WHERE post_id='$id'";
    mysqli_query($conn, $deleteQuery);

    function generateInsertQuery($categoryId) {
        global $id;
        return "INSERT INTO posts_categories (category_id, post_id) VALUES ('$categoryId', '$id');";
    }

    $insertQueries = array_map('generateInsertQuery', $categories);

    $insertQuery = implode("", $insertQueries);

    if(mysqli_multi_query($conn, $insertQuery)) {
        header("location:view_post.php?id=$id");
        exit(); 
    } else {
        echo "Error inserting categories: " . mysqli_error($conn);
    }
}
?>
<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Update post</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-QWTKZyjpPEjISv5WaRU9OFeRpok6YctnYmDr5pNlyT2bRjXh0JMhjY6hW+ALEwIH" crossorigin="anonymous">
    <link rel="icon" type="image/x-icon" href="download.png">
    <link href="https://stackpath.bootstrapcdn.com/bootstrap/3.4.1/css/bootstrap.min.css" rel="stylesheet">
    <script src="https://code.jquery.com/jquery-3.5.1.min.js"></script>
    <script src="https://stackpath.bootstrapcdn.com/bootstrap/3.4.1/js/bootstrap.min.js"></script>
    <link href="https://cdn.jsdelivr.net/npm/summernote@0.8.18/dist/summernote.min.css" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/summernote@0.8.18/dist/summernote.min.js"></script>
</head>
<body>
<div>
    <h3 style="text-align: center;">Edit Post</h3>
</div>

<form action="" method="post" enctype="multipart/form-data">
    <div class="mb-3">
        <label for="title" class="form-label">Title</label>
        <input type="text" class="form-control" id="title" aria-describedby="emailHelp" required name="title" value="<?= $result['title']; ?>">
    </div>
    <div class="mb-3">
        <label for="desc" class="form-label">Description</label>
        <textarea class="form-control" id="desc" name="description" required><?= $result['description']; ?></textarea>
    </div>

    <label>Category</label><br>
    <?php foreach ($categories as $catId => $catName): ?>
        <div>
            <input type="checkbox" name="categories[]" id="category<?= $catId; ?>" value="<?= $catId; ?>" <?php if (in_array($catId, explode(',', $result['category']))) echo "checked"; ?>>
            <label for="category<?= $catId; ?>"><?= $catName; ?></label>
            <?php if (isset($subcategories[$catId])): ?>
                <div style="margin-left: 20px;">
                    <?php foreach ($subcategories[$catId] as $subcatId => $subcatName): ?>
                        <div>
                            <input type="checkbox" name="categories[]" id="subcategory<?= $subcatId; ?>" value="<?= $subcatId; ?>" <?php if (in_array($subcatId, explode(',', $result['category']))) echo "checked"; ?>>
                            <label for="subcategory<?= $subcatId; ?>"><?= $subcatName; ?></label>
                        </div>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
        </div>
    <?php endforeach; ?>

    <label for="status">Status</label>
    <select name="status" id="status" required>
        <option value="Draft" <?= $result['status'] == 'Draft' ? 'selected' : '' ?>>Draft</option>
        <option value="publish" <?= $result['status'] == 'publish' ? 'selected' : '' ?>>Publish</option>
    </select>
    <div class="mb-3">
        <label for="image" class="form-label">Image</label>
        <input type="file" class="form-control" id="image" name="image">
        <span><?= isset($error['image']) ? $error['image'] : "" ?></span>
    </div>
    <img id="featured-image" src="uploads/<?= $result["image"] ?>" alt="Featured Image" style="width:200px; height:200px">
    <input type="submit" value="Update" name="update" style="margin-top: 50px;">
</form>

<script type="text/javascript">
    $(document).ready(function() {
        $("#desc").summernote({
            placeholder: "Enter Description",
            height: 300,
            callbacks: {
                onImageUpload: function(files) {
                    uploadImage(files[0]);
                }
            }
        });

        function uploadImage(file) {
            var formData = new FormData();
            formData.append('image', file);

            $.ajax({
                url: 'upload_image.php',
                method: 'POST',
                data: formData,
                processData: false,
                contentType: false,
                success: function(response) {
                    var imageUrl = response;
                    $('#desc').summernote('insertImage', imageUrl);
                },
                error: function(xhr, status, error) {
                    console.error(xhr.responseText);
                }
            });
        }

        function updateFeaturedImage(input) {
            if (input.files && input.files[0]) {
                var reader = new FileReader();
                reader.onload = function(e) {
                    $('#featured-image').attr('src', e.target.result);
                }
                reader.readAsDataURL(input.files[0]);
            }
        }

        $('#image').change(function() {
            updateFeaturedImage(this);
        });

        $('#featured-image').attr('src', 'uploads/<?= $result["image"] ?>');
    });
</script>
</body>
</html>
