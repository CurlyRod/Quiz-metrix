// Mobile Menu Toggle
const menuOpenBtn = document.getElementById("menu-open-button")
const menuCloseBtn = document.getElementById("menu-close-button")
const navMenu = document.querySelector(".nav-menu")
const navLinks = document.querySelectorAll(".nav-link")

if (menuOpenBtn) {
  menuOpenBtn.addEventListener("click", () => {
    document.body.classList.add("show-mobile-menu")
    navMenu.style.right = "0"
  })
}

if (menuCloseBtn) {
  menuCloseBtn.addEventListener("click", () => {
    document.body.classList.remove("show-mobile-menu")
    navMenu.style.right = "-100%"
  })
}

if (navLinks) {
  navLinks.forEach((link) => {
    link.addEventListener("click", () => {
      document.body.classList.remove("show-mobile-menu")
      navMenu.style.right = "-100%"
    })
  })
}

// Login Button Handler - Check user status before proceeding
function handleLoginButton() {
  const loginButtons = document.querySelectorAll('.login-btn, [data-login-button], .sign-in-btn');
  
  loginButtons.forEach(button => {
    button.addEventListener('click', function(e) {
      e.preventDefault();
      
      // Show loading state
      const originalText = this.textContent;
      this.textContent = 'Checking...';
      this.disabled = true;
      
      // Check if user already exists and their status
      fetch('../Middleware/auth/ValidateUser.php', {  // ← CORRECT PATH
        method: 'POST',
        headers: {
          'Content-Type': 'application/x-www-form-urlencoded',
          'X-Requested-With': 'XMLHttpRequest'
        },
        body: 'action=check-users'
      })
      .then(response => {
        if (!response.ok) {
          throw new Error(`HTTP error! status: ${response.status}`);
        }
        return response.text();
      })
      .then(text => {
        try {
          const data = JSON.parse(text);
          return data;
        } catch (e) {
          console.error('Invalid JSON response:', text);
          throw new Error('Server returned invalid JSON');
        }
      })
      .then(data => {
        if (data.isAuthenticate) {
          if (data.status === 'inactive') {
            window.location.href = '../Middleware/auth/403-Forbidden.html';  // ← CORRECT PATH
          } else {
            window.location.href = 'loader.php';  // ← Goes to local loader.php
          }
        } else {
          window.location.href = '../Middleware/auth/Callback.php';  // ← CORRECT PATH - Start OAuth
        }
      })
      .catch(error => {
        console.error('Login check failed:', error);
        // Fallback to OAuth login
        window.location.href = '../Middleware/auth/Callback.php';
      })
      .finally(() => {
        this.textContent = originalText;
        this.disabled = false;
      });
    });
  });
}

// Optional: Check if user is already logged in
// function checkExistingSession() {
//   fetch('../Middleware/auth/ValidateUser.php', { 
//     method: 'POST',
//     headers: {
//       'Content-Type': 'application/x-www-form-urlencoded',
//       'X-Requested-With': 'XMLHttpRequest'
//     },
//     body: 'action=check-users'
//   })
//   .then(response => {
//     if (!response.ok) {
//       throw new Error(`HTTP error! status: ${response.status}`);
//     }
//     return response.text();
//   })
//   .then(text => {
//     try {
//       const data = JSON.parse(text);
//       if (data.isAuthenticate && data.status !== 'inactive') {

//       }
//     } catch (e) {
//       console.error('Invalid JSON in session check:', text.substring(0, 100));
//     }
//   })
//   .catch(error => {
//     console.error('Session check failed:', error);
//   });
// }

// Initialize when DOM is loaded
document.addEventListener("DOMContentLoaded", () => {
  // Optional: Check if user already has an active session
  // checkExistingSession();
  
  // Set up login button handlers
  handleLoginButton();
  
  // Your existing Swiper code
  if (typeof Swiper !== "undefined") {
    const swiper = new Swiper(".swiper", {
      slidesPerView: 1,
      centeredSlides: true,
      spaceBetween: 30,
      loop: false,
      pagination: {
        el: ".swiper-pagination",
        clickable: true,
      },
      navigation: {
        nextEl: ".swiper-button-next",
        prevEl: ".swiper-button-prev",
      },
      breakpoints: {
        640: {
          slidesPerView: 2,
          centeredSlides: false,
        },
        1024: {
          slidesPerView: 3,
          centeredSlides: false,
        },
      },
      on: {
        init: function () {
          updateNavigationState(this)
          updatePaginationNumbers(this)
        },
        slideChange: function () {
          updateNavigationState(this)
          updatePaginationNumbers(this)
        },
      },
    })

    // Function to update navigation buttons state
    function updateNavigationState(swiper) {
      if (swiper.isBeginning) {
        document.querySelector(".swiper-button-prev").classList.add("swiper-button-disabled")
      } else {
        document.querySelector(".swiper-button-prev").classList.remove("swiper-button-disabled")
      }

      if (swiper.isEnd) {
        document.querySelector(".swiper-button-next").classList.add("swiper-button-disabled")
      } else {
        document.querySelector(".swiper-button-next").classList.remove("swiper-button-disabled")
      }
    }
    
    // Function to update pagination numbers
    function updatePaginationNumbers(swiper) {
      const paginationNumbers = document.querySelectorAll(".pagination-number")

      if (paginationNumbers.length > 0) {
        paginationNumbers.forEach((number, index) => {
          if (index === swiper.activeIndex) {
            number.classList.add("active")
          } else {
            number.classList.remove("active")
          }
        })
      }
    }

    // Add click event to pagination numbers
    const paginationNumbers = document.querySelectorAll(".pagination-number")
    if (paginationNumbers.length > 0) {
      paginationNumbers.forEach((number) => {
        number.addEventListener("click", () => {
          const slideIndex = Number.parseInt(number.getAttribute("data-index"), 10)
          swiper.slideTo(slideIndex)
        })
      })
    }
  } else {
    console.error("Swiper is not defined. Make sure Swiper.js is included in your HTML.")
  }

  // Your existing smooth scrolling code
  const anchorLinks = document.querySelectorAll('a[href^="#"]')

  anchorLinks.forEach((link) => {
    link.addEventListener("click", function (e) {
      if (this.getAttribute("href") !== "#") {
        e.preventDefault()

        const targetId = this.getAttribute("href")
        const targetElement = document.querySelector(targetId)

        if (targetElement) {
          const navbarHeight = document.querySelector("header").offsetHeight
          const targetPosition = targetElement.getBoundingClientRect().top + window.pageYOffset - navbarHeight - 20

          window.scrollTo({
            top: targetPosition,
            behavior: "smooth",
          })
        }
      }
    })
  })
})