			<nav id="sidebar" class="sidebar">
				<div class="sidebar-content js-simplebar">
					<a class="sidebar-brand" href="index.php">
          				<span class="align-middle">Duck'z Auto Detailing & Car Wash</span>
        			</a>
				<ul class="sidebar-nav">

					<li class="sidebar-header">
						Dashboards
					</li>

				<li class="sidebar-item <?php if(basename($_SERVER['PHP_SELF']) == 'dashboard.php'){echo 'active';} ?>">
					<a class="sidebar-link" href="dashboard.php">
						<i class="align-middle" data-feather="sliders"></i>
							<span class="align-middle">Main Dashboard</span>
					</a>
				</li>
				<li class="sidebar-item <?php if(basename($_SERVER['PHP_SELF']) == 'manage_services.php'){echo 'active';} ?>" >
					<a class="sidebar-link" href="manage_services.php">
              			<i class="align-middle" data-feather="check-circle"></i> 
							<span class="align-middle">Manage Service</span>
            		</a>

					</li>
					<!-- <li class="sidebar-item <?php if(basename($_SERVER['PHP_SELF']) == 'manage_reference.php'){echo 'active';} ?>" >
					<a class="sidebar-link" href="manage_reference.php">
              			<i class="align-middle" data-feather="check-circle"></i> 
							<span class="align-middle">Manage Reference</span>
            		</a>

					</li> -->
					<li class="sidebar-header">
						Status
					</li>

				<li class="sidebar-item <?php if(basename($_SERVER['PHP_SELF']) == 'manage_user.php'){echo 'active';} ?>">
					<a class="sidebar-link" href="manage_user.php">
              			<i class="align-middle" ></i> 
							<span class="align-middle">Manage User</span>
            		</a>
				</li>
				<li class="sidebar-item <?php if(basename($_SERVER['PHP_SELF']) == 'manage_bookings.php'){echo 'active';} ?>">
					<a class="sidebar-link" href="manage_bookings.php">
              			<i class="align-middle"></i> 
							<span class="align-middle">Manage Bookings</span>
            		</a>
				</li>
					
					<li class="sidebar-header">
						Reports
					</li>

					<li class="sidebar-item <?php if(basename($_SERVER['PHP_SELF']) == 'manage_reports.php'){echo 'active';} ?>">
					<a class="sidebar-link" href="manage_reports.php">
              			<i class="align-middle"></i> 
							<span class="align-middle">Manage Reports</span>
            		</a>
				</li>
				</li>
				<li class="sidebar-item <?php if(basename($_SERVER['PHP_SELF']) == 'manage_feedback.php'){echo 'active';} ?>">
					<a class="sidebar-link" href="manage_feedback.php">
              			<i class="align-middle" data-feather="bar-chart-2"></i> 
							<span class="align-middle">Manage Feedback</span>
            		</a>
				</li>
				</ul>
				<!-- <div class="sidebar-cta">
					<div class="sidebar-cta-content">
						<strong class="d-inline-block mb-2">Duck'z Detailing & Car Wash<small> v1.3.8</small></strong>
						<div class="mb-3 text-sm">
							All Rights Reserved
						</div>
						<a href="https://github.com/Macabulos" target="_blank" class="btn btn-outline-primary btn-block">Follow me on Github</a>
					</div>
				</div> -->
			</div>
		</nav>
		