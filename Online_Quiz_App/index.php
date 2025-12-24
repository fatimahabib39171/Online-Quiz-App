<?php 
session_start();
include 'config.php'; 

// Redirect if not logged in
if (!isset($_SESSION["loggedin"])) {
    header("Location: login.php");
    exit;
}

// Fetch questions from DB
$questions = [];
$result = mysqli_query($link, "SELECT * FROM questions");

while ($row = mysqli_fetch_assoc($result)) {
    $questions[] = [
        'numb' => (int)$row['id'],
        'question' => $row['question'],
        'answer' => $row['answer'], // This should be 'A','B','C','D'
        'options' => [
            'A' => $row['option1'],
            'B' => $row['option2'],
            'C' => $row['option3'],
            'D' => $row['option4']
        ]
    ];
}

$questions_json = json_encode($questions);
?>

<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Quiz</title>
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.2.0-beta1/dist/css/bootstrap.min.css" rel="stylesheet">
<style>
    .info_box .card {
    position: fixed;
    top: 50%;
    left: 50%;
    transform: translate(-50%, -50%);
    width: 90%;
    max-width: 500px;
    z-index: 1000;
    transition: transform 0.3s ease, box-shadow 0.3s ease;
}

.info_box .card:hover {
    transform: translate(-50%, -50%) scale(1.03);
    box-shadow: 0 15px 30px rgba(0,0,0,0.4);
}

.info_box button {
    transition: transform 0.2s ease;
}

.info_box button:hover {
    transform: scale(1.1);
}

.quiz_box, .result_box {
    display: none;
    max-width: 650px;
    margin: 50px auto;
    background: #fff;
    border-radius: 15px;
    padding: 25px;
    box-shadow: 0 15px 30px rgba(0,0,0,0.2);
}

