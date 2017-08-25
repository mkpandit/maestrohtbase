<!--
/*
    htvcenter Enterprise developed by htvcenter Enterprise GmbH.

    All source code and content (c) Copyright 2014, htvcenter Enterprise GmbH unless specifically noted otherwise.

    This source code is released under the htvcenter Enterprise Server and Client License, unless otherwise agreed with htvcenter Enterprise GmbH.
    The latest version of this license can be found here: http://htvcenter-enterprise.com/license

    By using this software, you acknowledge having read this license and agree to be bound thereby.

                http://htvcenter-enterprise.com

    Copyright 2014, htvcenter Enterprise GmbH <info@htvcenter-enterprise.com>
*/
-->
<link href="/cloud-fortis/designplugins/datatables/media/css/jquery.dataTables.min.css" rel="stylesheet" type="text/css">
<link href="/cloud-fortis/css/jquery.steps.css" rel="stylesheet" type="text/css">
<link href="/cloud-fortis/css/normalize.css" rel="stylesheet" type="text/css">
<link href="/cloud-fortis/css/ion.rangeSlider.css" rel="stylesheet" type="text/css">
<link href="/cloud-fortis/css/ion.rangeSlider.skinHTML5.css" rel="stylesheet" type="text/css">
<!--
<link href="/cloud-fortis/css/owl.carousel.min.css" rel="stylesheet" type="text/css">
<link href="/cloud-fortis/css/owl.theme.default.min.css" rel="stylesheet" type="text/css">
-->
<link href="/cloud-fortis/css/slick.css" rel="stylesheet" type="text/css">
<link href="/cloud-fortis/css/slick-theme.css" rel="stylesheet" type="text/css">

<script src="https://cdn.datatables.net/1.10.15/js/jquery.dataTables.min.js" type="text/javascript"></script>
<script src="/cloud-fortis/js/jquery.validate.min.js" type="text/javascript"></script>
<script src="/cloud-fortis/js/jquery.steps.min.js" type="text/javascript"></script>
<script src="/cloud-fortis/js/ion.rangeSlider.js" type="text/javascript"></script>
<!--
<script src="/cloud-fortis/js/owl.carousel.min.js" type="text/javascript"></script>
-->
<script src="/cloud-fortis/js/slick.min.js" type="text/javascript"></script>

<style>
	#project_tab_ui { display: none; }  /* hack for tabmenu issue */
	/*  table.dataTable tbody tr.hide { display: none; } */
	/* table.dataTable tbody tr.hide td { height: 0px; } */
	/* table.dataTable tbody tr.even { height: 140px; } */
	table.dataTable .thead-default th { background-color: rgb(255,255,255); }
	table.dataTable tbody td { padding-left: 18px; padding-right: 18px; } /* for aligning with thead */
	table.dataTable tbody td i { display: block; text-align: center; }
	table.dataTable tbody td.hide { display: none; } 
	table.dataTable.table-hover tbody tr:hover { background-color: rgb(189,199,231); }


	/*table.dataTable tbody tr.even { background-color: rgb(228,233,240); }  */
	table.dataTable tbody td section.card { margin-bottom: 0; }
	table.dataTable tbody td.status.active { color: rgb(112,173,71); }
	table.dataTable tbody td.status.inactive { color: red; }
	table.dataTable .dropdown ul.dropdown-menu { padding: 3px 8px; min-width: 5em; }
	.c3-graph { height: 179px; }
	.d-inline.pull-left { margin: 3px 0; padding: 0; text-align: left; background: inherit; }
	.d-inline-block.pull-left { margin: 0; padding: 0; }
	.card-header .d-inline-block span { margin: 0 15px; }
	.card-header i { display: inline !important; }

	.wizard .form-group .item img, .wizard .form-group .item label, .wizard .form-group .item input.checkbox {
		display: block;
		text-align: center;
		margin: 0.2rem auto;
	}

	.wizard .owl-carousel.owl-theme > .item {
		display: inline-block;
		width: 10.5rem;
		height: 10.5rem;
		margin-bottom: 1.1rem;
	}

	.wizard .owl-carousel.owl-theme > .item.hide {
		display: none;
	}
	/*
	.wizard .owl-carousel.owl-theme {
		padding: 1.3rem 1.3rem;
	}
	*/
	.wizard label {
		font-weight: bold;
	}

	.wizard > .content > .body ul.slick-dots {
		list-style: none;
	}

	.wizard > .content > .body ul.slick-dots > li {
		display: inline-block;
	}
	/*
	#owl-nav-container .owl-prev, #owl-nav-container .owl-next {
		display: inline-block;
		font-size: 0;
		width: 21px;
		height: 34px;
		margin: 0 auto;
		-webkit-border-radius: 2px; 
		-moz-border-radius: 2px; 
		border-radius: 2px; 
		background: url(../img/arrows.png) no-repeat;
		z-index: 10;
		margin-top: 52px; 
		cursor: pointer;
	}
	*/
	/*
	#owl-nav-container .owl-prev {
		float: left;
		background-position: -260px -43px;
	}

	#owl-nav-container .owl-next {
		float: right;
		background-position: -320px -43px;
	}
	*/
	#summary-tab label {
		min-width: 7.8em;
		max-width: 35em;
		display: inline-block;
		font-weight: normal;
		vertical-align: top;
	}

	/*
	select, textarea, input {
		background-color: #fff;
		background-image: none;
		border: 1px solid #ccc;
		height: 1.5em; 
		min-width: 75px;
		border-radius: 2px;
		text-align: center; 
	} */
