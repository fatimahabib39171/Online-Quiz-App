<?php
session_start();

// Redirect if already logged in
if (isset($_SESSION["loggedin"]) && $_SESSION["loggedin"] === true) {
    header("Location: dashboard.php");
    exit;
}

require_once "./config.php"; // Ensure $link is defined here

$user_login_err = $user_password_err = $login_err = "";
$user_login = $user_password = "";

if ($_SERVER["REQUEST_METHOD"] === "POST") {

    // Validate username/email
    $user_login = trim($_POST["user_login"] ?? '');
    $user_password = trim($_POST["user_password"] ?? '');

    if ($user_login === '') $user_login_err = "Please enter your username or email.";
    if ($user_password === '') $user_password_err = "Please enter your password.";

    if (empty($user_login_err) && empty($user_password_err)) {

        $sql = "SELECT id, username, password, role FROM users WHERE username = ? OR email = ?";

        if ($stmt = mysqli_prepare($link, $sql)) {

            mysqli_stmt_bind_param($stmt, "ss", $user_login, $user_login);

            if (mysqli_stmt_execute($stmt)) {

                mysqli_stmt_store_result($stmt);

                if (mysqli_stmt_num_rows($stmt) === 1) {

                    mysqli_stmt_bind_result($stmt, $id, $username, $hashed_password, $role);
                    mysqli_stmt_fetch($stmt);

                    if (password_verify($user_password, $hashed_password)) {

                        // Login success
                        session_regenerate_id(true);
                        $_SESSION["id"] = $id;
                        $_SESSION["username"] = $username;
                        $_SESSION["role"] = strtolower($role);
                        $_SESSION["loggedin"] = true;

                        header("Location: dashboard.php");
                        exit;

                    } else {
                        $login_err = "Incorrect password.";
                    }

                } else {
                    $login_err = "No user found with that username/email.";
                }

            } else {
                $login_err = "Something went wrong. Please try again later.";
            }

            mysqli_stmt_close($stmt);
        } else {
              $login_err = "Database error: " . mysqli_error($link);
        }
    }

    mysqli_close($link);
}
?>

<<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="UTF-8">
  <meta http-equiv="X-UA-Compatible" content="IE=edge">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>User Login System</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.2.0/dist/css/bootstrap.min.css" rel="stylesheet">
  <link rel="shortcut icon" href="./img/favicon-16x16.png" type="image/x-icon">
  <style>
    body {
      background: linear-gradient(to right, #667eea, #764ba2);
      min-height: 100vh;
      display: flex;
      justify-content: center;
      align-items: center;
      font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
    }

    .login-card {
      background: #fff;
      border-radius: 1rem;
      box-shadow: 0 10px 30px rgba(0, 0, 0, 0.2);
      padding: 2rem;
      transition: transform 0.3s, box-shadow 0.3s;
    }

    .login-card:hover {
      transform: translateY(-5px);
      box-shadow: 0 15px 40px rgba(0, 0, 0, 0.3);
    }

    .login-card h1 {
      font-weight: 700;
      color: #333;
      margin-bottom: 1rem;
    }

    .form-control:focus {
      border-color: #764ba2;
      box-shadow: 0 0 0 0.2rem rgba(118, 75, 162, 0.25);
    }

    .btn-primary {
      background: #764ba2;
      border: none;
      transition: background 0.3s;
    }

    .btn-primary:hover {
      background: #667eea;
    }

    .form-check-label {
      user-select: none;
    }

    .alert {
      border-radius: 0.5rem;
    }

    a {
      color: #764ba2;
      text-decoration: none;
      font-weight: 500;
    }

    a:hover {
      text-decoration: underline;
    }
  </style>
</head>

<body>
  <div class="container">
    <div class="row justify-content-center align-items-center min-vh-100">
      <div class="col-md-6 col-lg-5">
        <?php if (!empty($login_err)) : ?>
          <div class="alert alert-danger"><?= $login_err; ?></div>
        <?php endif; ?>
        <div class="login-card">
          <h1 class="text-center">Welcome Back!</h1>
          <p class="text-center text-muted mb-4">Login to your account to continue</p>

          <form action="<?= htmlspecialchars($_SERVER["PHP_SELF"]); ?>" method="post" novalidate>
            <div class="mb-3">
              <label for="user_login" class="form-label">Email or Username</label>
              <input type="text" class="form-control" name="user_login" id="user_login" value="<?= $user_login; ?>">
              <small class="text-danger"><?= $user_login_err; ?></small>
            </div>

            <div class="mb-3">
              <label for="password" class="form-label">Password</label>
              <input type="password" class="form-control" name="user_password" id="password">
              <small class="text-danger"><?= $user_password_err; ?></small>
            </div>

            <div class="mb-3 form-check">
              <input type="checkbox" class="form-check-input" id="togglePassword">
              <label class="form-check-label" for="togglePassword">Show Password</label>
            </div>

            <div class="d-grid mb-3">
              <button type="submit" class="btn btn-primary">Log In</button>
            </div>

            <p class="text-center mb-0">Don't have an account? <a href="./register.php">Sign Up</a></p>
          </form>
        </div>
      </div>
    </div>
  </div>

  <script>
    const togglePassword = document.querySelector('#togglePassword');
    const password = document.querySelector('#password');

    togglePassword.addEventListener('change', function () {
      password.type = this.checked ? 'text' : 'password';
    });
  </script>
</body>

</html>
