const navbarLinks = document.querySelectorAll(".nav-menu .nav-link");
const menuOpenButton = document.querySelector("#menu-open-button");
const menuCloseButton = document.querySelector("#menu-close-button");
menuOpenButton.addEventListener("click", () => {
  // Toggle mobile menu visibility
  document.body.classList.toggle("show-mobile-menu");
});
// Close menu when the close button is clicked
menuCloseButton.addEventListener("click", () => menuOpenButton.click());
// Close menu when nav link is clicked
navbarLinks.forEach((link) => {
  link.addEventListener("click", () => menuOpenButton.click());
});
/* Initializing Swiper */
let swiper = new Swiper(".slider-wrapper", {
  loop: true,
  grabCursor: true,
  spaceBetween: 25,
  // Pagination bullets
  pagination: {
    el: ".swiper-pagination",
    clickable: true,
    dynamicBullets: true,
  },
  // Navigation arrows
  navigation: {
    nextEl: ".swiper-button-next",
    prevEl: ".swiper-button-prev",
  },
  /* Responsive breakpoints */
  breakpoints: {
    0: {
      slidesPerView: 1,
    },
    768: {
      slidesPerView: 2,
    },
    1024: {
      slidesPerView: 3,
    },
  },
});
// const swiper = new Swiper('.slider-wrapper', {
//     loop: true, // Infinite loop
//     grabCursor: true,
//     spaceBetween: 30,
//     speed: 4000, // Smooth transition speed (adjust as needed)
//     autoplay: {
//         delay: 0, // No delay between slides
//         disableOnInteraction: false, // Keeps autoplay active even after user interaction
//     },
//     slidesPerView: 'auto', // Automatically adjusts slides
//     freeMode: true, // Enables smooth, natural scrolling
//     freeModeMomentum: false, // Disables momentum for continuous movement

//     pagination: {
//         el: '.swiper-pagination',
//         clickable: true,
//         dynamicBullets: true
//     },
//     navigation: {
//         nextEl: '.swiper-button-next',
//         prevEl: '.swiper-button-prev',
//     },
//     breakpoints: {
//         0: { slidesPerView: 1 },
//         768: { slidesPerView: 2 },
//         1024: { slidesPerView: 3 }
//     }
// });

// // Function to pause and resume after 3 seconds
// let autoplayTimeout;

// function stopAutoplay() {
//     swiper.autoplay.stop(); // Stop autoplay
//     clearTimeout(autoplayTimeout); // Clear existing timeout
//     autoplayTimeout = setTimeout(() => {
//         swiper.autoplay.start(); // Resume autoplay after 3 seconds
//     }, 5000);
// }

// // Add event listener to stop autoplay on click
// document.querySelector('.slider-wrapper').addEventListener('click', stopAutoplay);
