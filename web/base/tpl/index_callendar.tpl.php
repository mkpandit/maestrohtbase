<!DOCTYPE html>
<html lang="en">

<head>
	<meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
	<title>Maestro Enterprise Ultimate</title>
	<meta http-equiv="content-type" content="text/html; charset=utf-8">

	<link rel="icon" href="{baseurl}/img/favicon.ico" type="image/x-icon">
	<link rel="shortcut icon" href="{baseurl}/img/favicon.ico" type="image/x-icon">
	<script>
	var callendar = true;
	</script>

	<!--STYLESHEET-->
	<!--=================================================-->

	<!--Open Sans Font [ OPTIONAL ] -->
 	<link href="http://fonts.googleapis.com/css?family=Open+Sans:300,400,600,700&amp;subset=latin" rel="stylesheet">


	<!--Bootstrap Stylesheet [ REQUIRED ]-->
	<link href="{baseurl}/css/bootstrap.min.css" rel="stylesheet">


	<!--Nifty Stylesheet [ REQUIRED ]-->
	<link href="{baseurl}/css/nifty.css" rel="stylesheet">
	<link href="{baseurl}/css/default.css" rel="stylesheet">
	<link href="{baseurl}/css/carousel.css" rel="stylesheet">
	
	<!--Font Awesome [ OPTIONAL ]-->
	<link href="{baseurl}/css/fontawesome/css/font-awesome.css" rel="stylesheet">


	<!--Animate.css [ OPTIONAL ]-->
	<link href="{baseurl}/designplugins/animate-css/animate.min.css" rel="stylesheet">

	<!--Full Calendar [ OPTIONAL ]-->
	<link href="{baseurl}/designplugins/fullcalendar/fullcalendar.css" rel="stylesheet">




	<!--Morris.js [ OPTIONAL ]-->
	<link href="{baseurl}/designplugins/morris-js/morris.min.css" rel="stylesheet">
	<!--Switchery [ OPTIONAL ]-->
	<link href="{baseurl}/designplugins/switchery/switchery.min.css" rel="stylesheet">


	<!--Bootstrap Select [ OPTIONAL ]-->
	<link href="{baseurl}/designplugins/bootstrap-select/bootstrap-select.min.css" rel="stylesheet">


	<!--Demo script [ DEMONSTRATION ]-->
	<link href="{baseurl}/css/demo/nifty-demo.min.css" rel="stylesheet">

{style}

	
	<script src="{baseurl}/js/jquery-2.1.4.min.js"></script>

	
	

	
		<!--Morris.js [ OPTIONAL ]-->
	<script src="{baseurl}/designplugins/morris-js/morris.min.js"></script>
	<script src="{baseurl}/designplugins/morris-js/raphael-js/raphael.min.js"></script>
	
    
	<script src="{baseurl}/js/helpers.js" type="text/javascript"></script>
	<script src="{baseurl}/js/uiuiui.js" type="text/javascript"></script>



	<!--SCRIPT-->
	<!--=================================================-->

	<!--Page Load Progress Bar [ OPTIONAL ]-->
	<link href="{baseurl}/designplugins/pace/pace.min.css" rel="stylesheet">
	<script src="{baseurl}/designplugins/pace/pace.min.js"></script>


	
	<!--

	REQUIRED
	You must include this in your project.

	RECOMMENDED
	This category must be included but you may modify which designplugins or components which should be included in your project.

	OPTIONAL
	Optional designplugins. You may choose whether to include it in your project or not.

	DEMONSTRATION
	This is to be removed, used for demonstration purposes only. This category must not be included in your project.

	SAMPLE
	Some script samples which explain how to initialize designplugins or components. This category should not be included in your project.


	Detailed information and more samples can be found in the document.

	-->

	<script type="text/javascript">
$(document).ready(function(){
	$("#popupInfoClose").click(function(){
		disablePopup();
	});
	$("#backgroundPopup").click(function(){
		disablePopup();
	});
});

function set_language() {
	var username = $("#username").val();
	var selected_lang = $("#Language_select").val();

	$.ajax({
		url: "api.php?action=set_language&user=" + username + "&lang=" + selected_lang,
		cache: false,
		async: false,
		dataType: "text",
		success: function(response) {
			window.location.reload();
		}
	});

}

