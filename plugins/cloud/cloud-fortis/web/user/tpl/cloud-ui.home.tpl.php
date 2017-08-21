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

<script src="/cloud-fortis/js/jqplot.jquery.min.js" type="text/javascript"></script>
<script src="/cloud-fortis/js/excanvas.min.js" type="text/javascript"></script>
<script src="/cloud-fortis/js/jquery.jqplot.min.js" type="text/javascript"></script>
<script src="/cloud-fortis/js/jqplot.donutRenderer.min.js" type="text/javascript"></script>
<script src="/cloud-fortis/js/jqplot.canvasTextRenderer.min.js" type="text/javascript"></script>
<script src="/cloud-fortis/js/jqplot.categoryAxisRenderer.min.js" type="text/javascript"></script>
<script src="/cloud-fortis/js/cloud.ui.home.js" type="text/javascript"></script>

</div></div>



<div class="col-xs-12 col-sm-9 col-md-10 col-lg-10 windows_plane">
<div id="home_container">


<div class="row">

		<div class="col-xs-12 col-sm-6 col-md-4 col-lg-4 heightspan panel dashboard">
			<div class="panel-heading">
									<div class="panel-control">
										
									</div>
									<h3 class="panel-title">Services</h3>
								</div>
			<div class="col-xs-6 col-sm-6 col-md-6 col-lg-6">					
			<div id="chartdiv-inventory-systems" class="charto"></div>
			</div>
			<div id="chartdiv-inventory-systems-legend" class="donut-chart-legend col-xs-6 col-sm-6 col-md-6 col-lg-6"></div>
		</div>

		<div class="col-xs-12 col-sm-6 col-md-4 col-lg-4 heightspan panel dashboard">
			<div class="panel-heading">
									<div class="panel-control">
										
									</div>
									<h3 class="panel-title">Services</h3>
								</div>
			<div class="col-xs-6 col-sm-6 col-md-6 col-lg-6">					
			<div id="chartdiv-inventory-disk" class="charto"></div>
			</div>
			<div id="chartdiv-inventory-disk-legend" class="donut-chart-legend col-xs-6 col-sm-6 col-md-6 col-lg-6"></div>
		</div>

		<div class="col-xs-12 col-sm-6 col-md-4 col-lg-4 texto heightspan panel dashboard">
			<div class="panel-heading">
									<div class="panel-control">
										
									</div>
									<h3 class="panel-title">{label_limits}</h3>
								</div>
			<div id="limitscontent">
			<p><b>{limit_resource}:</b> {resource_limit_value}</p>
			<p><b>{limit_disk}:</b> {disk_limit_value}</p>
			<p><b>{limit_memory}:</b> {memory_limit_value}</p>
			<p><b>{limit_cpu}:</b> {cpu_limit_value}</p>
			<p><b>{limit_network}:</b> {network_limit_value}</p>
			</div>
		</div>

<!--
		<div id="quicklinks">
			<h3>{label_quicklinks}</h3>
			<p>{quicklinks}</p>
		</div>
//-->
</div>
		<div class="floatbreaker" style="line-height:0px;clear:both;">&#160;</div>

<div class="row">
	
		<div class="col-xs-12 col-sm-6 col-md-4 col-lg-4 heightspan panel dashboard">
			<div class="panel-heading">
									<div class="panel-control">
										
									</div>
									<h3 class="panel-title">Services</h3>
								</div>
			<div class="col-xs-6 col-sm-6 col-md-6 col-lg-6">					
			<div id="chartdiv-inventory-memory" class="charto"></div>
			</div>
			<div id="chartdiv-inventory-memory-legend" class="donut-chart-legend col-xs-6 col-sm-6 col-md-6 col-lg-6"></div>
		</div>

		<div class="col-xs-12 col-sm-6 col-md-4 col-lg-4 heightspan panel dashboard">
			<div class="panel-heading">
									<div class="panel-control">
										
									</div>
									<h3 class="panel-title">Services</h3>
								</div>
			<div class="col-xs-6 col-sm-6 col-md-6 col-lg-6">					
			<div id="chartdiv-inventory-cpu" class="charto"></div>
			</div>
			<div id="chartdiv-inventory-cpu-legend" class="donut-chart-legend col-xs-6 col-sm-6 col-md-6 col-lg-6"></div>
		</div>

		<div class="col-xs-12 col-sm-6 col-md-4 col-lg-4 heightspan panel dashboard">
		<div class="panel-heading">
									<div class="panel-control">
										
									</div>
									<h3 class="panel-title">Services</h3>
								</div>
			<div class="col-xs-6 col-sm-6 col-md-6 col-lg-6">
			<div id="chartdiv-inventory-network" class="charto"></div>
			</div>
			<div id="chartdiv-inventory-network-legend" class="donut-chart-legend col-xs-6 col-sm-6 col-md-6 col-lg-6"></div>
		</div>
		<div class="floatbreaker" style="line-height:0px;clear:both;">&#160;</div>
	

</div>


<form action="{thisfile}"></form>

</div>
</div>