<?php
  // viewrecipe.php

  // Name: Brianna Drew
  // ID: #0622446
  // Date: April 19th, 2021
  // Description: This is a page to be accessed via XMLHttpRequest from script.js to get the full details of a selected recipe to view
  // when a user clicks on a recipe. It will return these details already formatted in HTML to be inserted into the pop-up modal of
  // either main.php or searchresults.php

  // include the library file
  require 'includes/library.php';
  // create the database connection
  $pdo = connectDB();

  $recipe_id = $_GET['recipe']; // get the id of the selected recipe

  // retrieve the details of the selected recipe from the recipes table
  $query = "SELECT * FROM `yummy_recipes` WHERE id = ?";
  $stmt = $pdo->prepare($query);
  $stmt->execute([$recipe_id]);
  $results = $stmt->fetch();

  // get details of selected recipe from query results
  $title = $results['title'];
  $date = $results['date'];
  $username = $results['username'];
  $rating = $results['rating'];
  $image = $results['image'];
  $servings = $results['servings'];
  $cooktime = $results['minutes'];
  $category = $results['category'];
  $tags = $results['tags'];
  // *nl2br to ensure newlines are displayed properly*
  $description = nl2br($results['description']);
  $ingredients = nl2br($results['ingredients']);
  $directions = nl2br($results['directions']);

  // return modal content to script.js to be inserted into either main.php or searchresults.php displaying full recipe details
  echo '<div class="modal-content">
          <div class="modal-header">
            <span class="close">&times;</span>
            <h1>'.$title.'</h1>
          </div>
          <div class="modal-body" id="view-recipe">
            <div id="recipe-username">
              <span>By: '.$username.'</span>
            </div>
            <div id="view-date">
              <span>'.$date.'</span>
            </div>
            <div id="view-image">
              <img src="/~briannadrew/www_data/recipe_imgs/'.$image.'" alt="">
            </div>
            <div id="view-rating">
              <span>Rating: '.round($rating, 2).'<jsuites-rating id="'.$recipe_id.'" value="'.$rating.'"></jsuites-rating></span>
            </div>
            <div id="view-description">
              <p>'.$description.'</p>
            </div>
            <div id="view-servings">
              <span>Servings: '.$servings.'</span>
            </div>
            <div id="view-time">
              <span>Cook Time: '.$cooktime.' minutes</span>
            </div>
            <div id="view-ingredients">
              <h2>Ingredients</h2>
              <p>'.$ingredients.'</p>
            </div>
            <div id="view-directions">
              <h2>Directions</h2>
              <p>'.$directions.'</p>
            </div>
            <div id="view-tags">
              <jsuites-tags id="'.$recipe_id.'" value="'.$tags.'"></jsuites-tags>
            </div>
          </div>
        </div>';

  exit();
?>