var popupStatus = 0;
function loadPopup(){
	if(popupStatus==0){
		$("#backgroundPopup").css({ "opacity": "0.3" });
		$("#backgroundPopup").fadeIn();
		$("#popupInfo").fadeIn();
		popupStatus = 1;
	}
}

function disablePopup(){
	if(popupStatus==1){
		$("#backgroundPopup").fadeOut();
		$("#popupInfo").fadeOut();
		popupStatus = 0;
	}
}

function centerPopup(){
	var windowWidth = document.documentElement.clientWidth;
	var windowHeight = document.documentElement.clientHeight;
	var popupHeight = $("#popupInfo").height();
	var popupWidth = $("#popupInfo").width();
	$("#popupInfo").css({
		"position": "absolute",
		"top": "120px",
		"left": "400px" 
	});
	$("#backgroundPopup").css({
		"height": windowHeight
	});
}


function openPopup() {
	centerPopup();
	loadPopup();
	get_info_box();
}

function get_info_box() {
	$.ajax({
		url: "api.php?action=get_info_box",
		cache: false,
		async: false,
		dataType: "text",
		success: function(response) {

			$("#infoArea").html(response);
		}
	});
}
</script>
		

</head>

<!--TIPS-->
<!--You may remove all ID or Class names which contain "demo-", they are only used for demonstration. -->

<body>



	<div id="container" class="effect mainnav-lg">
		
		<!--NAVBAR-->
		<!--===================================================-->
		<header id="navbar">
			<div id="navbar-container" class="boxed">

				<!--Brand logo & name-->
				<!--================================-->
				<div class="navbar-header">
					<a href="/htvcenter/base/" class="navbar-brand">
						<img src="{baseurl}/img/logo.png" alt="Nifty Logo" class="brand-icon">
					
					</a>
				</div>
				<!--================================-->
				<!--End brand logo & name-->

