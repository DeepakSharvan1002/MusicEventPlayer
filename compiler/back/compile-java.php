<?php
header('Content-Type: application/json');

$data = json_decode(file_get_contents('php://input'), true);

$code = $data['code'];
$className = preg_replace('/[^a-zA-Z0-9]/', '', $data['className']); // Sanitize
$input = $data['input'];

if (!$className) {
    echo json_encode(["output" => "Error: Invalid class name"]);
    exit;
}

$filename = "$className.java";
$filePath = __DIR__ . "/$filename";

// Save the code to file
file_put_contents($filePath, $code);

// Compile
$compile = shell_exec("javac $filePath 2>&1");

if ($compile) {
    // Compilation error
    echo json_encode(["output" => "Compilation Error:\n" . $compile]);
    unlink($filePath);
    exit;
}

// Run (multiple test cases supported through custom input)
$run = shell_exec("echo " . escapeshellarg($input) . " | java -cp " . __DIR__ . " $className 2>&1");

// Output result
echo json_encode(["output" => trim($run)]);

// Cleanup
unlink($filePath);
$classFile = __DIR__ . "/$className.class";
if (file_exists($classFile)) {
    unlink($classFile);
}
?>
