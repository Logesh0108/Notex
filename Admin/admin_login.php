<?php
session_start();
include '../config.php'; // Database connection file

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $username = trim($_POST['username']);
    $email = trim($_POST['email']);
    $password = trim($_POST['password']);

    // Check if fields are empty
    if (empty($username) || empty($email) || empty($password)) {
        echo "<script>alert('All fields are required!'); window.location.href='admin_login.php';</script>";
        exit();
    }

    // Fetch admin details from the database
    $sql = "SELECT id, username, email, password FROM admin WHERE username = ? AND email = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("ss", $username, $email);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows == 1) {
        $row = $result->fetch_assoc();
        $db_password = $row['password']; // Retrieved password from DB

        // Check if password matches
        if (password_verify($password, $db_password) || $password === $db_password) { 
            $_SESSION['admin_logged_in'] = true;
            $_SESSION['admin_username'] = $row['username'];
            $_SESSION['admin_id'] = $row['id']; // Store admin ID in session

            echo "<script>alert('Login Successful!'); window.location.href='dashboard.php';</script>";
            exit();
        } else {
            echo "<script>alert('Invalid Password!'); window.location.href='admin_login.php';</script>";
            exit();
        }
    } else {
        echo "<script>alert('Invalid Username or Email!'); window.location.href='admin_login.php';</script>";
        exit();
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="description" content="Snap Notes - A web application to quickly save and manage notes with ease.">
<meta name="keywords" content="Snap Notes, Notes App, Web Development, JavaScript, Productivity">
<meta name="viewport" content="width=device-width, initial-scale=1.0">

    
    <!-- Bootstrap CSS (can be from CDN or locally) -->
    <link href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css" rel="stylesheet">
  
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <link rel="icon" type="image/png" href="../src/admin.png">
    <title>NoteSnap</title>
    <style>
   /* General Styles */


/* Navbar */
.navbar {
    background-color:rgb(0, 0, 0);
    width: 100%;
    position: fixed;
    top: 0;
    z-index: 1000;
    box-shadow: 0 0 20px #00ff00;
}

.navbar-brand {
    color: white !important;
}

.navbar-nav li a {
    color: white !important;
    font-weight: bolder;
    border-radius: 10px;
}

.navbar-nav li a:hover {
    background-color: white;
    color: black !important;
}

/* Global Styles */
body {
    
    background-color: #0a0a0a;
    color: #fff;
    display: flex;
    justify-content: center;
    align-items: center;
    height: 100vh;
    margin: 0;
}

/* Login Container */
.container {
    background: rgba(0, 0, 0, 0.8);
    padding: 30px;
    border-radius: 10px;
    box-shadow: 0 0 20px #00ff00;
    width: 350px;
    text-align: center;
}

/* Form Elements */
h2 {
    margin-bottom: 20px;
    color: #00ff00;
    text-transform: uppercase;
}

.form-group {
    margin-bottom: 15px;
    text-align: left;
}

label {
    display: block;
    font-weight: bold;
    color: #00ff00;
}

input {
    width: 100%;
    padding: 10px;
    margin-top: 5px;
    border: 2px solid #00ff00;
    background: transparent;
    color: #fff;
    border-radius: 5px;
    outline: none;
    transition: 0.3s;
}

input:focus {
    border-color: #ff00ff;
    box-shadow: 0 0 10px #ff00ff;
}

/* Button Group Styling */
.button-group {
    display: flex;
    justify-content: space-between;
    margin-top: 15px;
}

/* Login Button */
.btn-animated {
    width: 48%;
    padding: 12px;
    background: #00ff00;
    color: #111;
    border: none;
    border-radius: 5px;
    font-size: 18px;
    font-weight: bold;
    text-transform: uppercase;
    cursor: pointer;
    transition: 0.3s;
}

.btn-animated:hover {
    background:rgb(0, 0, 0);
    color: #fff;
    box-shadow: 0 0 10px #fff;
}

/* Reset Button */
.btn-reset {
    width: 48%;
    padding: 12px;
    background: #ff0000;
    color: #fff;
    border: none;
    border-radius: 5px;
    font-size: 18px;
    font-weight: bold;
    text-transform: uppercase;
    cursor: pointer;
    transition: 0.3s;
}

/* Overlay background */
.alert-overlay {
    position: fixed;
    top: 0;
    left: 0;
    width: 100%;
    height: 100%;
    background: rgba(0, 0, 0, 0.5); /* Semi-transparent background */
    display: flex;
    align-items: center;
    justify-content: center;
    z-index: 1000;
    visibility: hidden;
    opacity: 0;
    transition: opacity 0.3s ease-in-out, visibility 0.3s;
}

/* Alert box container */
.alert-box {
    background: white;
    padding: 20px;
    border-radius: 10px;
    box-shadow: 0px 4px 10px rgba(0, 0, 0, 0.2);
    text-align: center;
    max-width: 400px;
    width: 90%;
    position: relative;
}

/* Alert message */
.alert-message {
    font-size: 18px;
    color: #333;
    margin-bottom: 15px;
}

/* Close button */
.alert-close {
    background: #007bff;
    color: white;
    padding: 8px 15px;
    border: none;
    border-radius: 5px;
    cursor: pointer;
    font-size: 16px;
    transition: 0.3s;
}

.alert-close:hover {
    background: #0056b3;
}

/* Show alert */
.show-alert {
    visibility: visible;
    opacity: 1;
}

.btn-reset:hover {
    background: #cc0000;
}


/* Responsive Design */
@media (max-width: 400px) {
    .container {
        width: 90%;
    }
}


 
    </style>
</head>
<body>
<nav class="navbar navbar-expand-lg navbar-dark">
        <a class="navbar-brand" href="#">Note Snap</a>
        <button class="navbar-toggler" type="button" data-toggle="collapse" data-target="#navbarNav" aria-controls="navbarNav" aria-expanded="false" aria-label="Toggle navigation">
            <span class="navbar-toggler-icon"></span>
        </button>
        <div class="collapse navbar-collapse" id="navbarNav">
            <ul class="navbar-nav ml-auto">
                <li class="nav-item">
                    <a class="nav-link" href="index.php">Home</a>
                </li>
                
                <li class="nav-item">
                    <a class="nav-link" href="admin_login.php" >Login</a>
                </li>
                
            </ul>
        </div>
    </nav>
 


    <div class="container">
    <h2>Admin Login</h2>
    <form id="login-form" method="post" action="">
    <div class="form-group">
            <label for="username">Username</label>
            <input type="username" id="username" name="username" autocomplete="off" required>
        </div>
        <div class="form-group">
            <label for="email">Email</label>
            <input type="email" id="email" name="email" autocomplete="off" required>
        </div>
        <div class="form-group">
            <label for="password">Password</label>
            <input type="password" id="password" name="password" autocomplete="off" required>
        </div>
        <div class="button-group">
            <button type="submit" class="btn-animated">Admin</button>
            <button type="reset" class="btn-reset">Reset</button>
        </div>
    </form>
</div>


  
   
</div>

 
    
</body>

</script>
<script src="https://code.jquery.com/jquery-3.5.1.slim.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.5.2/dist/umd/popper.min.js"></script>
    <script src="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>
</html>
