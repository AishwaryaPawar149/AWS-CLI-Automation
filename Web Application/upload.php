<?php
require 'vendor/autoload.php';
use Aws\S3\S3Client;
use Dotenv\Dotenv;

// Load environment variables from .env
$dotenv = Dotenv::createImmutable(__DIR__);
$dotenv->load();

// === 1. MySQL (RDS) connection using env variables ===
$host = getenv('DB_HOST');
$user = getenv('DB_USER');
$pass = getenv('DB_PASS');
$db   = getenv('DB_NAME');

$conn = new mysqli($host, $user, $pass, $db);
if ($conn->connect_error) die("Connection failed: " . $conn->connect_error);

// === 2. AWS S3 Configuration using env variables ===
$s3 = new S3Client([
    'version' => 'latest',
    'region'  => getenv('AWS_REGION'),
    'credentials' => [
        'key'    => getenv('AWS_KEY'),
        'secret' => getenv('AWS_SECRET'),
    ],
]);

$bucket = getenv('AWS_BUCKET');
$message = '';
$success = false;

if(isset($_FILES['image'])){
    $title = $_POST['title'];
    $description = $_POST['description'];
    $image = $_FILES['image'];
    $filename = basename($image['name']);
    $tmpPath = $image['tmp_name'];

    try {
        // Upload image to S3
        $result = $s3->putObject([
            'Bucket' => $bucket,
            'Key'    => $filename,
            'SourceFile' => $tmpPath,
        ]);

        // Generate presigned URL
        $cmd = $s3->getCommand('GetObject', [
            'Bucket' => $bucket,
            'Key' => $filename
        ]);
        $request = $s3->createPresignedRequest($cmd, '+20 minutes');
        $imageUrl = (string)$request->getUri();

        // Insert into MySQL
        $stmt = $conn->prepare("INSERT INTO images (title, description, image_url) VALUES (?, ?, ?)");
        $stmt->bind_param("sss", $title, $description, $imageUrl);
        $stmt->execute();

        // Save locally (optional)
        move_uploaded_file($tmpPath, __DIR__ . '/uploads/' . $filename);

        $success = true;
        $message = "🎉 <strong>$title</strong> uploaded successfully!";
    } catch (Exception $e) {
        $success = false;
        $message = "❌ Upload Failed: " . $e->getMessage();
    }
}

$conn->close();
?>
<!DOCTYPE html>
<html>
<head>
    <title>✨ Image Upload</title>
    <link href="https://fonts.googleapis.com/css2?family=Roboto:wght@400;700&display=swap" rel="stylesheet">
    <style>
        body { font-family: 'Roboto', sans-serif; background: linear-gradient(to right, #ece9e6, #ffffff); margin:0; padding:0; }
        .container { max-width:600px; margin:50px auto; background:#fff; border-radius:20px; padding:40px; box-shadow:0 15px 35px rgba(0,0,0,0.2);}
        h1 { text-align:center; color:#00796b; margin-bottom:30px;}
        form input[type="text"], form input[type="file"] { width:100%; padding:12px 15px; margin-bottom:20px; border-radius:10px; border:1px solid #ddd; transition:0.3s;}
        form input[type="text"]:focus, form input[type="file"]:focus { border-color:#00796b; outline:none; box-shadow:0 0 8px rgba(0,121,107,0.3);}
        form input[type="submit"] { width:100%; background:#00796b; color:#fff; padding:15px; font-size:18px; border:none; border-radius:12px; cursor:pointer; transition:0.3s;}
        form input[type="submit"]:hover { background:#004d40; transform:translateY(-2px); box-shadow:0 5px 15px rgba(0,0,0,0.2);}
        .message { text-align:center; margin-bottom:30px; padding:20px; border-radius:15px; font-size:18px; animation:fadeIn 0.5s ease-in-out;}
        .success { background:#e0f7fa; color:#00796b; box-shadow:0 5px 15px rgba(0,121,107,0.2);}
        .error { background:#ffebee; color:#c62828; box-shadow:0 5px 15px rgba(198,40,40,0.2);}
        .image-preview { text-align:center; margin-top:20px;}
        .image-preview img { max-width:100%; border-radius:15px; box-shadow:0 8px 20px rgba(0,0,0,0.2);}
        a.view-link { display:inline-block; margin-top:10px; color:#00796b; font-weight:bold; text-decoration:none; transition:0.3s;}
        a.view-link:hover { color:#004d40; text-decoration:underline;}
        @keyframes fadeIn { from {opacity:0} to {opacity:1} }
    </style>
</head>
<body>
<div class="container">
    <h1>📤 Upload Image</h1>
    <?php if($message): ?>
        <div class="message <?= $success ? 'success' : 'error' ?>">
            <?= $success ? $message : htmlspecialchars($message) ?>
            <?php if($success): ?>
                <div class="image-preview">
                    <img src="<?= $imageUrl ?>" alt="Uploaded Image">
                    <br>
                    <a href="<?= $imageUrl ?>" target="_blank" class="view-link">View Full Image (expires in 20 min)</a>
                </div>
            <?php endif; ?>
        </div>
    <?php endif; ?>
    <form method="post" enctype="multipart/form-data">
        <input type="text" name="title" placeholder="Enter Title" required>
        <input type="text" name="description" placeholder="Enter Description" required>
        <input type="file" name="image" accept="image/*" required>
        <input type="submit" value="Upload">
    </form>
</div>
</body>
</html>
