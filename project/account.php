<?php
  // account.php

  // Name: Brianna Drew
  // ID: #0622446
  // Date: April 19th, 2021
  // Description: On this page, a user can edit their account credentials if they so desire or they can delete their account.
  // They can change either their username, email, password, or all three. They will receive a confirmation pop-up before they
  // proceed with deleting their account to confirm that is what they want to do.

  session_start();

  // user should be redirected to login page if they are not logged in
  if(!isset($_SESSION['username'])){
    header("Location:login.php");
    exit();
  }
  
  // include the library file
  require 'includes/library.php';
  // create the database connection
  $pdo = connectDB();

  $errors = array(); // array to hold possible errors
  $username = $_SESSION['username'] ?? null; // get username from session variables

  // get all user information from user table for the current user
  $query = "SELECT * FROM `yummy_users` WHERE username = ?";
  $stmt = $pdo->prepare($query);
  $stmt->execute([$username]);
  $results = $stmt->fetch();

  // store credentials retreived from user table
  $id = $results['id'];
  $email = $results['email'];
  $password = $results['password'];

  // if the user updates their account...
  if(isset($_POST['submit'])) {
    // get and sanitize all data from submitted form
    $new_user = $_POST['username'] ?? null;
    $new_user = filter_var($new_user, FILTER_SANITIZE_STRING);
    $new_email = $_POST['email'] ?? null;
    $new_email = filter_var($new_email, FILTER_SANITIZE_EMAIL);
    $new_pass = $_POST['password'] ?? null;
    $repeat_pass = $_POST['confirm-password'] ?? null;

    // get all user information from user table for the updated username
    // (checking if the user already exists)
    $uquery = "SELECT * FROM `yummy_users` WHERE username = ?";
    $ustmt = $pdo->prepare($uquery);
    $ustmt->execute([$new_user]);
    $uresults = $ustmt->fetch();

    // get all user information from user table for the updated email
    // (checking if the email is already being used)
    $equery = "SELECT * FROM `yummy_users` WHERE email = ?";
    $estmt = $pdo->prepare($equery);
    $estmt->execute([$new_email]);
    $eresults = $estmt->fetch();

    $uppercase = preg_match('@[A-Z]@', $new_pass); // check if submitted password contains an uppercase letter
    $lowercase = preg_match('@[a-z]@', $new_pass); // check if submitted password contains a lowercase letter
    $number = preg_match('@[0-9]@', $new_pass); // check if submitted password contains a number
    $special_chars = preg_match('@[^\w]@', $new_pass); // check if submitted password contains a special character

    if(!isset($new_user) or strlen($new_user) == 0 or !isset($new_email) or strlen($new_email) == 0 or !isset($new_pass) or strlen($new_pass) == 0 or !isset($repeat_pass) or strlen($repeat_pass) == 0) {
      $errors['empty'] = true; // if any of the form fields were empty upon submission, display appropriate error message
    }
    else {
      if($uresults) {
        $errors['user'] = true; // if user already exists, display appropriate error message
      }
      elseif(filter_var($new_email, FILTER_VALIDATE_EMAIL) === false) {
        $errors['email'] = true; // if email submitted is not a valid email address, display appropriate error message
      }
      elseif($eresults) {
        $errors['ematch'] = true; // if email submitted is already being used, display appropriate error message
      }
      elseif($new_pass !== $repeat_pass) {
        $errors['match'] = true; // if the submitted passwords do not match each other, display appropriate error message
      }
      elseif(!$uppercase or !$lowercase or !$number or !$special_chars or strlen($new_pass) < 8) {
        $errors['strength'] = true; // if the password is less than 8 characters and does not contain a uppercase letter, lowercase letter, number, or special character, display appropriate error message
      }
      else {
        // if user did actually change their password, hash it
        if($new_pass != $password) {
          $new_pass = password_hash($new_pass, PASSWORD_DEFAULT);
        }
        // if there were no errors, update user information in the user table, log back in, and redirect to home page
        $upquery = "UPDATE `yummy_users` SET username = ?, email = ?, password = ? WHERE username = ?";
        $upstmt = $pdo->prepare($upquery);
        $upstmt->execute([$new_user, $new_email, $new_pass, $username]);
        $_SESSION['username'] = $new_user;
        header("Location:main.php");
        exit();
      }
    }
  }

  // if the user chooses to delete their account...
  if(isset($_GET['delete-id'])) {
    $_SESSION['username'] = null; // log user out
    $del_id = $_GET['delete-id']; // get id of user to be deleted
    // delete user from the user table
    $del_query = "DELETE FROM `yummy_users` WHERE id = ?";
    $del_stmt = $pdo->prepare($del_query);
    $del_stmt->execute([$del_id]);
    header("Location:login.php"); // redirect to login page
    exit();
  }
?>

<!DOCTYPE html>
<html lang="en">
  <head>
    <?php
      $PAGE_TITLE = "My Account";
      include 'includes/metadata.php';
    ?>  
  </head>
  <body>
    <?php include 'includes/nav.php';?>  
    <main>
      <h1>My Account</h1>
      <form action="<?=htmlspecialchars($_SERVER['PHP_SELF'])?>" method="post" class="account-management" id="account-form" autocomplete="off">
        <div>
          <label for="a-username">Username:</label>
          <input type="text" name="username" id="a-username" value="<?php echo $username ?>" placeholder="Username">
        </div>
        <div>
          <label for="a-email">Email:</label>
          <input type="email" name="email" id="a-email" value="<?php echo $email ?>" placeholder="Email">
        </div>
        <div>
          <label for="a-password">Password:</label>
          <input type="password" name="password" id="a-password" value="<?php echo $password ?>" placeholder="Password">
        </div>
        <div>
          <label for="a-confirm-password">Confirm Password:</label>
          <input type="password" name="confirm-password" id="a-confirm-password" value="<?php echo $password ?>" placeholder="Confirm Password">
        </div>
        <div>
          <!-- ERROR MESSAGES -->
          <span id="a-empty-error" class="<?=!isset($errors['empty']) ? 'hidden' : "";?>">*Please enter an email, username, and password.</span>
          <span id="a-email-error" class="<?=!isset($errors['email']) ? 'hidden' : "";?>">*Please enter a valid email address.</span>
          <span id="a-user-error" class="<?=!isset($errors['user']) ? 'hidden' : "";?>">*User already exists.</span>
          <span id="a-ematch-error" class="<?=!isset($errors['ematch']) ? 'hidden' : "";?>">*Email already being used.</span>
          <span id="a-strength-error" class="<?=!isset($errors['strength']) ? 'hidden' : "";?>">*Password should be at least 8 characters in length 
          and should include at least one upper case letter, one number, and one special character.</span>
          <span id="a-match-error" class="<?=!isset($errors['match']) ? 'hidden' : "";?>">*Passwords do not match.</span>
        </div>
        <button type="submit" class="form-buttons" id="submit" name="submit">Save Changes</button>
      </form>
      <form action="<?=htmlspecialchars($_SERVER['PHP_SELF'])?>" method="get">
        <button type="button" class="form-buttons" id="delete-account" name="delete-id" value="<?php echo $id ?>">Delete Account</button>
      </form>
    </main>
    <?php include 'includes/footer.php';?>  
  </body>
</html>