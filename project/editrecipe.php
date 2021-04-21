<?php
  // editrecipe.php

  // Name: Brianna Drew
  // ID: #0622446
  // Date: April 19th, 2021
  // Description: On this page, a user can edit the information for one of their recipes, where all form fields must be filled out
  // (except for the file upload, which is optional). They must enter a title, image (optional), description, servings, minutes,
  // ingredients, directions, category, and privacy. All other fields in the table will be set by default. All form fields will be
  // prepopulated with the previous data (except for file upload as that is not possible).

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

  $recipe_id = $_GET['edit-recipe'] ?? null; // get the id of the recipe that's being edited
  // get all the recipe data for the specified recipe from recipe table
  $query = "SELECT * FROM `yummy_recipes` WHERE id = ?";
  $stmt = $pdo->prepare($query);
  $stmt->execute([$recipe_id]);
  $results = $stmt->fetch();

  $errors = array(); // array to hold possible errors

  $username = $_SESSION['username']; // get username from session variables
  $recipe_user = $_POST['recipe-user'] ?? null; // get the username of the owner of the specified recipe

  // if the user does not own this recipe, redirect back to the login page
  if ($username != $results['username'] and $username != $recipe_user) {
    header("Location:login.php");
    exit();
  }

  // get and sanitize all data from submitted form
  $title = $_POST['title'] ?? null;
  $title = filter_var($title, FILTER_SANITIZE_STRING);
  $description = $_POST['description'] ?? null;
  $description = filter_var($description, FILTER_SANITIZE_STRING);
  $servings = $_POST['servings'] ?? null;
  $servings = filter_var($servings, FILTER_SANITIZE_NUMBER_INT);
  $minutes = $_POST['minutes'] ?? null;
  $minutes = filter_var($minutes, FILTER_SANITIZE_NUMBER_INT);
  $ingredients = $_POST['ingredients'] ?? null;
  $ingredients = filter_var($ingredients, FILTER_SANITIZE_STRING);
  $directions = $_POST['directions'] ?? null;
  $directions = filter_var($directions, FILTER_SANITIZE_STRING);
  $category = $_POST['categories'] ?? null;
  $category = filter_var($category, FILTER_SANITIZE_STRING);
  $privacy = $_POST['privacy'] ?? null;
  $privacy = filter_var($privacy, FILTER_SANITIZE_NUMBER_INT);

  // function to validate file upload and then move it to the appropriate folder on server
  function checkAndMoveFile($filekey, $sizelimit, $newname){
    // modified from http://www.php.net/manual/en/features.file-upload.php
      // Undefined | Multiple Files | $_FILES Corruption Attack
      // if this request falls under any of them, treat it invalid.
      if(!isset($_FILES[$filekey]['error']) || is_array($_FILES[$filekey]['error'])) {
        $errors['imgpmt'] = true;
      }

      // check error value of file
      switch ($_FILES[$filekey]['error']) {
        case UPLOAD_ERR_OK:
          break;
        case UPLOAD_ERR_INI_SIZE:
        case UPLOAD_ERR_FORM_SIZE:
          $errors['imgsize'] = true;
        default:
          $errors['unknown'] = true;
      }

      if ($_FILES[$filekey]['size'] > $sizelimit) {
        $errors['imgsize'] = true; // if the file size is too large, display appropriate error message
      }

      if (exif_imagetype( $_FILES[$filekey]['tmp_name']) != IMAGETYPE_GIF
      and exif_imagetype( $_FILES[$filekey]['tmp_name']) != IMAGETYPE_JPEG
      and exif_imagetype( $_FILES[$filekey]['tmp_name']) != IMAGETYPE_PNG){
        $errors['imgfmt'] = true; // if the file is not an image, display appropriate error message
      }

      $upload_res = move_uploaded_file($_FILES[$filekey]['tmp_name'], $newname); // move the file to the specified location
      if(!$upload_res) {
        $errors['mvfailure'] = true; // if the move failed, display appropriate error message
      }
  }

  // if the user edits the recipe...
  if (isset($_POST['submit'])) {
    $recipeid = $_POST['recipe-id'] ?? null; // get the id of the recipe

    if(!isset($title) || strlen($title) === 0) {
      $errors['title'] = true; // if the title form field was empty upon submission, display appropriate error message
    }

    // if the user uploaded a file...
    if(is_uploaded_file($_FILES['img']['tmp_name'])){
      $uniqueID =  uniqid('IMG', true); // generate a unique id
      $path = WEBROOT.'/www_data/recipe_imgs/'; // location file should go
      $filename = $_FILES['img']['name']; // get the original file name for extension
      $fileroot = substr($filename, 0, strrpos($filename, ".")); // base file name
      $exts = explode(".", $filename); // split based on period
      $ext = $exts[count($exts)-1]; // take the last split (contents after last period)
      $filename = $fileroot.$uniqueID.".".$ext; // build new filename
      $newname = $path.$filename; // add path the file name

      checkAndMoveFile('img', 1000240, $newname); // send to function to validate image and move to proper location
    }
    else {
      $filename = $_POST['recipe-img']; // if no image was uploaded, set the filename to display the default placeholder image
    }

    if(!isset($description) || strlen($description) === 0) {
      $errors['description'] = true; // if the description form field was empty upon submission, display appropriate error message
    }

    if(!isset($servings) || empty($servings)) {
      $errors['servings'] = true; // if the servings form field was empty upon submission, display appropriate error message
    }

    if(!isset($minutes) || empty($minutes)) {
      $errors['minutes'] = true; // if the minutes form field was empty upon submission, display appropriate error message
    }

    if(!isset($ingredients) || strlen($ingredients) === 0) {
      $errors['ingredients'] = true; // if the ingredients form field was empty upon submission, display appropriate error message
    }

    if(!isset($directions) || strlen($directions) === 0) {
      $errors['directions'] = true; // if the directions form field was empty upon submission, display appropriate error message
    }

    if (count($errors) === 0) {
      // if there were no errors, update the recipe data in recipe table for the recipe and then redirect to a page displaying the user's recipes 
      $query = "UPDATE `yummy_recipes` SET username = ?, date = current_timestamp(), title = ?, image = ?, description = ?, ingredients = ?, directions = ?, minutes = ?, servings = ?, category = ?, private = ? WHERE id = ?";
      $stmt = $pdo->prepare($query);
      $stmt->execute([$username, $title, $filename, $description, $ingredients, $directions, $minutes, $servings, $category, $privacy, $recipeid]);
      header("Location:searchresults.php?user=$username");
      exit();
    }
  }
