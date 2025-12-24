<?php
session_start();

/* ===============================
   ACCESS CONTROL (Teacher Only)
   =============================== */
if (!isset($_SESSION["loggedin"]) || $_SESSION["role"] !== "teacher") {
    header("Location: login.php");
    exit;
}

/* ===============================
   DATABASE CONNECTION
   =============================== */
$conn = new mysqli("localhost", "root", "", "quiz_db");
if ($conn->connect_error) {
    die("Database Connection Failed: " . $conn->connect_error);
}

/* ===============================
   PREFILL DATA (FOR EDIT FROM PREVIEW)
   =============================== */
$question = $_POST['question'] ?? '';
$option1  = $_POST['option1'] ?? '';
$option2  = $_POST['option2'] ?? '';
$option3  = $_POST['option3'] ?? '';
$option4  = $_POST['option4'] ?? '';
$answer   = $_POST['answer'] ?? '';

/* ===============================
   SAVE TO DATABASE
   =============================== */
if (isset($_POST['submit'])) {

    $question_db = $conn->real_escape_string($_POST['question']);
    $option1_db  = $conn->real_escape_string($_POST['option1']);
    $option2_db  = $conn->real_escape_string($_POST['option2']);
    $option3_db  = $conn->real_escape_string($_POST['option3']);
    $option4_db  = $conn->real_escape_string($_POST['option4']);
    $answer_db   = $conn->real_escape_string($_POST['answer']);

    $sql = "INSERT INTO questions (question, option1, option2, option3, option4, answer)
            VALUES ('$question_db','$option1_db','$option2_db','$option3_db','$option4_db','$answer_db')";

    if ($conn->query($sql)) {
        // ✅ REDIRECT to clear POST and show message
        header("Location: create_quiz.php?success=1");
        exit;
    } else {
        header("Location: create_quiz.php?error=1");
        exit;
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<title>Create Quiz Question</title>
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.2.0-beta1/dist/css/bootstrap.min.css" rel="stylesheet">

<style>
body {
    background: linear-gradient(135deg,#667eea,#764ba2);
    min-height: 100vh;
    font-family:'Segoe UI',sans-serif;
}
.quiz-card {
    max-width: 720px;
    margin: 50px auto;
    background: #fff;
    border-radius: 22px;
    padding: 30px;
    box-shadow: 0 25px 45px rgba(0,0,0,0.25);
}
.quiz-card h2 {
    text-align:center;
    font-weight:800;
    color:#4a47a3;
}
label { font-weight:600; }
.form-control { border-radius:12px; }
.submit-btn, .preview-btn {
    width:48%;
    padding:14px;
    border-radius:35px;
    font-size:18px;
    font-weight:bold;
}
</style>
</head>

<body>

<div class="quiz-card">

    <h2>Create Quiz Question</h2>

    <!--  Show message ONLY after a question is added -->
    <?php if (isset($_GET['success'])): ?>
        <div id="successAlert" class="alert alert-success text-center">
            Question added successfully!
        </div>
    <?php endif; ?>

    <?php if (isset($_GET['error'])): ?>
        <div class="alert alert-danger text-center">
            Failed to add question.
        </div>
    <?php endif; ?>

    <!-- FORM -->
    <form method="post">

        <div class="mb-3">
            <label>Question</label>
            <textarea name="question" class="form-control" rows="3" required><?= htmlspecialchars($question) ?></textarea>
        </div>

        <div class="row">
            <div class="col-md-6 mb-3">
                <label>Option A</label>
                <input type="text" name="option1" class="form-control" value="<?= htmlspecialchars($option1) ?>" required>
            </div>
            <div class="col-md-6 mb-3">
                <label>Option B</label>
                <input type="text" name="option2" class="form-control" value="<?= htmlspecialchars($option2) ?>" required>
            </div>
            <div class="col-md-6 mb-3">
                <label>Option C</label>
                <input type="text" name="option3" class="form-control" value="<?= htmlspecialchars($option3) ?>" required>
            </div>
            <div class="col-md-6 mb-3">
                <label>Option D</label>
                <input type="text" name="option4" class="form-control" value="<?= htmlspecialchars($option4) ?>" required>
            </div>
        </div>

        <div class="mb-4">
            <label>Correct Answer</label>
            <select name="answer" class="form-control" required>
                <option value="">Select correct answer</option>
                <option value="A" <?= $answer=="A"?"selected":"" ?>>Option A</option>
                <option value="B" <?= $answer=="B"?"selected":"" ?>>Option B</option>
                <option value="C" <?= $answer=="C"?"selected":"" ?>>Option C</option>
                <option value="D" <?= $answer=="D"?"selected":"" ?>>Option D</option>
            </select>
        </div>

        <div class="d-flex justify-content-between">
            <button type="submit" name="preview" formaction="preview.php" formnovalidate class="btn btn-primary preview-btn">
                Preview
            </button>

            <button type="submit" name="submit" class="btn btn-success submit-btn">
                Add Question
            </button>
        </div>

    </form>
</div>

<script>
setTimeout(() => {
    const alertBox = document.getElementById("successAlert");
    if (alertBox) alertBox.remove();
}, 3000); // Message disappears after 10 seconds
</script>

</body>
</html>

<?php $conn->close(); ?>
