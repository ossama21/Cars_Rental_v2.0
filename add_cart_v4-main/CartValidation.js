document.getElementById('checkoutForm').addEventListener('click', function(event) {
    event.preventDefault();  // Prevent the form from submitting
    const carId = /* Get the selected car's ID */
    window.location.href = `checkout.html?id=${carId}`;
 
    // Basic validation (you can add more)
    let name = document.getElementById('name').value;
    let address = document.getElementById('address').value;
    let phone = document.getElementById('phone').value;
    let visa = document.getElementById('visa').value;
 
    if (name === "" || address === "" || phone === "" || visa === "") {
       alert("Please fill in all fields.");
    } else {
       // Show confirmation message
       document.getElementById('confirmationMessage').style.display = 'block';
    }
 });
 