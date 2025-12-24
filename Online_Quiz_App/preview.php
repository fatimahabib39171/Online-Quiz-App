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
   DELETE QUESTION
   =============================== */
if (isset($_GET['delete_id'])) {
    $id = intval($_GET['delete_id']);
    $conn->query("DELETE FROM questions WHERE id=$id");
    header("Location: preview.php");
    exit;
}

/* ===============================
   UPDATE QUESTION
   =============================== */
if (isset($_POST['update'])) {
    $id       = intval($_POST['id']);
    $question = $conn->real_escape_string($_POST['question']);
    $option1  = $conn->real_escape_string($_POST['option1']);
    $option2  = $conn->real_escape_string($_POST['option2']);
    $option3  = $conn->real_escape_string($_POST['option3']);
    $option4  = $conn->real_escape_string($_POST['option4']);
    $answer   = $conn->real_escape_string($_POST['answer']);

    $conn->query("UPDATE questions SET 
        question='$question',
        option1='$option1',
        option2='$option2',
        option3='$option3',
        option4='$option4',
        answer='$answer'
        WHERE id=$id");

    header("Location: preview.php");
    exit;
}

/* ===============================
   PREFILL FORM (for editing)
   =============================== */
$editQuestion = null;
if (isset($_GET['edit_id'])) {
    $id = intval($_GET['edit_id']);
    $res = $conn->query("SELECT * FROM questions WHERE id=$id");
    if ($res && $res->num_rows > 0) {
        $editQuestion = $res->fetch_assoc();
    }
}

/* ===============================
   GET ALL QUESTIONS
   =============================== */
$result = $conn->query("SELECT * FROM questions ORDER BY id ASC");
$questions = $result->fetch_all(MYSQLI_ASSOC);
?>

<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<title>Quiz Preview</title>
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.2.0-beta1/dist/css/bootstrap.min.css" rel="stylesheet">

<style>
body { background: linear-gradient(135deg,#667eea,#764ba2); font-family:'Segoe UI',sans-serif; padding:30px; }
.container { max-width:900px; margin:auto; }
.info-box {
    background: #f3f4ff; border-left:6px solid #4a47a3; border-radius:12px; padding:15px 18px; margin-bottom:25px;
}
.card {
    background:#fff; border-radius:22px; padding:20px 25px; margin-bottom:20px; box-shadow:0 10px 30px rgba(0,0,0,0.15);
}
.card h5 { color:#4a47a3; font-weight:700; }
.option { padding:10px 12px; margin:5px 0; border-radius:8px; border:1px solid #ddd; }
.correct { background:#28a745; color:#fff; border-color:#28a745; font-weight:600; }
.buttons { margin-top:10px; display:flex; gap:10px; }
</style>
</head>

<body>
<div class="container">
    <h2 class="text-center text-white mb-4">Quiz Preview & Management</h2>

    <!-- ================= INFO BOX + EDIT FORM ================= -->
    <?php if($editQuestion): ?>
    <div class="info-box">
        <h5>Edit Question</h5>
        <form method="post">
            <input type="hidden" name="id" value="<?= $editQuestion['id'] ?>">
            <div class="mb-2">
                <label>Question</label>
                <textarea name="question" class="form-control" rows="2" required><?= htmlspecialchars($editQuestion['question']) ?></textarea>
            </div>
            <div class="row">
                <div class="col-md-6 mb-2">
                    <label>Option A</label>
                    <input type="text" name="option1" class="form-control" value="<?= htmlspecialchars($editQuestion['option1']) ?>" required>
                </div>
                <div class="col-md-6 mb-2">
                    <label>Option B</label>
                    <input type="text" name="option2" class="form-control" value="<?= htmlspecialchars($editQuestion['option2']) ?>" required>
                </div>
                <div class="col-md-6 mb-2">
                    <label>Option C</label>
                    <input type="text" name="option3" class="form-control" value="<?= htmlspecialchars($editQuestion['option3']) ?>" required>
                </div>
                <div class="col-md-6 mb-2">
                    <label>Option D</label>
                    <input type="text" name="option4" class="form-control" value="<?= htmlspecialchars($editQuestion['option4']) ?>" required>
                </div>
            </div>
            <div class="mb-2">
                <label>Correct Answer</label>
                <select name="answer" class="form-control" required>
                    <option value="">Select correct answer</option>
                    <option value="A" <?= $editQuestion['answer']=="A"?"selected":"" ?>>Option A</option>
                    <option value="B" <?= $editQuestion['answer']=="B"?"selected":"" ?>>Option B</option>
                    <option value="C" <?= $editQuestion['answer']=="C"?"selected":"" ?>>Option C</option>
                    <option value="D" <?= $editQuestion['answer']=="D"?"selected":"" ?>>Option D</option>
                </select>
            </div>
            <button type="submit" name="update" class="btn btn-success">Update Question</button>
        </form>
    </div>
    <?php endif; ?>

    <!-- ================= LIST OF QUESTIONS ================= -->
    <?php if(empty($questions)): ?>
        <div class="alert alert-info text-center">No questions added yet.</div>
    <?php else: ?>
        <?php foreach($questions as $q): ?>
            <div class="card">
                <h5><?= htmlspecialchars($q['question']) ?></h5>
                <div class="option <?= $q['answer']=='A'?'correct':'' ?>">A. <?= htmlspecialchars($q['option1']) ?></div>
                <div class="option <?= $q['answer']=='B'?'correct':'' ?>">B. <?= htmlspecialchars($q['option2']) ?></div>
                <div class="option <?= $q['answer']=='C'?'correct':'' ?>">C. <?= htmlspecialchars($q['option3']) ?></div>
                <div class="option <?= $q['answer']=='D'?'correct':'' ?>">D. <?= htmlspecialchars($q['option4']) ?></div>

                <div class="buttons">
                    <!-- Edit → reload preview with form -->
                    <a href="preview.php?edit_id=<?= $q['id'] ?>" class="btn btn-warning btn-sm">✏ Edit</a>
                    <!-- Delete -->
                    <a href="preview.php?delete_id=<?= $q['id'] ?>" onclick="return confirm('Are you sure to delete this question?');" class="btn btn-danger btn-sm">🗑 Delete</a>
                </div>
            </div>
        <?php endforeach; ?>
    <?php endif; ?>

    <!-- Done button -->
    <div class="text-center mt-4">
        <a href="dashboard.php" class="btn btn-success btn-lg">Done</a>
    </div>
</div>

</body>
</html>

<?php $conn->close(); ?>