{top}

			</div>
		</header>
		<!--===================================================-->
		<!--END NAVBAR-->

		<div class="boxed">

			<!--CONTENT CONTAINER-->
			<!--===================================================-->
			<div id="content-container">
				  <div class="panel">
								<div class="panel-heading">
									<h3 class="panel-title">Calendar</h3>
								</div>
								<div class="panel-body">
					
									<!-- Calendar placeholder-->
									<!-- ============================================ -->
									<div id='demo-calendar'></div>
					
								</div>
							</div>
			</div>
			<!--===================================================-->
			<!--END CONTENT CONTAINER-->


			
					{menu}
				

				
					<!--================================-->
					<!--End menu-->

				
			
			<!--===================================================-->
			<!--END MAIN NAVIGATION-->
			
			<!--ASIDE-->
			<!--===================================================-->
			<aside id="aside-container">
				<div id="aside">
					<div class="nano">
						<div class="nano-content">
							
							<!--Nav tabs-->
							<!--================================-->
							<ul class="nav nav-tabs nav-justified" id="asideul">
								<li class="active first">
									<a href="#demo-asd-tab-1" data-toggle="tab">
										<i class="fa fa-envelope"></i>
										<span class="badge badge-warning">7</span>
									</a>
								</li>
								<li class="second">
									<a href="#demo-asd-tab-2" data-toggle="tab">
										<i class="fa fa-exclamation-triangle"></i>
										<span class="badge badge-danger">7</span>
									</a>
								</li>
								<li class="third">
									<a href="#demo-asd-tab-3" data-toggle="tab">
										<i class="fa fa-bell"></i>
										<span class="badge badge-purple">7</span>
									</a>
								</li>
							</ul>
							<!--================================-->
							<!--End nav tabs-->



							<!-- Tabs Content -->
							<!--================================-->
							<div class="tab-content">

								<!--First tab (Contact list)-->
								<!--~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~-->
								<div class="tab-pane fade in active" id="demo-asd-tab-1">
									<div class="sidebarallink">
										<a href="index.php?base=event"><button class="btn btn-block btn-success mar-top">Go to events page</button></a>
										<button class="btn btn-block btn-danger mar-top">Close events sidebar</button>
									</div>
									<hr>
									<h4 class="pad-hor text-thin">
										<i class="fa fa-bell"></i> Messages:
									</h4>

									<!--Works-->
									<div class="list-group bg-trans" id="sidebarallwarnings">
									</div>

									
									

								</div>
								<!--~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~-->
								<!--End first tab (Contact list)-->


								<!--Second tab (Custom layout)-->
								<!--~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~-->
								<div class="tab-pane fade" id="demo-asd-tab-2">
									<div class="sidebarallink">
										<a href="index.php?base=event"><button class="btn btn-block btn-success mar-top">Go to events page</button></a>
										<button class="btn btn-block btn-danger mar-top">Close events sidebar</button>
									</div>
									<hr>
									<h4 class="pad-hor text-thin">
										<i class="fa fa-exclamation-triangle"></i> Errors:
									</h4>

									<!--Works-->
									<div class="list-group bg-trans" id="sidebarallerrors">
									</div>


								</div>
								<!--End second tab (Custom layout)-->
								<!--~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~-->


								<!--Third tab (Settings)-->
								<!--~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~-->
								<div class="tab-pane fade" id="demo-asd-tab-3">
									
									<div class="sidebarallink">
										<a href="index.php?base=event"><button class="btn btn-block btn-success mar-top">Go to events page</button></a>
										<button class="btn btn-block btn-danger mar-top">Close events sidebar</button>
									</div>
									<hr>
									<h4 class="pad-hor text-thin">
										<i class="fa fa-envelope"></i> All Events:
									</h4>

									<!--Works-->
									<div class="list-group bg-trans" id="sidebarallevents">
									</div>

								</div>
								<!--~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~-->
								<!--Third tab (Settings)-->

							</div>
						</div>
					</div>

				</div>
			</aside>
			<!--===================================================-->
			<!--END ASIDE-->


		</div>

		

		<!-- FOOTER -->
		<!--===================================================-->
		<footer id="footer">

			<!-- Visible when footer positions are fixed -->
			<!-- ~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~ -->
			<div class="show-fixed pull-right">
				<ul class="footer-list list-inline">
					<li>
						<p class="text-sm">SEO Proggres</p>
						<div class="progress progress-sm progress-light-base">
							<div style="width: 80%" class="progress-bar progress-bar-danger"></div>
						</div>
					</li>

					<li>
						<p class="text-sm">Online Tutorial</p>
						<div class="progress progress-sm progress-light-base">
							<div style="width: 80%" class="progress-bar progress-bar-primary"></div>
						</div>
					</li>
					<li>
						<button class="btn btn-sm btn-dark btn-active-success">Checkout</button>
					</li>
				</ul>
			</div>



			<!-- Visible when footer positions are static -->
			<!-- ~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~ -->
			<div class="hide-fixed pull-right pad-rgt">Maestro Enterprise</div>



			<!-- ~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~ -->
			<!-- Remove the class name "show-fixed" and "hide-fixed" to make the content always appears. -->
			<!-- ~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~ -->

			<p class="pad-lft"></p>



		</footer>
		<!--===================================================-->
		<!-- END FOOTER -->


		<!-- SCROLL TOP BUTTON -->
		<!--===================================================-->
		<button id="scroll-top" class="btn"><i class="fa fa-chevron-up"></i></button>
		<!--===================================================-->



	</div>
	<!--===================================================-->
	<!-- END OF CONTAINER -->


	
	
	<!-- SETTINGS - DEMO PURPOSE ONLY -->
	<!--===================================================-->
	<div id="demo-set" class="demo-set">
		<div class="demo-set-body bg-dark">
			<div id="demo-set-alert"></div>
			<div class="demo-set-content clearfix">
				<div class="col-xs-6 col-md-4">
					<h4 class="text-lg mar-btm">Animations</h4>
					<div id="demo-anim" class="mar-btm">
						<label class="form-checkbox form-icon active">
							<input type="checkbox" checked=""> Enable Animations
						</label>
					</div>
					<p>Transition effects</p>
					<select id="demo-ease">
						<option value="effect" selected>ease (Default)</option>
						<option value="easeInQuart">easeInQuart</option>
						<option value="easeOutQuart">easeOutQuart</option>
						<option value="easeInBack">easeInBack</option>
						<option value="easeOutBack">easeOutBack</option>
						<option value="easeInOutBack">easeInOutBack</option>
						<option value="steps">Steps</option>
						<option value="jumping">Jumping</option>
						<option value="rubber">Rubber</option>
					</select>
					<hr class="bord-no">
					<br>
					<h4 class="text-lg mar-btm">Navigation</h4>
					<div class="mar-btm">
						<label id="demo-nav-fixed" class="form-checkbox form-icon">
							<input type="checkbox"> Fixed
						</label>
					</div>
					<label id="demo-nav-coll" class="form-checkbox form-icon">
						<input type="checkbox"> Collapsed
					</label>
					<hr class="bord-no">
					<br>
					<h4 class="text-lg mar-btm">Off Canvas Navigation</h4>
					<select id="demo-nav-offcanvas">
						<option value="none" selected disabled="disabled">-- Select Mode --</option>
						<option value="push">Push</option>
						<option value="slide">Slide in on top</option>
						<option value="reveal">Reveal</option>
					</select>
				</div>
				<div class="col-xs-6 col-md-3">
					<h4 class="text-lg mar-btm">Aside</h4>
					<div class="form-block">
						<label id="demo-asd-vis" class="form-checkbox form-icon">
							<input type="checkbox"> Visible
						</label>
						<label id="demo-asd-fixed" class="form-checkbox form-icon">
							<input type="checkbox"> Fixed
						</label>
						<label id="demo-asd-align" class="form-checkbox form-icon">
							<input type="checkbox"> Aside on the left side
						</label>
						<label id="demo-asd-themes" class="form-checkbox form-icon">
							<input type="checkbox"> Bright Theme
						</label>
					</div>
					<hr class="bord-no">
					<br>
					<h4 class="text-lg mar-btm">Header / Navbar</h4>
					<label id="demo-navbar-fixed" class="form-checkbox form-icon">
						<input type="checkbox"> Fixed
					</label>
					<hr class="bord-no">
					<br>
					<h4 class="text-lg mar-btm">Footer</h4>
					<label id="demo-footer-fixed" class="form-checkbox form-icon">
						<input type="checkbox"> Fixed
					</label>
				</div>
				<div class="col-xs-12 col-md-5">
					<div id="demo-theme">
						<h4 class="text-lg mar-btm">Color Themes</h4>
						<div class="demo-theme-btn">
							<a href="#" class="demo-theme demo-a-light add-tooltip" data-theme="theme-light" data-type="a" data-title="(A). Light">
								<div class="demo-theme-brand"></div>
								<div class="demo-theme-head"></div>
								<div class="demo-theme-nav"></div>
							</a>
							<a href="#" class="demo-theme demo-a-navy add-tooltip" data-theme="theme-navy" data-type="a" data-title="(A). Navy Blue">
								<div class="demo-theme-brand"></div>
								<div class="demo-theme-head"></div>
								<div class="demo-theme-nav"></div>
							</a>
							<a href="#" class="demo-theme demo-a-ocean add-tooltip" data-theme="theme-ocean" data-type="a" data-title="(A). Ocean">
								<div class="demo-theme-brand"></div>
								<div class="demo-theme-head"></div>
								<div class="demo-theme-nav"></div>
							</a>
							<a href="#" class="demo-theme demo-a-lime add-tooltip" data-theme="theme-lime" data-type="a" data-title="(A). Lime">
								<div class="demo-theme-brand"></div>
								<div class="demo-theme-head"></div>
								<div class="demo-theme-nav"></div>
							</a>
							<a href="#" class="demo-theme demo-a-purple add-tooltip" data-theme="theme-purple" data-type="a" data-title="(A). Purple">
								<div class="demo-theme-brand"></div>
								<div class="demo-theme-head"></div>
								<div class="demo-theme-nav"></div>
							</a>
							<a href="#" class="demo-theme demo-a-dust add-tooltip" data-theme="theme-dust" data-type="a" data-title="(A). Dust">
								<div class="demo-theme-brand"></div>
								<div class="demo-theme-head"></div>
								<div class="demo-theme-nav"></div>
							</a>
							<a href="#" class="demo-theme demo-a-mint add-tooltip" data-theme="theme-mint" data-type="a" data-title="(A). Mint">
								<div class="demo-theme-brand"></div>
								<div class="demo-theme-head"></div>
								<div class="demo-theme-nav"></div>
							</a>
							<a href="#" class="demo-theme demo-a-yellow add-tooltip" data-theme="theme-yellow" data-type="a" data-title="(A). Yellow">
								<div class="demo-theme-brand"></div>
								<div class="demo-theme-head"></div>
								<div class="demo-theme-nav"></div>
							</a>
							<a href="#" class="demo-theme demo-a-well-red add-tooltip" data-theme="theme-well-red" data-type="a" data-title="(A). Well Red">
								<div class="demo-theme-brand"></div>
								<div class="demo-theme-head"></div>
								<div class="demo-theme-nav"></div>
							</a>
							<a href="#" class="demo-theme demo-a-coffee add-tooltip" data-theme="theme-coffee" data-type="a" data-title="(A). Coffee">
								<div class="demo-theme-brand"></div>
								<div class="demo-theme-head"></div>
								<div class="demo-theme-nav"></div>
							</a>
							<a href="#" class="demo-theme demo-a-prickly-pear add-tooltip" data-theme="theme-prickly-pear" data-type="a" data-title="(A). Prickly pear">
								<div class="demo-theme-brand"></div>
								<div class="demo-theme-head"></div>
								<div class="demo-theme-nav"></div>
							</a>
							<a href="#" class="demo-theme demo-a-dark add-tooltip" data-theme="theme-dark" data-type="a" data-title="(A). Dark">
								<div class="demo-theme-brand"></div>
								<div class="demo-theme-head"></div>
								<div class="demo-theme-nav"></div>
							</a>
						</div>
						<div class="demo-theme-btn">
							<a href="#" class="demo-theme demo-b-light add-tooltip" data-theme="theme-light" data-type="b" data-title="(B). Light">
								<div class="demo-theme-brand"></div>
								<div class="demo-theme-head"></div>
								<div class="demo-theme-nav"></div>
							</a>
							<a href="#" class="demo-theme demo-b-navy add-tooltip" data-theme="theme-navy" data-type="b" data-title="(B). Navy Blue">
								<div class="demo-theme-brand"></div>
								<div class="demo-theme-head"></div>
								<div class="demo-theme-nav"></div>
							</a>
							<a href="#" class="demo-theme demo-b-ocean add-tooltip" data-theme="theme-ocean" data-type="b" data-title="(B). Ocean">
								<div class="demo-theme-brand"></div>
								<div class="demo-theme-head"></div>
								<div class="demo-theme-nav"></div>
							</a>
							<a href="#" class="demo-theme demo-b-lime add-tooltip" data-theme="theme-lime" data-type="b" data-title="(B). Lime">
								<div class="demo-theme-brand"></div>
								<div class="demo-theme-head"></div>
								<div class="demo-theme-nav"></div>
							</a>
							<a href="#" class="demo-theme demo-b-purple add-tooltip" data-theme="theme-purple" data-type="b" data-title="(B). Purple">
								<div class="demo-theme-brand"></div>
								<div class="demo-theme-head"></div>
								<div class="demo-theme-nav"></div>
							</a>
							<a href="#" class="demo-theme demo-b-dust add-tooltip" data-theme="theme-dust" data-type="b" data-title="(B). Dust">
								<div class="demo-theme-brand"></div>
								<div class="demo-theme-head"></div>
								<div class="demo-theme-nav"></div>
							</a>
							<a href="#" class="demo-theme demo-b-mint add-tooltip" data-theme="theme-mint" data-type="b" data-title="(B). Mint">
								<div class="demo-theme-brand"></div>
								<div class="demo-theme-head"></div>
								<div class="demo-theme-nav"></div>
							</a>
							<a href="#" class="demo-theme demo-b-yellow add-tooltip" data-theme="theme-yellow" data-type="b" data-title="(B). Yellow">
								<div class="demo-theme-brand"></div>
								<div class="demo-theme-head"></div>
								<div class="demo-theme-nav"></div>
							</a>
							<a href="#" class="demo-theme demo-b-well-red add-tooltip" data-theme="theme-well-red" data-type="b" data-title="(B). Well red">
								<div class="demo-theme-brand"></div>
								<div class="demo-theme-head"></div>
								<div class="demo-theme-nav"></div>
							</a>
							<a href="#" class="demo-theme demo-b-coffee add-tooltip" data-theme="theme-coffee" data-type="b" data-title="(B). Coofee">
								<div class="demo-theme-brand"></div>
								<div class="demo-theme-head"></div>
								<div class="demo-theme-nav"></div>
							</a>
							<a href="#" class="demo-theme demo-b-prickly-pear add-tooltip" data-theme="theme-prickly-pear" data-type="b" data-title="(B). Prickly pear">
								<div class="demo-theme-brand"></div>
								<div class="demo-theme-head"></div>
								<div class="demo-theme-nav"></div>
							</a>
							<a href="#" class="demo-theme demo-b-dark add-tooltip" data-theme="theme-dark" data-type="b" data-title="(B). Dark">
								<div class="demo-theme-brand"></div>
								<div class="demo-theme-head"></div>
								<div class="demo-theme-nav"></div>
							</a>
						</div>
						<div class="demo-theme-btn">
							<a href="#" class="demo-theme demo-c-light add-tooltip" data-theme="theme-light" data-type="c" data-title="(C). Light">
								<div class="demo-theme-brand"></div>
								<div class="demo-theme-head"></div>
								<div class="demo-theme-nav"></div>
							</a>
							<a href="#" class="demo-theme demo-c-navy add-tooltip" data-theme="theme-navy" data-type="c" data-title="(C). Navy Blue">
								<div class="demo-theme-brand"></div>
								<div class="demo-theme-head"></div>
								<div class="demo-theme-nav"></div>
							</a>
							<a href="#" class="demo-theme demo-c-ocean add-tooltip" data-theme="theme-ocean" data-type="c" data-title="(C). Ocean">
								<div class="demo-theme-brand"></div>
								<div class="demo-theme-head"></div>
								<div class="demo-theme-nav"></div>
							</a>
							<a href="#" class="demo-theme demo-c-lime add-tooltip" data-theme="theme-lime" data-type="c" data-title="(C). Lime">
								<div class="demo-theme-brand"></div>
								<div class="demo-theme-head"></div>
								<div class="demo-theme-nav"></div>
							</a>
							<a href="#" class="demo-theme demo-c-purple add-tooltip" data-theme="theme-purple" data-type="c" data-title="(C). Purple">
								<div class="demo-theme-brand"></div>
								<div class="demo-theme-head"></div>
								<div class="demo-theme-nav"></div>
							</a>
							<a href="#" class="demo-theme demo-c-dust add-tooltip" data-theme="theme-dust" data-type="c" data-title="(C). Dust">
								<div class="demo-theme-brand"></div>
								<div class="demo-theme-head"></div>
								<div class="demo-theme-nav"></div>
							</a>
							<a href="#" class="demo-theme demo-c-mint add-tooltip" data-theme="theme-mint" data-type="c" data-title="(C). Mint">
								<div class="demo-theme-brand"></div>
								<div class="demo-theme-head"></div>
								<div class="demo-theme-nav"></div>
							</a>
							<a href="#" class="demo-theme demo-c-yellow add-tooltip" data-theme="theme-yellow" data-type="c" data-title="(C). Yellow">
								<div class="demo-theme-brand"></div>
								<div class="demo-theme-head"></div>
								<div class="demo-theme-nav"></div>
							</a>
							<a href="#" class="demo-theme demo-c-well-red add-tooltip" data-theme="theme-well-red" data-type="c" data-title="(C). Well Red">
								<div class="demo-theme-brand"></div>
								<div class="demo-theme-head"></div>
								<div class="demo-theme-nav"></div>
							</a>
							<a href="#" class="demo-theme demo-c-coffee add-tooltip" data-theme="theme-coffee" data-type="c" data-title="(C). Coffee">
								<div class="demo-theme-brand"></div>
								<div class="demo-theme-head"></div>
								<div class="demo-theme-nav"></div>
							</a>
							<a href="#" class="demo-theme demo-c-prickly-pear add-tooltip" data-theme="theme-prickly-pear" data-type="c" data-title="(C). Prickly pear">
								<div class="demo-theme-brand"></div>
								<div class="demo-theme-head"></div>
								<div class="demo-theme-nav"></div>
							</a>
							<a href="#" class="demo-theme demo-c-dark add-tooltip" data-theme="theme-dark" data-type="c" data-title="(C). Dark">
								<div class="demo-theme-brand"></div>
								<div class="demo-theme-head"></div>
								<div class="demo-theme-nav"></div>
							</a>
						</div>
					</div>
				</div>
			</div>
			<div class="pad-all text-left">
				<hr class="hr-sm">
				<p class="demo-set-save-text">* All settings will be saved automatically.</p>
				<button id="demo-reset-settings" class="btn btn-primary btn-labeled fa fa-refresh mar-btm">Restore Default Settings</button>
			</div>
		</div>
		<button id="demo-set-btn" class="btn btn-sm"><i class="fa fa-cog fa-2x"></i></button>
	</div>
	<!--===================================================-->
	<!-- END SETTINGS -->

	
	<!--JAVASCRIPT-->
	<!--=================================================-->

	<!--jQuery [ REQUIRED ]-->
	

	<!--BootstrapJS [ RECOMMENDED ]-->
	<script src="{baseurl}/js/bootstrap.min.js"></script>

	<!--Fast Click [ OPTIONAL ]-->
	<script src="{baseurl}/designplugins/fast-click/fastclick.min.js"></script>

	<!--Nifty Admin [ RECOMMENDED ]-->
	<script src="{baseurl}/js/nifty.js"></script>

	

	<!--Sparkline [ OPTIONAL ]-->
	<script src="{baseurl}/designplugins/sparkline/jquery.sparkline.min.js"></script>

	<!--Skycons [ OPTIONAL ]-->
	<script src="{baseurl}/designplugins/skycons/skycons.min.js"></script>


	<!--Switchery [ OPTIONAL ]-->
	<script src="{baseurl}/designplugins/switchery/switchery.min.js"></script>


	<!--Bootstrap Select [ OPTIONAL ]-->
	<script src="{baseurl}/designplugins/bootstrap-select/bootstrap-select.min.js"></script>


	<!--Demo script [ DEMONSTRATION ]-->
	<script src="{baseurl}/js/demo/nifty-demo.js"></script>


	<!--Specify page [ SAMPLE ]-->
	<script src="{baseurl}/js/demo/dashboard.js"></script>

		{jstranslation}
	{script}
	
	
	<!-- <script src="{baseurl}/js/interface/interface.js" type="text/javascript"></script> -->
	<script src="{baseurl}/js/menu.js" type="text/javascript"></script>

	<script src="{baseurl}/js/bootstrap-select.js" type="text/javascript"></script>
	<script src="{baseurl}/js/scroll.js"></script>
	<script src="{baseurl}/js/multiselect.js" type="text/javascript"></script>
	<script src="{baseurl}/js/interface.js" type="text/javascript"></script>
	
