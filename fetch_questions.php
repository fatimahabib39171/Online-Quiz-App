<?php
session_start();
include 'config.php';

header('Content-Type: application/json');

$questions = [];
$sql = "SELECT * FROM questions";
$result = mysqli_query($link, $sql);

if ($result && mysqli_num_rows($result) > 0) {
    while ($row = mysqli_fetch_assoc($result)) {
        if(!isset($row['answer'])) continue;
        $questions[] = [
            'numb' => (int)$row['id'],
            'question' => $row['question'],
            'answer' => $row['answer'],
            'options' => [$row['option1'], $row['option2'], $row['option3'], $row['option4']]
        ];
    }
}

echo json_encode($questions);
