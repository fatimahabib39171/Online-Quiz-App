<?php
session_start();

// Redirect if not logged in
if (!isset($_SESSION["loggedin"]) || $_SESSION["loggedin"] !== true) {
    header("Location: login.php");
    exit;
}

// Safe session access
$username = htmlspecialchars($_SESSION["username"] ?? 'User');
$role = strtolower($_SESSION["role"] ?? 'student'); // normalize role
?>

<!DOCTYPE html>
<html lang="en">

<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>User Dashboard</title>

<!-- Google Fonts -->
<link href="https://fonts.googleapis.com/css2?family=Poppins:wght@500&family=Roboto:wght@300;400&display=swap" rel="stylesheet">

<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
<style>
    /* Body & font */
    body {
      background: linear-gradient(to right, #667eea, #764ba2);
      min-height: 100vh;
      justify-content: center;
      align-items: center;
      font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
    }

    /* Container */
    .dashboard-container {
        max-width: 900px;
        margin: 0 auto;
        padding: 2rem;
    }

    /* Header */
    .dashboard-header {
        text-align: center;
        margin-bottom: 2rem;
        color: #fff;
    }

    .dashboard-header img {
        width: 100px;
        margin-bottom: 10px;
    }

    .dashboard-header h1 {
        font-family: 'Poppins', sans-serif;
        font-weight: 500; /* Semi-bold headings */
        font-size: 2rem;
        margin-bottom: 0.5rem;
    }

    .greeting {
        font-family: 'Roboto', sans-serif;
        font-weight: 300;
        font-size: 1.3rem;
        color: #f0f0f0;
    }

    /* Cards */
    .cards {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
        gap: 1.5rem;
        margin-bottom: 2rem;
    }

    .card {
        background: #fff;
        border-radius: 12px;
        cursor: pointer;
        box-shadow: 0 6px 18px rgba(0, 0, 0, 0.15);
        transition: all 0.3s ease-in-out;
        text-align: center;
        padding: 2rem 1rem;
        font-family: 'Roboto', sans-serif;
        font-weight: 300; /* Light card text */
    }

    .card:hover {
        transform: translateY(-8px);
        box-shadow: 0 10px 25px rgba(0, 0, 0, 0.2);
    }

    .card img {
        width: 60px;
        margin-bottom: 1rem;
    }

    .card span {
        display: block;
        font-size: 1.2rem;
        font-family: 'Poppins', sans-serif;
        font-weight: 500; /* Semi-bold for titles */
        color: #333;
    }

    /* Logout Button */
    .logout-btn {
        display: block;
        width: 100%;
        max-width: 250px;
        margin: 0 auto;
        padding: 12px;
        background: #FF6347;
        color: #fff;
        font-size: 18px;
        font-weight: 500;
        border: none;
        border-radius: 8px;
        transition: background 0.3s;
        font-family: 'Poppins', sans-serif;
    }

    .logout-btn:hover {
        background: #e5533c;
    }

    /* Scrollable cards container */
    .scrollable-cards {
        max-height: 60vh;
        overflow-y: auto;
        padding-right: 5px;
    }

    /* Custom scrollbar */
    .scrollable-cards::-webkit-scrollbar {
        width: 6px;
    }

    .scrollable-cards::-webkit-scrollbar-thumb {
        background: rgba(108, 99, 255, 0.7);
        border-radius: 3px;
    }
</style>
</head>

<body>
<div class="dashboard-container">

    <!-- Header -->
    <div class="dashboard-header">
        <img src="./img/ic_quiz_logo.png" alt="Quiz App Logo">
        <h1>Quiz App</h1>
        <div class="greeting">Welcome, <?= $username ?>!</div>
    </div>

    <!-- Cards -->
    <div class="scrollable-cards cards">

        <!-- Profile Card -->
        <div class="card" onclick="window.location.href='profile.php'">
            <img src="<?= isset($_SESSION['profile_pic']) ? $_SESSION['profile_pic'] : './img/ic_profile.png' ?>" alt="Profile" style="width:60px; height:60px; border-radius:50%;">
            <span>Profile</span>
        </div>


        <!-- Create Quiz Card (Only for Teacher) -->
        <?php if($role === 'teacher'): ?>
        <div class="card" onclick="window.location.href='create_quiz.php'">
            <img src="./img/ic_create_quiz.png" alt="Create Quiz">
            <span>Create Quiz</span>
        </div>
        <?php endif; ?>

        <!-- Start Quiz Card -->
        <div class="card" onclick="window.location.href='index.php'">
            <img src="./img/ic_start_quiz.png" alt="Start Quiz">
            <span>Start Quiz</span>
        </div>


    </div>

    <!-- Logout -->
    <button class="logout-btn" onclick="window.location.href='logout.php'">Log Out</button>
</div>
</body>
</html>
