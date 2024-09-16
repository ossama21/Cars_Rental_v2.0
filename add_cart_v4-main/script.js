import cars from './products.js'; // Assuming your paths are correct

const carContainer = document.getElementById('car-list'); // Assuming this is the container

cars.forEach(car => {
  const carElement = document.createElement('div');
  carElement.classList.add('car-item');
  carElement.innerHTML = `
    <img src="${car.image}" alt="${car.name}">
    <h3>${car.name}</h3>
    <p>$${car.price} per day</p>
  `;
  carContainer.appendChild(carElement);
});
