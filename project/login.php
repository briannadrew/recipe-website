<?php
// login.php

// Name: Brianna Drew
// ID: #0622446
// Date: April 19th, 2021
// Description: On this page, a user can login to their account by providing their username and password. There are also options to
// navigate to the page where you can create a new account, or one to navigate to the page where you can be sent an email to reset your
// password if you have forgotten it, or one where you can set cookies to remember your login information.

// include the library file
require 'includes/library.php';
// create the database connection
$pdo = connectDB();

$errors = array(); // array to hold possible errors

// get and sanitize username and password from submitted form
$username = $_POST['username'] ?? null;
$username = filter_var($username, FILTER_SANITIZE_STRING);
$password = $_POST['password'] ?? null;

// get all user information from user table for the submitted username
$query = "SELECT * FROM `yummy_users` WHERE username = ?";
$stmt = $pdo->prepare($query);
$stmt->execute([$username]);
$results = $stmt->fetch();

// if the user tries to login...
  if (isset($_POST['submit'])) {
    if (!isset($username) or strlen($username) == 0 or !isset($password) or strlen($password) == 0) {
      $errors['empty'] = true; // if the username or password form fields were empty upon submission, display appropriate error message
    }
    else {
      if(!$results) {
        $errors['user'] = true; // if the submitted username was not found in the user table, display appropriate error message
      }
      else {
        // if the provided password matches the stored password for that username...
        if(password_verify($password, $results['password'])) {
          if(isset($_POST['remember-me'])) {
            // if the user ticks the "remember me" checkbox, set session cookies for the provided username and password
            setcookie("username",$username,time()+60*60*24*30*12);
	          setcookie("password",$password,time()+60*60*24*30*12);
          }
          // log in the user and redirect to the home page
          session_start();
          $_SESSION['username'] = $username;
          header("Location:main.php");
          exit();
        }
        else {
          $errors['login'] = true; // if the provided password does not match the stored password for that username, display appropriate error message
        }
      }
    }
  }
?>

<!DOCTYPE html>
<html lang="en">
  <head>
    <?php
      $PAGE_TITLE = "Log In";
      include 'includes/metadata.php';
    ?>  
  </head>
  <body>
    <?php include 'includes/nav.php';?>  
    <main>
    <h1>Log In</h1>
      <form action="<?=htmlspecialchars($_SERVER['PHP_SELF'])?>" method="post" class="account-management" id="login-form" autocomplete="off">
        <div>
          <label for="l-username">Username:</label>
          <input type="text" name="username" id="l-username" placeholder="Username"  value="<?php if(isset($_COOKIE['username'])) { echo $_COOKIE['username']; } ?>">
        </div>
        <div>
          <label for="l-password">Password:</label>
          <input type="password" name="password" id="l-password" value="<?php if(isset($_COOKIE['password'])) { echo $_COOKIE['password']; } ?>" placeholder="Password">
        </div>
        <a href="forgotpassword.php">Forgot Password?</a>
        <div>
          <!-- ERROR MESSAGES -->
          <span id="l-empty-error" class="<?=!isset($errors['empty']) ? 'hidden' : "";?>">*Please enter your username and password.</span>
          <span id="l-user-error" class="<?=!isset($errors['user']) ? 'hidden' : "";?>">*User does not exist.</span>
          <span id="l-login-error" class="<?=!isset($errors['login']) ? 'hidden' : "";?>">*Incorrect username or password.</span>
        </div>
        <div id="remember">
          <input type="checkbox" name="remember-me" id="remember-me">
          <label id="remember-label" for="remember-me">Remember Me</label>
        </div>
        <button class="form-buttons" id="submit" name="submit">Log In</button>
      </form>
      <form action="newaccount.php" method="post" class="wrong-place">
        <label for="go-join">Don't have an account yet?</label>
        <button type="submit" id="go-join" name="go-join">Sign Up</button>
      </form>
    </main>
    <?php include 'includes/footer.php';?>  
  </body>
</html>