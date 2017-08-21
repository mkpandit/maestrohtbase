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





<h1 class="app">{label}</h1>
{table}


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
