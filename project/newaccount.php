<?php
// newaccount.php

// Name: Brianna Drew
// ID: #0622446
// Date: April 19th, 2021
// Description: On this page, a user can create a new account and then be logged into that account by submitting their email, a username,
// and a password. There is also the option to navigate to the page where you can login to an existing account.

// include the library file
require 'includes/library.php';
// create the database connection
$pdo = connectDB();

$errors = array(); // array to hold possible errors

// get and sanitize all data from submitted form
$email = $_POST['email'] ?? null;
$email = filter_var($email, FILTER_SANITIZE_EMAIL);
$username = $_POST['username'] ?? null;
$username = filter_var($username, FILTER_SANITIZE_STRING);
$password = $_POST['password'] ?? null;

// get all user information from user table for the new username
// (checking if the user already exists)
$query = "SELECT * FROM `yummy_users` WHERE username = ?";
$stmt = $pdo->prepare($query);
$stmt->execute([$username]);
$results = $stmt->fetch();

// get all user information from user table for the new email
// (checking if the email is already being used)
$equery = "SELECT * FROM `yummy_users` WHERE email = ?";
$estmt = $pdo->prepare($equery);
$estmt->execute([$email]);
$eresults = $estmt->fetch();

$uppercase = preg_match('@[A-Z]@', $password); // check if submitted password contains an uppercase letter
$lowercase = preg_match('@[a-z]@', $password); // check if submitted password contains a lowercase letter
$number = preg_match('@[0-9]@', $password); // check if submitted password contains a number
$special_chars = preg_match('@[^\w]@', $password); // check if submitted password contains a special character

// if the user tries to create a new account...
  if (isset($_POST['submit'])) {
    if (!isset($username) or strlen($username) == 0 or !isset($password) or strlen($password) == 0 or !isset($email) or strlen($email) == 0) {
      $errors['empty'] = true; // if any of the form fields were empty upon submission, display appropriate error message
    }
    else {
      if($results) {
        $errors['user'] = true; // if user already exists, display appropriate error message
      }
      elseif(filter_var($email, FILTER_VALIDATE_EMAIL) === false) {
        $errors['email'] = true; // if email submitted is not a valid email address, display appropriate error message
      }
      elseif($eresults) {
        $errors['ematch'] = true; // if email submitted is already being used, display appropriate error message
      }
      elseif (!$uppercase or !$lowercase or !$number or !$special_chars or strlen($password) < 8){
        $errors['strength'] = true; // if the password is less than 8 characters and does not contain a uppercase letter, lowercase letter, number, or special character, display appropriate error message
      }
      else {
        $hash = password_hash($password, PASSWORD_DEFAULT); // hash the submitted password
        // if there were no errors, insert the new account into the users table, log into the new account, and redirect to home page
        $new_query = "INSERT INTO `yummy_users` (`username`, `email`, `password`) VALUES (?,?,?)";
        $new_stmt = $pdo->prepare($new_query);
        $new_stmt->execute([$username, $email, $hash]);
        session_start();
        $_SESSION['username'] = $username;
        header("Location:main.php");
        exit();
      }
    }
  }
?>

<!DOCTYPE html>
<html lang="en">
  <head>
    <?php
      $PAGE_TITLE = "Sign Up";
      include 'includes/metadata.php';
    ?>  
  </head>
  <body>
    <?php include 'includes/nav.php';?>  
    <main>
    <h1>New Account</h1>
      <form action="<?=htmlspecialchars($_SERVER['PHP_SELF'])?>" method="post" class="account-management" id="new-acc-form" autocomplete="off">
        <div>
          <label for="n-email">Email:</label>
          <input type="email" name="email" id="n-email" placeholder="Email">
        </div>
        <div>
          <label for="n-username">Username:</label>
          <input type="text" name="username" id="n-username" placeholder="Username">
        </div>
        <div>
          <label for="n-password">Password:</label>
          <input type="password" name="password" id="n-password" placeholder="Password">
        </div>
        <div class="strength-bar">
          <input type="text" disabled="disabled" class="bar-1"/>
          <input type="text" disabled="disabled" class="bar-2"/>
          <input type="text" disabled="disabled" class="bar-3" />
          <input type="text" disabled="disabled" class="bar-4" />
        </div>
        <div>
          <span id="strength-display" class="hidden"></span>
          <!-- ERROR MESSAGES -->
          <span id="n-empty-error" class="<?=!isset($errors['empty']) ? 'hidden' : "";?>">*Please enter an email, username, and password.</span>
          <span id="n-email-error" class="<?=!isset($errors['email']) ? 'hidden' : "";?>">*Please enter a valid email address.</span>
          <span id="n-user-error" class="<?=!isset($errors['user']) ? 'hidden' : "";?>">*User already exists.</span>
          <span id="n-ematch-error" class="<?=!isset($errors['ematch']) ? 'hidden' : "";?>">*Email already being used.</span>
          <span id="n-strength-error" class="<?=!isset($errors['strength']) ? 'hidden' : "";?>">*Password should be at least 8 characters in length 
          and should include at least one upper case letter, one number, and one special character.</span>
        </div>
        <button class="form-buttons" id="submit" name="submit">Create Account</button>
      </form>
      <form action="login.php" method="post" class="wrong-place">
        <label for="go-login">Already have an account?</label>
        <button type="submit" id="go-login" name="go-login">Login</button>
      </form>
    </main>
    <?php include 'includes/footer.php';?>  
  </body>
</html>