let profileDropdownList = document.querySelector(".profile-dropdown-list");
let btn = document.querySelector(".profile-dropdown-btn");

let classList = profileDropdownList.classList;

const toggle = () => classList.toggle("active");

window.addEventListener("click", function (e) {
  if (!btn.contains(e.target)) classList.remove("active");
});
// Script to toggle dropdown
function toggleDropdown() {
    const dropdownList = document.querySelector('.profile-dropdown-list');
    dropdownList.classList.toggle('active');
}

// Simulate a user sign-in for now (replace this with actual sign-in logic)

// Call the function to set the user name
