<!-- NAVBAR -->
<section id="content">
	<!-- NAVBAR -->
	<nav>
		<i class='bx bx-menu toggle-sidebar'></i>
		<form action="#">
			
		</form> 
		<h6><?php 
					echo str_replace(" (Student)", "", $_SESSION['USER_NAME']); 
			?></h6>
		<div class="profile">
			<img src="../../assets/img/avatars/avatar_2.png" alt="">
			<ul class="profile-link">
				<li style="display: flex; align-items: center; gap: 10px; margin-bottom: 5px;">
					<img src="../../assets/img/avatars/avatar_2.png" alt="" style="width: 40px; height: 40px; border-radius: 50%; object-fit: cover;">
					<div>
						<div class="nav-link" style="font-weight: bold;"><?php echo str_replace(" (Student)", "", $_SESSION['USER_NAME']); ?></div>
						<div class="nav-link" style="font-size: 11px; color: #666;"><?php echo $_SESSION['USER_EMAIL'];?></div>
					</div>
				</li>
				<li><hr style="margin: 10px 0; border-color: black;"></li>
				<li><a class="nav-link" href="../../shared-student/logout.php"><i class='bx bx-log-out-circle icon'></i>Logout</a></li>
			</ul>
		</div>
	</nav>
	<!-- NAVBAR -->