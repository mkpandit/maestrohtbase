<!DOCTYPE html>
<html>
<head>
<title>Score Enterprise Ultimate</title>
<meta http-equiv="content-type" content="text/html; charset=UTF-8">
<script type="text/javascript" src="{baseurl}js/jquery-2.1.4.min.js"></script>
<!-- <script type="text/javascript" src="{baseurl}js/cloud.js"></script> -->
<link type="text/css" href="{baseurl}css/vender/tether/tether.min.css" rel="stylesheet">
<link type="text/css" href="{baseurl}css/vender/bootstrap/bootstrap.min.css" rel="stylesheet">
<link type="text/css" href="{baseurl}css/vender/bootstrap/bootstrap.css" rel="stylesheet">
<link type="text/css" href="{baseurl}/css/fontawesome/css/font-awesome.min.css" rel="stylesheet">
<link href="https://fonts.googleapis.com/css?family=Open+Sans" rel="stylesheet">
<link href="https://fonts.googleapis.com/css?family=PT+Sans:400,400i,700,700i&amp;subset=cyrillic" rel="stylesheet">
<link rel="icon" href="{baseurl}img/favicon.ico" type="image/x-icon">
<link rel="shortcut icon" href="{baseurl}img/favicon.ico" type="image/x-icon">
<script type="text/javascript" src="{baseurl}js/login-interface.js"></script>
<script type="text/javascript" src="{baseurl}js/cookie.js"></script>
<link type="text/css" href="{baseurl}css/cleanui/core.cleanui.css" rel="stylesheet">
<link type="text/css" href="{baseurl}css/cleanui/pages.cleanui.css" rel="stylesheet">
<link type="text/css" href="{baseurl}css/modal.css" rel="stylesheet">
<link type="text/css" href="{baseurl}css/normalize.css" rel="stylesheet">
<style>
#page {
	text-align: center;
	min-height: 460px;
	padding-top: 100px;
}
html, body, #page {
	height: 100%;
}
#page h1 {
	font-size: 36px;
	font-family: "Open Sans";
}
body {
	padding: 0 !important;
	background: rgb(37,52,63);
	overflow-y: hidden;
}
.score-banner {
	display: block;
	margin-left: auto;
	margin-right: auto;
	width: 114px;
	min-height: 187px;
}
.score-logan, .score-logo {
	display: block;
	float: left;
	margin: 0;
	margin-bottom: 0px;
	width: 114px;
}
.score-logan {
	padding: 0;
}
.score-logan h1, .msgBox, footer {
	color: rgb(255,255,255);
}
footer {
	clear: both;
	position: relative;
	z-index: 10;
	height: 4em;
	margin-top: -4em;
	text-align: right;
}
footer label {
	padding-right: 2em;
}
.login-content {
	position: absolute;
	text-align: left;
	float: left;
	-webkit-transform: scaleY(-1);
	transform: scaleY(-1);
}
div.login-content > div {
	position: relative;
	display: block;
	-webkit-transform: scaleY(-1);
	transform: scaleY(-1);
}
.btn.btn-primary {

	/* font-size: 1.2em;
	padding-top: 0.3em; */
}
.modal-content .modal-footer {
	height:70px;
}

.modal-content .modal-footer .btn {
	height: 100%;
}

.modal-content .modal-footer .btn.btn-primary {
	display: block;
	float: right;
	margin-right: 0px !important;
	width: 100px;
	background: #2c3e50;
	border: 1px solid #2c3e50;
	color: rgb(255,255,255) !important;
	border-radius: 0.3rem;
	box-sizing: border-box; /* add this */
	-moz-box-sizing: border-box; /* Firefox */
	-webkit-box-sizing: border-box; /* Older Webkit browsers */
}


/*fix modal jump issue 
body.modal-open-noscroll
{
	margin-right: 0!important;
	overflow: hidden;
}
.modal-open-noscroll .navbar-fixed-top, .modal-open .navbar-fixed-bottom
{
	margin-right: 0!important;
}
end fix modal jump issue */
</style>
</head>
<body>
<div id="page">
	<div class="score-banner">
		<div class="score-logo">
			<a href="/cloud-fortis"><img src="{baseurl}/img/fortis-logo.png" alt="htvcenter Enterprise Cloud" id="logo-score">
			</a>
		</div>
		<div class="score-logan">
			<h1>SCORE</h1>
		</div>
	</div>
	<div class="login-content">
		{content}
	</div>
	</body>
</div>

<footer>
	<label>&copy; 2017 HTBASE</label>
