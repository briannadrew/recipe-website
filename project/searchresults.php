<?php
  // searchresults.php

  // Name: Brianna Drew
  // ID: #0622446
  // Date: April 19th, 2021
  // Description: This is the search page of the website where all public recipes and recipes owned by the current user if they are
  // logged in will be displayed that contain a search term in either its title, category, description, username, tags, or ingredients
  // with a limit of 16 per page. The title, username, image, rating, and tags for the recipe are displayed for each, and clicking on
  // either the image or title will you to view the recipe in a pop-up modal window. If the user is not logged in, no icons will be
  // present on the recipe. If the user is logged in, they will have the option to duplicate any of the recipes, and the options to edit
  // or delete the recipes that they own. Page navigation is also located at the bottom of the page.

  session_start();
  // include the library file
  require 'includes/library.php';
  // create the database connection
  $pdo = connectDB();

  $searchfor = null; // variable to hold search term
  $all = false; // variable that when true will display all public recipes or recipes owned by the user if they are logged in
  $self = false; // variable that when true will display all recipes owned by the user if they are logged in
  $username = $_SESSION['username'] ?? null; // get username from session variables
  $type = null; // variable to hold type of search to perform

  // search via category
  if(isset($_GET['category'])) {
    $searchfor = $_GET['category'];
    $type = "category";
  }
  // search via search term
  elseif(isset($_GET['search'])) {
    $searchfor = $_GET['search'];
    $type = "search";
  }
  // search via username
  elseif(isset($_GET['user'])) {
    $searchfor = $_GET['user'];
    $type = "user";
    if($searchfor == $username) { // if the user searched for their own recipes
      $self = true;
    }
  }
  else {
    $all = true; // if no search parameters are provided, show all public recipes
  }

  $limit = 16; // a maximum of 16 recipes to be displayed on a single page

  if (isset($_GET['page'])) {
    $pn = $_GET['page']; // if a page number is provided, retrieve it
  }
  else {
    $pn = 1; // default page number is 1
  }

  $start_from = ($pn - 1) * $limit; // pagination offset

  if($self) {
    // get the id, username, title, image, rating, and tags for all recipes owned by the current user currently in the recipes table with a limit of 16 per page
    $query = "SELECT id,username,title,image,rating,tags FROM `yummy_recipes` WHERE username = '$username' LIMIT $start_from, $limit";
  }
  elseif(!$all) {
    // get the id, username, title, image, rating, and tags for all public recipes that contain the search term in its title, category, description, username, tags, or ingredients currently in the recipes table with a limit of 16 per page
    $query = "SELECT id,username,title,image,rating,tags FROM `yummy_recipes` WHERE title LIKE '%$searchfor%' OR category LIKE '%$searchfor%' OR description LIKE '%$searchfor%' OR username LIKE '%$searchfor%' OR tags LIKE '%$searchfor%' OR ingredients LIKE '%$searchfor%' AND private = 0 LIMIT $start_from, $limit";
  }
  else {
    // get the id, username, title, image, rating, and tags for all public recipes (or recipes owned by the current user if they are logged in) currently in the recipes table with a limit of 16 per page
    $query = "SELECT id,username,title,image,rating,tags FROM `yummy_recipes` WHERE private = 0 OR username = '$username' LIMIT $start_from, $limit";
  }

  $stmt = $pdo->query($query);

  // if the user wants to duplicate a recipe...
  if(isset($_GET['save-id'])) {
    $get_id = $_GET['save-id']; // get the id of the recipe to be duplicated
    // retrieve the details of the recipe to be duplicated from the recipes table
    $dup_query = "SELECT * FROM `yummy_recipes` WHERE id = $get_id";
    $dup_stmt = $pdo->query($dup_query);
    $dup_res = $dup_stmt->fetch();

    // get details of recipe to be duplicated from query results
    $dup_title = $dup_res['title'];
    $dup_img_name = $dup_res['image'];
    $dup_desc = $dup_res['description'];
    $dup_ingredients = $dup_res['ingredients'];
    $dup_directions = $dup_res['directions'];
    $dup_time = $dup_res['minutes'];
    $dup_servings = $dup_res['servings'];
    $dup_rating = $dup_res['rating'];
    $dup_category = $dup_res['category'];

    // insert details of duplicated recipe into recipes table as a new recipe, automatically set to private, then retrieve the id of the duplicate recipe
    $new_query = "INSERT INTO `yummy_recipes` (`username`, `title`, `image`, `description`, `ingredients`, `directions`, `minutes`, `servings`, `rating`, `category`, `private`) VALUES (?,?,?,?,?,?,?,?,?,?,?)";
    $new_stmt = $pdo->prepare($new_query);
    $new_stmt->execute([$username, $dup_title, $dup_img_name, $dup_desc, $dup_ingredients, $dup_directions, $dup_time, $dup_servings, $dup_rating, $dup_category, 1]);
    $new_id = $pdo->lastInsertId();

    // redirect to the page where the user can edit the newly duplicated recipe
    $url = "edit-recipe=".$new_id;
    header("Location:editrecipe.php?$url");
    exit();
  }

  // if the user wants to delete a recipe...
  if(isset($_GET['delete-id'])) {
    $del_id = $_GET['delete-id']; // get the id of the recipe to be deleted
    // delete the specified recipe from the recipes table and reload the page.
    $del_query = "DELETE FROM `yummy_recipes` WHERE id = ?";
    $del_stmt = $pdo->prepare($del_query);
    $del_stmt->execute([$del_id]);
    header("Location:searchresults.php");
    exit();
  }
