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
//-->

<link href="/cloud-fortis/css/vender/bootstrap/css/utilities.css" rel="stylesheet" type="text/css">
<link href="/cloud-fortis/css/vender/bootstrap/css/card.css" rel="stylesheet" type="text/css">
<link href="/cloud-fortis/designplugins/datatables/media/css/jquery.dataTables.min.css" rel="stylesheet" type="text/css">
<script src="https://cdn.datatables.net/1.10.15/js/jquery.dataTables.min.js" type="text/javascript"></script>

<style>
	#project_tab_ui { display: none; }
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
	.op-button{
		display: none;
	}
</style>

<span id="storagekvmid">{storagekvmid}</span>

<h2>{label}<span class="pull-right" id="servadddd">{add}</span></h2>

<div id="serverpanel" class="row">
	
	<div class="col-sm-12 col-md-4 col-lg-4 col-sm-4">
		<a href="/htvcenter/base/index.php?base=image">
		<div class="panel media pad-all ">
			<div class="media-body">
				<p class="text-2x mar-no text-thin"><span class="icon-wrap icon-wrap-sm icon-circle bg-success"><i class="fa fa-upload"></i></span>Images</p>
			</div>
		</div>
		</a>
	</div>
	
	<div class="col-sm-12 col-md-4 col-lg-4 col-sm-4">
		<a href="/htvcenter/base/index.php?base=resource">
		<div class="panel media pad-all ">
			<div class="media-body">
				<p class="text-2x mar-no text-thin"><span class="icon-wrap icon-wrap-sm icon-circle bg-warning"><i class="fa fa-database"></i></span>Resource</p>
			</div>
		</div>
		</a>
	</div>

	<div class="col-sm-12 col-md-4 col-lg-4 col-sm-4">
		<a href="/htvcenter/base/index.php?base=storage">
		<div class="panel media pad-all ">
			<div class="media-body">
				<p class="text-2x mar-no text-thin"><span class="icon-wrap icon-wrap-sm icon-circle bg-danger"><i class="fa fa-hdd-o"></i></span>Storage</p>
			</div>
		</div>
		</a>
	</div>
</div>

<div id="form">
	<form action="{thisfile}" method="POST">
		{form}
		<!-- {resource_filter}
		{resource_type_filter}-->
		<div class="search-elements">
			{resource_type_filter}
			<!-- <div id="pagination"> {pagerContainer} </div> -->
		</div>
		
		<div class="divTable">
			<!-- <div class="headRow">
				<div class="divCell-big">
					<div class="divCell" style="width: 13%;">VM Name</div>
					<div class="divCell" style="width: 15%;">VM IP</div>
					<div class="divCell" style="width: 18%;">Image</div>
					<div class="divCell" style="width: 10%;">Total<br />Memory</div>
					<div class="divCell" style="width: 10%;">Memory<br/>Used</div>
					<div class="divCell" style="width: 10%;">CPU</div>
					<div class="divCell" style="width: 10%;">CPU<br />Used</div>
					<div class="divCell" style="width: 10%;">Status</div>
				</div>
			</div> -->
			{div_html}
		</div>
		
	</form>
</div>

<div id="volumepopup" class="modal-dialog">
	<div class="panel">
		<!-- Classic Form Wizard -->
		<!--===================================================-->
		<div id="demo-cls-wz">
			<!--Nav-->
			<ul class="wz-nav-off wz-icon-inline wz-classic">
				<li class="col-xs-3 bg-info active">
					<a href="#demo-cls-tab1" data-toggle="tab" aria-expanded="true"><span class="icon-wrap icon-wrap-xs bg-trans-dark"><i class="fa fa-server"></i></span> Server Alert</a>
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
							<div id="storageform"></div>
						</div>
					</div>
				</div>
			</div>
		</div>
		<!--===================================================-->
		<!-- End Classic Form Wizard -->
	</div>
</div>



