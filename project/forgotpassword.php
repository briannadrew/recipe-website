<?php
  // forgotpassword.php

  // Name: Brianna Drew
  // ID: #0622446
  // Date: April 19th, 2021
  // Description: On this page, a user can enter their email to be sent a link to reset their password in case they have forgotten it.
  // There is also the option to navigate back to the login page.

  // include the library file
  require 'includes/library.php';
  // create the database connection
  $pdo = connectDB();

  $messages = array(); // array to hold possible error messages
  $email = $_POST['email'] ?? null;
  $email = filter_var($email, FILTER_SANITIZE_EMAIL);

  // get all user information from user table for the updated email
  // (checking if the email is already being used)
  $query = "SELECT * FROM `yummy_users` WHERE email = ?";
  $stmt = $pdo->prepare($query);
  $stmt->execute([$email]);
  $results = $stmt->fetch();

  // if the user updates their account...
  if(isset($_POST['submit'])) {
    if (!isset($email) or strlen($email) == 0) {
      $messages['empty'] = true; // if the email form field was empty upon submission, display appropriate error message
    }
    else {
      if(!$results) {
        $messages['user'] = true; // if email submitted does not belong to any current users, display appropriate error message
      }
      elseif(filter_var($email, FILTER_VALIDATE_EMAIL) === false) {
        $messages['email'] = true; // if email submitted is not a valid email address, display appropriate error message
      }
      else {
        // if there were no errors, build and send an email to the user's email containing a link to reset their password
        $id = $results['id']; // get the id of the user who uses the submitted email address
        require_once "Mail.php";  // this includes the pear SMTP mail library
        $from = "YummyShare Password System Reset <noreply@loki.trentu.ca>"; // sender's email (from the server)
        $to = $email;  // user's submitted email
        $subject = "YummyShare Password Reset"; // subject line of email
        ob_start();
        echo 'Reset your password here: https://loki.trentu.ca/~briannadrew/3420/project/passwordreset.php?id='.$id; // body of email containing link to reset password, with user's id passed as a url parameter
        $body = ob_get_contents(); // catch contents of email body echo
        ob_end_clean();
        $host = "smtp.trentu.ca"; // server host of email sending capabilities
        // define header of email
        $headers = array ('From' => $from,
          'To' => $to,
          'Subject' => $subject);
        // activate the library to send the email (?)
        $smtp = Mail::factory('smtp',
          array ('host' => $host));
        $mail = $smtp->send($to, $headers, $body); // send the email
        if (PEAR::isError($mail)) {
          $messages['failed'] = true; // if the email failed to send, display appropriate error message
        } else {
          $messages['sent'] = true; // if the email was sent successfully, display message telling user that it was and to check their email
        }
      }     
    }
  }
?>

<!DOCTYPE html>
<html lang="en">
  <head>
    <?php
      $PAGE_TITLE = "Password Retrieval";
      include 'includes/metadata.php';
    ?>  
  </head>
  <body>
    <?php include 'includes/nav.php';?>
    <main>
      <h1>Password Retrieval</h1>
      <form action="<?=htmlspecialchars($_SERVER['PHP_SELF'])?>" method="post" class="account-management" id="forgot-pass-form" autocomplete="off">
        <label for="f-email">Enter Email Associated With Your Account:</label>
        <div id="forgot-input">
          <input type="email" name="email" id="f-email" placeholder="Email">
          <button id="submit" name="submit"><i class="far fa-paper-plane"></i></button>
        </div>
        <div>
          <!-- ERROR MESSAGES -->
          <span id="f-empty-error" class="<?=!isset($messages['empty']) ? 'hidden' : "";?>">*Please enter the email address associated with your account.</span>
          <span id="f-user-error" class="<?=!isset($messages['user']) ? 'hidden' : "";?>">*This email is not associated with any existing accounts.</span>
          <span id="f-email-error" class="<?=!isset($messages['email']) ? 'hidden' : "";?>">*Please enter a valid email address.</span>
          <span class="<?=!isset($messages['failed']) ? 'hidden' : "";?>">*Email failed to send. Please ensure that you entered your email address correctly.</span>
          <span class="<?=!isset($messages['sent']) ? 'hidden' : "";?>">Password reset email sent successfully! Check your inbox.</span>
        </div>
      </form>
      <form action="login.php" method="post" class="wrong-place">
        <label for="back-login">Remembered Your Password?</label>
        <button type="submit" id="back-login" name="back-login">Log In</button>
      </form>
    </main>
    <?php include 'includes/footer.php';?>
  </body>
</html>