// JavaScript Logic to Control Dropdown Activation
let profileDropdownList = document.querySelector(".profile-dropdown-list");
let btn = document.querySelector(".profile-dropdown-btn");
let signInLink = document.getElementById("sign-in-link");

if (btn && !signInLink) {  // Only activate dropdown if the user is signed in (no sign-in link)
  btn.addEventListener("click", function () {
    profileDropdownList.classList.toggle("active");
  });

  // Close dropdown if clicked outside
  window.addEventListener("click", function (e) {
    if (!btn.contains(e.target)) {
      profileDropdownList.classList.remove("active");
    }
  });
}
