/*
  script.js

  Name: Brianna Drew
  ID: #0622446
  Date: April 19th, 2021
  Description: This script includes validation for all forms, will display confirm pop-up dialog boxes for all delete operations,
  perform updates to database for plugins, and provide pop-up modal functionality for view recipe pages.
*/

"use strict";

window.addEventListener("DOMContentLoaded", () => {
  /* ####################################### 
  #                                        #
  #            GLOBAL SELECTORS            #
  #                                        #
  ####################################### */
  const deleteForm = document.querySelectorAll(".delete-form");
  const mainModal = document.getElementById("main-modal");
  const searchModal = document.getElementById("search-modal");
  const mView = document.querySelectorAll(
    ".m-recipe-results img, .m-recipe-results h2"
  );
  const sView = document.querySelectorAll(
    ".s-recipe-results img, .s-recipe-results h2"
  );
  const rate = document.querySelectorAll("jsuites-rating");
  const tags = document.querySelectorAll("jsuites-tags");
  const editForm = document.getElementById("edit-form");
  const createForm = document.getElementById("create-form");
  const loginForm = document.getElementById("login-form");
  const accForm = document.getElementById("account-form");
  const newAccForm = document.getElementById("new-acc-form");
  const forgotPassForm = document.getElementById("forgot-pass-form");
  const passResetForm = document.getElementById("pass-reset-form");
  const bar1 = document.querySelector(".bar-1");
  const bar2 = document.querySelector(".bar-2");
  const bar3 = document.querySelector(".bar-3");
  const bar4 = document.querySelector(".bar-4");
  const strengthDis = document.querySelector("#strength-display");

  // test password for strength using a regular expression. returns true if it is strong, false if not
  function passwordStrength(password) {
    let passStrong = true;
    const strongRegex = new RegExp(
      "^(?=.*[a-z])(?=.*[A-Z])(?=.*[0-9])(?=.*[!@#$%^&*])(?=.{8,})"
    );
    if (!strongRegex.test(password)) {
      passStrong = false;
    }
    return passStrong;
  }

  // test password for strength using regular expressions for each requirement. returns number of requirements met
  function indvPassStrength(password) {
    let strengthLevel = 0;
    const upper = new RegExp("[A-Z]+"); // contains 1 or more uppercase letters?
    const lower = new RegExp("[a-z]+"); // contains 1 or more lowercase letters?
    const num = new RegExp("[0-9]+"); // contains 1 or more numbers?
    const special = new RegExp("[!@#$%^&*]+"); // contains 1 or more special characters?
    if (upper.test(password) && lower.test(password)) {
      strengthLevel++;
    }
    if (num.test(password)) {
      strengthLevel++;
    }
    if (special.test(password)) {
      strengthLevel++;
    }
    if (password.length >= 8) {
      strengthLevel++;
    }
    return strengthLevel;
  }

  // test email for validity using a regular expression. returns true if it is valid, false if not
  function emailValid(email) {
    let emailValid = true;
    const validRegex = new RegExp(
      "^[a-zA-Z0-9.!#$%&'*+/=?^_`{|}~-]+@[a-zA-Z0-9-]+(?:.[a-zA-Z0-9-]+)*$"
    );
    if (!validRegex.test(email)) {
      emailValid = false;
    }
    return emailValid;
  }

  // display a confirmation box when user tries to delete a recipe
  deleteForm.forEach(function (deletes) {
    deletes.addEventListener("submit", (ev) => {
      let deletey = confirm("Are you sure you want to delete this recipe?");
      if (!deletey) {
        ev.preventDefault(); // if they cancel, stop deletion from happening
      }
    });
  });

  // send XMLHttpRequest to get recipe details when user clicks on a recipe to view it, then populate the pop-up modal window with
  // these details and display the modal (from main page)
  mView.forEach(function (views) {
    views.addEventListener("click", () => {
      let recipe = views.id;
      const xhr = new XMLHttpRequest();
      xhr.open("GET", "viewrecipe.php?recipe=" + recipe); // pass id of recipe
      xhr.addEventListener("load", () => {
        if (xhr.status == 200) {
          mainModal.innerHTML = xhr.response;
          // close the pop-up modal if user clicks the close button
          const close = mainModal.querySelector(".close");
          close.addEventListener("click", () => {
            mainModal.style.display = "none";
          });
          mainModal.style.display = "block";
        } else {
          console.log("XMLHttpRequest Failed.");
        }
      });
      xhr.send();
    });
  });

  // send XMLHttpRequest to get recipe details when user clicks on a recipe to view it, then populate the pop-up modal window with
  // these details and display the modal (from search results page)
  sView.forEach(function (views) {
    views.addEventListener("click", () => {
      let recipe = views.id;
      const xhr = new XMLHttpRequest();
      xhr.open("GET", "viewrecipe.php?recipe=" + recipe); // pass id of recipe
      xhr.addEventListener("load", () => {
        if (xhr.status == 200) {
          searchModal.innerHTML = xhr.response;
          // close the pop-up modal if user clicks the close button
          const close = searchModal.querySelector(".close");
          close.addEventListener("click", () => {
            searchModal.style.display = "none";
          });
          searchModal.style.display = "block";
        } else {
          console.log("XMLHttpRequest Failed.");
        }
      });
      xhr.send();
    });
  });

  // close view recipe pop-up modal if user clicks anywhere but the modal (from main page)
  window.addEventListener("click", (ev) => {
    if (ev.target == mainModal) {
      mainModal.style.display = "none";
    }
  });

  // close view recipe pop-up modal if user clicks anywhere but the modal (from search results page)
  window.addEventListener("click", (ev) => {
    if (ev.target == searchModal) {
      searchModal.style.display = "none";
    }
  });

  // send XMLHttpRequest to update average rating when the value of the rating element for a given recipe is changed
  rate.forEach(function (rating) {
    rating.addEventListener("onchange", () => {
      let id = rating.id;
      let star = rating.value;
      const xhr = new XMLHttpRequest();
      xhr.open("GET", "ratingupdate.php?rating=" + star + "&id=" + id); // pass user's rating and recipe id
      xhr.addEventListener("load", () => {
        if (xhr.status != 200) {
          console.log("XMLHttpRequest Failed.");
        }
      });
      xhr.send();
    });
  });

  // send XMLHttpRequest to update tags in the database for a given recipe when the tag element loses focus
  tags.forEach(function (tag) {
    tag.addEventListener("onblur", () => {
      let id = tag.id;
      let alltags = tag.value;
      const xhr = new XMLHttpRequest();
      xhr.open("GET", "tagupdate.php?tags=" + alltags + "&id=" + id); // pass tags and recipe id
      xhr.addEventListener("load", () => {
        if (xhr.status != 200) {
          console.log("XMLHttpRequest Failed.");
        }
      });
      xhr.send();
    });
  });

  /* ####################################### 
  #                                        #
  #              EDIT RECIPE               #
  #                                        #
  ####################################### */

  if (editForm) {
    // SELECTORS
    const etitle = document.getElementById("etitle");
    const edescription = document.getElementById("edescription");
    const eservings = document.getElementById("eservings");
    const eminutes = document.getElementById("eminutes");
    const eingredients = document.getElementById("eingredients");
    const edirections = document.getElementById("edirections");
    const eTitleError = document.getElementById("e-title-error");
    const eDescError = document.getElementById("e-description-error");
    const eServError = document.getElementById("e-servings-error");
    const eMinError = document.getElementById("e-minutes-error");
    const eIngredError = document.getElementById("e-ingredients-error");
    const eDirectError = document.getElementById("e-directions-error");

    editForm.addEventListener("submit", (ev) => {
      let evalid = true;
      // if user did not enter a title, display appropriate error message
      if (etitle.value == null || etitle.value == "") {
        eTitleError.classList.remove("hidden");
        evalid = false;
      } else {
        eTitleError.classList.add("hidden");
      }

      // if user did not enter a description, display appropriate error message
      if (edescription.value == null || edescription.value == "") {
        eDescError.classList.remove("hidden");
        evalid = false;
      } else {
        eDescError.classList.add("hidden");
      }

      // if user did not enter number of servings, display appropriate error message
      if (eservings.value == null || eservings.value == "") {
        eServError.classList.remove("hidden");
        evalid = false;
      } else {
        eServError.classList.add("hidden");
      }

      // if user did not enter minutes, display appropriate error message
      if (eminutes.value == null || eminutes.value == "") {
        eMinError.classList.remove("hidden");
        evalid = false;
      } else {
        eMinError.classList.add("hidden");
      }

      // if user did not enter ingredients, display appropriate error message
      if (eingredients.value == null || eingredients.value == "") {
        eIngredError.classList.remove("hidden");
        evalid = false;
      } else {
        eIngredError.classList.add("hidden");
      }

      // if user did not enter directions, display appropriate error message
      if (edirections.value == null || edirections.value == "") {
        eDirectError.classList.remove("hidden");
        evalid = false;
      } else {
        eDirectError.classList.add("hidden");
      }

      // if any errors were found above, stop form from submitting
      if (!evalid) ev.preventDefault();
    });
  }

  /* ####################################### 
  #                                        #
  #              CREATE RECIPE             #
  #                                        #
  ####################################### */

  if (createForm) {
    // SELECTORS
    const ctitle = document.getElementById("ctitle");
    const cdescription = document.getElementById("cdescription");
    const cservings = document.getElementById("cservings");
    const cminutes = document.getElementById("cminutes");
    const cingredients = document.getElementById("cingredients");
    const cdirections = document.getElementById("cdirections");
    const cTitleError = document.getElementById("c-title-error");
    const cDescError = document.getElementById("c-description-error");
    const cServError = document.getElementById("c-servings-error");
    const cMinError = document.getElementById("c-minutes-error");
    const cIngredError = document.getElementById("c-ingredients-error");
    const cDirectError = document.getElementById("c-directions-error");

    createForm.addEventListener("submit", (ev) => {
      let cvalid = true;
      // if user did not enter a title, display appropriate error message
      if (ctitle.value == null || ctitle.value == "") {
        cTitleError.classList.remove("hidden");
        cvalid = false;
      } else {
        cTitleError.classList.add("hidden");
      }

      // if user did not enter a description, display appropriate error message
      if (cdescription.value == null || cdescription.value == "") {
        cDescError.classList.remove("hidden");
        cvalid = false;
      } else {
        cDescError.classList.add("hidden");
      }

      // if user did not enter number of servings, display appropriate error message
      if (cservings.value == null || cservings.value == "") {
        cServError.classList.remove("hidden");
        cvalid = false;
      } else {
        cServError.classList.add("hidden");
      }

      // if user did not enter minutes, display appropriate error message
      if (cminutes.value == null || cminutes.value == "") {
        cMinError.classList.remove("hidden");
        cvalid = false;
      } else {
        cMinError.classList.add("hidden");
      }

      // if user did not enter ingredients, display appropriate error message
      if (cingredients.value == null || cingredients.value == "") {
        cIngredError.classList.remove("hidden");
        cvalid = false;
      } else {
        cIngredError.classList.add("hidden");
      }

      // if user did not enter directions, display appropriate error message
      if (cdirections.value == null || cdirections.value == "") {
        cDirectError.classList.remove("hidden");
        cvalid = false;
      } else {
        cDirectError.classList.add("hidden");
      }

      // if any errors were found above, stop form from submitting
      if (!cvalid) ev.preventDefault();
    });
  }

  /* ####################################### 
  #                                        #
  #                 LOGIN                  #
  #                                        #
  ####################################### */

  if (loginForm) {
    // SELECTORS
    const lUsername = document.getElementById("l-username");
    const lPass = document.getElementById("l-password");
    const lEmptyError = document.getElementById("l-empty-error");

    loginForm.addEventListener("submit", (ev) => {
      let lvalid = true;
      // if user did not enter a username or password, display appropriate error message
      if (
        lUsername.value == null ||
        lUsername.value == "" ||
        lPass.value == null ||
        lPass.value == ""
      ) {
        lEmptyError.classList.remove("hidden");
        lvalid = false;
      } else {
        lEmptyError.classList.add("hidden");
      }

      // if any errors were found above, stop form from submitting
      if (!lvalid) ev.preventDefault();
    });
  }

  /* ####################################### 
  #                                        #
  #              EDIT ACCOUNT              #
  #                                        #
  ####################################### */

  if (accForm) {
    // SELECTORS
    const aEmail = document.getElementById("a-email");
    const aUsername = document.getElementById("a-username");
    const aPass = document.getElementById("a-password");
    const aConfirmPass = document.getElementById("a-confirm-password");
    const aEmptyError = document.getElementById("a-empty-error");
    const aEmailError = document.getElementById("a-email-error");
    const aUserError = document.getElementById("a-user-error");
    const aStrengthError = document.getElementById("a-strength-error");
    const aMatchError = document.getElementById("a-match-error");
    const delAcc = document.getElementById("delete-account");
    let auvalid = true;

    // when the username field loses focus, send XMLHttpRequest to check if the username is already present in the database.
    // if it is present, display appropriate error message
    aUsername.addEventListener("blur", () => {
      auvalid = true;
      const uxhr = new XMLHttpRequest();
      uxhr.open("GET", "userexists.php?username=" + aUsername.value); // pass provided username
      uxhr.addEventListener("load", () => {
        if (uxhr.status == 200) {
          if (uxhr.response == "true") {
            aUserError.classList.remove("hidden");
            auvalid = false;
          } else {
            aUserError.classList.add("hidden");
          }
        } else {
          console.log("XMLHttpRequest Failed.");
        }
      });
      uxhr.send();
    });

    accForm.addEventListener("submit", (ev) => {
      let avalid = true;
      // if user left any form field empty, display appropriate error message
      if (
        aEmail.value == null ||
        aEmail.value == "" ||
        aUsername.value == null ||
        aUsername.value == "" ||
        aPass.value == null ||
        aPass.value == "" ||
        aConfirmPass.value == null ||
        aConfirmPass.value == ""
      ) {
        aEmptyError.classList.remove("hidden");
        avalid = false;
      } else {
        aEmptyError.classList.add("hidden");
      }

      // if email provided is not a valid email address, display appropriate error message
      let eValid = emailValid(aEmail.value);
      if (!eValid) {
        aEmailError.classList.remove("hidden");
        avalid = false;
      } else {
        aEmailError.classList.add("hidden");
      }

      // if password provided does not meet the strength requirements, display appropriate error message
      let strong = passwordStrength(aPass.value);
      if (!strong) {
        aStrengthError.classList.remove("hidden");
        avalid = false;
      } else {
        aStrengthError.classList.add("hidden");
      }

      // if the passwords provided are not the same, display appropriate error message
      if (aConfirmPass.value != aPass.value) {
        aMatchError.classList.remove("hidden");
        avalid = false;
      } else {
        aMatchError.classList.add("hidden");
        avalid = true;
      }

      // if any errors were found above, stop form from submitting
      if (!avalid || !auvalid) ev.preventDefault();
    });

    // display a confirmation box when user tries to delete their account
    delAcc.addEventListener("click", (event) => {
      let deletey = confirm("Are you sure you want to delete your account?");
      if (!deletey) {
        event.preventDefault(); // if they cancel, stop deletion from happening
      }
    });
  }

  /* ####################################### 
  #                                        #
  #              NEW ACCOUNT               #
  #                                        #
  ####################################### */

  if (newAccForm) {
    // SELECTORS
    const nEmail = document.getElementById("n-email");
    const nUsername = document.getElementById("n-username");
    const nPass = document.getElementById("n-password");
    const nEmptyError = document.getElementById("n-empty-error");
    const nEmailError = document.getElementById("n-email-error");
    const nUserError = document.getElementById("n-user-error");
    const nStrengthError = document.getElementById("n-strength-error");
    let nuvalid = true;

    // when the username field loses focus, send XMLHttpRequest to check if the username is already present in the database.
    // if it is present, display appropriate error message
    nUsername.addEventListener("blur", () => {
      nuvalid = true;
      const uxhr = new XMLHttpRequest();
      uxhr.open("GET", "userexists.php?username=" + nUsername.value); // pass provided username
      uxhr.addEventListener("load", () => {
        if (uxhr.status == 200) {
          if (uxhr.response == "true") {
            nUserError.classList.remove("hidden");
            nuvalid = false;
          } else {
            nUserError.classList.add("hidden");
          }
        } else {
          console.log("XMLHttpRequest Failed.");
        }
      });
      uxhr.send();
    });

    // each time the user presses a key in the password field, check how many strength requirements have been met and update progress bar
    nPass.addEventListener("keyup", () => {
      bar1.classList.add("bar-trans");
      strengthDis.classList.remove("hidden");
      let level = indvPassStrength(nPass.value);
      console.log(level);
      switch (level) {
        case 0: // if 0 requirements have been met, the password is weak and only one level of progress on the bar should show (red)
          strengthDis.innerText = "weak...";
          bar4.classList.remove("bar-trans");
          bar3.classList.remove("bar-trans");
          bar2.classList.remove("bar-trans");
          bar1.classList.add("bar-trans");
          break;
        case 1: // if 1 requirement has been met, the password is weak and only one level of progress on the bar should show (red)
          strengthDis.innerText = "weak...";
          bar4.classList.remove("bar-trans");
          bar3.classList.remove("bar-trans");
          bar2.classList.remove("bar-trans");
          bar1.classList.add("bar-trans");
          break;
        case 2: // if 2 requirements have been met, the password is okay and only two levels of progress on the bar should show (red and orange)
          strengthDis.innerText = "okay...";
          bar4.classList.remove("bar-trans");
          bar3.classList.remove("bar-trans");
          bar2.classList.add("bar-trans");
          break;
        case 3: // if 3 requirements have been met, the password is almost there and three levels of progress on the bar should show (red, orange, and yellow)
          strengthDis.innerText = "almost there...";
          bar4.classList.remove("bar-trans");
          bar3.classList.add("bar-trans");
          break;
        case 4: // if all requirements have been met, the password is strong and all four levels of progress on the bar should show (red, orange, yellow, and green)
          strengthDis.innerText = "strong!";
          bar4.classList.add("bar-trans");
      }
    });

    newAccForm.addEventListener("submit", (ev) => {
      let nvalid = true;
      // if user left any form field empty, display appropriate error message
      if (
        nEmail.value == null ||
        nEmail.value == "" ||
        nUsername.value == null ||
        nUsername.value == "" ||
        nPass.value == null ||
        nPass.value == ""
      ) {
        nEmptyError.classList.remove("hidden");
        nvalid = false;
      } else {
        nEmptyError.classList.add("hidden");
      }

      // if email provided is not a valid email address, display appropriate error message
      let eValid = emailValid(nEmail.value);
      if (!eValid) {
        nEmailError.classList.remove("hidden");
        nvalid = false;
      } else {
        nEmailError.classList.add("hidden");
      }

      // if password provided does not meet the strength requirements, display appropriate error message
      let strong = passwordStrength(nPass.value);
      if (!strong) {
        nStrengthError.classList.remove("hidden");
        nvalid = false;
      } else {
        nStrengthError.classList.add("hidden");
      }

      // if any errors were found above, stop form from submitting
      if (!nvalid || !nuvalid) ev.preventDefault();
    });
  }

  /* ####################################### 
  #                                        #
  #            FORGOT PASSWORD             #
  #                                        #
  ####################################### */

  if (forgotPassForm) {
    // SELECTORS
    const fEmail = document.getElementById("f-email");
    const fEmptyError = document.getElementById("f-empty-error");
    const fEmailError = document.getElementById("f-email-error");

    // if user did not provide an email, display appropriate error message
    forgotPassForm.addEventListener("submit", (ev) => {
      let fvalid = true;
      if (fEmail.value == null || fEmail.value == "") {
        fEmptyError.classList.remove("hidden");
        fvalid = false;
      } else {
        fEmptyError.classList.add("hidden");
      }

      // if email provided is not a valid email address, display appropriate error message
      let eValid = emailValid(fEmail.value);
      if (!eValid) {
        fEmailError.classList.remove("hidden");
        fvalid = false;
      } else {
        fEmailError.classList.add("hidden");
      }

      // if any errors were found above, stop form from submitting
      if (!fvalid) ev.preventDefault();
    });
  }

  /* ####################################### 
  #                                        #
  #             PASSWORD RESET             #
  #                                        #
  ####################################### */

  if (passResetForm) {
    // SELECTORS
    const pNewPass = document.getElementById("new-password");
    const pConfirmPass = document.getElementById("confirm-password");
    const pEmptyError = document.getElementById("p-empty-error");
    const pMatchError = document.getElementById("p-match-error");
    const pStrengthError = document.getElementById("p-strength-error");
    let ppvalid = true;
    let pcvalid = true;

    // each tome a user presses a key in the password field, check to see if it meets all of the strength requirements
    // if it does not, display appropriate error message
    pNewPass.addEventListener("keyup", () => {
      ppvalid = true;
      let strong = passwordStrength(pNewPass.value);
      if (!strong) {
        pStrengthError.classList.remove("hidden");
        ppvalid = false;
      } else {
        pStrengthError.classList.add("hidden");
        ppvalid = true;
      }
    });

    // each time a user presses a key in the confirm password field, check to see if it matches
    // if they do not match, display appropriate error message
    pConfirmPass.addEventListener("keyup", () => {
      pcvalid = true;
      if (pConfirmPass.value != pNewPass.value) {
        pMatchError.classList.remove("hidden");
        pcvalid = false;
      } else {
        pMatchError.classList.add("hidden");
        pcvalid = true;
      }
    });

    passResetForm.addEventListener("submit", (ev) => {
      let pvalid = true;
      // if user did not provide either passwords, display appropriate error message
      if (
        pNewPass.value == null ||
        pNewPass.value == "" ||
        pConfirmPass.value == null ||
        pConfirmPass.value == ""
      ) {
        pEmptyError.classList.remove("hidden");
        pvalid = false;
      } else {
        pEmptyError.classList.add("hidden");
      }

      // if any errors were found above, stop form from submitting
      if (!pvalid || !ppvalid || !pcvalid) ev.preventDefault();
    });
  }
});