.quiz_box header, .quiz_box footer { display: flex; justify-content: space-between; align-items: center; }
.option { background: #f1f1f1; border-radius: 8px; margin: 10px 0; padding: 12px; cursor: pointer; transition: 0.2s; }
.option:hover { background: #dbe2ff; }
.option.correct { background-color: #28a745; color: #fff; }
.option.incorrect { background-color: #dc3545; color: #fff; }
.option.disabled { pointer-events: none; opacity: 0.7; }
.next_btn { margin-top: 15px; display: none; }
.next_btn.show { display: inline-block; }
.time_line { height: 5px; background: #17a2b8; margin-top: 10px; }
.total_que span p { display: inline; font-weight: bold; }
</style>
</head>
<body>

<!-- Info Box -->
<div class="info_box">
    <div class="card text-center shadow-lg p-4" style="border-radius:20px; background: linear-gradient(135deg, #6a11cb, #2575fc); color: white;">
        <div class="card-header mb-3">
            <h2><i class="fas fa-clipboard-list"></i> Quiz Rules</h2>
        </div>
        <div class="card-body text-start">
            <p><i class="fas fa-clock"></i> You have only <strong>15 seconds</strong> per question.</p>
            <p><i class="fas fa-ban"></i> Once you select an answer, it cannot be undone.</p>
            <p><i class="fas fa-times-circle"></i> You cannot select any option once the time is over.</p>
            <p><i class="fas fa-door-closed"></i> You cannot exit the quiz while playing.</p>
            <p><i class="fas fa-star"></i> Points are awarded for correct answers.</p>
        </div>
        <div class="card-footer mt-3 d-flex justify-content-around">
            <button class="quit btn btn-danger btn-lg shadow-sm"><i class="fas fa-sign-out-alt"></i> Exit</button>
            <button class="restart btn btn-success btn-lg shadow-sm"><i class="fas fa-play"></i> Start Quiz</button>
        </div>
    </div>
</div>


<!-- Quiz Box -->
<div class="quiz_box">
    <header>
        <div class="title">Quiz Application</div>
        <div class="timer">
            <div class="time_left_txt">Time Left</div>
            <div class="timer_sec">15</div>
        </div>
    </header>
    <div class="time_line"></div>
    <section>
        <div class="que_text"></div>
        <div class="option_list"></div>
    </section>
    <footer>
        <div class="total_que"></div>
        <button class="next_btn btn btn-primary">Next Question</button>
    </footer>
</div>

<!-- Result Box -->
<div class="result_box" style="display:none; max-width:600px; margin:50px auto; text-align:center;">
    <h2 class="mb-3">🎉 Quiz Completed!</h2>
    <p class="final_score fs-4"></p>

    <div class="mt-4">
        <button class="btn btn-success btn-lg me-2" id="retakeQuiz">
            Retake Quiz
        </button>
        <button class="btn btn-danger btn-lg" id="exitQuiz">
            Exit Quiz
        </button>
    </div>
</div>


<script>
let questions = <?php echo $questions_json; ?>;
let que_count = 0;
let userScore = 0;
let counter;
let timeValue = 15;

const infoBox = document.querySelector(".info_box");
const restartBtn = document.querySelector(".restart");
const quitBtn = document.querySelector(".quit");
const quizBox = document.querySelector(".quiz_box");
const queText = document.querySelector(".que_text");
const optionList = document.querySelector(".option_list");
const nextBtn = document.querySelector(".next_btn");
const bottomQueCounter = document.querySelector(".total_que");
const timerSec = document.createElement("div"); // For timer
timerSec.className = "timer_sec";
quizBox.querySelector("header").appendChild(timerSec);
const resultBox = document.querySelector(".result_box");
const finalScore = document.querySelector(".final_score");
const retakeBtn = document.getElementById("retakeQuiz");
const exitBtn = document.getElementById("exitQuiz");


// Start Quiz
restartBtn.addEventListener("click", () => {
    infoBox.style.display = "none";
    quizBox.style.display = "block";
    que_count = 0;
    userScore = 0;
    showQuestion(que_count);
    nextBtn.style.display = "none";
    queCounter(que_count + 1);
});

// Quit / Exit button
quitBtn.addEventListener("click", () => {
    window.location.href = "dashboard.php";
});

exitBtn.addEventListener("click", () => {
    window.location.href = "dashboard.php"; // change if your dashboard filename is different
});

retakeBtn.addEventListener("click", () => {
    resultBox.style.display = "none";
    quizBox.style.display = "block";

    que_count = 0;
    userScore = 0;
    nextBtn.style.display = "none";

    showQuestion(que_count);
    queCounter(1);
});


// Show question
function showQuestion(index){
    clearInterval(counter);
    timeValue = 15;
    timerSec.textContent = `Time Left: ${timeValue}s`;
    counter = setInterval(timer, 1000);

    let q = questions[index];
    queText.innerHTML = `${q.numb}. ${q.question}`;
    optionList.innerHTML = Object.keys(q.options).map(letter => 
        `<div class="option"><strong>${letter}:</strong> ${q.options[letter]}</div>`
    ).join("");

    document.querySelectorAll(".option").forEach(option => {
        option.addEventListener("click", () => selectOption(option, q.answer));
    });
}

// Timer function
function timer(){
    timeValue--;
    timerSec.textContent = `Time Left: ${timeValue}s`;
    if(timeValue < 0){
        clearInterval(counter);
        autoSelectCorrect();
    }
}

// Option selected
function selectOption(option, correctAnswer){
    clearInterval(counter); // stop timer
    const selectedLetter = option.textContent.charAt(0);

    if(selectedLetter === correctAnswer){
        option.classList.add("correct");
        userScore++;
    } else {
        option.classList.add("incorrect");
        // Highlight correct
        document.querySelectorAll(".option").forEach(opt => {
            if(opt.textContent.charAt(0) === correctAnswer){
                opt.classList.add("correct");
            }
        });
    }

    document.querySelectorAll(".option").forEach(opt => opt.classList.add("disabled"));
    nextBtn.style.display = "inline-block";
}

// Auto select correct if time runs out
function autoSelectCorrect(){
    document.querySelectorAll(".option").forEach(opt => {
        const letter = opt.textContent.charAt(0);
        if(letter === questions[que_count].answer){
            opt.classList.add("correct");
        }
        opt.classList.add("disabled");
    });
    nextBtn.style.display = "inline-block";
}

// Next Question
nextBtn.addEventListener("click", () => {
    que_count++;
    if(que_count < questions.length){
        showQuestion(que_count);
        nextBtn.style.display = "none";
        queCounter(que_count + 1);
    } else {
        quizBox.style.display = "none";
        resultBox.style.display = "block";
        finalScore.innerHTML = `Your Score: <strong>${userScore}</strong> / ${questions.length}`;
    }
});

// Question Counter
function queCounter(index){
    bottomQueCounter.innerHTML = `<span><p>${index}</p> of <p>${questions.length}</p> Questions</span>`;
}

</script>

</body>
</html>
