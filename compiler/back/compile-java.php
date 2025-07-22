<?php

header('Content-Type: application/json');

$input = json_decode(file_get_contents('php://input'), true);

$code = $input['code'] ?? '';
$userInput = $input['customInput'] ?? '';

if (empty($code)) {
    echo json_encode(["success" => false, "visibleOutput" => "No code provided."]);
    exit;
}

// 🛑 Check for multiple public classes
preg_match_all('/public\s+class\s+(\w+)/', $code, $matches);
if (count($matches[1]) > 1) {
    echo json_encode([
        "success" => false,
        "visibleOutput" => "Error: Only one 'public class' is allowed. Found: " . implode(', ', $matches[1])
    ]);
    exit;
}

// ✅ Get the public class name
$className = $matches[1][0] ?? 'Main';

$javaFile = "$className.java";
$classFile = "$className.class";
$inputFile = 'input.txt';
$outputFile = 'output.txt';
$errorFile = 'error.txt';

// Save files
file_put_contents($javaFile, $code);
file_put_contents($inputFile, $userInput);

// Compile
exec("javac $javaFile 2>$errorFile");
$compileError = file_get_contents($errorFile);

if (!empty($compileError)) {
    echo json_encode(["success" => false, "visibleOutput" => "Compilation Error:\n" . $compileError]);
    cleanup([$javaFile, $classFile, $inputFile, $outputFile, $errorFile]);
    exit;
}

// Run
exec("java $className < $inputFile > $outputFile 2>>$errorFile");
$output = file_get_contents($outputFile);
$runtimeError = file_get_contents($errorFile);

// Return output
echo json_encode([
    "success" => true,
    "visibleOutput" => $output ?: $runtimeError
]);

// Cleanup temp files
cleanup([$javaFile, $classFile, $inputFile, $outputFile, $errorFile]);

function cleanup($files) {
    foreach ($files as $file) {
        if (file_exists($file)) {
            unlink($file);
        }
    }
}
