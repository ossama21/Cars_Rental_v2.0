// Import the products array from products.js
import products from './products.js';

// Function to display products dynamically
function displayProducts() {
  const carListingsContainer = document.getElementById('car-listings');

  // Loop through each product in the products array
  products.forEach(product => {
    // Create a new card for each product
    const carCard = `
      <div class="col-lg-4 mb-5">
        <div class="card shadow rounded">
          <img src="${product.image}" class="card-img-top" alt="${product.name}">
          <div class="card-body">
            <h5 class="card-title">${product.name}</h5>
            <hr>
            <p class="card-text">${product.price}$/day 
              <a href="checkout.php?product_id=${product.id}" class="button btn btn-dark">Book Now</a>
            </p>
            <p class="card-description">${product.description}</p>
            <p class="card-details">
              <strong>Model:</strong> ${product.model}<br>
              <strong>Transmission:</strong> ${product.transmission}<br>
              <strong>Interior:</strong> ${product.interior}<br>
              <strong>Brand:</strong> ${product.brand}
            </p>
          </div>
        </div>
      </div>
    `;

    // Append the card to the container
    carListingsContainer.innerHTML += carCard;
  });
}


// Call the function to display products when the page loads
document.addEventListener('DOMContentLoaded', displayProducts);
