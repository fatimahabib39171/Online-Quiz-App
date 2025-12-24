<?php
session_start();

// ACCESS CONTROL
if(!isset($_SESSION['loggedin'])){
    header("Location: login.php");
    exit;
}

// DB Connection
$conn = new mysqli("localhost","root","","quiz_db");
if($conn->connect_error) die("Connection failed: " . $conn->connect_error);

// Fetch user data
$user_id = $_SESSION['id'];
$result = $conn->query("SELECT * FROM users WHERE id=$user_id");
$user = $result->fetch_assoc();
$msg = "";

// Handle profile update
if(isset($_POST['update_profile'])){
    $username = $conn->real_escape_string($_POST['username']);
    $email    = $conn->real_escape_string($_POST['email']);
    $updated = false;

    // Update password if provided
    if(!empty($_POST['password'])){
        if($_POST['password'] === $_POST['confirm_password']){
            $password_hash = password_hash($_POST['password'], PASSWORD_DEFAULT);
            $conn->query("UPDATE users SET password='$password_hash' WHERE id=$user_id");
            $updated = true;
        } else {
            $msg = "❌ Passwords do not match!";
        }
    }

    // Update profile picture if uploaded
    if(isset($_FILES['profile_pic']) && $_FILES['profile_pic']['error'] == 0){
        $ext = pathinfo($_FILES['profile_pic']['name'], PATHINFO_EXTENSION);
        $allowed = ['jpg','jpeg','png','gif'];
        if(in_array(strtolower($ext), $allowed)){
            $uploadDir = "uploads/";
            if(!is_dir($uploadDir)) mkdir($uploadDir, 0755, true);
            $new_name = $uploadDir . "profile_$user_id.$ext";
            if(move_uploaded_file($_FILES['profile_pic']['tmp_name'], $new_name)){
                $conn->query("UPDATE users SET profile_pic='$new_name' WHERE id=$user_id");
                $updated = true;
            }
        }
    }

    // Update username & email
    if($username !== $user['username'] || $email !== $user['email']){
        $conn->query("UPDATE users SET username='$username', email='$email' WHERE id=$user_id");
        $updated = true;
    }

    if($updated) $msg = "✅ Profile updated successfully!";

    // Refresh user data
    $result = $conn->query("SELECT * FROM users WHERE id=$user_id");
    $user = $result->fetch_assoc();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<title>User Profile</title>
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.2.0-beta1/dist/css/bootstrap.min.css" rel="stylesheet">
<style>
body {
    background: linear-gradient(135deg,#667eea,#764ba2);
    font-family:'Segoe UI',sans-serif;
    min-height:100vh;
    display:flex;
    justify-content:center;
    align-items:center;
    padding:20px;
}
.profile-container {
    background:#fff;
    border-radius:20px;
    max-width:450px;
    width:100%;
    padding:30px 25px;
    box-shadow:0 15px 35px rgba(0,0,0,0.2);
    position:relative;
    text-align:center;
}

.toggle-password {
    position: absolute;
    right: 12px;
    top: 50%;
    transform: translateY(-50%);
    cursor: pointer;
    font-size: 18px;
    color: #666;
    user-select: none;
}

.profile-header { margin-bottom:25px; }
.profile-header h2 { font-weight:700; color:#4a47a3; }

.alert { text-align:center; margin-bottom:15px; }

/* Avatar */
.avatar-container { position:relative; width:120px; height:120px; margin:0 auto 20px; cursor:pointer; }
.avatar-container img { width:120px; height:120px; border-radius:50%; border:3px solid #4a47a3; object-fit:cover; transition:0.3s; }
.avatar-container:hover img { transform:scale(1.1); box-shadow:0 0 20px rgba(0,0,0,0.3); }
.avatar-container input { position:absolute; width:120px; height:120px; opacity:0; cursor:pointer; border-radius:50%; }

/* Floating input labels */
.form-group { position:relative; margin-bottom:20px; }
.form-group input { width:100%; padding:12px 12px; border-radius:10px; border:1px solid #ccc; transition:0.3s; }
.form-group label { position:absolute; top:12px; left:15px; color:#aaa; transition:0.3s; pointer-events:none; }
.form-group input:focus { border-color:#667eea; }
.form-group input:focus + label,
.form-group input:not(:placeholder-shown) + label { top:-10px; left:10px; font-size:12px; color:#4a47a3; background:#fff; padding:0 5px; }

/* Buttons */
.btn { border-radius:30px; padding:10px 25px; font-weight:600; transition:0.3s; }
.btn-primary:hover { background:#5650d4; }
.btn-secondary:hover { background:#555; }
</style>
</head>
<body>

<div class="profile-container">
    <div class="profile-header">
        <h2>My Profile</h2>
    </div>

    <?php if($msg): ?>
        <div id="successAlert" class="alert alert-info"><?= $msg ?></div>
    <?php endif; ?>

    <form method="post" enctype="multipart/form-data">

        <!-- Avatar -->
        <div class="avatar-container" title="Click to change profile picture">
            <img id="profilePreview" src="<?= isset($user['profile_pic']) && file_exists($user['profile_pic']) ? $user['profile_pic'] : 'img/ic_profile.png' ?>" alt="Profile">
            <input type="file" name="profile_pic" accept="image/*" onchange="previewImage(event)">
        </div>

        <div class="form-group">
            <input type="text" name="username" value="<?= htmlspecialchars($user['username']) ?>" placeholder=" " required>
            <label>Username</label>
        </div>

        <div class="form-group">
            <input type="email" name="email" value="<?= htmlspecialchars($user['email']) ?>" placeholder=" " required>
            <label>Email</label>
        </div>

        <!-- Password Fields with Toggle -->
        <div class="form-group position-relative">
            <input type="password" id="password" name="password" placeholder=" ">
            <label>New Password</label>
            <span class="toggle-password" onclick="togglePassword('password')">&#128065;</span>
        </div>

        <div class="form-group position-relative">
            <input type="password" id="confirm_password" name="confirm_password" placeholder=" ">
            <label>Confirm Password</label>
            <span class="toggle-password" onclick="togglePassword('confirm_password')">&#128065;</span>
        </div>

        <div class="d-flex justify-content-between">
            <a href="dashboard.php" class="btn btn-secondary">Back</a>
            <button type="submit" name="update_profile" class="btn btn-primary">Update</button>
        </div>
    </form>
</div>

<script>
// Live preview of uploaded profile picture
function previewImage(event){
    const reader = new FileReader();
    reader.onload = function(){
        document.getElementById('profilePreview').src = reader.result;
    }
    reader.readAsDataURL(event.target.files[0]);
}

// Hide success message after 3 seconds
setTimeout(() => {
    const alert = document.getElementById('successAlert');
    if(alert) alert.style.display = 'none';
}, 3000);

// Toggle password visibility
function togglePassword(fieldId){
    const input = document.getElementById(fieldId);
    input.type = input.type === "password" ? "text" : "password";
}
</script>

</body>
</html>

<?php $conn->close(); ?>
