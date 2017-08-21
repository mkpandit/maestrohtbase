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
<h2>{label}</h2>
<div id="form">
	<div style="width:400px;float:left;">
		<div><b>{lang_id}</b>: <span id="appido">{id}</span></div>
		<div><b>{lang_name}</b>: {name}</div>
		<div><b>{lang_resource}</b>: {resource}</div>
		<div><b>{lang_state}</b>: {state}</div>
	</div>

	<div style="width:230px;float:right;">
		<div id="dsadder">{ds_add_pool}</div>
	</div>
	<div style="clear:both; margin: 0 0 25px 0;" class="floatbreaker">&#160;</div>

	{table}
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
                                                <span class="icon-wrap icon-wrap-xs bg-trans-dark"><i class="fa fa-plus-square"></i></span> Add pool
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
                                                    <div id="storageform" class="bros lolbros">
                                                    aa
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

<script type="text/javascript">
    var apido = $('#appido').text();
	MousePosition.init();
	function tr_hover() {}
	function tr_click() {}
	var filepicker = {
		init : function() {
			mouse = MousePosition.get();
			document.getElementById('canvas').innerHTML = '<img src="/htvcenter/base/img/loading.gif" style="margin-top:150px;">';
			document.getElementById('filepicker').style.left = (mouse.x + -120)+'px';
			document.getElementById('filepicker').style.top  = (mouse.y - 180)+'px';
			document.getElementById('filepicker').style.display = 'block';
			$.ajax({
				url: "/htvcenter/base/api.php?action=plugin&plugin=hyperv&controller=hyperv-vm&appliance_id="+apido+"&path=C:/&hyperv_vm_action=dirbrowser",
				dataType: "text",
				success: function(response) {
					document.getElementById('canvas').innerHTML = response;
				}
			});
		},
		browse : function(target) {
			document.getElementById('canvas').innerHTML = '<img src="/htvcenter/base/img/loading.gif" style="margin-top:150px;">';
			$.ajax({
				url: "/htvcenter/base/api.php?action=plugin&plugin=hyperv&controller=hyperv-vm&appliance_id="+apido+"&path="+target+"&hyperv_vm_action=dirbrowser",
				dataType: "text",
				success: function(response) {
					document.getElementById('canvas').innerHTML = response;
				}
			});
		},
		insert : function(value) {
			document.getElementById('path').value = value.replace("@", " "); ;
			document.getElementById('filepicker').style.display = 'none';
		}
	};

</script>

<div id="filepicker" style="display:none;position:absolute;top:15;left:15px;"  class="function-box">
		<div class="functionbox-capation-box"
				id="caption"
				onclick="MousePosition.init();"
				onmousedown="Drag.init(document.getElementById('filepicker'));"
				onmouseup="document.getElementById('filepicker').onmousedown = null;">
			<div class="functionbox-capation">
				Directorypicker
				<input type="button" id ="close" class="functionbox-closebutton" value="X" onclick="document.getElementById('filepicker').style.display = 'none';">
			</div>
		</div>
		<div id="canvas"></div>
	</div>
