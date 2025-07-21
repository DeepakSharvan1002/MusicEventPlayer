<?php
header("Content-Type: application/json");

// 1. Read incoming JSON
$data = json_decode(file_get_contents("php://input"), true);
$code = $data['code'] ?? "";

// 2. File setup
$filename = "Main.java";
file_put_contents($filename, $code);

// 3. Compile the code
exec("javac $filename 2>&1", $compileOutput, $compileStatus);

// Check for compilation errors
if ($compileStatus !== 0) {
    echo json_encode([
        "visibleOutput" => implode("\n", $compileOutput),
        "success" => false
    ]);
    exit;
}

// 4. Visible test case
$visibleInput = "2 3";
file_put_contents("input.txt", $visibleInput);

// Run the program with visible input
exec("java Main < input.txt", $visibleOutput);

// 5. Hidden test cases
$hiddenTests = [
    ["input" => "5 6", "expected" => "11"],
    ["input" => "10 20", "expected" => "30"],
    ["input" => "1 1", "expected" => "2"]
];

$allPassed = true;
foreach ($hiddenTests as $test) {
    file_put_contents("input.txt", $test['input']);
    exec("java Main < input.txt", $result);
    $output = trim(end($result));
    if ($output !== $test['expected']) {
        $allPassed = false;
        break;
    }
}

// 6. Final response
echo json_encode([
    "visibleOutput" => implode("\n", $visibleOutput),
    "success" => $allPassed
]);
?>
