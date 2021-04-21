<?php
  // tagupdate.php

  // Name: Brianna Drew
  // ID: #0622446
  // Date: April 19th, 2021
  // Description: This is a page to be accessed via XMLHttpRequest from script.js to update the tags for a given recipe when a user
  // edits the tags (by adding or deleting tags and losing focus). 

  // include the library file
  require 'includes/library.php';
  // create the database connection
  $pdo = connectDB();

  // get the id of the recipe and get and sanitize the current tags
  $id = $_GET['id'] ?? null;
  $tags = $_GET['tags'] ?? null;
  $tags = filter_var($tags, FILTER_SANITIZE_STRING);

  // update the old tags to the new tags for the specified recipe in the recipes table
  $query = "UPDATE `yummy_recipes` SET tags = ? WHERE id = ?";
  $stmt = $pdo->prepare($query);
  $stmt->execute([$tags, $id]);

  exit();
?>