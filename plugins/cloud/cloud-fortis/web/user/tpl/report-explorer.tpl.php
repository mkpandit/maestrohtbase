<style>
    #project_tab_ui { display: none; }  /* hack for tabmenu issue */
</style>
<script>
//var nocontent = true;
var nocontent = false;
var explorer = true;
</script>
</div></div>
<link href="/cloud-fortis/designplugins/datatables/media/css/jquery.dataTables.min.css" rel="stylesheet" type="text/css">
<script src="https://cdn.datatables.net/1.10.15/js/jquery.dataTables.min.js" type="text/javascript"></script>
<script>
$(document).ready(function() {
	var dt = $("#score_cloud_appliances_table").DataTable( {
		"columns": [
				null, null, null, null, null, null, null, null, null,
		],
		"order": [], "bLengthChange": false, "pageLength": 20, "search": { "regex": true }, "bAutoWidth": true
	});
});
</script>

<div class="col-xs-12 col-sm-9 col-md-10 col-lg-10 windows_plane">
<div id="home_container">
	<div class="row paddingrow cost-explorer">
		<div class="cost-explorer-header">
			<h3 class="text-black d-inline"><strong>Instance Cost Explorer</strong></h3>
		</div>
    	<div id="server-cost-table-data" class="row">
			<p>Loading cost data ...</p>
		</div>
	</div>
</div>
</div>
		<!-- <div class="col-xs-6 col-sm-6 col-md-6 col-lg-6">
    		<div class="jcarousel carcarcar">
    			<ul id="namespaces">
    			</ul>
    		</div>
    		<div class="carcarbtn">
    			<a class="btn buttoncarouselback btn-primary"><i class="fa fa-arrow-left"></i> Previous</a><a class="btn buttoncarousel btn-primary">Next <i class="fa fa-arrow-right"></i></a>
    		</div>

    	</div>

    	<div class="col-xs-6 col-sm-6 col-md-6 col-lg-6">

    		<div class="panel panel-primary">
					
								<!--Panel heading-->
								<!-- <div class="panel-heading">
									<div class="panel-control">
					
										<!--Nav tabs-->
										<!-- <ul class="nav nav-tabs">
											<li class="active"><a href="#demo-tabs-box-1" data-toggle="tab">Resources</a>
											</li>
											<li><a href="#demo-tabs-box-2" data-toggle="tab">Explorer</a>
											</li>
										</ul>
					
									</div>
									<h3 class="panel-title">Instance Details</h3>
								</div>
					
								<!--Panel body-->
								<!-- <div class="panel-body">
					
									<!--Tabs content-->
									<!-- <div class="tab-content">
										<div class="tab-pane fade in active" id="demo-tabs-box-1">
											<h4 class="text-thin">Resources Information</h4>
											<p>
											   <table class="lowtable">
												<tr><td><b>CPU</b>: </td><td> <span id="cpuexp"></span></td></tr>
								    			<tr><td><b>Memory</b>: </td><td> <span id="memoryexp"></span></td></tr>
								    			<tr><td><b>Storage</b>: </td><td> <span id="storageexp"></span></td></tr>
								    			<tr><td><b>Status</b>: </td><td> <span id="statusexp"></span></td></tr>
								    			</table>
											</p>
										</div>
										<div class="tab-pane fade" id="demo-tabs-box-2">
											<h4 class="text-thin">Explorer</h4>
											<p>
											   <table class="lowtable">
												<tr><td><b>Creation Date</b>:</td><td> <span id="creationexp"></span></td></tr>
	    										<tr><td><b>Working Time</b>:</td><td> <span id="timeexp"></span></td></tr>
	    										<tr><td><b>Total Cost</b>:</td><td> <span id="totalexp"></span></td></tr>
	    									   </table>
											</p>
										</div>
									</div>
								</div>
							</div>
    		
    		</div>
    	</div>
    </div>                                          
</div>
</div> -->