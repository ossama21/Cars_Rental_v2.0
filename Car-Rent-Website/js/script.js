// Function to fetch cars from the database and display them dynamically
function fetchAndDisplayCars() {
  const carListingsContainer = document.getElementById('car-listings');

  // Clear existing content (if any)
  carListingsContainer.innerHTML = '';

  // Make an AJAX request to fetch car data from the database (book.php)
  fetch('book.php', { method: 'POST' })
    .then(response => response.json())
    .then(data => {
      // Loop through each car and create the corresponding card
      data.cars.forEach(car => {
        const carCard = `
          <div style="height:600px" class="col-lg-4 mb-5 d-flex align-items-stretch">
            <div class="card shadow rounded d-flex flex-column">
              <img src="${car.image}" class="card-img-top" alt="${car.name}">
              <div class="card-body d-flex flex-column">
                <h5 class="card-title">${car.name}</h5>
                <hr>
                <p class="card-text">${car.price}$/day 
                  <a href="checkout.php?product_id=${car.id}" class="button btn btn-dark">Book Now</a>
                </p>
                <p class="card-description">${car.description}</p>
                <p class="card-details">
                  <strong>Model:</strong> ${car.model}<br>
                  <strong>Transmission:</strong> ${car.transmission}<br>
                  <strong>Interior:</strong> ${car.interior}<br>
                  <strong>Brand:</strong> ${car.brand}
                </p>
              </div>
            </div>
          </div>
        `;

        // Append the car card to the container
        carListingsContainer.innerHTML += carCard;
      });
    })
    .catch(error => console.error('Error fetching cars:', error));
}

// Call the function to fetch and display cars when the page loads
document.addEventListener('DOMContentLoaded', fetchAndDisplayCars);