</style>
<script src="/cloud-fortis/js/c3/d3.v3.min.js" type="text/javascript"></script>
<script src="/cloud-fortis/js/c3/c3.min.js" type="text/javascript"></script>
<script type="text/javascript">
/* run DataTable.js */
$(document).ready(function() {


	function makeSummary() {
		var apps = [];
		var html = '';
		html += '<label>VM Name: </label><label>' + $("#vmName").val() + '</label><br/>';
		html += '<label>VM Description: </label><label>' + $("#vmDesc").val() + '</label><br/>';
		html += '<label>VM Type: </label><label>' + $("#vmType").val() + '</label><br/>';
		html += '<label>VM Iamge: </label><label>' + $("#vmImage").val() + '</label><br/>';
		html += '<label>DIsk: </label><label>' + $("#vmDisk").val() + ' GB</label><br/>';
		html += '<label>CPU: </label><label>' + $("#vmCPU").val() + '</label><br/>';
		html += '<label>Memory: </label><label>' + $("#vmMemory").val() + ' MB</label><br/>';
		html += '<label>Network: </label><label>' + $("#vmNetwork").val() + '</label><br/>';

		$(".owl-carousel div.item:not(.slick-cloned) input.checkbox:checked").each(function () {
			var elem = $(this).data("name");

			if (apps.indexOf(elem) == -1) {
				apps.push(elem); 
			}
		});
		html += '<label>Apps: </label><label>' + (apps.length > 0 ? apps.join(", ") : "No App Selected") + '</span><br/>';
		$("#summary-tab p").empty().append(html);
	}

	function setChecks(e) {
		
		$(".owl-carousel label").on('click',function () {
			var rel = $(this).attr("rel");

			$(".owl-carousel input.checkbox[name='"+rel+"']").each(function () {
				this.checked = !this.checked;
			});
		});

		$(".owl-carousel input.checkbox").on('click',function () {
			var rel = $(this).attr("name");
			var chckd = this.checked;

			$(".owl-carousel input.checkbox[name='"+rel+"']").each(function () {
				this.checked = chckd;
			});
		});
	}

	function initiateOwlCarousel() {
		if ($(".owl-carousel.uninitiated").length > 0) {
			$(".owl-carousel.uninitiated").slick({
				dots: true,
				infinite: true,
				slidesToShow: 4
			});

			$(".owl-carousel").removeClass("uninitiated");
			setChecks();
		} else {
			$(window).trigger('resize'); 
		}
	}

	var form = $("#create-vm-form");

	form.validate({
		errorPlacement: function errorPlacement(error, element) { element.before(error); },
		rules: {
			confirm: {
				equalTo: "#password"
			}
		}
	}); 

	form.children("div").steps({
		headerTag: "h3",
		bodyTag: "section",
		transitionEffect: "slideLeft",
		onStepChanging: function (event, currentIndex, newIndex)
		{
			form.validate().settings.ignore = ":disabled,:hidden";

			if (newIndex == 4) {
				makeSummary();
			}

			return form.valid();
		},
		onStepChanged: function(event, currentIndex, priorIndex)
		{
			// if (currentIndex == 3) {
			//	initiateOwlCarousel();
			// }
		},
	/*	onInit: function(event, currentIndex)
		{
			console.log("steps onInit, currentIndex" + currentIndex);
			if (currentIndex == 3) {
				initiateOwlCarousel();
			}
		}, */
		onFinishing: function (event, currentIndex)
		{
			form.validate().settings.ignore = ":disabled";
			return form.valid();
		},
		onFinished: function (event, currentIndex)
		{
			// ("Submitted!");
		}
	}); 

	$("#create-vm").click(function () {
		$("#create-vm-modal").modal("show");
		return false;
	});

	function format (d) {
		// `d` is the original data object for the row
		return	'<section class="card">'+
					'<div class="card-header">'+
						'<div class="d-inline-block col-sm-3 text-center">'+
							'<span>'+d[1]+'</span>'+
						'</div>'+
						'<div class="d-inline-block col-sm-9 text-center">'+
							'<span><a href="#"><i class="fa fa-play"></i> Start</a></span>'+
							'<span><a href="#"><i class="fa fa-stop"></i> Stop</a></span>'+
							'<span><a href="#"><i class="fa fa-server"></i> noVNC</a></span>'+
							'<span><a href="#"><i class="fa fa-cogs"></i> Edit Compute</a></span>'+
							'<span><a href="#"><i class="fa fa-pencil"></i> Edit Volume</a></span>'+
							'<span><a href="#"><i class="fa fa-eraser"></i> Remove</a></span>'+
						'</div>'+
					'</div>'+
					'<div class="card-block">'+
						'<div class="d-block col-sm-3 text-center pull-left">'+
							'<section class="card">'+
								'<div class="card-header">'+
									'<div class="bg-faded"><span><strong>VM Details</strong></span></div>'+
								'</div>'+
								'<div class="card-block">'+
									'<div class="bg-faded">'+
										'<div class="d-inline col-sm-6 pull-left"><strong>VM Name:</strong></div>'+
										'<div class="d-inline col-sm-6 pull-left">' + d[1] +'</div>'+
									'</div>'+
									'<div class="bg-faded">'+
										'<div class="d-inline col-sm-6 pull-left"><strong>VM IP Address</strong></div>'+
										'<div class="d-inline col-sm-6 pull-left">' + d[2] +'</div>'+
									'</div>'+
									'<div class="bg-faded">'+
										'<div class="d-inline col-sm-6 pull-left"><strong>Image Name:</strong></div>'+
										'<div class="d-inline col-sm-6 pull-left">' + d[3] +'</div>'+
									'</div>'+
									'<div class="bg-faded">'+
										'<div class="d-inline col-sm-6 pull-left"><strong>Requested by:</strong></div>'+
										'<div class="d-inline col-sm-6 pull-left">' + d[8] +'</div>'+
									'</div>'+
								'</div>'+
							'</section>'+
						'</div>'+
						'<div class="d-block col-sm-8 text-center pull-left">'+
							'<div id="cpu-usage-' + d[0] + '" class="c3-graph col-sm-6 d-inline-block pull-left"></div>'+
							'<div id="memory-usage-' + d[0] + '" class="c3-graph col-sm-6 d-inline-block pull-left"></div>'+
						'</div>'+
					'</div>'+
				'</section>';
    /*
    return	'<table cellpadding="5" cellspacing="0" border="0" style="padding-left:50px;">'+
				'<tr style="height: 175px;">'+
					'<td>Full name:</td>'+
					'<td>abc</td>'+
				'</tr>'+
			'</table>';*/
	}

	var dt = $("#cloud_appliances").DataTable( {

		"columns": [
			{ "visible": false },
			null,
			null,
			null,
			null,
			null,
			null,
			null,
			null,
			{ "orderable": false }
		],
		"order": [], //  [[0, 'asc']],
		"bLengthChange": false,
		"pageLength": 10,
		"search": {
			"regex": true
		},
		"bAutoWidth": true
	} );

	$(".toggle-graph a").click(function () {

		var tr = $(this).closest('tr');
		var row = dt.row( tr );
		var row_id = tr.attr("id");

		if (row.child.isShown()) {
			tr.removeClass('details');
			row.child.hide();
		} else {
			tr.addClass('details');
			row.child( format(row.data()) ).show();
		}

		var cpu_data_request = $.ajax({
			url : "api.php?action=get_cpu_usage&request="+row_id,
			type: "GET",
			cache: false,
			async: true,
			dataType: "json",
		});

		/*
		cpu_data_request.done(function(data) {

			var chart_cpu = c3.generate({
				bindto: "#cpu-usage-" + row_id,
				data: {
					columns: [
						['cpu used'].concat(data)
					],
					type: 'spline'
				},
				legend: {
					show: true,
					position: 'inset'
				}
			});
		});

		cpu_data_request.fail(function( jqXHR, textStatus ) {
			alert( "CPU datae request failed: " + textStatus );
		});

		var memory_data_request = $.ajax({
			url : "api.php?action=get_memory_usage&request="+row_id,
			type: "GET",
			cache: false,
			async: true,
			dataType: "json",
		});

		memory_data_request.done(function(data) {

			var chart_memory = c3.generate({
				bindto: "#memory-usage-" + row_id,
				data: {
					columns: [
						['memory used'].concat(data)
					],
					type: 'spline'
				},
				legend: {
					show: true,
					position: 'inset'
				}
			});
		});

		memory_data_request.fail(function( jqXHR, textStatus ) {
			alert( "Memory datae request failed: " + textStatus );
		});
		*/
	});

	$("#vmDisk").ionRangeSlider({
		hide_min_max: false,
		keyboard: true,
		min: 2,
		max: 100,
		values: [2, 5, 10, 20, 50, 100],
		type: 'single',
		step: 0.2,
		grid: true,
		postfix: " GB",
	});

	$("#vmCPU").ionRangeSlider({
		hide_min_max: false,
		keyboard: true,
		min: 1,
		max: 4,
		values: [1,2,4],
		type: 'single',
		step: 1,
		grid: true,
	});

	$("#vmMemory").ionRangeSlider({
		hide_min_max: false,
		keyboard: true,
		min: 256,
		max: 2048,
		values: [256, 512, 1024, 2048],
		type: 'single',
		step: 0.2,
		grid: true,
		postfix: " MB",
	});

	function searchApp(txt) {
		var txt_lc = txt.toLowerCase();
		var found_match = false;
		
		/* $(".owl-carousel").slick("slickUnfilter"); */
		$(".owl-carousel .item").each(function () {
			$(this).removeClass("matched");
			var app_name_lc = $(this).find("label span").text().toLowerCase();

			if (app_name_lc.indexOf(txt_lc) == -1) {
				$(this).addClass("hide");
			} else {
				$(this).removeClass("hide");
				found_match = true;
			}
		});

		if (!found_match) {
			$(".owl-carousel .item").removeClass("hide");
			/* $(".owl-carousel").slick("slickFilter",".matched"); */
		} /* else {
			$(".owl-carousel").slick("slickUnfilter");
		} */
	}

	var delay = (function(){
		var timer = 0;
		return function(callback, ms){
			clearTimeout (timer);
			timer = setTimeout(callback, ms);
		};
	})();

	$("#search-app").keyup(function() {
		var txt = $(this).val();

		delay(function() {
			searchApp(txt);
		}, 300);
	});
});




