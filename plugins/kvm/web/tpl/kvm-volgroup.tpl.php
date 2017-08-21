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
<h2>Edit Volume group</h2>

<div id="form">

	<div style="float:left; width:380px; margin: 15px 0 0 15px;">
		<div><b>{lang_id}</b>: <span id="storageid">{id}</span></div>
		<!--<div><b>{lang_name}</b>: {name}</div>-->
		<div><b>{lang_resource}</b>: {resource}</div>
		<!--<div><b>{lang_deployment}</b>: {deployment}</div>-->
		<div><b>{lang_state}</b>: {state}</div>
	</div>

	<div style="float: left; margin: 15px 0 0 0;" id="volgroupupdatewrap">
		<div id="volgroupupdate">
		<div><b>{lang_name}</b>: <span id="storagecount">{volgroup_name}</span></div>
		<div><b>{lang_attr}</b>: {volgroup_attr}</div>
		<!--<div><b>{lang_pv}</b>: {volgroup_pv} / {volgroup_lv} / {volgroup_sn}</div>-->
		<div><b>{lang_size}</b>: {volgroup_vsize} / {volgroup_vfree}</div>
		</div>
	</div>


	<div style="float:right; margin: 15px 20px 0 0;">
		<div id="add" class="popupvolumebtn">{add}</div>
		<div id="addschvol"><a class="add btn-labeled fa fa-plus"><span class="halflings-icon white plus-sign"><i></i></span>Schedule</a></div>
		
	</div>

	<div style="clear:both; margin: 0 0 25px 0;" class="floatbreaker">&#160;</div>
	<div id="tblbtn">
	<!-- table was here {table} -->
	</div>


	

</div>
<br/><br/><br/><br/><br/><br/><br/><br/>
<div id="treefiles">
{tree}
</div>


<span id="getChecked" class="btn btn-primary">Remove</span>

<script>
$(document).ready(function(){
	$('#treefiles').jstree({"plugins" : [ "wholerow", "checkbox", "types" ],
	"types" : {
	      "default" : {
	        "icon" : "fa fa-file-o fa-2x treefa"
	      },
	      "demo" : {
	        "icon" : "fa fa-folder fa-2x treefa"
	      }
	    },
});

	var width = $(window).width();

	if ( (width > 980) && (width < 1280)) {
		$('.divtxt').width('19%');
	}

	if ( (width > 1280) && (width < 1920)) {
		$('.divtxt').width('20%');
	}

	if ( (width > 1920) || (width == 1920) ) {
		$('.divtxt').width('22%');
	}



	$("#getChecked").click(function () {
		//var datasend = [];
		var parameters = [];
    	var selectedElmsIds = $('#treefiles').jstree("get_selected");
    	$(selectedElmsIds).each(function(i){
    		$('#nodisplay').text('');
    		var strtxt = $('#treefiles').jstree("get_path", selectedElmsIds[i] );
    		$('#nodisplay').append(strtxt);
    		var path = $('#nodisplay').find('.fspath:last').text(); 
    		console.log(path);
    		var imgid = $('#nodisplay').find('.unimgpath').text(); 
    		var datasend = {'path':path, 'imgid':imgid};

	    	parameters.push(datasend);
    	});
    	
    	var urlstring = window.location.href;
    	urlstring = urlstring + '&treeaction=remove';

    	wait();
		//console.log(parameters);
    	$.ajax({
			  type: 'POST',
			  url: urlstring,
			  data: {'data' : parameters},
			 
			  success: function(data){
			  		$('.lead').hide();
			  		
			  		
			  		if (data == 1) {
			  			blackalert('All Selected Files Removed Successfully!');
			  			setTimeout(function () {
						    window.location.reload();
						}, 3000); 
			  		} else {
			  			blackalert(data);
			  		}
			  }
		});
	});
});
</script>

<div id="nodisplay"></div>
<div id="volumepopup" class="modal-dialog volumepopup2">
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
													<div id="storageform" class="addkvmoforme">
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