</footer>
<script type="text/javascript" src="{baseurl}js/vender/tether/tether.min.js"></script>
<script type="text/javascript" src="{baseurl}js/vender/bootstrap/bootstrap.min.js"></script>

	<div id="accountModal" class="modal" data-backdrop="static">
		<div class="modal-content">
			<div class="modal-header">
				<h5 class="modal-title">Create A New Account</h5>
				<button type="button" class="close" data-dismiss="modal" aria-label="Close">
				<span aria-hidden="true">&times;</span>
				</button>
			</div>
			<form action="index.php" method="POST">
				<div class="modal-body">
					<input class="hidden" name="langselect" type="hidden">
					<input class="hidden" name="register_action" value="account" type="hidden">
					<div class="col-lg-12">
						<div class="form-group row">
							<label class="col-md-2 col-form-label" for="cu_name">Account Name</label>
							<div class="col-md-9">
								<input class="form-control" id="cu_name" name="cu_name" placeholder="Account Name" type="text">
							</div>
						</div>
						<div class="form-group row">
							<label class="col-md-2 col-form-label" for="cu_forename">First Name</label>
							<div class="col-md-9">
								<input class="form-control" id="cu_forename" name="cu_forename" placeholder="First Name" type="text">
							</div>
						</div>
						<div class="form-group row">
							<label class="col-md-2 col-form-label" for="cu_lastname">Last Name</label>
							<div class="col-md-9">
								<input class="form-control" id="cu_lastname" name="cu_lastname" placeholder="Last Name" type="text">
							</div>
						</div>
						<div class="form-group row">
							<label class="col-md-2 col-form-label" for="cu_street">Street</label>
							<div class="col-md-9">
								<input class="form-control" id="cu_street" name="cu_street" placeholder="Street" type="text">
							</div>
						</div>
						<div class="form-group row">
							<label class="col-md-2 col-form-label" for="cu_city">City</label>
							<div class="col-md-9">
								<input class="form-control" id="cu_city" name="cu_city" placeholder="City" type="text">
							</div>
						</div>
						<div class="form-group row">
							<label class="col-md-2 col-form-label" for="cu_country">Country</label>
							<div class="col-md-9">
								<input class="form-control" id="cu_country" name="cu_country" placeholder="Country" type="text">
							</div>
						</div>
						<div class="form-group row">
							<label class="col-md-2 col-form-label" for="cu_email">Email</label>
							<div class="col-md-9">
								<input class="form-control" id="cu_email" name="cu_email" placeholder="Email" type="email">
							</div>
						</div>
						<div class="form-group row">
							<label class="col-md-2 col-form-label" for="cu_phone">Phone</label>
							<div class="col-md-9">
								<input class="form-control" id="cu_phone" name="cu_phone" placeholder="Phone" type="phone">
							</div>
						</div>
						<div class="form-group row">
							<label class="col-md-2 col-form-label" for="cu_password">Password</label>
							<div class="col-md-9">
								<div class="input-group">
									<input class="form-control" id="cu_password" name="cu_password" placeholder="Password" type="password">
									 <span class="input-group-addon">
										<i class="fa fa-key"></i>
									</span>
								</div>
							</div>
						</div>
						<div class="form-group row">
							<label class="col-md-2 col-form-label" for="cu_password_repeat">Password (repeat)</label>
							<div class="col-md-9">
								<div class="input-group">
									<input class="form-control" id="cu_password_repeat" name="cu_password_repeat" placeholder="Password" type="password">
									<span class="input-group-addon">
										<i class="fa fa-key"></i>
									</span>
								</div>
							</div>
						</div>
					</div>
				</div>
				<div class="modal-footer">
					<input class="submit btn btn-primary" name="response[submit]" value="Submit" type="submit" data-message="">
					<input class="submit btn btn-secondary" name="response[cancel]" value="Cancel" type="submit" data-message="" data-dismiss="modal">
				</div>
			</form>
		</div>
	</div>

	<div id="activateModal" class="modal" data-backdrop="static">
		<div class="modal-content">
			<div class="modal-header">
				<h5 class="modal-title">Activate Your Account</h5>
				<button type="button" class="close" data-dismiss="modal" aria-label="Close">
				<span aria-hidden="true">&times;</span>
				</button>
			</div>
			<form action="index.php" method="POST">
				<input class="hidden" name="langselect" type="hidden">
				<input class="hidden" name="register_action" value="activate" type="hidden">
				<div class="modal-body">
					<div class="col-lg-12">
						<div class="form-group row">
							<label class="col-md-3 col-form-label" for="cu_token">Activation Token:</label>
							<div class="col-md-8">
								<div class="input-group">
									<input class="form-control" id="cu_token" name="cu_token" placeholder="Token" type="password">
									 <span class="input-group-addon">
										<i class="fa fa-key"></i>
									</span>
								</div>
							</div>
						</div>
					</div>
				</div>
				<div class="modal-footer">
					<input class="submit btn btn-primary" name="response[submit]" value="Submit" type="submit" data-message="">
					<input class="submit btn btn-secondary" name="response[cancel]" value="Cancel" type="submit" data-message="" data-dismiss="modal">
				</div>
			</form>
		</div>
	</div>

	<div id="recoverModal" class="modal" data-backdrop="static">
		<div class="modal-content">
			<div class="modal-header">
				<h5 class="modal-title">Recover Your Password</h5>
				<button type="button" class="close" data-dismiss="modal" aria-label="Close">
				<span aria-hidden="true">&times;</span>
				</button>
			</div>
			<form>
				<input class="hidden" name="langselect" type="hidden">
				<input class="hidden" name="register_action" value="recover" type="hidden">
				<div class="modal-body">
					<div class="col-lg-12">
						<div class="form-group row">
							<label class="col-md-2 col-form-label" for="cu_name">Account Name</label>
							<div class="col-md-9">
								<input class="form-control" id="cloud_user_name" name="cloud_user_name" placeholder="Account Name" type="text">
							</div>
						</div>
						<div class="form-group row">
							<label class="col-md-2 col-form-label" for="cu_email">Email</label>
							<div class="col-md-9">
								<input class="form-control" id="cloud_user_email" name="cloud_user_email" placeholder="Email" type="email">
							</div>
						</div>
					</div> 
				</div>
				<div class="modal-footer">
					<input class="submit btn btn-primary" name="response[submit]" value="Submit" type="submit" data-message="">
					<input class="submit btn btn-secondary" name="response[cancel]" value="Cancel" type="submit" data-message="" data-dismiss="modal">
				</div>
			</form>
		</div>
	</div>
</body>
</html>