function get_state( id ) {
/*
	var data = $.ajax({
		url : "api.php?action=state&request="+id,
		type: "POST",
		cache: false,
		async: false,
		dataType: "json",
		success : function () { }
	}).responseText;
	elem = document.getElementById(id);
	elem.innerHTML = data + "\n" + elem.innerHTML;
	window.setTimeout("get_state( "+id+" )", 100);
*/
}


/*
	window.onload = function() {
		var th = $('#cloud_appliances').height();
		$('#htvcenter_enterprise_footer').css("top",th + 220);

	};


	$(document).ready(function(){
		$("#cloudpopupInfoClose").click(function(){
			clouddisablePopup();
		});
		$("#cloudbackgroundPopup").click(function(){
			clouddisablePopup();
		});
	});


	var cloudpopupStatus = 0;
	function cloudloadPopup(){
		if(cloudpopupStatus==0){
			$("#cloudbackgroundPopup").css({
				"opacity": "0.7"
			});
			$("#cloudbackgroundPopup").fadeIn("slow");
			$("#cloudpopupInfo").fadeIn("slow");
			cloudpopupStatus = 1;
		}
	}

	function clouddisablePopup(){
		if(cloudpopupStatus==1){
			$("#cloudbackgroundPopup").fadeOut("slow");
			$("#cloudpopupInfo").fadeOut("slow");
			cloudpopupStatus = 0;
		}
	}


	function cloudcenterPopup(){
		var windowWidth = document.documentElement.clientWidth;
		var windowHeight = document.documentElement.clientHeight;
		var popupHeight = $("#cloudpopupInfo").height();
		var popupWidth = $("#cloudpopupInfo").width();
		$("#cloudpopupInfo").css({
			"position": "absolute",
			"top": "120px",
			"left": "400px"
		});
		$("#cloudbackgroundPopup").css({
			"height": windowHeight + 20
		});
	}


	function cloudopenPopup(cr_id) {
		cloudcenterPopup();
		cloudloadPopup();
		cloudget_info_box(cr_id);
	}



	function cloudget_info_box(cr_id) {
		$.ajax({
			url: "/cloud-fortis/user/api.php?action=request_details&cr_id=" + cr_id,
			cache: false,
			async: false,
			dataType: "text",
			success: function(response) {
				$("#cloudinfoArea").html(response);
			}
		});
	}

*/
</script>
<!--
<div id="cloudpopupInfo">
	<a id="cloudpopupInfoClose">x</a>
	<h1>{cr_details_title}</h1>
	<div id="cloudinfoScrollArea">
		<p id="cloudinfoArea">
		</p>
	</div>
