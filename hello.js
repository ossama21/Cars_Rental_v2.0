// document.addEventListener("DOMContentLoaded", function() {
//     const translations = {
//      "whyChooseUs": {
//   "title": "Why Rent With Us",
//   "description": "Discover the ultimate car rental experience with unparalleled service and an extensive fleet of vehicles tailored to your needs.",
//   "features": [
//     {
//       "title": "Diverse Vehicle Selection",
//       "description": "From sleek city cars to robust SUVs, our fleet is designed to meet your every need. Whether you're navigating urban streets or embarking on a cross-country adventure, we have the perfect vehicle for you."
//     },
//     {
//       "title": "Transparent Pricing",
//       "description": "Enjoy competitive rates with no hidden fees. Our clear, straightforward pricing ensures you receive the best value, combining affordability with top-notch quality and service."
//     },
//     {
//       "title": "Effortless Booking",
//       "description": "Our intuitive online booking system allows you to quickly reserve your vehicle in just a few clicks. Choose your car, select your pickup location, and secure your rental with ease."
//     },
//     {
//       "title": "Proven Reliability",
//       "description": "With years of industry experience, we are committed to delivering exceptional service and dependable vehicles. Trust us for a smooth, hassle-free rental experience every time."
//     }
//   ]
// }

//     };
  
    // const titleElement = document.querySelector(".title");
    // const descriptionElement = document.querySelector(".description");
    // const featureElements = document.querySelectorAll(".feature");
  
    // titleElement.textContent = translations.whyChooseUs.title;
  //   descriptionElement.textContent = translations.whyChooseUs.description;
  
  //   featureElements.forEach((feature, index) => {
  //     feature.querySelector(".feature-title").textContent = translations.whyChooseUs.features[index].title;
  //     feature.querySelector(".feature-description").textContent = translations.whyChooseUs.features[index].description;
  //   });
  // });
  // Sample translations
// const translations = {
//     en: {
//       clientSpeak: {
//         title: "What Our Clients Say",
//         description: "Heres what some of our satisfied clients have to say about us.",
//         testimonials: [
//           { heading: "Great Experience", text: "The service was fast, reliable, and affordable.", name: "John Doe", title: "CEO, Company A" },
//           { heading: "Highly Recommend", text: "Professional and friendly service. I would recommend it to anyone.", name: "Jane Smith", title: "Manager, Company B" },
//           { heading: "Outstanding Service", text: "They went above and beyond my expectations.", name: "Mark Wilson", title: "Director, Company C" }
//         ]
//       }
//     },
//     fr: {
//       clientSpeak: {
//         title: "Ce que disent nos clients",
//         description: "Voici ce que certains de nos clients satisfaits ont à dire.",
//         testimonials: [
//           { heading: "Grande expérience", text: "Le service était rapide, fiable et abordable.", name: "Jean Dupont", title: "PDG, Société A" },
//           { heading: "Fortement recommandé", text: "Service professionnel et amical. Je le recommanderais à tout le monde.", name: "Marie Dupuis", title: "Directrice, Société B" },
//           { heading: "Service exceptionnel", text: "Ils ont dépassé mes attentes.", name: "Marc Wilson", title: "Directeur, Société C" }
//         ]
//       }
//     }
//   };
  
  // Function to update the content based on the language
  // function updateContent(lang = "en") {
  //   const t = translations[lang].clientSpeak;
  
  //   document.querySelector(".title").innerText = t.title;
  //   document.querySelector(".description").innerText = t.description;
  
  //   const testimonials = document.querySelectorAll(".testimonial");
  //   testimonials.forEach((testimonial, index) => {
  //     testimonial.querySelector(".testimonial-heading").innerText = t.testimonials[index].heading;
  //     testimonial.querySelector(".testimonial-text").innerText = t.testimonials[index].text;
  //     testimonial.querySelector(".avatar-name").innerText = t.testimonials[index].name;
  //     testimonial.querySelector(".avatar-title").innerText = t.testimonials[index].title;
  //   });
  // }
  
  // Event listener for changing language
  // document.querySelector("#language-selector").addEventListener("change", function() {
  //   updateContent(this.value);
  // });
  
  // Initial content load
  // updateContent();
  
  