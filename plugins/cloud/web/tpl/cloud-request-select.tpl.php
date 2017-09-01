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
<h2>{title}</h2>
<script type="text/javascript">

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
		"position": "fixed",
		"top": "50px",
		"left": "280px" 
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
		url: "/htvcenter/base/api.php?action=plugin&plugin=cloud&controller=cloud-request&cloud-request=details&cloud_request_id=" + cr_id,
		cache: false,
		async: false,
		dataType: "text",
		success: function(response) {
			$("#cloudinfoArea").html(response);
		}
	});
}

</script>

<div id="cloudpopupInfo">
	<a id="cloudpopupInfoClose">x</a>
	<div id="cloudinfoScrollArea">
		<p id="cloudinfoArea">
	</p>
	</div>
</div>
<div id="cloudbackgroundPopup"></div>


<form action="{thisfile}">

{form}
<div id="form" class="thetableee">
	<div class="filter-left">
		{filter}
	</div>
	<div class="cleanup-right">
		<div>{clean_up}</div>
	</div>
	<div style="clear:both;" class="floatbreaker" >&#160;</div>
	<div class="cloud-table-class">
		{table}
	</div>
</div>


</form>


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
                                                    <div id="storageform" class="fortiso">
                                                    
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