</div>
<div id="cloudbackgroundPopup"></div>
//-->


<div class="cat__content">
	<cat-page>
	<div class="row">
		<div class="col-sm-12">
			<section class="card">	
				<div class="card-header">
					<div class="panel-heading"> <!-- pull-right -->
						<h3 class="text-black d-inline"><strong>Virtual Machines</strong></h3>
						<!--
						<a class="btn btn-primary" href="index.php?project_tab_ui=3&cloud_ui=create" target="_blank">New Instance <i class="fa fa-plus"></i></a>
						-->
						<div class="pull-right d-inline">
							<a class="btn btn-sm btn-primary" href="#" id="create-vm">Create New Virtual Machine <i class="fa fa-plus"></i></a>
						</div>
					</div>
				</div>
				<div class="card-block">
					{table}
				</div>
			</section>
		</div>
	</div>
	</cat-page>
</div>

<div id="create-vm-modal" class="modal" data-backdrop="static">
	<div class="modal-content">
		<div class="modal-header">
			<h3 class="text-black">Create New Virtual Machine</h3>
			<button type="button" class="close" data-dismiss="modal" aria-label="Close">
			<span aria-hidden="true">&times;</span>
			</button>
		</div>
		<form id="create-vm-form" action="#">
			<div class="modal-body">
			<h3>Profile</h3>
				<section>
					<!--<div class="row"> -->
						<div class="col-lg-12">
							<div class="form-group">
								<label for="vmName">Name *</label>
								<input id="vmName" name="vmName" type="text" class="form-control required" placeholder="Name">
							</div>
							<div class="form-group">
								<label for="vmDesc">Description *</label>
								<input id="vmDesc" name="vmDesc" type="text" class="form-control required" placeholder="Description">
							</div>
							<div class="form-group">
								<label for="vmType">Type *</label>
								<select class="form-control required" id="vmType" name="vmType">
									<option value="14936736988105">Cloud VM (localboot)</option>
								</select>
							</div>
							<div class="form-group">
								<label for="vmImage">Image *</label>
								<select class="form-control required" id="vmImage" name="vmImage">
									<option value="HyperTask">HyperTask</option>
									<option value="Resrouce1234">Resrouce1234</option>
								</select>
							</div>
						</div>
						<p>(*) Mandatory</p>
					<!-- </div> -->
				</section>
			<h3>Compute</h3>
				<section>
					<!-- <div class="row">  -->
						<div class="col-lg-12">
							<div class="form-group">
								<label for="vmDisk">Disk *</label>
								<input id="vmDisk" name="vmDisk" type="text"" value="" />
							</div>
							<div class="form-group">
								<label for="vmCPU">CPU *</label>
								<input id="vmCPU" name="vmCPU" type="text" value="" />
							</div>
							<div class="form-group">
								<label for="vmMemory">Memory *</label>
								<input id="vmMemory" name="vmMemory" type="text" value="" />
							</div>
						</div>
						<p>(*) Mandatory</p>
					<!-- </div> -->
				</section>
			<h3>Network</h3>
				<section>
					<!-- <div class="row">  -->
						<div class="col-lg-12">
							<div class="form-group">
								<label for="vmNetwork">Network *</label>
								<select id="vmNetwork" name="vmNetwork" class="form-control required">
									<option value="192.168.0.111">192.168.0.111</option>
									<option value="192.168.0.112">192.168.0.112</option>
									<option value="192.168.0.113">192.168.0.113</option>
									<option value="192.168.0.114">192.168.0.114</option>
								</select>
							</div>
						</div>
						<p>(*) Mandatory</p>
					<!-- </div> -->
				</section>
			<h3>Marketplace</h3>
				<section>
					<!-- <div class="row">  -->
						<!--
						<div class="col-sm-1">
							<i class="fa fa-arrow-left" aria-hidden="true"></i>
						</div>
						-->
						<div class="col-lg-12">
							<div class="form-group row d-inline">
								<label class="col-sm-3 col-form-label">Marketplace</label>
								<div class="col-sm-6 pull-right">
									<div class="form-group row pull-right">
										<label class="col-form-label" for="search-app">Search:&nbsp;&nbsp;</label>
										<div class="form-input-icon form-input-icon-right">
											<i class="fa fa-search" aria-hidden="true"></i>
											<input class="form-control" id="search-app" placeholder="Search" type="text">
										</div>
									</div>
								</div>
							</div>

							<div class="form-group d-inline-block" style="width: 100%;">
								<div class="owl-carousel uninitiated owl-theme">
									<div class="item">
										<label rel="cloud_application_select_0"><img src="/cloud-fortis/img/servericon.png" width="75%" height="75%" /><span>Basic Server</span>
										</label>
										<input class="checkbox" name="cloud_application_select_0" value="puppet/basic-server" data-name="Basic Server" type="checkbox" />
									</div>
									<div class="item">
										<label rel="cloud_application_select_1"><img src="/cloud-fortis/img/databaseicon.png" width="75%" height="75%" /><span>Database Server</span>
										</label>
										<input class="checkbox" name="cloud_application_select_1" value="puppet/database-server" data-name="Database Server" type="checkbox" />
									</div>
									<div class="item">
										<label rel="cloud_application_select_2"><img src="/cloud-fortis/img/dockericon.png" width="75%" height="75%" /><span>Docker</span>
										</label>
										<input class="checkbox" name="cloud_application_select_2" value="puppet/docker" data-name="Docker" type="checkbox" />
									</div>
									<div class="item">
										<label rel="cloud_application_select_3"><img src="/cloud-fortis/img/lampicon.png" width="75%" height="75%" /><span>Lamp</span>
										</label>
										<input class="checkbox" name="cloud_application_select_3" value="puppet/lamp" data-name="Lamp" type="checkbox" />
									</div>
									<div class="item">
										<label rel="cloud_application_select_4"><img src="/cloud-fortis/img/openstackicon.png" width="75%" height="75%" /><span>Open Stack</span>
										</label>
										<input class="checkbox" name="cloud_application_select_4" value="puppet/openstack" data-name="Open Stack" type="checkbox" />
									</div>
									<div class="item">
										<label rel="cloud_application_select_5"><img src="/cloud-fortis/img/webminicon.png" width="75%" height="75%" /><span>Web Admin</span>
										</label>
										<input class="checkbox" name="cloud_application_select_5" value="puppet/webmin" data-name="Web Admin" type="checkbox" />
									</div>
									<div class="item">
										<label rel="cloud_application_select_6"><img src="/cloud-fortis/img/webservericon.png" width="75%" height="75%" /><span>Web Server</span>
										</label>
										<input class="checkbox" name="cloud_application_select_6" value="puppet/webserver" data-name="Web Server" type="checkbox" />
									</div>
									<div class="item">
										<label rel="cloud_ha_select"><img src="/cloud-fortis/img/haicon.png" width="75%" height="75%" /><span>High Availability</span>
										</label>
										<input class="checkbox" name="cloud_ha_select" value="ha" data-name="High Availability" type="checkbox" />
									</div>
								</div>
							</div>
						</div>
						
				</section>
			<h3>Summary</h3>
				<section>
					<label>Review VM Settings:</label>
					<div id="summary-tab" class="col-lg-12">
						<p>
						</p>
					</div>
				</section>
			</div>
		</form>
	</div>
