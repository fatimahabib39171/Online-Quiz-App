<?php
session_start();

// Only teacher access
if (!isset($_SESSION["loggedin"]) || $_SESSION["role"] !== "teacher") {
    header("Location: login.php");
    exit;
}

// Database connection
$conn = new mysqli("localhost", "root", "", "quiz_db");
if ($conn->connect_error) die("Connection failed: " . $conn->connect_error);

// Add question
if (isset($_POST['add'])) {
    $question = $conn->real_escape_string($_POST['question']);
    $option1 = $conn->real_escape_string($_POST['option1']);
    $option2 = $conn->real_escape_string($_POST['option2']);
    $option3 = $conn->real_escape_string($_POST['option3']);
    $option4 = $conn->real_escape_string($_POST['option4']);
    $answer = $conn->real_escape_string($_POST['answer']);
    
    $conn->query("INSERT INTO questions (question, option1, option2, option3, option4, answer) VALUES ('$question','$option1','$option2','$option3','$option4','$answer')");
}

// Update question
if (isset($_POST['update'])) {
    $id = intval($_POST['id']);
    $question = $conn->real_escape_string($_POST['question']);
    $option1 = $conn->real_escape_string($_POST['option1']);
    $option2 = $conn->real_escape_string($_POST['option2']);
    $option3 = $conn->real_escape_string($_POST['option3']);
    $option4 = $conn->real_escape_string($_POST['option4']);
    $answer = $conn->real_escape_string($_POST['answer']);
    
    $conn->query("UPDATE questions SET question='$question', option1='$option1', option2='$option2', option3='$option3', option4='$option4', answer='$answer' WHERE id=$id");
}

// Delete question
if (isset($_GET['delete'])) {
    $id = intval($_GET['delete']);
    $conn->query("DELETE FROM questions WHERE id=$id");
}

// Fetch questions
$result = $conn->query("SELECT * FROM questions ORDER BY id ASC");
$questions = [];
while ($row = $result->fetch_assoc()) {
    $questions[] = $row;
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<title>Manage Questions</title>
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.2.0-beta1/dist/css/bootstrap.min.css" rel="stylesheet">
<style>
.card-preview { background: #f8f9fa; border-left: 5px solid #2575fc; }
.card-preview .correct { font-weight: bold; color: green; }
</style>
</head>
<body>
<div class="container my-5">
    <h2 class="mb-4">Add / Edit Questions</h2>

    <!-- Form to add question -->
    <form method="post" class="mb-5">
        <input type="hidden" name="id" id="question-id">
        <div class="mb-3">
            <label>Question</label>
            <textarea name="question" id="question-text" class="form-control" required></textarea>
        </div>
        <div class="row">
            <div class="col mb-3"><input type="text" name="option1" id="option1" class="form-control" placeholder="Option A" required></div>
            <div class="col mb-3"><input type="text" name="option2" id="option2" class="form-control" placeholder="Option B" required></div>
            <div class="col mb-3"><input type="text" name="option3" id="option3" class="form-control" placeholder="Option C" required></div>
            <div class="col mb-3"><input type="text" name="option4" id="option4" class="form-control" placeholder="Option D" required></div>
        </div>
        <div class="mb-3">
            <label>Correct Answer</label>
            <select name="answer" id="answer" class="form-control" required>
                <option value="">Select correct answer</option>
                <option value="option1">Option A</option>
                <option value="option2">Option B</option>
                <option value="option3">Option C</option>
                <option value="option4">Option D</option>
            </select>
        </div>
        <button type="submit" name="add" class="btn btn-success">Add Question</button>
        <button type="submit" name="update" class="btn btn-primary">Update Question</button>
    </form>

    <h3>Preview / Manage Questions</h3>
    <?php foreach($questions as $q): ?>
        <div class="card mb-3 card-preview">
            <div class="card-body">
                <p><strong><?php echo $q['id'] ?>. <?php echo $q['question'] ?></strong></p>
                <ul class="list-group">
                    <li class="list-group-item <?php if($q['answer']=='option1') echo 'correct' ?>">A) <?php echo $q['option1'] ?></li>
                    <li class="list-group-item <?php if($q['answer']=='option2') echo 'correct' ?>">B) <?php echo $q['option2'] ?></li>
                    <li class="list-group-item <?php if($q['answer']=='option3') echo 'correct' ?>">C) <?php echo $q['option3'] ?></li>
                    <li class="list-group-item <?php if($q['answer']=='option4') echo 'correct' ?>">D) <?php echo $q['option4'] ?></li>
                </ul>
                <div class="mt-2">
                    <button class="btn btn-warning btn-sm edit-btn" 
                        data-id="<?php echo $q['id'] ?>"
                        data-question="<?php echo htmlspecialchars($q['question']) ?>"
                        data-option1="<?php echo htmlspecialchars($q['option1']) ?>"
                        data-option2="<?php echo htmlspecialchars($q['option2']) ?>"
                        data-option3="<?php echo htmlspecialchars($q['option3']) ?>"
                        data-option4="<?php echo htmlspecialchars($q['option4']) ?>"
                        data-answer="<?php echo $q['answer'] ?>"
                    >Edit</button>
                    <a href="?delete=<?php echo $q['id'] ?>" class="btn btn-danger btn-sm" onclick="return confirm('Delete this question?')">Delete</a>
                </div>
            </div>
        </div>
    <?php endforeach; ?>
</div>

<script>
document.querySelectorAll(".edit-btn").forEach(btn => {
    btn.addEventListener("click", () => {
        document.getElementById("question-id").value = btn.dataset.id;
        document.getElementById("question-text").value = btn.dataset.question;
        document.getElementById("option1").value = btn.dataset.option1;
        document.getElementById("option2").value = btn.dataset.option2;
        document.getElementById("option3").value = btn.dataset.option3;
        document.getElementById("option4").value = btn.dataset.option4;
        document.getElementById("answer").value = btn.dataset.answer;
        window.scrollTo({top:0, behavior:'smooth'});
    });
});
</script>
</body>
</html>
