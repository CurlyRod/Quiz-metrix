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
    <link rel="icon" type="image/x-icon" href="../assets/img/logo/apple-touch-icon.png">

  </head>
  <body>
     <?php include 'login/login-modal.php';?>
    <header>
      <nav class="navbar">
        <a href="#" class="nav-logo">
          <img class="icon" src="../assets/img/logo/apple-touch-icon.png" alt="Quizmetrix Logo" style="height:43px; width:43px;"><h2 class="logo-text">Quizmetrix</h2>
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
            <div class="hero-badge">
              <span class="badge-icon">✨</span>
              <span class="badge-text">Introducing Quizmetrix</span>
            </div>
            <h1 class="hero-title">Master Your Learning with Interactive Quizzes</h1>
            <p class="hero-description">A quiz web-platform designed to enhance learning and assessment. Create and ace every quiz with study tools.</p>
            <div class="buttons">
              <button type="button" 
                    class="button button-primary" id="btnAddUser" data-bs-toggle="modal" data-bs-target="#loginUser">
                    Start Learning Now
                </button>
              <a href="#tools" class="button button-secondary">Explore Tools</a>
            </div>
          </div>
        </div>
      </section>

      <!-- About section -->
      <section class="about-section" id="about">
        <div class="section-content">
          <div class="about-details">
            <div class="about-header">
              <h2 class="section-title">About Quizmetrix</h2>
              <p class="section-subtitle">Revolutionizing the way students learn and assess their knowledge</p>
            </div>
            <p class="about-text">Welcome to Quizmetrix, your comprehensive learning companion designed to help students excel through interactive quizzes and assessments. Whether you're preparing for critical exams, reviewing challenging lessons, or testing your knowledge on new topics, Quizmetrix makes your learning journey more effective and engaging.</p>
            
            <div class="about-features">
              <div class="feature-item">
                <div class="feature-icon">
                  <i class="fas fa-brain"></i>
                </div>
                <div>
                  <h3>Smart Learning</h3>
                  <p>Study Tools that adapt to your learning style</p>
                </div>
              </div>
              <div class="feature-item">
                <div class="feature-icon">
                  <i class="fas fa-chart-line"></i>
                </div>
                <div>
                  <h3>Track Progress</h3>
                  <p>Real-time analytics of your performance</p>
                </div>
              </div>
              <div class="feature-item">
                <div class="feature-icon">
                  <i class="fa-solid fa-file"></i>
                </div>
                <div>
                  <h3>Study Materials</h3>
                  <p>Upload and Access your files</p>
                </div>
              </div>
            </div>
          </div>
        </div>
      </section>
        
      <!-- Study Tools section -->
      <section class="tools-section" id="tools">
        <div class="section-content">
          <div class="tools-header">
            <h2 class="section-title">Study Tools</h2>
            <p class="section-subtitle">Everything you need to succeed in your studies</p>
          </div>
          
          <div class="swiper-container">
            <div class="swiper">
              <div class="swiper-wrapper">
                <div class="tool-card swiper-slide">
                  <div class="tool-icon">
                    <i class="fas fa-file-alt"></i>
                  </div>
                  <h3>Quizzes</h3>
                  <p>Engage with quizzes that adapt to your learning style</p>
                </div>
                <div class="tool-card swiper-slide">
                  <div class="tool-icon">
                    <i class="fas fa-layer-group"></i>
                  </div>
                  <h3>Flashcards</h3>
                  <p>Create and study with flashcards for better knowledge retention and recall</p>
                </div>
                <div class="tool-card swiper-slide">
                  <div class="tool-icon">
                    <i class="fas fa-sticky-note"></i>
                  </div>
                  <h3>Notes</h3>
                  <p>Organize your study materials with integrated notes</p>
                </div>
                <div class="tool-card swiper-slide">
                  <div class="tool-icon">
                    <i class="fas fa-chart-line"></i>
                  </div>
                  <h3>Progress Tracking</h3>
                  <p>Monitor your improvement with analytics</p>
                </div>
                <div class="tool-card swiper-slide">
                  <div class="tool-icon">
                    <i class="fas fa-hourglass-end"></i>
                  </div>
                  <h3>Timed Assessments</h3>
                  <p>Practice under exam conditions with timed quizzes to boost confidence</p>
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