<script src="{baseurl}/js/htvcenter-ui.js" type="text/javascript"></script>
<script src="{baseurl}/designplugins/gauge-js/gauge.min.js"></script>

<script src="{baseurl}/js/demo/charts.js" type="text/javascript"></script>

<link href="{baseurl}/designplugins/bootstrap-datepicker/bootstrap-datepicker.css" rel="stylesheet">
	<script src="{baseurl}/designplugins/bootstrap-datepicker/bootstrap-datepicker.js"></script>

	<!--Bootstrap Timepicker [ OPTIONAL ]-->
	<link href="{baseurl}/designplugins/bootstrap-timepicker/bootstrap-datepicker.css" rel="stylesheet">
	<script src="{baseurl}/designplugins/bootstrap-timepicker/bootstrap-timepicker.min.js"></script>

<!--Bootstrap Timepicker [ OPTIONAL ]-->
	<link href="{baseurl}/designplugins/bootstrap-timepicker/bootstrap-timepicker.min.css" rel="stylesheet">
	<!--Bootstrap Timepicker [ OPTIONAL ]-->
	<script src="{baseurl}/designplugins/bootstrap-timepicker/bootstrap-timepicker.min.js"></script>


	<!--Full Calendar [ OPTIONAL ]-->
	<script src="{baseurl}/designplugins/fullcalendar/lib/moment.min.js"></script>
	<script src="{baseurl}/designplugins/fullcalendar/lib/jquery-ui.custom.min.js"></script>
	<script src="{baseurl}/designplugins/fullcalendar/fullcalendar.min.js"></script>
	


	<!--

	REQUIRED
	You must include this in your project.

	RECOMMENDED
	This category must be included but you may modify which designplugins or components which should be included in your project.

	OPTIONAL
	Optional designplugins. You may choose whether to include it in your project or not.

	DEMONSTRATION
	This is to be removed, used for demonstration purposes only. This category must not be included in your project.

	SAMPLE
	Some script samples which explain how to initialize designplugins or components. This category should not be included in your project.


	Detailed information and more samples can be found in the document.

	-->

</body>
</html>
