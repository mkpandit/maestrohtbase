<!--
/*
    htvcenter Enterprise developed by htvcenter Enterprise GmbH.

    All source code and content (c) Copyright 2012, htvcenter Enterprise GmbH unless specifically noted otherwise.

    This source code is released under the htvcenter Enterprise Server and Client License, unless otherwise agreed with htvcenter Enterprise GmbH.
    The latest version of this license can be found here: http://htvcenter-enterprise.com/license

    By using this software, you acknowledge having read this license and agree to be bound thereby.

                http://htvcenter-enterprise.com

    Copyright 2012, htvcenter Enterprise GmbH <info@htvcenter-enterprise.com>
*/
//-->
<script type="text/javascript">
MousePosition.init();
var filepicker = {
	init : function() {
		mouse = MousePosition.get();
		document.getElementById('canvas').innerHTML = '<img src="{baseurl}/img/loading.gif" style="margin-top:150px;">';
		document.getElementById('filepicker').style.left = (mouse.x + -120)+'px';
		document.getElementById('filepicker').style.top  = (mouse.y - 180)+'px';
		document.getElementById('filepicker').style.display = 'block';
		$.ajax({
			url: "{baseurl}/api.php?action=plugin&plugin=xen&controller=xen-vm&{actions_name}=filepicker&path=/&appliance_id={appliance_id}",
			dataType: "text",
			success: function(response) {
				document.getElementById('canvas').innerHTML = response;	
			}
		});
	},
	browse : function(target) {
		document.getElementById('canvas').innerHTML = '<img src="{baseurl}/img/loading.gif" style="margin-top:150px;">';
		$.ajax({
			url: "{baseurl}/api.php?action=plugin&plugin=xen&controller=xen-vm&{actions_name}=filepicker&path="+target+"&appliance_id={appliance_id}",
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
}
</script>

<h2>{label}</h2>


<div id="form">
	<form action="{thisfile}" method="GET">
	{form}


<div class="tab-base ochtabz">
					
								<!--Nav Tabs-->
								<ul class="nav nav-tabs">
									<li class="active">
										<a data-toggle="tab" href="#demo-lft-tab-1">{lang_basic}</a>
									</li>
									<li>
										<a data-toggle="tab" href="#demo-lft-tab-2">{lang_hardware}</a>
									</li>
									<li>
										<a data-toggle="tab" href="#demo-lft-tab-3">{lang_net}</a>
									</li>
									<li>
										<a data-toggle="tab" href="#demo-lft-tab-4">{lang_boot}</a>
									</li>
									
								</ul>

	<div class="tab-content">
	<div id="demo-lft-tab-1" class="tab-pane fade active in">

	<fieldset>
		<legend>{lang_basic}</legend>
			{name}
	</fieldset>
	</div>

	<div id="demo-lft-tab-2" class="tab-pane fade ochaddhrdw">
	<fieldset>
		<legend>{lang_hardware}</legend>
		<div style="float:left;">
		{cpus}
		{memory}
		</div>
		<div style="float:right; width: 500px;">
			<br>
			<br>
			<br>
			{add_image}
		</div>
		<div class="floatbreaker" style="line-height:0px;height:0px;clear:both;">&nbsp;</div>
	</fieldset>
	</div>

	<div id="demo-lft-tab-3" class="tab-pane fade ochaddnetz">
	<fieldset>
		<legend>{lang_net}</legend>
		<div style="float:left;">
		<fieldset style="">
			
			{mac}
			{bridge}
		</fieldset>
		</div>
		<div style="float:right; width: 500px;">
			<br>
			<br>
			{add_networks}
		</div>
		<div class="floatbreaker" style="line-height:0px;height:0px;clear:both;">&nbsp;</div>


		<div>
			<fieldset style="float:left;">
				<div class="netbox">
				<legend>Network 2</legend>
				
				{net1}
				{mac1}
				{bridge1}
				</div>
			</fieldset>
			<fieldset style="float:right;">
				<div class="netbox netbox3">
				<legend>Network 3</legend>
				
				{net2}
				{mac2}
				{bridge2}
				</div>
			</fieldset>
			<div class="floatbreaker" style="line-height:0px;height:0px;clear:both;">&nbsp;</div>
		</div>

		<div>
			<fieldset style="float:left;">
				<div class="netbox">
				<legend>Network 4</legend>
				
				{net3}
				{mac3}
				{bridge3}
				</div>
			</fieldset>
			<fieldset style="float:right;">
				<div class="netbox netbox5">
				<legend>Network 5</legend>
				
				{net4}
				{mac4}
				{bridge4}
				</div>
			</fieldset>
			<div class="floatbreaker" style="line-height:0px;height:0px;clear:both;">&nbsp;</div>
		</div>
	</fieldset>
	</div>
	<div id="demo-lft-tab-4" class="tab-pane fade ochaddboot">
	<fieldset>
		<legend>{lang_boot}</legend>
		{boot_cd}
		<div>
			{boot_iso}
			{boot_iso_path}
			{browse_button}
			<div class="floatbreaker" style="line-height:0px;height:0px;clear:both;">&nbsp;</div>
		</div>
		{boot_net}
		{boot_local}
	</fieldset>
	</div>
	</div>
	<div id="buttons">{submit}&#160;{cancel}</div>

	</form>
</div>
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
if(document.getElementById('browsebutton')) {
	document.getElementById('browsebutton').style.display = 'inline';
}
</script>
