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
<script type="text/javascript">
//<![CDATA[
var lang_systems = "{lang_systems}";
var lang_disk = "{lang_disk}";
var lang_memory = "{lang_memory}";
var lang_cpu = "{lang_cpu}";
var lang_network = "{lang_network}";
//]]>
</script>
<style>
	#project_tab_ui { display: none; }  /* hack for tabmenu issue */
	/* #chart-area { background-color: #fff; } */
   .c3-chart-arcs .c3-chart-arcs-title { font-size: 20px; }
   .c3-chart { height: 14rem; }
   .chartjs-chart { height: 172px; }
   .chartjs-chart .chart-legend li span{
	    display: inline-block;
	    width: 10px;
	    height: 10px;
	    margin-right: 3px;
	}
   .c3-axis-y-label{ font-size: 0.9em; }
   .current-month { min-height: 250px; padding-top: 15px;}
   .current-month .form-label { width : 30%; min-width: auto; color: #515151; font-size: 16px; }
   .current-month .form-control { width: 70%; }
   .current-month .form-group { margin-bottom: 0.5rem; }
</style>
<!--
<script src="/cloud-fortis/js/jqplot.jquery.min.js" type="text/javascript"></script>
<script src="/cloud-fortis/js/excanvas.min.js" type="text/javascript"></script>
<script src="/cloud-fortis/js/jquery.jqplot.min.js" type="text/javascript"></script>
<script src="/cloud-fortis/js/jqplot.donutRenderer.min.js" type="text/javascript"></script>
<script src="/cloud-fortis/js/jqplot.canvasTextRenderer.min.js" type="text/javascript"></script>
<script src="/cloud-fortis/js/jqplot.categoryAxisRenderer.min.js" type="text/javascript"></script>
-->
<script src="/cloud-fortis/js/c3/d3.v3.min.js" type="text/javascript"></script>
<script src="/cloud-fortis/js/c3/c3.min.js" type="text/javascript"></script>
<script src="/cloud-fortis/js/chartjs/Chart.bundle.min.js" type="text/javascript"></script>
<script src="/cloud-fortis/js/chartjs/utils.js" type="text/javascript"></script>
<script src="/cloud-fortis/js/fetch-report.js" type="text/javascript"></script>
<script src="/cloud-fortis/js/cloud.ui.home.js" type="text/javascript"></script>


<!-- </div></div> -->

<!-- <div class="col-sm-12 col-sm-9 col-md-10 col-lg-10 windows_plane"> -->
<div class="cat__content">
	<cat-page>
	<!--
	<section class="card">	
	<div class="card-header">
        <span class="cat__core__title">
            <strong>Dashboard</strong>
        </span>
    </div>
	-->
	<div class="row">
		<!-- left column -->
		<div class="col-sm-12 col-lg-3">
			<div class="row">
				<div class="col-sm-12 dashboard">
					<section class="card">	
						<div class="card-header">
					        <span class="cat__core__title">
					            <strong>Score Overview</strong>
					        </span>
					    </div>
					    <div class="card-block half">
					    	<div class="panel-heading">
								<div class="panel-control">
								</div>
								<h3 class="panel-title">IP Management</h3>
							</div>
							<div>
								<p><b>VLAN Name: </b>{ip_mgmt_name}</p>
								<p><b>VLAN ID: </b>{ip_mgmt_name}</p>
								<p><b>IP Range: </b>{ip_mgmt_range_start} ~ {ip_mgmt_range_end}</p>
							</div>
						</div>
					</section>
				</div>
			</div>
			<div class="row">
				<div class="col-sm-12 dashboard">
					<section class="card">	
						<div class="card-header">
					        <span class="cat__core__title">
					            <strong>Events</strong>
					        </span>
					    </div>
					    <div class="card-block half">
					    	<div class="panel-heading">
								<div class="panel-control">
								</div>
								<h3 class="panel-title">&nbsp;</h3>
							</div>
							<div>
								<p><b>Error: </b>0</p>
								<p><b>Info: </b>0</p>
							</div>
						</div>
					</section>
				</div>
			</div>
			<div class="row">
				<div class="col-sm-12 dashboard">
					<section class="card">	
						<div class="card-header">
					        <span class="cat__core__title">
					            <strong>VM Summary</strong>
					        </span>
					    </div>
					    <div class="card-block full">
					    	<div class="panel-heading">
								<div class="panel-control">
								</div>
								<h3 class="panel-title">Services</h3>
							</div>
							<div id="chartdiv-inventory-systems-legend" class="donut-chart-legend"></div>
							<div id="chartdiv-inventory-systems" class="c3-chart"></div>
						</div>
					</section>
				</div>
			</div>		
		</div>
		<!-- end left column -->	
		<!-- right column -->
		<div class="col-sm-12 col-lg-9">
			<div class="row">
				<div class="col-sm-12">
					<section class="card">	
						<div class="card-header">
					        <span class="cat__core__title">
					            <strong>Resource Consumption</strong>
					        </span>
					    </div>
					    <div class="card-block full">
							<div class="col-sm-12 col-md-6 col-lg-3 dashboard pull-left">
								<div class="panel-heading">
									<div class="panel-control">
									</div>
									<h3 class="panel-title">Services</h3>
								</div>
								<div id="chartdiv-inventory-memory-legend" class="donut-chart-legend"></div>
								<div id="chartdiv-inventory-memory" class="c3-chart"></div>
							</div>

							<div class="col-sm-12 col-md-6 col-lg-3 dashboard pull-left">
								<div class="panel-heading">
									<div class="panel-control">	
									</div>
									<h3 class="panel-title">Services</h3>
								</div>
								<div id="chartdiv-inventory-cpu-legend" class="donut-chart-legend"></div>
								<div id="chartdiv-inventory-cpu" class="c3-chart"></div>
							</div>

							<div class="col-sm-12 col-md-6 col-lg-3 dashboard pull-left">
								<div class="panel-heading">
									<div class="panel-control">	
									</div>
									<h3 class="panel-title">Services</h3>
								</div>
								<div id="chartdiv-inventory-disk-legend" class="donut-chart-legend"></div>
								<div id="chartdiv-inventory-disk" class="c3-chart"></div>
							</div>

							<div class="col-sm-12 col-md-6 col-lg-3 dashboard pull-left">
								<div class="panel-heading">
									<div class="panel-control">	
									</div>
									<h3 class="panel-title">Services</h3>
								</div>
								<div id="chartdiv-inventory-network-legend" class="donut-chart-legend"></div>
								<div id="chartdiv-inventory-network" class="c3-chart"></div>
							</div>
						</div>
					</section>
				</div>
			</div>
			<div class="row">
				<div class="col-sm-12 col-lg-4 dashboard">
					<section class="card">	
						<div class="card-header">
					        <span class="cat__core__title">
					            <strong>Current Billing</strong>
					        </span>
					    </div>
					    <div class="card-block full">
					    	<div class="panel-heading">
								<div class="panel-control">
								</div>
								<h3 class="panel-title">{current_month}</h3>
							</div>
							<div id="chartdiv-inventory-monthlybilling-legend" class="donut-chart-legend"></div>
							<div class="chartjs-chart">	
								<canvas id="chartdiv-inventory-monthlybilling" ></canvas>
							</div>
							<p style="text-align: center"><b>Monthly Total: </b><span id="monthlybilling-total"> --- </span></p>	
							
							<!--
							<div class="current-month">
								<div class="form-group row">
                                	<label class="form-label" for="cpu">CPU</label>
                               		<input class="form-control" id="cpu" type="text" disabled>
                            	</div>
								<div class="form-group row">
                                	<label class="form-label" for="storage">Storage</label>
                               		<input class="form-control" id="storage" type="text" disabled>
                            	</div>
                            	<div class="form-group row">
                                	<label class="form-label" for="memory">Memory</label>
                               		<input class="form-control" id="memory" type="text" disabled>
                            	</div>
								<div class="form-group row">
                                	<label class="form-label" for="virtualization">Virtualization</label>
                               		<input class="form-control" id="virtualization" type="text" disabled>
                            	</div>
                            	<div class="form-group row">
                                	<label class="form-label" for="networking">Networking</label>
                               		<input class="form-control" id="networking" type="text" disabled>
                            	</div>
							</div>
							-->
						</div>
					</section>
				</div>
				<div class="col-sm-12 col-lg-8 dashboard">
					<section class="card">	
						<div class="card-header">
					        <span class="cat__core__title">
					            <strong>Spend Summary</strong>
					        </span>
					    </div>
					    <div class="card-block full">
					    	<div class="panel-heading">
								<h3 class="panel-title">Monthly Projection</h3>
							</div>
							<div>
								<div id="chartdiv-inventory-monthlychart-legend" class="donut-chart-legend"></div>
								<div id="chartdiv-inventory-monthlychart" class="c3-chart"></div>
							</div>
						</div>
					</section>
				</div>
			</div>
		</div>
		<!-- end right column -->	
	</div>		

	<form action="{thisfile}"></form>
	</cat-page>
</div>