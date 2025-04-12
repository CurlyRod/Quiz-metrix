<!DOCTYPE html>
<html lang="en">
  <head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>Quizmetrix</title>
    <!-- Linking Font Awesome for icons -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.6.0/css/all.min.css" />
    <!-- Linking Swiper CSS -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/swiper@11/swiper-bundle.min.css" />
    <link rel="stylesheet" href="./index/style.css" />
  </head>
  <body>
    <header>
      <nav class="navbar">
        <a href="#" class="nav-logo">
          <h2 class="logo-text">Quizmetrix</h2>
        </a>
        <ul class="nav-menu">
          <button id="menu-close-button" class="fas fa-times"></button>
          <li class="nav-item">
            <a href="#" class="nav-link">Home</a>
          </li>
          <li class="nav-item">
            <a href="#about" class="nav-link">About</a>
          </li>
          <li class="nav-item">
            <a href="#testimonials" class="nav-link">Study Tools</a>
          </li>
          <li class="nav-item">
            <a href="#menu" class="nav-link">Create</a>
          </li>
          <li class="nav-item">
            <a href="#contact" class="nav-link">Contact</a>
          </li>
          <li class="login">
            <a href="../login/index.html">Login</a>
          </li>
        </ul>
        <button id="menu-open-button" class="fas fa-bars"></button>
      </nav>
    </header>
    <main>
      <!-- Hero section -->
      <section class="hero-section">
        <div class="section-content">
          <div class="hero-details">
            <h3 class="subtitle">"I thought making quizzes was hard… <br>then I found QuizMetrix!"</h3>
            <p class="description">is an intelligent and interactive quiz platform designed to enhance learning and assessment</p>
            <div class="buttons">
              <a href="#" class="button order-now">Join Now</a>
              <a href="#contact" class="button contact-us">Learn More</a>
            </div>
          </div>
          <div class="hero-image-wrapper">
            <img src="./assets/image/Screenshot_2025-03-07_234512-removebg-preview.png" alt="Coffee" class="hero-image" />
          </div>
        </div>
      </section>
      <!-- About section -->
      <section class="about-section" id="about">
        <div class="section-content">
          <div class="about-image-wrapper">
            <img src="./assets/image/2.png" alt="About" class="about-image" />
          </div>
          <div class="about-details">
            <h2 class="section-title">About Us</h2>
            <p class="text">At Coffee House in Berndorf, Germany, we pride ourselves on being a go-to destination for coffee lovers and conversation seekers alike. We're dedicated to providing an exceptional coffee experience in a cozy and inviting atmosphere, where guests can relax, unwind, and enjoy their time in comfort.</p>
            <div class="social-link-list">
              <a href="#" class="social-link"><i class="fa-brands fa-facebook"></i></a>
              <a href="#" class="social-link"><i class="fa-brands fa-instagram"></i></a>
              <a href="#" class="social-link"><i class="fa-brands fa-x-twitter"></i></a>
            </div>
          </div>
        </div>
      </section>
       <!-- Testimonials section -->
       <section class="testimonials-section" id="testimonials">
        <h2 class="section-title">Study Tools</h2>
        <div class="section-content">
          <div class="slider-container swiper">
            <div class="slider-wrapper">
              <ul class="testimonials-list swiper-wrapper">
                <li class="testimonial swiper-slide">
                  <img src="./assets/image/1.png" alt="User" class="user-image" />
                  <h3 class="name">Ivan Gonzales</h3>
                  <i class="feedback">"Arat nga po j "ML""</i>
                </li>
                <li class="testimonial swiper-slide">
                  <img src="./assets/image/1.png" alt="User" class="user-image" />
                  <h3 class="name">Despi Mj</h3>
                  <i class="feedback">"Designer"</i>
                </li>
                <li class="testimonial swiper-slide">
                  <img src="./assets/image/1.png" alt="User" class="user-image" />
                  <h3 class="name">Brandon Diaz</h3>
                  <i class="feedback">"Best in thesis"</i>
                </li>
                <li class="testimonial swiper-slide">
                  <img src="./assets/image/1.png" alt="User" class="user-image" />
                  <h3 class="name">Mark Paningbatan</h3>
                  <i class="feedback">"Best in sampa"</i>
                </li>
                <li class="testimonial swiper-slide">
                  <img src="./assets/image/1.png" alt="User" class="user-image" />
                  <h3 class="name">Unknown</h3>
                  <i class="feedback">"Best in ewan"</i>
                </li>
              </ul>
              <div class="swiper-pagination"></div>
              <div class="swiper-slide-button swiper-button-prev"></div>
              <div class="swiper-slide-button swiper-button-next"></div>
            </div>
          </div>
        </div>
      </section>
      <!-- Menu section -->
      <section class="menu-section" id="menu">
        <h2 class="section-title">Create</h2>
        <div class="section-content">
          <ul class="menu-list">
            <li class="menu-item">
              <img src="./assets/image/home.png" alt="Hot Beverages" class="menu-image" />
              <div class="menu-details">
                <h3 class="name">LABEL</h3>
                <p class="text">label</p>
              </div>
            </li>
            <li class="menu-item">
              <img src="./assets/image/home.png" alt="Cold Beverages" class="menu-image" />
              <div class="menu-details">
                <h3 class="name">LABEL</h3>
                <p class="text">label</p>
              </div>
            </li>
            <li class="menu-item">
              <img src="./assets/image/home.png" alt="Refreshment" class="menu-image" />
              <div class="menu-details">
                <h3 class="name">LABEL</h3>
                <p class="text">label</p>
              </div>
            </li>
          </ul>
        </div>
      </section>
     
      <section class="contact-section" id="contact">
        <h2 class="section-title">Contact Us</h2>
        <div class="section-content">
          <ul class="contact-info-list">
            <li class="contact-info">
              <i class="fa-solid fa-location-crosshairs"></i>
              <p>Sti Academic Center Alabang</p>
            </li>
            <li class="contact-info">
              <i class="fa-regular fa-envelope"></i>
              <p>quizmetrix@gmail.com</p>
            </li>
            <li class="contact-info">
              <i class="fa-solid fa-phone"></i>
              <p>(123) 456-78909</p>
            </li>
            <li class="contact-info">
              <i class="fa-regular fa-clock"></i>
              <p>Monday - Friday: 9:00 AM - 5:00 PM</p>
            </li>
            <li class="contact-info">
              <i class="fa-regular fa-clock"></i>
              <p>Saturday: 10:00 AM - 3:00 PM</p>
            </li>
            <li class="contact-info">
              <i class="fa-solid fa-globe"></i>
              <p>www.codingnepalweb.com</p>
            </li>
          </ul>
          <form action="#" class="contact-form">
            <input type="text" placeholder="Your name" class="form-input" required />
            <input type="email" placeholder="Your email" class="form-input" required />
            <textarea placeholder="Your message" class="form-input" required></textarea>
            <button type="submit" class="button submit-button">Submit</button>
          </form>
        </div>
      </section>
      <!-- Footer section -->
      <footer class="footer-section">
        <div class="section-content">
          <p class="copyright-text">© 2025 QuizMetrix</p>
          <div class="social-link-list">
            <a href="#" class="social-link"><i class="fa-brands fa-facebook"></i></a>
            <a href="#" class="social-link"><i class="fa-brands fa-instagram"></i></a>
            <a href="#" class="social-link"><i class="fa-brands fa-x-twitter"></i></a>
          </div>
          <p class="policy-text">
            <a href="#" class="policy-link">Privacy policy</a>
          </p>
        </div>
      </footer>
    </main>
    <!-- Linking Swiper script -->
    <script src="https://cdn.jsdelivr.net/npm/swiper@11/swiper-bundle.min.js"></script>
    <!-- Linking custom script -->
    <script src="./index/script.js"></script>
  </body>
</html>