<?php
  // userexists.php

  // Name: Brianna Drew
  // ID: #0622446
  // Date: April 19th, 2021
  // Description: This is a page to be accessed via XMLHttpRequest from script.js to check whether the username the user enters when
  // creating a new account is already taken (A.K.A. it exists in the users table) when the username form field loses focus. It will
  // respond with true if it does already exist, and false if it does not.

  // include the library file
  require 'includes/library.php';
  // create the database connection
  $pdo = connectDB();

  // get and sanitize the username entered
  $username = $_GET['username'] ?? null;
  $username = filter_var($username, FILTER_SANITIZE_STRING);

  // get all user information from user table for the provided username
  // (checking if the user already exists)
  $query = "SELECT * FROM `yummy_users` WHERE username = ?";
  $stmt = $pdo->prepare($query);
  $stmt->execute([$username]);
  $results = $stmt->fetch();

  if ($results) {
    echo 'true'; // username already taken
  } else {
    echo 'false'; // username not yet taken
  }

  exit();
?>