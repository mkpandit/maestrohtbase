<!DOCTYPE html>
<html>
<head>
<title>Score Enterprise Ultimate</title>
<meta http-equiv="content-type" content="text/html; charset=UTF-8">
<script type="text/javascript" src="{baseurl}/js/jquery-2.1.4.min.js"></script>
<script type="text/javascript" src="{baseurl}/js/cloud.js"></script>
<!--
<script type="text/javascript" src="{baseurl}/js/morris.js"></script> -->
<script type="text/javascript" src="{baseurl}/js/datetimepicker2.js"></script>

 <!-- Example assets 
        <link rel="stylesheet" type="text/css" href="{baseurl}/css/jcarousel.css">
        <link rel="stylesheet" type="text/css" href="{baseurl}/css/datepicker.css">
     
        <script type="text/javascript" src="{baseurl}/js/jcarousel.min.js"></script>
        <link type="text/css" href="{baseurl}/css/htmlobject.css" rel="stylesheet">
        <script type="text/javascript" src="{baseurl}/js/jcarousel.responsive.js"></script>
        <link type="text/css" href="{baseurl}/css/bootstrap/css/bootstrap.min.css" rel="stylesheet">
--> 
<link type="text/css" href="{baseurl}css/vender/tether/tether.min.css" rel="stylesheet">
<link type="text/css" href="{baseurl}css/vender/bootstrap/bootstrap.min.css" rel="stylesheet">
<link type="text/css" href="{baseurl}css/vender/bootstrap/bootstrap.css" rel="stylesheet">
<link type="text/css" href="{baseurl}/css/fontawesome/css/font-awesome.min.css" rel="stylesheet">
<link type="text/css" href="{baseurl}/css/style.css" rel="stylesheet">
<link type="text/css" href="{baseurl}/css/cloud.css" rel="stylesheet">
<!--
<link type="text/css" href="{baseurl}/css/cloud-custom-branding.css" rel="stylesheet">

<link type="text/css" href="{baseurl}/css/nifty.css" rel="stylesheet">
-->
<link href="http://fonts.googleapis.com/css?family=Open+Sans:300,400,600,700&amp;subset=latin" rel="stylesheet">
<link rel="icon" href="{baseurl}/img/favicon.ico?v=2" type="image/x-icon">
<link rel="shortcut icon" href="{baseurl}/img/favicon.ico?v=2" type="image/x-icon">

	<!--Morris.js [ OPTIONAL ]
	<link href="{baseurl}designplugins/morris-js/morris.min.css" rel="stylesheet"> -->
	<!--Morris.js [ OPTIONAL ]
	<script src="{baseurl}designplugins/morris-js/morris.min.js"></script> 
	<script src="{baseurl}designplugins/morris-js/raphael-js/raphael.min.js"></script> -->

	<!--noUiSlider [ OPTIONAL ]-->
	<!-- <script src="{baseurl}designplugins/noUiSlider/jquery.nouislider.all.js"></script> -->
	<!--noUiSlider [ OPTIONAL ]-->
	<!--<link href="{baseurl}designplugins/noUiSlider/jquery.nouislider.min.css" rel="stylesheet">  -->
	<!--<link href="{baseurl}designplugins/noUiSlider/jquery.nouislider.pips.min.css" rel="stylesheet">  -->

	<script type="text/javascript" src="{baseurl}/js/interface.js"></script>
    <!--
    <link type="text/css" href="{baseurl}/css/menu.css" rel="stylesheet">
    -->
    <link type="text/css" href="{baseurl}css/c3/c3.min.css" rel="stylesheet">
    <link type="text/css" href="{baseurl}css/cleanui/core.cleanui.css" rel="stylesheet">
    <link type="text/css" href="{baseurl}css/cleanui/top-bar.cleanui.css" rel="stylesheet">
    <link type="text/css" href="{baseurl}css/cleanui/pages.cleanui.css" rel="stylesheet">
    <link type="text/css" href="{baseurl}css/modal.css" rel="stylesheet">