?>

<!DOCTYPE html>
<html lang="en">
  <head>
    <?php
      $PAGE_TITLE = "Search Results: ".$searchfor;
      include 'includes/metadata.php';
    ?>  
  </head>
  <body>
    <?php include 'includes/nav.php';?>  
    <main>
    <h1>Search Results: <?php echo $searchfor ?></h1>
      <div class="result-grid">
        <!-- RECIPE DISPLAY -->
        <?php foreach($stmt as $row): ?>
          <?php
            // get details of each recipe to be displayed
            $id = $row['id'];
            $title = $row['title'];
            $user_name = $row['username'];
            $img_name = $row['image'];
            $rating = $row['rating'];
            $tags = $row['tags'];
          ?>
          <div class="s-recipe-results">
            <h2 id="<?php echo $id ?>"><?php echo $title ?></h2>
            <div class="username-container">
              <span><?php echo 'By '.$user_name ?></span>
            </div>
            <div class="rating-container">
              <span><?php echo 'Rating: '.round($rating, 2).' ' ?><jsuites-rating id="<?php echo $id ?>" value="<?php echo $rating ?>"></jsuites-rating></span>
            </div>
            <img id="<?php echo $id ?>" src="<?php echo '/~briannadrew/www_data/recipe_imgs/'.$img_name ?>" alt="">
            <jsuites-tags id="<?php echo $id ?>" value="<?php echo $tags ?>"></jsuites-tags>
            <div class="icon-container">
            <?php
            // if the user is logged in, they will be able to duplicate any recipe
              if(isset($username)) {
                echo '<form action="'.htmlspecialchars($_SERVER['PHP_SELF']).'" method="get">
                <button type="submit" id="save-id" name="save-id" value="'.$id.'"><i class="far fa-copy"></i></button>
                </form>';
              }
              // the user will be able to edit or delete any recipe that belongs to them
              if($user_name == $username) {
                echo '<form action="editrecipe.php" method="get">
                <button type="submit" id="edit-recipe" name="edit-recipe" value="'.$id.'"><i class="far fa-edit"></i></button>
                </form> 
                <form action="'.htmlspecialchars($_SERVER['PHP_SELF']).'" method="get" class="delete-form">
                <button type="submit" id="delete-id" name="delete-id" value="'.$id.'"><i class="far fa-trash-alt"></i></button>
                </form>';
              }
              echo '</div>';
            ?>
          </div>
        <?php endforeach ?>
      </div>
      <div>
        <ul class="pagination">
          <?php
          $query2 = null;
          // get the number of recipes to be displayed from the recipes table based on the search
          if($self) {
            // get number of recipes owned by current user
            $query2 = "SELECT COUNT(*) FROM `yummy_recipes` WHERE username = '$username'";
          }
          elseif(!$all) {
            // get number of recipes that contain the provided search term
            $query2 = "SELECT COUNT(*) FROM `yummy_recipes` WHERE title LIKE '%$searchfor%' OR category LIKE '%$searchfor%' OR description LIKE '%$searchfor%' OR username LIKE '%$searchfor%' OR tags LIKE '%$searchfor%' OR ingredients LIKE '%$searchfor%' AND private = 0";
          }
          else {
            // get number of all public recipes and ones owned by the current user
            $query2 = "SELECT COUNT(*) FROM `yummy_recipes` WHERE private = 0 OR username = '$username'";
          }
          $stmt2 = $pdo->query($query2);
          $count = $stmt2->fetchColumn();
          $pages = ceil($count / $limit); // calculate the number of pages to be generated based on the limit of recipes per page
          ?>
          <!-- PAGINATION LINKS -->
          <li><a href="<?php if($type == null){echo "?page=1";} else {echo "?".$type."=".$searchfor."&page=1";}?>"><i class="fas fa-angle-double-left"></i></a></li>
          <li class="<?php if($pn <= 1){echo 'hidden';}?>">
            <a href="<?php if($pn <= 1){echo '#';} else {if($type == null){echo "?page=".($pn - 1);} else {echo "?".$type."=".$searchfor."&page=".($pn - 1);}}?>"><i class="fas fa-angle-left"></i></a>
          </li>
          <li class="<?php if($pn >= $pages){echo 'hidden';}?>">
            <a href="<?php if($pn >= $pages){echo '#';} else {if($type == null){echo "?page=".($pn + 1);} else {echo "?".$type."=".$searchfor."&page=".($pn + 1);}}?>"><i class="fas fa-angle-right"></i></a>
          </li>
          <li><a href="<?php if($type == null){echo "?page=".$pages;} else {echo "?".$type."=".$searchfor."&page=".$pages;}?>"><i class="fas fa-angle-double-right"></i></a></li>
        </ul>
      </div>
      <div class="modal" id="search-modal">
      </div>
    </main>
    <?php include 'includes/footer.php';?>  
  </body>
</html>