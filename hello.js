document.addEventListener("DOMContentLoaded", function() {
    const translations = {
      "whyChooseUs": {
        "title": "Why Choose Us",
        "description": "Experience hassle-free car rentals with exceptional service and a wide selection of vehicles.",
        "features": [
          {
            "title": "Feature 1",
            "description": "We offer a diverse range of cars to suit your needs and preferences. Whether you're looking for a compact car for city driving or a spacious SUV for a family trip, we have a wide selection of vehicles to choose from.."
          },
          {
            "title": "Feature 2",
            "description": "We provide competitive prices that fit your budget. Our transparent pricing ensures that you get the best value for your money without compromising on quality or service."
          },
          {
            "title": "Feature 3",
            "description": "Our user-friendly online booking system makes it quick and convenient to reserve your desired car. With just a few clicks, you can easily select your pickup location, choose your preferred car, and book it for your desired dates."
          },
          {
            "title": "Feature 4",
            "description": "With years of experience in the car rental industry, we have established a reputation for trust and reliability. You can rely on us to provide quality vehicles, excellent service, and a seamless rental experience."
          }
        ]
      }
    };
  
    const titleElement = document.querySelector(".title");
    const descriptionElement = document.querySelector(".description");
    const featureElements = document.querySelectorAll(".feature");
  
    titleElement.textContent = translations.whyChooseUs.title;
    descriptionElement.textContent = translations.whyChooseUs.description;
  
    featureElements.forEach((feature, index) => {
      feature.querySelector(".feature-title").textContent = translations.whyChooseUs.features[index].title;
      feature.querySelector(".feature-description").textContent = translations.whyChooseUs.features[index].description;
    });
  });
  // Sample translations
const translations = {
    en: {
      clientSpeak: {
        title: "What Our Clients Say",
        description: "Here’s what some of our satisfied clients have to say about us.",
        testimonials: [
          { heading: "Great Experience", text: "The service was fast, reliable, and affordable.", name: "John Doe", title: "CEO, Company A" },
          { heading: "Highly Recommend", text: "Professional and friendly service. I would recommend it to anyone.", name: "Jane Smith", title: "Manager, Company B" },
          { heading: "Outstanding Service", text: "They went above and beyond my expectations.", name: "Mark Wilson", title: "Director, Company C" }
        ]
      }
    },
    fr: {
      clientSpeak: {
        title: "Ce que disent nos clients",
        description: "Voici ce que certains de nos clients satisfaits ont à dire.",
        testimonials: [
          { heading: "Grande expérience", text: "Le service était rapide, fiable et abordable.", name: "Jean Dupont", title: "PDG, Société A" },
          { heading: "Fortement recommandé", text: "Service professionnel et amical. Je le recommanderais à tout le monde.", name: "Marie Dupuis", title: "Directrice, Société B" },
          { heading: "Service exceptionnel", text: "Ils ont dépassé mes attentes.", name: "Marc Wilson", title: "Directeur, Société C" }
        ]
      }
    }
  };
  
  // Function to update the content based on the language
  function updateContent(lang = "en") {
    const t = translations[lang].clientSpeak;
  
    document.querySelector(".title").innerText = t.title;
    document.querySelector(".description").innerText = t.description;
  
    const testimonials = document.querySelectorAll(".testimonial");
    testimonials.forEach((testimonial, index) => {
      testimonial.querySelector(".testimonial-heading").innerText = t.testimonials[index].heading;
      testimonial.querySelector(".testimonial-text").innerText = t.testimonials[index].text;
      testimonial.querySelector(".avatar-name").innerText = t.testimonials[index].name;
      testimonial.querySelector(".avatar-title").innerText = t.testimonials[index].title;
    });
  }
  
  // Event listener for changing language
  document.querySelector("#language-selector").addEventListener("change", function() {
    updateContent(this.value);
  });
  
  // Initial content load
  updateContent();
  
  