<?php
session_start();
require "db.php";

if (!isset($_SESSION["loggedin"])) {
    exit;
}

$data = json_decode(file_get_contents("php://input"), true);

$user_id = $_SESSION["id"];
$question_id = $data["question_id"];
$selected_answer = $data["selected_answer"];
$is_correct = $data["is_correct"];

$stmt = $conn->prepare(
    "INSERT INTO user_answers (user_id, question_id, selected_answer, is_correct)
     VALUES (?, ?, ?, ?)"
);
$stmt->bind_param("iisi", $user_id, $question_id, $selected_answer, $is_correct);
$stmt->execute();
