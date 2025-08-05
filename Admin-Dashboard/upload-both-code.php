<?php
// Database configuration
$host = 'localhost';
$db   = 'music';
$user = 'root';
$pass = '';
$table = 'code_uploads'; // You can name it anything

// Connect to the database
$conn = new mysqli($host, $user, $pass, $db);

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Sanitize input
$correctCode = trim($_POST['correct_code']);
$wrongCode = trim($_POST['wrong_code']);

if (empty($correctCode) || empty($wrongCode)) {
    die("Both code fields are required.");
}

// Prepare and bind
$stmt = $conn->prepare("INSERT INTO $table (correct_code, wrong_code, uploaded_at) VALUES (?, ?, NOW())");
$stmt->bind_param("ss", $correctCode, $wrongCode);

if ($stmt->execute()) {
    echo "<h2>✅ Code uploaded successfully!</h2>";
    echo "<p><a href='question_upload.html'>Upload another</a></p>";
} else {
    echo "❌ Error: " . $stmt->error;
}

$stmt->close();
$conn->close();
?>
