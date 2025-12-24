<?php
session_start();
header('Content-Type: application/json');

$conn = mysqli_connect("localhost","root","","quiz_db");

if (!$conn) {
    echo json_encode([]);
    exit;
}

$sql = "SELECT * FROM questions";
$result = mysqli_query($conn, $sql);

$questions = [];
$numb = 1;

while ($row = mysqli_fetch_assoc($result)) {
    $questions[] = [
        "numb" => $numb++,
        "question" => $row['question'],
        "answer" => $row['correct_answer'],
        "options" => [
            $row['option1'],
            $row['option2'],
            $row['option3'],
            $row['option4']
        ]
    ];
}

echo json_encode($questions);
