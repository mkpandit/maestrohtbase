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
<div id="btnsideee">
	<!-- <span class="pull-right newstoragepop">{add}</span> -->
	<a class="add btn-labeled fa fa-plus-o newstoragepop" title="Add new storage" href="index.php?base=storage&amp;storage_filter=&amp;storage_action=add&amp;storage[sort]=storage_id&amp;storage[order]=ASC&amp;storage[limit]=20&amp;storage[offset]=0">Add new storage</a>
    <a class="add btn-labeled fa fa-hdd-o" id="stvolumesbtn" href="index.php?base=storage&storage_filter=&storage_action=load&splugin=kvm&kvm_action=edit&storage_id={storagekvmid}">Storage Volumes</a><br /><br />
    <a class="add btn-labeled fa fa-hdd-o" id="addnewdiskbtn" href="/htvcenter/base/index.php?base=storage&storage_action=diskadd">Add New Disk</a><br /><br />
	<a class="add btn-labeled fa fa-hdd-o" id="addnewdiskbtn" href="/htvcenter/base/index.php?base=storage&storage_action=memoryadd">Add Memory Disk</a><br /><br />
    <!--<a class="add btn-labeled fa fa-hdd-o" id="showstoragesbtn">Storage Details</a>
    <a class="add btn-labeled fa fa-hdd-o" id="showlizardbtn">HTSDS Details</a> -->
</div>

<div id="serverpanel">
                        <div class="col-sm-12 col-md-4 col-lg-4 col-sm-4">
                            <a href="/htvcenter/base/index.php?base=image">
                         <div class="panel media pad-all ">
                              
                                <div class="media-body">
                                    <p class="text-2x mar-no text-thin"><span class="icon-wrap icon-wrap-sm icon-circle bg-success">
                                    <i class="fa fa-upload"></i>
                                    </span>Images</p>
                                </div>

                            </div>
                            </a>
                         </div>



                         <!--<div class="col-sm-12 col-md-3 col-lg-3 col-sm-3">
                            <a href="/htvcenter/base/index.php?base=kernel">
                            <div class="panel media pad-all ">
                                <div class="media-left">
                                    <span class="icon-wrap icon-wrap-sm icon-circle bg-info">
                                    <i class="fa fa-rocket fa-2x"></i>
                                    </span>
                                </div>
                                <div class="media-body">
                                    <p class="text-2x mar-no text-thin">Kernels</p>
                                </div>
                            </div>
                        </a>
                        </div>-->


                        <div class="col-sm-12 col-md-4 col-lg-4 col-sm-4">
                            <a href="/htvcenter/base/index.php?base=resource">
                            <div class="panel media pad-all ">
                               
                                <div class="media-body">
                                    <p class="text-2x mar-no text-thin"><span class="icon-wrap icon-wrap-sm icon-circle bg-warning">
                                    <i class="fa fa-database"></i>
                                    </span>Resource</p>
                                </div>
                            </div>
                            </a>
                        </div>

                        <div class="col-sm-12 col-md-4 col-lg-4 col-sm-4">
                            <a href="/htvcenter/base/index.php?base=storage">
                            <div class="panel media pad-all ">
                               
                                <div class="media-body">
                                   
                                    <p class="text-2x mar-no text-thin"><span class="icon-wrap icon-wrap-sm icon-circle bg-danger">
                                    <i class="fa fa-hdd-o"></i>
                                    </span>Storage</p>
                                </div>
                            </div>
                            </a>
                        </div>
</div>


<div class="row">
	<div id="disk-management">
		<div id="pie-chart">
			<div id="info-hidden">
				<p class="free-space">Free Space: {free_storage_data}</p>
				<p class="used-space">Used Space: {used_storage_data}</p>
				<p>{percent_storage_data}</p>
			</div>
			<p>&nbsp;</p>
			<p>&nbsp;</p>
			<div id="storage-donut-diskadd"></div>
			<p>&nbsp;</p>
			<p class="total-space">Total Space: <b>{total_storage_data}</b> </p>
			<div id="storage-donut-type"></div>
		</div>
		<div id="add-new-disk-search">
			<input type="text" name="search_host" id="search-host" placeholder="Search host (192.168.0.123)" class="searchhost" />
			<div id="add-new-disk">
    			{html_information}
			</div>
		</div>
		<div id="disk-from-host">
			
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
                                        <li class="col-xs-3 bg-info active">
                                            <a href="#demo-cls-tab1" data-toggle="tab" aria-expanded="true">
                                                <span class="icon-wrap icon-wrap-xs bg-trans-dark"><i class="fa fa-hdd-o"></i></span> New Storage
                                            </a>
                                        </li>
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
                                                <div class="tab-pane active in" id="demo-cls-tab1">
                                                    <div id="storageform">
                                                    </div>
                                                </div>
                    
                                                
                                            </div>
                                        </div>
                    
                    
                                    </form>
                                </div>
                                <!--===================================================-->
                                <!-- End Classic Form Wizard -->
                    
                            </div>
</div>

<!-- Ajax call to get the available Host and Disk information -->
<script type="text/javascript">

function scanDevice(keyword){
	$.ajax({
	  url: 'index.php?base=storage&scandevice=yes&key='+keyword,
	})
	.done(function(data) {
		$('#add-new-disk').html('');
		$('#add-new-disk').append(data);
		$('#add-new-disk .disk').on('click', function(){  
			$(".disk-hd").hide();
			var divId = $(this).attr("id");
			var childDivID = $("#"+divId+" .disk-hd").attr("id");
			var ipAddress = $("#"+divId+" #ip-header").text();
			$('#disk-management #disk-from-host').html("<i class='fa fa fa-refresh fa-spin fa-2x'></i> Loading available disks from " + ipAddress);
			scanDisk(ipAddress);
		});
	})
	.fail(function() {
		alert("Ajax failed to fetch data");
	});
}