?>

<!DOCTYPE html>
<html lang="en">
  <head>
    <?php
      $PAGE_TITLE = "Edit Recipe";
      include 'includes/metadata.php';
    ?>  
  </head>
  <body>
    <?php include 'includes/nav.php';?>  
    <main>
    <?php
        // get recipe details to prepopulate the form fields
        $ppusername = $results['username'];
        $pptitle = $results['title'];
        $ppimage = $results['image'];
        $ppservings = $results['servings'];
        $ppcooktime = $results['minutes'];
        $ppdescription = $results['description'];
        $ppingredients = $results['ingredients'];
        $ppdirections = $results['directions'];
        $ppcategory = $results['category'];
        $ppprivacy = $results['private'];
        $tags = $results['tags'];
      ?>
    <h1>Edit Recipe</h1>
    <form enctype="multipart/form-data" action="<?=htmlspecialchars($_SERVER['PHP_SELF'])?>" method="post" class="recipe-form" id="edit-form">
      <div class="title-container">
        <label for="etitle">Title:</label>
        <input type="text" name="title" id="etitle" value="<?php echo $pptitle ?>" placeholder="Title">
      </div>
      <div>
        <span id="e-title-error" class="<?=!isset($errors['title']) ? 'hidden' : "";?>">*Please enter a title.</span>
      </div>
      <div class="image-container">
        <input type="hidden" name="MAX_FILE_SIZE" value="1000240">
        <label for="eimg">Image (Optional):</label>
        <input type="file" id="eimg" name="img" accept="image/*">
      </div>
      <div>
        <span class="<?=!isset($errors['imgpmt']) ? 'hidden' : "";?>">*Invalid image parameters.</span>
        <span class="<?=!isset($errors['imgsize']) ? 'hidden' : "";?>">*Image exceeds file size limit.</span>
        <span class="<?=!isset($errors['imgfmt']) ? 'hidden' : "";?>">*Invalid image format.</span>
        <span class="<?=!isset($errors['mvfailure']) ? 'hidden' : "";?>">*Failed to upload image.</span>
        <span class="<?=!isset($errors['unknown']) ? 'hidden' : "";?>">*Unknown errors occurred.</span>
      </div>
      <div class="desc-container">
        <label for="edescription">Description:</label>
        <textarea name="description" id="edescription" placeholder="Description"><?php echo $ppdescription ?></textarea>
      </div>
      <div>
        <span id="e-description-error" class="<?=!isset($errors['description']) ? 'hidden' : "";?>">*Please enter a description.</span>
      </div>
      <div class="servings-container">
        <label for="eservings">Servings:</label>
        <input type="number" name="servings" id="eservings" min="1" max="100" value="<?php echo $ppservings ?>">
      </div>
      <div>
        <span id="e-servings-error" class="<?=!isset($errors['servings']) ? 'hidden' : "";?>">*Please enter number of servings.</span>
      </div>
      <div class="minutes-container">
        <label for="eminutes">Minutes:</label>
        <input type="number" name="minutes" id="eminutes" min="0" max="5000" value="<?php echo $ppcooktime ?>">
      </div>
      <div>
        <span id="e-minutes-error" class="<?=!isset($errors['minutes']) ? 'hidden' : "";?>">*Please enter number of minutes.</span>
      </div>
      <div class="ingredients-container">
        <label for="eingredients">Ingredients:</label>
        <textarea name="ingredients" id="eingredients" placeholder="Ingredients"><?php echo $ppingredients ?></textarea> 
      </div>
      <div>
        <span id="e-ingredients-error" class="<?=!isset($errors['ingredients']) ? 'hidden' : "";?>">*Please enter ingredients.</span>
      </div>
      <div class="directions-container">
        <label for="edirections">Directions:</label>
        <textarea name="directions" id="edirections" placeholder="Directions"><?php echo $ppdirections ?></textarea>
      </div>
      <div>
        <span id="e-directions-error" class="<?=!isset($errors['directions']) ? 'hidden' : "";?>">*Please enter directions.</span>
      </div>
      <div class="category-container">
        <label for="categories">Category:</label>
        <select name="categories" id="categories" class="categories">
          <option value="breakfast" <?php if($ppcategory == 'breakfast'){echo 'selected="selected"';} ?>>Breakfast</option>
          <option value="lunch" <?php if($ppcategory == 'lunch'){echo 'selected="selected"';} ?>>Lunch</option>
          <option value="dinner" <?php if($ppcategory == 'dinner'){echo 'selected="selected"';} ?>>Dinner</option>
          <option value="snacks" <?php if($ppcategory == 'snacks'){echo 'selected="selected"';} ?>>Snacks/Appetizers</option>
          <option value="desserts" <?php if($ppcategory == 'desserts'){echo 'selected="selected"';} ?>>Desserts</option>
          <option value="drinks" <?php if($ppcategory == 'drinks'){echo 'selected="selected"';} ?>>Drinks</option>
        </select>
      </div>
      <div class="privacy-container">
        <label for="privacy">Privacy:</label>
        <select name="privacy" id="privacy" class="privacy">
          <option value="0" <?php if($ppprivacy == 0){echo 'selected="selected"';} ?>>Public</option>
          <option value="1" <?php if($ppprivacy == 1){echo 'selected="selected"';} ?>>Private</option>
        </select>
      </div>
      <div id="tags-container">
        <jsuites-tags id="<?php echo $recipe_id ?>" value="<?php echo $tags ?>"></jsuites-tags>
      </div>
      <input type="hidden" name="recipe-img" id="recipe-img" value="<?php echo $results['image'] ?>" />
      <input type="hidden" name="recipe-user" id="recipe-user" value="<?php echo $ppusername ?>" />
      <input type="hidden" name="recipe-id" id="recipe-id" value="<?php echo $recipe_id ?>" />
      <button id="submit" class="form-buttons" name="submit">Save Changes</button>
    </form>
    </main>
    <?php include 'includes/footer.php';?>  
  </body>
</html>