<?php
  // nav.php

  // Name: Brianna Drew
  // ID: #0622446
  // Date: April 19th, 2021
  // Description: This page contains the header/navigation to be displayed at the top of all main pages. It contains a dropdown menu
  // on the left that when hovered over contains options to search recipes by category or show all recipes. In the middle we have the
  // main website title which doubles as a link to the home page. Next to it we have the search bar where a user can type and search
  // term and upon entering will display recipes that contain that search term. To the right, ther is another dropdown menu that
  // contains links related to account. If the user is not logged in, it will just have the option to go to the page to create an
  // account. If the user is logged in, it will contain the option to go to the page where they can edit there account credentials,
  // an option to review recipes that belong to them, and an option to sign out of their account.

  // if the user wants to logout
  if(isset($_POST['logout'])) {
    // logout user and redirect them to the login page
    $_SESSION['username'] = null;
    header("Location:login.php");
    exit();
  }
?>

<header>
  <nav>
    <div id="dropdown-menu">
      <!-- CATEGORY DROPDOWN MENU containing options to show recipes in each category, or all recipes -->
      <button id="dropdown-menu-button"><i class="fas fa-bars fa-2x"></i></button>
      <div id="dropdown-menu-links">
        <ul>
          <li>
            <form action="searchresults.php" method="get" class="dropdown-items">
              <button type="submit" id="breakfast" name="category" value="breakfast">Breakfast</button>
            </form>  
          </li>
          <li>
            <form action="searchresults.php" method="get" class="dropdown-items">
              <button type="submit" id="lunch" name="category" value="lunch">Lunch</button>
            </form>  
          </li>
          <li>
            <form action="searchresults.php" method="get" class="dropdown-items">
              <button type="submit" id="dinner" name="category" value="dinner">Dinner</button>
            </form>  
          </li>
          <li>
            <form action="searchresults.php" method="get" class="dropdown-items">
              <button type="submit" id="snacks" name="category" value="snack">Snacks & Appetizers</button>
            </form>  
          </li>
          <li>
            <form action="searchresults.php" method="get" class="dropdown-items">
              <button type="submit" id="desserts" name="category" value="dessert">Desserts</button>
            </form>  
          </li>
          <li>
            <form action="searchresults.php" method="get" class="dropdown-items">
              <button type="submit" id="drinks" name="category" value="drink">Drinks</button>
            </form>  
          </li>
          <li>
            <form action="searchresults.php" method="get" class="dropdown-items">
              <button type="submit" id="view-all" name="all" value="true">View All</button>
            </form>
          </li>
        </ul>
      </div>
    </div>
    <h1 id="main-title"><a href="main.php">YummyShare <i class="fas fa-pizza-slice"></i></a></h1>
    <div>
      <!-- SEARCH BAR -->
      <form id="search-form" action="searchresults.php" method="get">
        <input type="text" id="search-box" name="search" />
        <button type="submit"><i class="fas fa-search fa-lg"></i></button>
      </form>  
    </div>
    <div id="nav-right">
      <a href="createrecipe.php" id="create-button"><i class="fas fa-plus fa-2x"></i></a>
      <!-- ACCOUNT MANAGEMENT DROPDOWN MENU -->
      <div id="account-dropdown">
        <button id="account-dropdown-button"><i class="far fa-user-circle fa-2x"></i></button>
        <div id="account-dropdown-links">
          <ul>
            <li>
              <?php
                // if the user is logged in, display option to go to page to edit their account
                if(isset($_SESSION['username'])){
                  echo '<form action="account.php" method="post" class="dropdown-items">
                  <button type="submit" id="account" name="account">My Account</button>
                 </form>';
                }
                // if the user is not logged in, display option to redirect to page to create a new account
                else {
                  echo '<form action="newaccount.php" method="post" class="dropdown-items">
                  <button type="submit" id="new-account" name="new-account">Join / Login</button>
                  </form>';
                }
              ?>
            </li>
            <?php
            // if the user is logged in, display options to view all recipes user owns and option to sign out of their account
              if(isset($_SESSION['username'])) {
                echo '<li><form action="searchresults.php" method="get" class="dropdown-items">
                <button type="submit" id="my-recipes" name="user" value="'.$_SESSION['username'].'">My Recipes</button>
                </form></li>
                <li><form action="'.htmlspecialchars($_SERVER['PHP_SELF']).'" method="post" class="dropdown-items">
                <button type="submit" id="logout" name="logout">Sign Out</button>
                </form></li>'; 
              }
            ?>
          </ul>
        </div>
      </div>
    </div>
  </nav>
</header>