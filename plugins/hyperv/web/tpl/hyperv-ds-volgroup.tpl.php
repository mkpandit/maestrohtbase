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
    var apido = $('#appido').text();
    var actionname = 'filebrowser';
    var maintarget='';
     
     $(document).ready(function(){
         apido = $('#appido').text();
         actionname = 'hyperv_vm_action';

      $('body').on('click', '.filepickerooo a', function(s){
           

            if ( $(this).text() == 'Create new Virtual Disk here') {
                 s.preventDefault();

                var urlstring = '/htvcenter/base/index.php?plugin=hyperv&controller=hyperv-ds&appliance_id='+apido+'&image_path='+maintarget+'&hyperv_ds_action=create';
                $('#storageform').html('');
                $('#storageform').load(urlstring+" form", function(){
                    $('.lead').hide();
                    $('#storageform').find('form').addClass('fixformhyperv');
                    $('#storageform select').selectpicker();
                    $('#storageform select').hide();
                    $('#volumepopup').show();
                }); 
            }
      });



     });

    MousePosition.init();
   
    function tr_hover() {}
    function tr_click() {}
    var filepicker = {
        init : function() {
            mouse = MousePosition.get();
            document.getElementById('canvas').innerHTML = '<img src="{baseurl}/img/loading.gif" style="margin-top:150px;">';
            document.getElementById('filepicker').style.left = (mouse.x + -120)+'px';
            document.getElementById('filepicker').style.top  = (mouse.y - 180)+'px';
            document.getElementById('filepicker').style.display = 'block';
            $.ajax({
                url: "{baseurl}/api.php?action=plugin&plugin=hyperv&controller=hyperv-vm&appliance_id="+apido+"&path=C:/&"+actionname+"=filebrowser",
                dataType: "text",
                success: function(response) {
                    document.getElementById('canvas').innerHTML = response;
                }
            });
        },
        browse : function(target) {
            maintarget = target;
            document.getElementById('canvas').innerHTML = '<img src="{baseurl}/img/loading.gif" style="margin-top:150px;">';
            $.ajax({
                url: "{baseurl}/api.php?action=plugin&plugin=hyperv&controller=hyperv-vm&appliance_id="+apido+"&path="+target+"&"+actionname+"=filebrowser",
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




<h2>{label}</h2>
<div id="form">
	<div style="width:400px;float:left;" id="vlolgaaa">
		<div><b>{lang_id}</b>: <span id="appido">{id}</span></div>
		<div><b>{lang_name}</b>: {name}</div>
		<div><b>{lang_resource}</b>: {resource}</div>
		<div><b>{lang_state}</b>: {state}</div>
	</div>
	
	<div style="width:230px;float:right;">
		<div>{ds_add}</div>
	</div>
	<div style="clear:both; margin: 0 0 25px 0;" class="floatbreaker">&#160;</div>

    <div class="listb">
	{table}
    </div>
</div>

<div id="volumepopup" class="modal-dialog volgrrripo volgrrripozz">
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
                                                    <div id="storageform" class="bbbrrr">
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


<div id="volumepopuprem" class="modal-dialog popupremdialog remremrem remach">
<div class="panel">
                    
                                <!-- Classic Form Wizard -->
                                <!--===================================================-->
                                <div id="demo-cls-wz">
                    
                                    <!--Nav-->
                                    <ul class="wz-nav-off wz-icon-inline wz-classic">
                                        <li class="col-xs-3 bg-info active">
                                            <a href="#demo-cls-tab1" data-toggle="tab" aria-expanded="true">
                                                <span class="icon-wrap icon-wrap-xs bg-trans-dark"><i class="fa fa-cog"></i></span> Volumes
                                            </a>
                                        </li>
                                        <div class="volumepopupclass"><a id="volumepopupremclose"><i class="fa fa-icon fa-close"></i></a></div>
                                        
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
                                                    <div id="storageformrem">
                                                  
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


    <div id="filepicker" style="display:none;position:absolute;top:15;left:15px;"  class="function-box filepickerooo">
        <div class="functionbox-capation-box"
                id="caption"
                onclick="MousePosition.init();"
                onmousedown="Drag.init(document.getElementById('filepicker'));"
                onmouseup="document.getElementById('filepicker').onmousedown = null;">
            <div class="functionbox-capation">
                Filepicker
                <input type="button" id ="close" class="functionbox-closebutton" value="X" onclick="document.getElementById('filepicker').style.display = 'none';">
            </div>
        </div>
        <div id="canvas"></div>
    </div>



<script type="text/javascript">
tmp = document.getElementById('browsebutton');
if(tmp) {
    tmp.style.display = 'inline';
}
</script>