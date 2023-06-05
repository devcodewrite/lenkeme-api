
<!DOCTYPE html>
<html lang="en">

<!-- Mirrored from html.truelysell.com/template3/provider-subscription.html by HTTrack Website Copier/3.x [XR&CO'2014], Mon, 05 Jun 2023 21:58:44 GMT -->
<head>
	<meta charset="utf-8">
	<meta name="viewport" content="width=device-width, initial-scale=1.0, user-scalable=0">
	<title>Truelysell | Template</title>

	<!-- Favicon -->
	<link rel="shortcut icon" href="<?=base_url('assets/img/favicon.png'); ?>"> 
	
	<!-- Bootstrap CSS -->
	<link rel="stylesheet" href="<?=base_url('assets/css/bootstrap.min.css'); ?>">

	<!-- Fontawesome CSS -->
	<link rel="stylesheet" href="<?=base_url('assets/plugins/fontawesome/css/fontawesome.min.css'); ?>">
	<link rel="stylesheet" href="<?=base_url('assets/plugins/fontawesome/css/all.min.css'); ?>">

	<!-- Fearther CSS -->
	<link rel="stylesheet" href="<?=base_url('assets/css/feather.css'); ?>">
	
	<!-- select CSS -->
	<link rel="stylesheet" href="<?=base_url('assets/plugins/select2/css/select2.min.css'); ?>">

    <!-- Datetimepicker CSS -->
    <link rel="stylesheet" href="<?=base_url('assets/css/bootstrap-datetimepicker.min.css'); ?>">
		
	<!-- Datatables CSS -->
	<link rel="stylesheet" href="<?=base_url('assets/plugins/datatables/datatables.min.css'); ?>">

	<!-- Main CSS -->
	<link rel="stylesheet" href="<?=base_url('assets/css/style.css'); ?>">
	
</head>

<body class="provider-body">
	<div class="main-wrapper">
		<div class="page-wrapper">
			<div class="content container-fluid">
				<div class="row">	
					<div class="col-md-6">	
						<div class="widget-title">
							<h4>Subscription</h4>
						</div>
					</div>
				</div>
									
				<!-- Subscription -->
				<div class="row provider-price">
					<div class="col-md-12">
						<div class="choose-title text-center">
							<h6>Choose Plan</h6>
							<div class="status-toggle status-tog d-inline-flex align-items-center">
								Yearly
								<input type="checkbox" id="status_1" class="check" checked>
								<label for="status_1" class="checktoggle">checkbox</label>
								Monthly
							</div>
						</div>
					</div>
					
					<!-- Price List -->
					<div class="col-md-4 d-flex">
						<div class="price-card flex-fill">
							<div class="price-head">
								<div class="price-level">
									<h6>Basic</h6>
								</div>
								<h1>$50 <span>/ month</span></h1>
							</div>	
							<div class="price-body">
								<ul>
									<li class="active">10 Services</li>
									<li class="active">10 Stafff</li>
									<li class="active">100 Appointments</li>
									<li class="inactive">Gallery</li>
									<li class="inactive">Addition Services</li>
								</ul>
								<div class="text-center">
									<a href="#" class="btn btn-choose">Choose Plan <i class="feather-arrow-right-circle"></i></a>
								</div>							
							</div>							
						</div>							
					</div>
					<!-- /Price List -->
					
					<!-- Price List -->
					<div class="col-md-4 d-flex">
						<div class="price-card active flex-fill">
							<div class="price-head">
								<div class="price-level">
									<h6>Business</h6>
									<span class="badge-success">Active</span>
								</div>
								<h1>$200 <span>/ month</span></h1>
							</div>	
							<div class="price-body">
								<ul>
									<li class="active">20 Services</li>
									<li class="active">20 Stafff</li>
									<li class="active">Unlimited Appointments</li>
									<li class="active">Gallery</li>
									<li class="inactive">Addition Services</li>
								</ul>
								<div class="text-center">
									<a href="#" class="btn btn-choose">Choose Plan <i class="feather-arrow-right-circle"></i></a>
								</div>							
							</div>							
						</div>							
					</div>
					<!-- /Price List -->
					
					<!-- Price List -->
					<div class="col-md-4 d-flex">
						<div class="price-card flex-fill">
							<div class="price-head">
								<div class="price-level">
									<h6>Enterprise</h6>
								</div>
								<h1>$450 <span>/ month</span></h1>
							</div>	
							<div class="price-body">
								<ul>
									<li class="active">Unlimited Services</li>
									<li class="active">Unlimited Stafff</li>
									<li class="active">Unlimited Appointments</li>
									<li class="active">Gallery</li>
									<li class="active">Addition Services</li>
								</ul>
								<div class="text-center">
									<a href="#" class="btn btn-choose">Choose Plan <i class="feather-arrow-right-circle"></i></a>
								</div>							
							</div>							
						</div>							
					</div>
					<!-- /Price List -->
				
				</div>
				<!-- /Subscription -->
				
			</div>
		</div>
		<!-- Delete Account -->
		<div class="modal fade custom-modal" id="del-account">
			<div class="modal-dialog modal-dialog-centered">
				<div class="modal-content">
					<div class="modal-header border-bottom-0 justify-content-between">
						<h5 class="modal-title">Delete Account</h5>
						<button type="button" class="close-btn" data-bs-dismiss="modal" aria-label="Close"><i class="feather-x"></i></button>
					</div>
					<div class="modal-body pt-0">
						<div class="write-review">
							<form action="https://html.truelysell.com/template3/login.html">
								<p>Are you sureyou want to delete This Account? To delete your account, Type your password.</p>
								<div class="form-group">
									<label class="col-form-label">Password</label>
									<div class="pass-group">
										<input type="password" class="form-control pass-input" placeholder="*************">
										<span class="toggle-password feather-eye"></span>
									</div>
								</div>
								<div class="modal-submit text-end">
									<a href="#" class="btn btn-secondary me-2" data-bs-dismiss="modal">Cancel</a>
									<button type="submit" class="btn btn-danger">Delete Account</button>
								</div>
							</form>
						</div>
					</div>
				</div>
			</div>
		</div>
		<!-- /Delete Account -->
		
		<!-- Cursor -->
		<div class="mouse-cursor cursor-outer"></div>
		<div class="mouse-cursor cursor-inner"></div>
		<!-- /Cursor -->
		
	</div>
	
	<!-- jQuery -->
	<script data-cfasync="false" src="https://html.truelysell.com/cdn-cgi/scripts/5c5dd728/cloudflare-static/email-decode.min.js') ?>"></script>
    <script src="<?=base_url('assets/js/jquery-3.6.1.min.js') ?>"></script>

	<!-- Bootstrap Core JS -->
	<script src="<?=base_url('assets/js/bootstrap.bundle.min.js') ?>"></script>

	<!-- Fearther JS -->
	<script src="<?=base_url('assets/js/feather.min.js') ?>"></script>
	
	<!-- select JS -->
	<script src="<?=base_url('assets/plugins/select2/js/select2.min.js') ?>"></script>

    <!-- Datetimepicker JS -->
    <script src="<?=base_url('assets/js/moment.min.js') ?>"></script>
    <script src="<?=base_url('assets/js/bootstrap-datetimepicker.min.js') ?>"></script>

	<!-- Slimscroll JS -->
	<script src="<?=base_url('assets/plugins/slimscroll/jquery.slimscroll.min.js') ?>"></script>
		
	<!-- Datatables JS -->
	<script src="<?=base_url('assets/plugins/datatables/jquery.dataTables.min.js') ?>"></script>
	<script src="<?=base_url('assets/plugins/datatables/datatables.min.js') ?>"></script>		

	<!-- Custom JS -->
	<script src="<?=base_url('assets/js/script.js') ?>"></script>
	
</body>

<!-- Mirrored from html.truelysell.com/template3/provider-subscription.html by HTTrack Website Copier/3.x [XR&CO'2014], Mon, 05 Jun 2023 22:01:13 GMT -->
</html>