</div>





<!--
<div class="function-box" style="display: none" id="filepicker">
	<div onmouseup="document.getElementById('filepicker').onmousedown = null;" onmousedown="Drag.init(document.getElementById('filepicker'));" onclick="MousePosition.init();" id="caption" class="functionbox-capation-box">
		<div class="functionbox-capation">
			Select iso file
			<input type="button" onclick="document.getElementById('filepicker').style.display = 'none';" value="X" class="functionbox-closebutton" id="close" class="filepickclose">
		</div>
	</div>
	<div id="canvas">
<table border="1" id="Table" class="filepicker_table">
<tbody id="isofilez">

</tbody></table>
</div>
</div>
-->


<div id="modal-volume" class="modal fade">
  <div class="modal-dialog">
    <div class="modal-content">
      <div class="modal-header">
        <button aria-label="Close" data-dismiss="modal" class="close" type="button"><span aria-hidden="true">×</span></button>
        <h4 class="modal-title">Disk Management</h4>
      </div>
      <div class="modal-body">
				 	<table class="table table-striped table-hover" id="moredisktbl">
				 	<tr class="warning">
				 		<td style="display:none">Type</td><td style="display:none">Name</td><td>Size</td><td class="text-center">Action</td>
				 	
				 	</tr>
				 	
				 	</table>
      </div>
      <div class="modal-footer">
        <button data-dismiss="modal" class="btn btn-default" type="button">Close</button>
      </div>
    </div><!-- /.modal-content -->
  </div><!-- /.modal-dialog -->