<!--
    <link type="text/css" href="{baseurl}css/cleanui/vender/bootstrap/bootstrap.min.css" rel="stylesheet">
-->
<style>
    body {
        overflow-y: hidden; /* to fix the 4 <br>s */
    }
    #page {
        height: 100vh;
        overflow-y: auto;
        padding-bottom: 3.34em;
    }
    a {
        cursor: pointer;
    }
    a.disabled {
        opacity: 0.25;
        cursor: not-allowed;
    }
</style>
</head>
<body>
<div id="page">
    <!-- top-bar left main menu -->
    <div class="cat__top-bar">
        <div class="cat__top-bar__left">
            <div class="cat__top-bar__logo">
              <a href="{baseurl}user/index.php?project_tab_ui=0&cloud_ui=home">
                <div class="logo">
                    <img src="{baseurl}img/fortis-logo.png">
                </div>
                <div class="slogan">
                    <h4>Score</h4>
                    <h5>Self-Service Portal</h5>
                </div>
              </a>
            </div>

            <div class="cat__top-bar__item hidden-sm-down link">
                <a class="dropdown-item" href="{baseurl}user/index.php?project_tab_ui=1&cloud_ui=appliances">
                    <i class="fa fa-sitemap"></i><span>Instances</span>
                </a>
            </div>

            <!--
            <div class="cat__top-bar__item hidden-sm-down dropdown">
                <a aria-expanded="false" class="dropdown-toggle" data-toggle="dropdown" href="javascript: void(0);">
                  <i class="fa fa-server"></i>Instances
                </a>
                <ul aria-labelledby="" class="dropdown-menu" role="menu">
                    <li>
                        <a class="dropdown-item" href="{baseurl}user/index.php?project_tab_ui=1&cloud_ui=appliances">
                            <i class="fa fa-sitemap"></i><span>List Instances</span>
                        </a>
                    </li>
                    <li>
                        <a class="dropdown-item" href="{baseurl}user/index.php?project_tab_ui=3&cloud_ui=create">
                            <i class="fa fa-plus"></i><span>New Instance</span>
                        </a>
                    </li>
                </ul>
            </div>
            -->
            <!--
            <div class="cat__top-bar__item">
              <a href="javascript: void();"><i class="fa fa-money" aria-hidden="true"></i>Billing</a>
            </div>
            
            <div class="cat__top-bar__item">
                <a href="javascript: void(0);" class="dropdown-toggle" data-toggle="dropdown" aria-expanded="false">
                    <i class="fa fa-money"></i>Billing
                </a>
                <ul class="dropdown-menu dropdown-menu-left" aria-labelledby="" role="menu">
                    <a class="dropdown-item" href="javascript:void(0)">Current search</a>
                    <a class="dropdown-item" href="javascript:void(0)">Search for issues</a>
                </ul>
            </div>
            -->

            <div class="cat__top-bar__item hidden-sm-down dropdown">
                <a aria-expanded="false" class="dropdown-toggle" data-toggle="dropdown" href="javascript: void(0);">
                  <i class="fa fa-money"></i>Billing
                </a>
                <ul aria-labelledby="" class="dropdown-menu" role="menu">
                    <li>
                        <a class="dropdown-item" href="{baseurl}user/index.php?report=report_dashboard">
                            <i class="fa fa-circle-o-notch"></i><span>Dashboard</span>
                        </a>
                    </li>
                    <li>
                        <a class="dropdown-item" href="{baseurl}user/index.php?report=report_bills">
                            <i class="fa fa-usd"></i><span>Bills</span>
                        </a>
                    </li>
                    <li>
                        <a class="dropdown-item" href="{baseurl}user/index.php?report=report_explorer">
                            <i class="fa fa-search"></i><span>Explorer</span>
                        </a>
                    </li>
                    <li>
                        <a class="dropdown-item" href="{baseurl}user/index.php?report=report_budget">
                            <i class="fa fa-credit-card"></i><span>Budget</span>
                        </a>
                    </li>
                </ul>              
            </div>

            <div class="cat__top-bar__item hidden-sm-down pull-right dropdown">
                <a aria-expanded="false" class="dropdown-toggle" data-toggle="dropdown" href="javascript: void(0);">
                  <i class="fa fa-user"></i>Account
                </a>
                <ul aria-labelledby="" class="dropdown-menu" role="menu">
                    <li>
                        <a class="dropdown-item" href="{baseurl}user/index.php?project_tab_ui=2&cloud_ui=account">
                            <i class="fa fa-user-plus"></i><span>Manage Account</span>
                        </a>
                    </li>
                    <li>
                        <a class="dropdown-item" href="{baseurl}?register_msg=You have successfully logged out&lang_select=en">
                            <i class="fa fa-sign-out"></i><span>Logout</span>
                        </a>
                    </li>
                </ul>
            </div>
        </div>
    </div>
    <!-- end top-bar left main menu -->
    <!--
	<div id="cloud_top_menu">
		<div id="cloud_logo" class="col-xs-12 col-sm-3 col-md-2 col-lg-2"><a href="/cloud-fortis"><img src="{baseurl}/img/fortis-logo.png" alt="htvcenter Enterprise Cloud" id="logo_cl_img"></a></div>
		    <a id="menubutton"><i class="fa fa-bars"></i></a> 
		    <a id="report"><i class="fa fa-file-text-o"></i></a>
            
    <div id="cloud_language_select">{langbox}
    </div>
	</div>
    -->
    <div id="cloud-content">
    	<!--
        <div class="sidebar"></div>
        -->
        <!-- hack to deal with that tabmenu -->
       
            <!-- 
            <span id="topspan"><i class="fa fa-gear"></i> Cloud Management</span>
            -->
            {content}
        
       <div class="floatbreaker" style="line-height:0px;clear:both;">&#160;</div>
       <div class="pull-right" id="htvcenter_enterprise_footer"></div>
    </div>

    <script type="text/javascript" src="{baseurl}/js/cookie.js"></script>
    <div class="modal-dialog" id="popup">
        <div class="panel">            
            <!-- Classic Form Wizard -->
            <!--===================================================-->
            <div id="demo-cls-wz">

                <!--Nav-->
                <ul class="wz-nav-off wz-icon-inline wz-classic">
                    <li class="col-xs-3 bg-info active">
                        <a aria-expanded="true" data-toggle="tab" href="#demo-cls-tab1">
                            <span class="icon-wrap icon-wrap-xs bg-trans-dark"><i class="fa fa-file-text-o"></i></span> Report
                        </a>
                    </li>
                    <div class="volumepopupclass"><a id="popupclose"><i class="fa fa-icon fa-close"></i></a></div>
                    
                </ul>

                <!--Progress bar-->
                <div class="progress progress-sm progress-striped active">
                    <div style="width: 100%;" class="progress-bar progress-bar-info"></div>
                </div>


                <!--Form-->
                <div class="form-horizontal mar-top">
                    <div class="panel-body">
                        <div class="tab-content">

                            <!--First tab-->
                            <div id="demo-cls-tab1" class="tab-pane active in">
                                <div id="popupform">
                                    <h2>Cloud Billing Report</h2>
                                    {hidenuser}
    								<label>Month:</label> <select id="reportmonth">
    									<option value="Jan">January</option>
    									<option value="Feb">February</option>
    									<option value="Mar">March</option>
    									<option value="Apr">April</option>
    									<option value="May">May</option>
    									<option value="Jun">June</option>
    									<option value="Jul">July</option>
    									<option value="Aug">August</option>
    									<option value="Sep">September</option>
    									<option value="Oct">October</option>
    									<option value="Nov">November</option>
    									<option value="Dec">December</option>
    									
    								</select>
    								<label>Year:</label>  <select id="reportyear">{reportyear}</select>
    								<div id="buttons"><input type="submit" value="Report" class="submit" id="orderreport"></div>
    							</div>
                            </div>

                            
                        </div>
                    </div>
                </div>
            </div>
        <!--===================================================-->
        <!-- End Classic Form Wizard -->
        </div>
    </div>
    <!-- End Modal Dialog Popup -->

    <div class="modal-dialog" id="popuptable">
        <div class="panel">
                    
            <!-- Classic Form Wizard -->
            <!--===================================================-->
            <div id="demo-cls-wz">

                <!--Nav-->
                <ul class="wz-nav-off wz-icon-inline wz-classic">
                    <li class="col-xs-3 bg-info active">
                        <a aria-expanded="true" data-toggle="tab" href="#demo-cls-tab1">
                            <span class="icon-wrap icon-wrap-xs bg-trans-dark"><i class="fa fa-file-text-o"></i></span> Report
                        </a>
                    </li>
                    <div class="volumepopupclass"><a id="popuptableclose"><i class="fa fa-icon fa-close"></i></a></div>
                    
                </ul>

                <!--Progress bar-->
                <div class="progress progress-sm progress-striped active">
                    <div style="width: 100%;" class="progress-bar progress-bar-info"></div>
                </div>

                <!--Form-->
                <div class="form-horizontal mar-top">
                    <div class="panel-body">
                        <div class="tab-content">

                            <!--First tab-->
                            <div id="demo-cls-tab1" class="tab-pane active in">
                                <div id="popuptableform">
                                    
								</div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <!--===================================================-->
            <!-- End Classic Form Wizard -->
        </div>
    </div>
    <!-- end modal dialog popuptable -->

    <div class="dropdown-menu dropdown-menu-md with-arrow reportdropdown" id="reportdropdown">
        <div class="pad-all bord-btm">
            <p class="text-lg text-muted text-thin mar-no">Report features:</p>
        </div>
        <div class="nano scrollable has-scrollbar" style="height: 265px;">
            <div class="nano-content" tabindex="0" style="right: -13px;">
                <ul class="head-list serverul">
                    <li>
                        <a href="index.php?report=report_dashboard" class="dashboardreport">
                            <i class="fa fa-circle-o-notch dropfa fadash"></i>
                            <span class="headspan">Dashboard</span>
                        </a>
                    </li>

                    <li>
                        <a href="index.php?report=report_bills" class="dashboardreport">
                            <i class="fa fa-usd dropfa fabills"></i>
                            <span class="headspan">Bills</span>
                        </a>
                    </li>

                    <li>
                        <a href="index.php?report=report_explorer" class="dashboardreport">
                            <i class="fa fa-search dropfa faexplorer"></i>
                            <span class="headspan">Explorer</span>
                        </a>
                    </li>

                    <li>
                        <a href="index.php?report=report_budget" class="dashboardreport">
                            <i class="fa fa-credit-card dropfa faexplorer"></i>
                            <span class="headspan">Budget</span>
                        </a>
                    </li>            
                </ul>
            </div>
        <div class="nano-pane" style="display: block;"><div class="nano-slider" style="height: 193px; transform: translate(0px, 0px);"></div></div></div>

        <!--Dropdown footer-->
        <div class="pad-all bord-top">
        
        </div>
    </div>
    <!-- end reportdropdown -->
</div>
<!-- end div id=page -->
<!--
<script type="text/javascript" src="{baseurl}/css/bootstrap/js/bootstrap.min.js"></script>
-->
<script type="text/javascript" src="{baseurl}js/vender/tether/tether.min.js"></script>
<script type="text/javascript" src="{baseurl}js/vender/bootstrap/bootstrap.min.js"></script>
<script>
$(document).ready(function(){
	
    var billreport = '{configbill}';
	if (billreport == 'false') {
		$('#report').hide();
	} else {
		$('#report').show();
	}
    
    $(".cat__top-bar__item.dropdown").hover(
        function () {
            $(this).addClass("show");
            $(this).find("a.dropdown-toggle").attr("aria-expanded",true);
        }, 
        function () {
            $(this).removeClass("show");
            $(this).find("a.dropdown-toggle").attr("aria-expanded",false);
        }
    );
});
</script>

</body>
</html>
