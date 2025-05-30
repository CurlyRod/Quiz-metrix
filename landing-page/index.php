<?php 
    session_start();
      require '../Middleware/Class/Config.php'; 
      use Middleware\Class\Config;    

      echo (new Config())->VendorConfig(); 
      $state = bin2hex(random_bytes(16));
      $_SESSION['oauth_state'] = $state;

      $params = [
          'client_id'     => CLIENT_ID,
          'response_type' => 'code',
          'redirect_uri'  => REDIRECT_URI,
          'response_mode' => 'query',
          'scope'         => SCOPES,
          'state'         => $state,
      ];
      
      $authUrl = AUTHORIZE_ENDPOINT . '?' . http_build_query($params);
      
?>

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
    <link rel="stylesheet" href="../vendor/bootstrap/bootstrap.min.css">
    <link rel="stylesheet" href="landing-page.css" />
    <link rel="icon" type="image/x-icon" href="../../assets/img/logo/apple-touch-icon.png">


  </head>
  <body>
     <?php include 'login/login-modal.php';?>
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
            <a href="#tools" class="nav-link">Study Tools</a>
          </li>
          <button type="button" 
                    class="btn" id="btnAddUser" data-bs-toggle="modal" data-bs-target="#loginUser">
                    Login
                </button>
        </ul>
        <button id="menu-open-button" class="fas fa-bars">
          
        </button>
      </nav>
    </header>
    <main>
      <!-- Hero section -->
      <section class="hero-section">
        <div class="section-content">
          <div class="hero-details">
            <h3 class="subtitle">"I thought making quizzes was hard… <br>then I found QuizMetrix!"</h3>
            <p class="description">An interactive quiz platform designed to enhance learning and assessment</p>
            <div class="buttons">
              <a href="#" class="button order-now">Try Now</a>
              <a href="#contact" class="button contact-us">Learn More</a>
            </div>
          </div>
          <div class="hero-image-wrapper">
            <img src="../assets/DrawKit/DrawKit - Education Illustration Pack/PNG/3 SCENE.png" alt="Coffee" class="hero-image" />
          </div>
        </div>
      </section>
      <!-- About section -->
      <section class="about-section" id="about">
        <div class="section-content">
          <div class="about-image-wrapper">
            <img src="../assets/DrawKit/DrawKit - Education Illustration Pack/PNG/7 SCENE.png" alt="About" class="about-image" />
          </div>
          <div class="about-details">
            <h2 class="section-title">About Us</h2>
            <p class="text">Welcome to Quizmetrix, designed to help students like you excel in your studies through interactive quizzes and assessments! Whether you are preparing for exams, reviewing lessons, or just testing your knowledge, Quizmetrix is here to make your learning journey easier and more effective.</p>
            <div class="social-link-list">
              <!-- <a href="#" class="social-link"><i class="fa-brands fa-facebook"></i></a>
              <a href="#" class="social-link"><i class="fa-brands fa-instagram"></i></a> -->
              <!-- <a href="#" class="social-link"><i class="fa-brands fa-x-twitter"></i></a> -->
            </div>
          </div>
        </div>
      </section>
        
      <!-- Study Tools section -->
      <section class="tools-section" id="tools">
        <h2 class="section-title">Study Tools</h2>
        <div class="section-content">
          <div class="swiper-container">
            <div class="swiper">
              <div class="swiper-wrapper">
                <div class="tool-card swiper-slide">
                  <div class="tool-icon">
                    <i class="fas fa-book-open"></i>
                  </div>
                  <h3>Interactive Quizzes</h3>
                  <p>Engage with dynamic quizzes that adapt to your learning style</p>
                </div>
                <div class="tool-card swiper-slide">
                  <div class="tool-icon">
                    <i class="fas fa-chart-line"></i>
                  </div>
                  <h3>Progress Tracking</h3>
                  <p>Monitor your improvement with detailed analytics</p>
                </div>
                <div class="tool-card swiper-slide">
                  <div class="tool-icon">
                    <i class="fas fa-users"></i>
                  </div>
                  <h3>Collaborative Learning</h3>
                  <p>Share quizzes and study with friends in real-time</p>
                </div>
                <div class="tool-card swiper-slide">
                  <div class="tool-icon">
                    <i class="fas fa-brain"></i>
                  </div>
                  <h3>Flashcard</h3>
                  <p>High knowlede and retention high grades</p>
                </div>
                <div class="tool-card swiper-slide">
                  <div class="tool-icon">
                    <i class="fas fa-clock"></i>
                  </div>
                  <h3>Timed Assessments</h3>
                  <p>Practice under exam conditions with timed quizzes</p>
                </div>
              </div>
              <div class="swiper-pagination"></div>
              <div class="swiper-button-prev"></div>
              <div class="swiper-button-next"></div>
            </div>

          </div>
        </div>
      </section>

      <!-- Footer -->
      <footer class="footer-section">
        <div class="section-content">
          <div class="footer-logo">
            <h2>Quizmetrix</h2>
            <p>Elevate your learning experience</p>
          </div>
          <div class="footer-links">
            <div class="footer-column">
              <h3>Quick Links</h3>
              <ul>
                <li><a href="#">Home</a></li>
                <li><a href="#about">About</a></li>
                <li><a href="#tools">Study Tools</a></li>
              </ul>
            </div>
          </div>
        </div>
        <div class="copyright">
          <p>&copy; 2025 Quizmetrix. All rights reserved.</p>
        </div>
      </footer>
    </main>
      
    <!-- Linking Swiper script -->
    <script src="https://cdn.jsdelivr.net/npm/swiper@11/swiper-bundle.min.js"></script>
    <!-- Linking custom script -->
    <script src="landing-page.js"></script>
    <script src="../vendor/bootstrap/bootstrap.bundle.min.js"></script>
  </body>
</html>