</div>



<div id="modal-volumeadd" class="modal fade">
  <div class="modal-dialog">
    <div class="modal-content">
      <div class="modal-header">
        <button aria-label="Close" data-dismiss="modal" class="close" type="button"><span aria-hidden="true">×</span></button>
        <h4 class="modal-title">Add one more volume</h4>
      </div>
      <div class="modal-body">
      		<p style="display:none"> You can add few volumes here. Available space for it is <span id="freembsp">{freemb}</span> </p>
				 	<div class="moredisk">
				 		<span>Input volume information:</span><br/><br/>
				 		<input  type="text" id="namevolumeinput"/><br/>
				 		<div class="selecto">
				 		<select id="typevolumeselect">
				 			<!--<option value="qcow2">qcow2</option> -->
				 			<option value="raw">raw</option>
				 		</select></div><br/>
				 		<div class="selecto">
				 		<label>Size:</label> 	{volumeselect}
				 		</div>
				 		<br/><br/><br/>
				 	</div>
      </div>
      <div class="modal-footer">
        <button data-dismiss="modal" class="btn btn-default" type="button">Close</button>
        <button data-dismiss="modal" class="btn btn-success" type="button" id="addvolumebtnvv">Add</button>
      </div>
    </div><!-- /.modal-content -->
  </div><!-- /.modal-dialog -->
