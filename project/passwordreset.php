<?php
  // passwordreset.php

  // Name: Brianna Drew
  // ID: #0622446
  // Date: April 19th, 2021
  // Description: On this page, a user can reset the password to their account in case they have forgotten it. This page is accessed from
  // the email sent to the user from the forgotpassword.php page when they request it.

  // include the library file
  require 'includes/library.php';
  // create the database connection
  $pdo = connectDB();

  $errors = array(); // array to hold possible errors

  $user_id = $_GET['id'] ?? null; // get the id of the user who's password is to be reset from the url parameters
  // get and sanitize all data from submitted form
  $new_pass = $_POST['new-password'] ?? null;
  $repeat_pass = $_POST['confirm-password'] ?? null;

  $uppercase = preg_match('@[A-Z]@', $new_pass); // check if submitted password contains an uppercase letter
  $lowercase = preg_match('@[a-z]@', $new_pass); // check if submitted password contains a lowercase letter
  $number = preg_match('@[0-9]@', $new_pass); // check if submitted password contains a number
  $special_chars = preg_match('@[^\w]@', $new_pass); // check if submitted password contains a special character

  // if the user wants to reset their password..
  if (isset($_POST['submit'])) {
    if (!isset($new_pass) or strlen($new_pass) == 0 or !isset($repeat_pass) or strlen($repeat_pass) == 0) {
      $errors['empty'] = true; // if any of the form fields were empty upon submission, display appropriate error message
    }
    else {
      if ($new_pass !== $repeat_pass) {
        $errors['match'] = true; // if the submitted passwords do not match each other, display appropriate error message
      }
      elseif (!$uppercase or !$lowercase or !$number or !$special_chars or strlen($new_pass) < 8) {
        $errors['strength'] = true; // if the password is less than 8 characters and does not contain a uppercase letter, lowercase letter, number, or special character, display appropriate error message
      }
      else {
        // if there were no errors, hash password and update it in the user table for the given user
        $hash = password_hash($new_pass, PASSWORD_DEFAULT);
        $query = "UPDATE `yummy_users` SET password = ? WHERE id = ?";
        $stmt = $pdo->prepare($query);
        $stmt->execute([$hash, $user_id]);

        // get the username for the given user from the users table, login, and redirect to the main page
        $query2 = "SELECT * FROM `yummy_users` WHERE id = ?";
        $stmt2 = $pdo->prepare($query2);
        $stmt2->execute([$user_id]);
        $results = $stmt2->fetch();
        $username = $results['username'];
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
      $PAGE_TITLE = "Reset Password";
      include 'includes/metadata.php';
    ?>  
  </head>
  <body>
  <?php include 'includes/nav.php';?>
    <main>
      <h1>Password Reset</h1>
      <form action="<?=htmlspecialchars($_SERVER['PHP_SELF'])?>" method="post" class="account-management" id="pass-reset-form" autocomplete="off">
        <div>
          <label for="new-password">New Password:</label>
          <input type="password" name="new-password" id="new-password" placeholder="New Password">
        </div>
        <div>
          <label for="confirm-password">Confirm Password:</label>
          <input type="password" name="confirm-password" id="confirm-password" placeholder="Confirm Password">
        </div>
        <div>
          <!-- ERROR MESSAGES -->
          <span id="p-empty-error" class="<?=!isset($errors['empty']) ? 'hidden' : "";?>">*Please enter a password.</span>
          <span id="p-match-error" class="<?=!isset($errors['match']) ? 'hidden' : "";?>">*Passwords do not match.</span>
          <span id="p-strength-error" class="<?=!isset($errors['strength']) ? 'hidden' : "";?>">*Password should be at least 8 characters in length 
          and should include at least one upper case letter, one lower case letter, one number, and one special character.</span>
        </div>
        <button class="form-buttons" type="submit" name="submit">Reset Password</button>
      </form>
    </main>
    <?php include 'includes/footer.php';?>
  </body>
</html>