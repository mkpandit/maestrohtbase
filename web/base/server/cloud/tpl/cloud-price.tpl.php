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


<h2 class="inner">{label}</h2>

<div class="row">
	
	<div id="azure-disk-vm-list">
		<div class='multi-cloud'>
			{memory}
			{operating_system}
			{vcpu}
			<div id="buttons">
				<a class="add btn-labeled fa fa-usd" onclick="getCloudPrice(); return false;" href="#"><span class="halflings-icon white plus-sign"><i></i></span>Get Cloud Price</a>
			</div>
		</div>
		
		<div class="multi-cloud-price" id="cloud-price-result">

		</div>
		<div class="cloud-price-description">
			<p align="center">This graph compares the prices between Aamazon Web Service and Azure by Microsoft. To generate the graph, only the prices for different cores/cpu numbers has been considered. Other attributes like RAM, Storage, Location etc. has not considered. X-axis represents the number of cpus and Y-axis represents the prcie for one month.</p>
		</div>
	</div>
</div>

<div id="volumepopup" class="modal-dialog volumepopup3">
	<div class="panel">
		<!-- Classic Form Wizard -->
		<!--===================================================-->
		<div id="demo-cls-wz">
			<!--Nav-->
			<ul class="wz-nav-off wz-icon-inline wz-classic">
				<li class="col-xs-3 bg-info active"><a href="#demo-cls-tab1" data-toggle="tab" aria-expanded="true"><span class="icon-wrap icon-wrap-xs bg-trans-dark"><i class="fa fa-hdd-o"></i></span> New Storage </a></li>
				<div class="volumepopupclass"><a id="volumepopupclose"><i class="fa fa-icon fa-close"></i></a></div>
			</ul>
			<!--Progress bar-->
			<div class="progress progress-sm progress-striped active">
				<div class="progress-bar progress-bar-info" style="width: 100%;"></div>
			</div>

			<!--Form-->
			<form class="form-horizontal mar-top">
				<div class="panel-body">
					<div class="tab-content">
						<!--First tab-->
						<div class="tab-pane active in" id="demo-cls-tab1"><div id="storageform"></div></div>
					</div>
				</div>
			</form>
		</div>
		<!--===================================================-->
		<!-- End Classic Form Wizard -->
	</div>
</div>
<script type="text/javascript">
	function getCloudPrice(){
		var memory 						= $('#memory').find(":selected").text();
		var operating_system 			= $('#operating_system').find(":selected").text();
		var vcpu 						= $('#vcpu').find(":selected").text();
		
		$.ajax({
		  url: 'index.php?base=cloud&cloudprice=yes&memory='+memory+'&operating_system='+operating_system+'&vcpu='+vcpu,
		})
		.done(function(data) {
			$('#cloud-price-result').html('');
			$('.cloud-price-description').html('');
			$('#cloud-price-result').append(data);
		})
		.fail(function() {
			alert("Ajax failed to fetch data");
		});
		return false;
	}
	$(document).ready(function(){
		$.ajax({
		  url: 'index.php?base=cloud&graphprice=yes',
		})
		.done(function(data) {
			var rawGraphData = data; //{ y: '1', aws: temp[1], az: temp[11] },  { y: '3', aws: temp[3], az: temp[13] },  { y: '5', aws: temp[5], az: temp[15] },  { y: '12', aws: temp[7], az: temp[17] },  { y: '24', aws: temp[9], az: temp[19] },
			var temp = rawGraphData.split("_");
			var graphData = [
				{ y: '2', aws: temp[2], az: temp[12] },
				{ y: '4', aws: temp[4], az: temp[14] },
				{ y: '8', aws: temp[6],  az: temp[16] },
				{ y: '16', aws: temp[8],  az: temp[18] },
				{ y: '32', aws: temp[10],  az: temp[20] }
			];
			Morris.Line({
				element: 'cloud-price-result',
				data: graphData,
				xkey: 'y',
				ykeys: ['aws', 'az'],
				labels: ['AWS', 'Azure'],
				lineColors: ['#ff9900', '#3399ff'],
				lineWidth: 1,
				parseTime:false,
				resize: true
			});
		})
		.fail(function() {
			alert("Ajax failed to fetch data");
		});
	});
</script>