</div>


<div id="volumepopup" class="modal-dialog">
<div class="panel">
                    
                                <!-- Classic Form Wizard -->
                                <!--===================================================-->
                                <div id="demo-cls-wz">
                    
                                    <!--Nav-->
                                    <ul class="wz-nav-off wz-icon-inline wz-classic">
                                        <li class="col-xs-3 bg-info active">
                                            <a href="#demo-cls-tab1" data-toggle="tab" aria-expanded="true">
                                                <span class="icon-wrap icon-wrap-xs bg-trans-dark"><i class="fa fa-cloud"></i></span> Fortis
                                            </a>
                                        </li>
                                        <div class="volumepopupclass"><a id="volumepopupclose"><i class="fa fa-icon fa-close"></i></a></div>
                                        
                                    </ul>
                    
                                    <!--Progress bar-->
                                    <div class="progress progress-sm progress-striped active">
                                        <div class="progress-bar progress-bar-info" style="width: 100%;"></div>
                                    </div>
                    
                    
                                    <!--Form-->
                                    <div class="form-horizontal mar-top">
                                        <div class="panel-body">
                                            <div class="tab-content">
                    
                                                <!--First tab-->
                                                <div class="tab-pane active in" id="demo-cls-tab1">
                                                    <div id="storageform">
                                                    
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
