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

<script type="text/javascript">
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
				url: "{baseurl}/api.php?action=plugin&plugin=hyperv&controller=hyperv-vm&appliance_id={appliance_id}&path=/&{actions_name}=filepicker",
				dataType: "text",
				success: function(response) {
					document.getElementById('canvas').innerHTML = response;
				}
			});
		},
		browse : function(target) {
			document.getElementById('canvas').innerHTML = '<img src="{baseurl}/img/loading.gif" style="margin-top:150px;">';
			$.ajax({
				url: "{baseurl}/plugins/hyperv/api.php?action=plugin&plugin=hyperv&controller=hyperv-vm&appliance_id={appliance_id}&path="+target+"&{actions_name}=filepicker",
				dataType: "text",
				success: function(response) {
					document.getElementById('canvas').innerHTML = response;
				}
			});
		},
		insert : function(value) {
			document.getElementById('iso_path').value = value;
			document.getElementById('filepicker').style.display = 'none';
		}
	};

</script>

<script>
	function nettoggle(element) {
		if(element.checked == false) {
			document.getElementById(element.name+'box').style.display = 'none';
		}
		else {
			document.getElementById(element.name+'box').style.display = 'block';
		}
	}
</script>

<h2>{label}</h2>

<div id="form">
	<form action="{thisfile}" method="GET">
	{form}

	<div class="tab-base span7">
          
                <!--Nav Tabs-->
                <ul class="nav nav-tabs">
                	<li class="active"><a data-toggle="tab" href="#demo-lft-tab-1">{lang_basic}</a></li>
                	<li><a data-toggle="tab" href="#demo-lft-tab-2">{lang_hardware}</a></li>
                	<li><a data-toggle="tab" href="#demo-lft-tab-3">{lang_virtual_disk}</a></li>
                	<li><a data-toggle="tab" href="#demo-lft-tab-4">{lang_net}</a></li>
                	<li><a data-toggle="tab" href="#demo-lft-tab-5">{lang_boot}</a></li>
                	
                </ul>
              

              <!--Tabs Content-->
            <div class="tab-content">
                 
                 <div id="demo-lft-tab-1" class="tab-pane fade active in">
					<h3>{lang_basic}</h3>
					{name}
				 </div>

				  <div id="demo-lft-tab-2" class="tab-pane fade">
				  		<h3>{lang_hardware}</h3>
						{cpu}
						{memory}
				  </div>


				  <div id="demo-lft-tab-3" class="tab-pane fade">
				  		<h3>{lang_virtual_disk}</h3>

						{disk}
						{pool}
						{existing_vhd}
						{netboot_image}
						{add_vm_image}
				
				</div>


				 <div id="demo-lft-tab-4" class="tab-pane fade">
				  		<h3>{lang_net}</h3>



		
			
				<div style="float:left;">{net0}</div>
				<div id="net0box" class="netbox">
					{mac}
					{vswitch}
				</div>
		

			
				<div style="float:left;">{net1}</div>
				<div id="net1box" class="netbox">
					{mac1}
					{vswitch1}
				</div>
			
			<script>
				if(document.getElementById('net1').checked == false) {
					document.getElementById('net1box').style.display = 'none';
				}
			</script>

			
				<div style="float:left;">{net2}</div>
				<div id="net2box" class="netbox">
					{mac2}
					{vswitch2}
				</div>
			
			<script>
				if(document.getElementById('net2').checked == false) {
					document.getElementById('net2box').style.display = 'none';
				}
			</script>

			
				<div style="float:left;">{net3}</div>
				<div id="net3box" class="netbox">
					{mac3}
					{vswitch3}
				</div>
			
			<script>
				if(document.getElementById('net3').checked == false) {
					document.getElementById('net3box').style.display = 'none';
				}
			</script>

			
				<div style="float:left;">{net4}</div>
				<div id="net4box" class="netbox">
					{mac4}
					{vswitch4}
				</div>
			
			<script>
				if(document.getElementById('net4').checked == false) {
					document.getElementById('net4box').style.display = 'none';
				}
			</script>
		
			</div>


		


				 

				  <div id="demo-lft-tab-5" class="tab-pane fade">
				  		<h3>{lang_boot}</h3>

						{boot_local}
					
						{boot_iso}
						{boot_iso_path}
						{browse_button}
						
						{boot_net}
				  </div>


				

	

	
	
	
		
	</div>
	</div>
	

	<div id="buttons">{submit}&#160;{cancel}</div>

	</form>
</div>

	<div id="filepicker" style="display:none;position:absolute;top:15;left:15px;"  class="function-box">
		<div class="functionbox-capation-box"
				id="caption"
				onclick="MousePosition.init();"
				onmousedown="Drag.init(document.getElementById('filepicker'));"
				onmouseup="document.getElementById('filepicker').onmousedown = null;">
			<div class="functionbox-capation">
				{lang_browser}
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