<div id="volumepopupvmf" class="modal-dialog">
	<div class="panel">
		<!-- Classic Form Wizard -->
		<!--===================================================-->
		<div id="demo-cls-wz">
			<!--Nav-->
			<ul class="wz-nav-off wz-icon-inline wz-classic">
				<li class="col-xs-3 bg-info active"><a href="#demo-cls-tab1" data-toggle="tab" aria-expanded="true"><span class="icon-wrap icon-wrap-xs bg-trans-dark"><i class="fa fa-server"></i></span> Server Action</a></li>
				<div class="volumepopupclass"><a id="volumepopupclosevmf"><i class="fa fa-icon fa-close"></i></a></div>
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
							<div id="actionvmf"></div>
							<div id="storageformvmf"></div>
						</div>
					</div>
				</div>
			</div>
		</div>
		<!--===================================================-->
		<!-- End Classic Form Wizard -->
	</div>
</div>


<script>
$(document).ready(function() {
		
	function format (d) {
	
		var onclickHtml = '<section class="card-maestro">'+
		'<div class="card-header card-header-top">'+
			
			'<div class="d-inline-block col-sm-3 text-left">'+
				'<span>'+d[1]+'</span>'+
			'</div>'+
			'<div class="d-inline-block col-sm-9 text-right">';
				if (d[10]){
					if (d[10].indexOf('stop') !== -1){
						onclickHtml = onclickHtml + '<span><i class="fa fa-stop"></i> '+d[10]+'</span>';
					} else {
						onclickHtml = onclickHtml + '<span><i class="fa fa-play"></i> '+d[10]+'</span>';
					}
				}
				if (d[11]){
					onclickHtml = onclickHtml + '<span><i class="fa fa-edit"></i> '+d[11]+'</span>';
				}
				if (d[12]) {
					onclickHtml = onclickHtml + '<span><i class="fa fa-refresh"></i> '+d[12]+'</span>';
				}
				onclickHtml = onclickHtml + '</div>'+
			'</div>'+
		'</section>';
		return onclickHtml;
	}

	var dt = $("#cloud_appliances_table").DataTable( {
		"columns": [
				{ "visible": false },
				null, null, null, null, null, null, null, null,
				{ "orderable": false },
				{ "visible": false },
				{ "visible": false },
				{ "visible": false },
				{ "visible": false },
		],
		"order": [], "bLengthChange": false, "pageLength": 10, "search": { "regex": true }, "bAutoWidth": true
	});

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
			row.child().addClass('hv-bg')
		}
	});
	
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

	function noVNCPOPUP(url) {
		//path = "{url}";
		noVncWindow = window.open(url, "noVnc_{port}", "titlebar=no, location=no, scrollbars=yes, width=800, height=500, top=50");
		noVncWindow.focus();
	}
	$('a#novnc-popup').on('click', function(){
		var storagelink = $(this).attr('href');
		noVNCPOPUP(storagelink);
 		/*$('#novncpopup').load(storagelink);
		$('#volumepopupvnc').show();*/
		return false;
	});
	$('#volumepopupvncclose').click(function(){
		$('#volumepopupvnc').hide();
	});
	
	$(".divRow").click(function(){
		var showClass = 'child-div-'+$(this).attr('id');
		if($("."+showClass).is(":visible")){
			$("."+showClass).hide();
		} else {
			$("."+showClass).css('display', 'block');
		}
	});
	$('#servadddd').click(function(e){
		e.preventDefault();
		$('.lead').hide();
	  		var storagelink = $(this).find('a.add').attr('href');
	 		$('#storageformaddn').load(storagelink+" #step1", function(){
	  			$('.lead').hide();
	  			$('#storageformaddn select').selectpicker();
	  			$('#storageformaddn select').hide();
	  			var heder = $('#appliance_tab0').find('h2').text();

				if (heder == 'ServerAdd a new Server') {
					$('#storageformaddn').find('#name').css('left','-20px');
				}
				$('#storageformaddn').find('#info').remove();
  				$('#volumepopupaddn').show();
	  		});  			
	});
	$('#volumepopupcloseaddn').click(function(){
		$('#volumepopupaddn').hide();
	});
</script>