<?php
session_start();
# Include connection
require_once "./config.php";

# Define variables and initialize with empty values
$username_err = $email_err = $password_err = "";
$username = $email = $password = "";

# Processing form data when form is submitted
if ($_SERVER["REQUEST_METHOD"] == "POST") {
  # Validate username
  if (empty(trim($_POST["username"]))) {
    $username_err = "Please enter a username.";
  } else {
    $username = trim($_POST["username"]);
    if (!ctype_alnum(str_replace(array("@", "-", "_"), "", $username))) {
      $username_err = "Username can only contain letters, numbers and symbols like '@', '_', or '-'.";
    } else {
      # Prepare a select statement
      $sql = "SELECT id FROM users WHERE username = ?";

      if ($stmt = mysqli_prepare($link, $sql)) {
        # Bind variables to the statement as parameters
        mysqli_stmt_bind_param($stmt, "s", $param_username);

        # Set parameters
        $param_username = $username;

        # Execute the prepared statement 
        if (mysqli_stmt_execute($stmt)) {
          # Store result
          mysqli_stmt_store_result($stmt);

          # Check if username is already registered
          if (mysqli_stmt_num_rows($stmt) == 1) {
            $username_err = "This username is already registered.";
          }
        } else {
          echo "<script>" . "alert('Oops! Something went wrong. Please try again later.')" . "</script>";
        }

        # Close statement 
        mysqli_stmt_close($stmt);
      }
    }
  }

  # Validate email 
  if (empty(trim($_POST["email"]))) {
    $email_err = "Please enter an email address";
  } else {
    $email = filter_var($_POST["email"], FILTER_SANITIZE_EMAIL);
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
      $email_err = "Please enter a valid email address.";
    } else {
      # Prepare a select statement
      $sql = "SELECT id FROM users WHERE email = ?";

      if ($stmt = mysqli_prepare($link, $sql)) {
        # Bind variables to the statement as parameters
        mysqli_stmt_bind_param($stmt, "s", $param_email);

        # Set parameters
        $param_email = $email;

        # Execute the prepared statement 
        if (mysqli_stmt_execute($stmt)) {
          # Store result
          mysqli_stmt_store_result($stmt);

          # Check if email is already registered
          if (mysqli_stmt_num_rows($stmt) == 1) {
            $email_err = "This email is already registered.";
          }
        } else {
          echo "<script>" . "alert('Oops! Something went wrong. Please try again later.');" . "</script>";
        }

        # Close statement
        mysqli_stmt_close($stmt);
      }
    }
  }

  # Validate password
  if (empty(trim($_POST["password"]))) {
    $password_err = "Please enter a password.";
  } else {
    $password = trim($_POST["password"]);
    if (strlen($password) < 8) {
      $password_err = "Password must contain at least 8 or more characters.";
    }
  }

# ADD THIS: Get the selected role from the form
$role = trim($_POST["role"]); // 'student' or 'teacher'


  # Check input errors before inserting data into database
  if (empty($username_err) && empty($email_err) && empty($password_err)) {
    # Prepare an insert statement
    $sql = "INSERT INTO users(username, email, password, role) VALUES (?, ?, ?, ?)";

    if ($stmt = mysqli_prepare($link, $sql)) {
      # Bind varibales to the prepared statement as parameters
      mysqli_stmt_bind_param($stmt, "ssss", $param_username, $param_email, $param_password, $param_role);

      # Set parameters
      $param_username = $username;
      $param_email = $email;
      $param_password = password_hash($password, PASSWORD_DEFAULT);
      $param_role = $role;

      # Execute the prepared statement
      if (mysqli_stmt_execute($stmt)) {
        echo "<script>" . "alert('Registeration completed successfully. Login to continue.');" . "</script>";
        echo "<script>" . "window.location.href='./login.php';" . "</script>";
        exit;
      } else {
        echo "<script>" . "alert('Oops! Something went wrong. Please try again later.');" . "</script>";
      }

      # Close statement
      mysqli_stmt_close($stmt);
    }
  }

  # Close connection
  mysqli_close($link);
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Sign Up - Quiz System</title>
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.2.0-beta1/dist/css/bootstrap.min.css" rel="stylesheet">
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
<style>
body {
    background: linear-gradient(to right, #667eea, #764ba2);
    min-height: 100vh;
    display: flex;
    justify-content: center;
    align-items: center;
    font-family: 'Segoe UI', sans-serif;
}

.signup-card {
    background: #fff;
    border-radius: 25px;
    box-shadow: 0 20px 40px rgba(0,0,0,0.2);
    padding: 40px;
    max-width: 450px;
    width: 100%;
    animation: fadeIn 0.8s ease;
}

@keyframes fadeIn {
    from { opacity: 0; transform: translateY(30px); }
    to { opacity: 1; transform: translateY(0); }
}

.signup-card h2 {
    text-align: center;
    color: #4a47a3;
    font-weight: 700;
    margin-bottom: 20px;
}

.form-control:focus {
    border-color: #764ba2;
    box-shadow: 0 0 0 0.2rem rgba(118,75,162,.25);
}

.btn-primary {
    background: linear-gradient(to right, #667eea, #764ba2);
    border: none;
    font-weight: bold;
    transition: 0.3s;
}

.btn-primary:hover {
    transform: scale(1.05);
}

.text-danger {
    font-size: 0.85rem;
}

.alert {
      border-radius: 8px;
}
</style>
</head>
<body>

<div class="signup-card">
    <h2><i class="fas fa-user-plus"></i> Sign Up</h2>
    <p class="text-center text-muted">Create your account to start the quiz</p>

    <?php if(!empty($username_err) || !empty($email_err) || !empty($password_err)): ?>
        <div class="alert alert-danger">
            <ul class="mb-0">
                <?php if($username_err) echo "<li>$username_err</li>"; ?>
                <?php if($email_err) echo "<li>$email_err</li>"; ?>
                <?php if($password_err) echo "<li>$password_err</li>"; ?>
            </ul>
        </div>
    <?php endif; ?>

    <form action="<?= htmlspecialchars($_SERVER["PHP_SELF"]); ?>" method="post">
        <div class="mb-3">
            <label>Username</label>
            <input type="text" name="username" class="form-control" value="<?= $username; ?>" placeholder="Enter username">
        </div>
        <div class="mb-3">
            <label>Email Address</label>
            <input type="email" name="email" class="form-control" value="<?= $email; ?>" placeholder="Enter email">
        </div>
        <div class="mb-3">
            <label>Password</label>
            <input type="password" name="password" class="form-control" id="password" placeholder="Enter password">
        </div>
        <div class="mb-3 form-check">
            <input type="checkbox" class="form-check-input" id="togglePassword">
            <label for="togglePassword" class="form-check-label">Show Password</label>
        </div>
        <div class="mb-3">
            <label>Register as</label>
            <select name="role" class="form-select">
                <option value="student" selected>Student</option>
                <option value="teacher">Teacher</option>
            </select>
        </div>
        <button type="submit" class="btn btn-primary w-100">Sign Up</button>
        <p class="mt-3 text-center mb-0">Already have an account? <a href="./login.php">Log In</a></p>
    </form>
</div>

<script>
const togglePassword = document.querySelector('#togglePassword');
const password = document.querySelector('#password');

togglePassword.addEventListener('change', () => {
    password.type = togglePassword.checked ? 'text' : 'password';
});
</script>

</body>
</html>