$("#addnewdiskbtn").on('click', function(){
	$('#add-new-disk').html("<div class='disk-loader'><i class='fa fa fa-spinner fa-spin fa-4x'></i><p>&nbsp;</p><p>Loading available resource hosts</p></div>");
	$('.searchhost').val('');
	$('#disk-management #disk-from-host').html('');
	scanDevice('');
	return false;
});

$(document).on('click', '.disk-bullet', function(){ 
	var pID = $(this).attr("id");
	if(pID.length > 0){
		var vals = $("#"+pID).text().split(" ");
		var ip = $("#"+pID).attr("data");
		var disk = vals[0];
		$(this).text("Mounting disk "+ disk + " from host " + ip + " ... ");
		mountdisk(ip, disk, pID)
	}
});

function mountdisk(ip, disk, pid){
	$.ajax({
		url: 'index.php?base=storage&mountdisk=yes&ipaddress='+ip+'&disk='+disk,
	}).done(function(data){
		$("#"+pid).html('');
		if(data){
			//$("#"+pid).html('<h4>Disk mounted on ' + ip +':</h4>');
			$("#"+pid).append(data);
		} else {
			$("#"+pid).html('<h4>Not mounted </h4>');
		}
	}).fail(function(){
		alert("Disk scanning failed for " + ipAddress);
	});
}

function scanDisk(ipAddress){
	$.ajax({
		url: 'index.php?base=storage&scandisk=yes&ipaddress='+ipAddress,
	}).done(function(data){
		$('#disk-management #disk-from-host').html('');
		if(data){
			$('#disk-management #disk-from-host').html('<h4>Disk available on ' + ipAddress +':</h4>');
			$('#disk-management #disk-from-host').append(data);
		} else {
			$('#disk-management #disk-from-host').html('<h4>No disk available on this </h4>');
		}
	}).fail(function(){
		alert("Disk scanning failed for " + ipAddress);
	});
}

$(document).ready(function(){
	
	scanDevice('');
	
	/* Searchbox Ajax Call */
	var searchHostRequest = null;
	var delay = (function(){
	  var timer = 0;
	  return function(callback, ms){
	    clearTimeout (timer);
	    timer = setTimeout(callback, ms);
	  };
	})();
	$('.searchhost').keyup(function() {
	    delay(function(){
		  if (searchHostRequest != null) {
		  	searchHostRequest.abort();
		  }
		  var key = $('.searchhost').val();
		  searchHostRequest = scanDevice(key);
	    }, 500 );
	});
	
	
	/* Render the Donut chart */
	var storage_list = htvcenter.get_storage_list();
	
	var storage_values = [];
	var storage_values2 = [];
	var deploment, deployment_list = [];
	var hist = {};
	
	$.each(storage_list, function(k,storage){
		deployment_list.push(storage['storage_type']);
	});
	deployment_list.map( function (a) { if (a in hist) hist[a] ++; else hist[a] = 1; } );
	
	$.each(hist, function(k,v){
		storage_values.push([k ,v]);
		storage_values2.push([k + ' (' +v+ ')',v]);
	})
	
	var legend = [];

	$.each(storage_values, function(k,v) {
		legend.push({label: v[0], value: v[1]});
	});	
	
	
	/* Processing donught chart for storages */
	var freeSpace = $("div#info-hidden p.free-space").text();
	var totalSpace = $("div#info-hidden p.total-space").text();
	var usedSpace = $("div#info-hidden p.used-space").text();
	
	var tFreeSpace = freeSpace;
	var tUsedSpace = usedSpace;
	
	freeSpace = parseFloat(freeSpace.replace(/[^\d\.]*/g, '')); 
	usedSpace = parseFloat(usedSpace.replace(/[^\d\.]*/g, ''));
	
	
	freeSpaceUnit = tFreeSpace.slice(-2);
	usedSpaceUnit = tUsedSpace.slice(-2);
	
	if(freeSpaceUnit != usedSpaceUnit) {
		if(usedSpaceUnit == "KB"){
			tUsedSpace = usedSpace / ( 1024 * 1024 ) ;
		}
		else if(usedSpaceUnit == "MB"){
			tUsedSpace = usedSpace / 1024;
		}
	} else {
		tUsedSpace = usedSpace;
	}
	
	Morris.Donut({
	  	element: 'storage-donut-diskadd',
	  	data: [
	    	{label: "Free Space", value: freeSpace},
			{label: "Used Space", value: usedSpace}
	  	],
		colors: [
			'#a6c600',
			'#177bbb',
			'#afd2f0',
			"#1fa67a",  "#ffd055", "#39aacb", "#cc6165", "#c2d5a0", "#579575", "#839557", "#958c12", "#953579", "#4b5de4", "#d8b83f", "#ff5800", "#0085cc"
		],
		resize:true
	});
	
	/*Morris.Donut({
	  	element: 'storage-donut-type',
	  	data: legend,
		colors: [
			'#a6c600',
			'#177bbb',
			'#afd2f0',
			"#1fa67a",  "#ffd055", "#39aacb", "#cc6165", "#c2d5a0", "#579575", "#839557", "#958c12", "#953579", "#4b5de4", "#d8b83f", "#ff5800", "#0085cc"
		],
		resize:true
	});*/
});

</script>