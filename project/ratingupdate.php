<?php
  // ratingupdate.php

  // Name: Brianna Drew
  // ID: #0622446
  // Date: April 19th, 2021
  // Description: This is a page to be accessed via XMLHttpRequest from script.js to update the average rating and number of ratings for
  // a given recipe when a user rates it (by clicking on a star). 

  // include the library file
  require 'includes/library.php';
  // create the database connection
  $pdo = connectDB();

  // get the id and selected rating of the specified recipe
  $id = $_GET['id'] ?? null;
  $rating = $_GET['rating'] ?? null;

  // retrieve the rating and number of ratings for the specified recipe from the recipes table
  $query = "SELECT rating,ratecount FROM `yummy_recipes` WHERE id = ?";
  $stmt = $pdo->prepare($query);
  $stmt->execute([$id]);
  $results = $stmt->fetch();

  $old_count = $results['ratecount']; // get the number of ratings for the specified recipe from the query results
  $new_count = (int)$old_count + 1; // increase the rating count by 1

  // update the number of ratings (increased by 1) for the specified recipe in the recipes table
  $query2 = "UPDATE `yummy_recipes` SET ratecount = ? WHERE id = ?";
  $stmt2 = $pdo->prepare($query2);
  $stmt2->execute([$new_count, $id]);

  $rate_count = $new_count;
  $old_rating = $results['rating']; // get the old average rating for the specified recipe from the query results
  $new_rating = ((float)$old_rating + (float)$rating) / (float)$rate_count; // calculate the new average rating

  // update the average rating for the specified recipe in the recipes table
  $query3 = "UPDATE `yummy_recipes` SET rating = ? WHERE id = ?";
  $stmt3 = $pdo->prepare($query3);
  $stmt3->execute([$new_rating, $id]);

  exit();
?>