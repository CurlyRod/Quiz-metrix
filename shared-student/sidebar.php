<!-- SIDEBAR -->
<section id="sidebar" style="display:flex; flex-direction:column; height:100vh;">
	<a href="../home" class="brand text-decoration-none">
		<img class="icon" src="../../assets/img/logo/apple-touch-icon.png" alt="" style="height:43px">Quizmetrix
	</a>

	<!-- Main menu -->
	<ul class="side-menu" id="side-menu" style="flex:1;">
		<li><a href="../home" class="nav-link <?php echo ($currentPage == 'home') ? 'active' : ''; ?>"><i class='bx bx-home-alt icon'></i>Home</a></li>		
		<li><a href="../study-materials" class="nav-link <?php echo ($currentPage == 'study-materials') ? 'active' : ''; ?>"><i class='bx bx-folder-open icon'></i> File Manager </a></li>
		<li class="divider" data-text="study - tools">Tools</li>
		<li><a href="../flashcard" class="nav-link <?php echo ($currentPage == 'flashcard') ? 'active' : ''; ?>"><i class='bx bx-card icon'></i> Flashcard</a></li>	
		<li><a href="../quiz" class="nav-link <?php echo ($currentPage == 'quiz') ? 'active' : ''; ?>"><i class='bx bx-check-double icon'></i> Quiz</a></li>		
		<li><a href="../notes" class="nav-link <?php echo ($currentPage == 'notes') ? 'active' : ''; ?>"><i class='bx bx-notepad icon'></i> Notes</a></li>	
		<li class="divider" data-text="Shortcut - url's">ShortCut</li>
		<li><a class="nav-link" href="https://elms.sti.edu/" target="_blank"><img class="icon" src="../../assets/img/icons/brands/STI.png" style="width:10px; border-radius: 180px">STI ELMS</a></li>
		<li><a class="nav-link" href="https://one.sti.edu/" target="_blank"><img class="icon" src="../../assets/img/icons/brands/ONESTI.png" style="width:10px; border-radius: 180px">ONE STI</a></li>
	</ul>

	
	<ul class="side-menu">
		<li><a href="../recyclebin" class="nav-link <?php echo ($currentPage == 'recyclebin') ? 'active' : ''; ?>"><i class='bx bx-trash icon'></i> Recycle Bin</a></li>
	</ul>
</section>
<!-- SIDEBAR -->
