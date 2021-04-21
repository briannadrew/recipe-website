<?php
  // createrecipe.php

  // Name: Brianna Drew
  // ID: #0622446
  // Date: April 19th, 2021
  // Description: On this page, a user can create a new recipe, where all form fields must be filled out (except for the file upload,
  // which is optional). They must enter a title, image (optional), description, servings, minutes, ingredients, directions, category,
  // and privacy. All other fields in the table will be set by default.

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
  $username = $_SESSION['username']; // get username from session variables
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
      // if this request falls under any of them, treat it invalid
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
  
  // if the user creates a new recipe...
  if (isset($_POST['submit'])) {
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
      $filename = 'placeholder.png'; // if no image was uploaded, set the filename to display the default placeholder image
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
      // if there were no errors, submit data into recipe table as a new recipe and then redirect to a page displaying the user's recipes 
      $query = "INSERT INTO `yummy_recipes` (username, title, image, description, ingredients, directions, minutes, servings, category, private) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";
      $stmt = $pdo->prepare($query);
      $stmt->execute([$username, $title, $filename, $description, $ingredients, $directions, $minutes, $servings, $category, $privacy]);
      header("Location:searchresults.php?user=$username");
      exit();
    }
  }
?>

<!DOCTYPE html>
<html lang="en">
  <head>
    <?php
      $PAGE_TITLE = "New Recipe";
      include 'includes/metadata.php';
    ?>  
  </head>
  <body>
    <?php include 'includes/nav.php';?>  
    <main>
    <h1>New Recipe</h1>
    <form enctype="multipart/form-data" action="<?=htmlspecialchars($_SERVER['PHP_SELF'])?>" method="post" class="recipe-form" id="create-form">
      <div class="title-container">
        <label for="ctitle">Title:</label>
        <input type="text" name="title" id="ctitle" placeholder="Title">
      </div>
      <div>
        <span id="c-title-error" class="<?=!isset($errors['title']) ? 'hidden' : "";?>">*Please enter a title.</span>
      </div>
      <div class="image-container">
        <input type="hidden" name="MAX_FILE_SIZE" value="1000240">
        <label for="img">Image (Optional):</label>
        <input type="file" id="img" name="img" accept="image/*">
      </div>
      <div>
        <!-- IMAGE ERROR MESSAGES -->
        <span class="<?=!isset($errors['imgpmt']) ? 'hidden' : "";?>">*Invalid image parameters.</span>
        <span class="<?=!isset($errors['imgsize']) ? 'hidden' : "";?>">*Image exceeds file size limit.</span>
        <span class="<?=!isset($errors['imgfmt']) ? 'hidden' : "";?>">*Invalid image format.</span>
        <span class="<?=!isset($errors['mvfailure']) ? 'hidden' : "";?>">*Failed to upload image.</span>
        <span class="<?=!isset($errors['unknown']) ? 'hidden' : "";?>">*Unknown errors occurred.</span>
      </div>
      <div class="desc-container">
        <label for="cdescription">Description:</label>
        <textarea name="description" id="cdescription" placeholder="Description"></textarea>
      </div>
      <div>
        <span id="c-description-error" class="<?=!isset($errors['description']) ? 'hidden' : "";?>">*Please enter a description.</span>
      </div>
      <div class="servings-container">
        <label for="cservings">Servings:</label>
        <input type="number" name="servings" id="cservings" min="1" max="100">
      </div>
      <div>
        <span id="c-servings-error" class="<?=!isset($errors['servings']) ? 'hidden' : "";?>">*Please enter number of servings.</span>
      </div>
      <div class="minutes-container">
        <label for="cminutes">Minutes:</label>
        <input type="number" name="minutes" id="cminutes" min="0" max="5000">
      </div>
      <div>
        <span id="c-minutes-error" class="<?=!isset($errors['minutes']) ? 'hidden' : "";?>">*Please enter number of minutes.</span>
      </div>
      <div class="ingredients-container">
        <label for="cingredients">Ingredients:</label>
        <textarea name="ingredients" id="cingredients" placeholder="Ingredients"></textarea> 
      </div>
      <div>
        <span id="c-ingredients-error" class="<?=!isset($errors['ingredients']) ? 'hidden' : "";?>">*Please enter ingredients.</span>
      </div>
      <div class="directions-container">
        <label for="cdirections">Directions:</label>
        <textarea name="directions" id="cdirections" placeholder="Directions"></textarea>
      </div>
      <div>
        <span id="c-directions-error" class="<?=!isset($errors['directions']) ? 'hidden' : "";?>">*Please enter directions.</span>
      </div>
      <div class="category-container">
        <label for="categories">Category:</label>
        <select name="categories" id="categories" class="categories">
          <option value="breakfast">Breakfast</option>
          <option value="lunch">Lunch</option>
          <option value="dinner">Dinner</option>
          <option value="snacks">Snacks/Appetizers</option>
          <option value="desserts">Desserts</option>
          <option value="drinks">Drinks</option>
        </select>
      </div>
      <div class="privacy-container">
        <label for="privacy">Privacy:</label>
        <select name="privacy" id="privacy" class="privacy">
          <option value="0">Public</option>
          <option value="1">Private</option>
        </select>
      </div>
      <button id="submit" class="form-buttons" name="submit">Create</button>
    </form>
    </main>
    <?php include 'includes/footer.php';?>  
  </body>
</html>