<!--
/*
    HyperTask Enterprise developed by HyperTask Enterprise GmbH.

    All source code and content (c) Copyright 2014, HyperTask Enterprise GmbH unless specifically noted otherwise.

    This source code is released under the HyperTask Enterprise Server and Client License, unless otherwise agreed with HyperTask Enterprise GmbH.
    The latest version of this license can be found here: http://htvcenter-enterprise.com/license

    By using this software, you acknowledge having read this license and agree to be bound thereby.

                http://htvcenter-enterprise.com

    Copyright 2014, HyperTask Enterprise GmbH <info@htvcenter-enterprise.com>
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
				url: "{baseurl}/api.php?action=plugin&plugin=vmware-vsphere&controller=vmware-vsphere-vm&appliance_id={appliance_id}&path=/&{actions_name}=filepicker",
				dataType: "text",
				success: function(response) {
					document.getElementById('canvas').innerHTML = response;
				}
			});
		},
		browse : function(target) {
			document.getElementById('canvas').innerHTML = '<img src="{baseurl}/img/loading.gif" style="margin-top:150px;">';
			$.ajax({
				url: "{baseurl}/plugins/vmware-vsphere/api.php?action=plugin&plugin=vmware-vsphere&controller=vmware-vsphere-vm&appliance_id={appliance_id}&path="+target+"&{actions_name}=filepicker",
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

	var passgen = {
		generate : function() {
			pass = GeneratePassword();
			document.getElementById('vnc').value = pass;
		},
		toggle : function() {
			vnc = document.getElementById('vnc');
			but = document.getElementById('passtoggle');
			if(vnc.type == 'password') {
				but.value = "{lang_password_hide}";
				np = vnc.cloneNode(true);
				np.type='text';
				vnc.parentNode.replaceChild(np,vnc);
			}
			if(vnc.type == 'text') {
				but.value = "{lang_password_show}";
				np = vnc.cloneNode(true);
				np.type='password';
				vnc.parentNode.replaceChild(np,vnc);
			}
		}
	}

	function namegen() {
		var name = "";
		var name_characters = "0123456789";
		var one_random_char;
		for (j=0; j<6; j++) {
			one_random_char = name_characters.charAt(Math.floor(Math.random()*name_characters.length));
			name += one_random_char;
		}
		document.getElementById('name').value = 'cs_'+name;
	}
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
<form action="{thisfile}" method="GET">
<div id="form">
	{form}
	{vm_id}
	<fieldset>
		<legend>{lang_basic}</legend>
		{name}
	</fieldset>

	<fieldset>
		<legend>{lang_hardware}</legend>
		{cpu}
		{memory}
	</fieldset>

<!--
	<fieldset>
		<legend>{lang_virtual_disk}</legend>
			{guestid}
	</fieldset>
//-->

	<fieldset>
		<legend>{lang_net}</legend>

		<div class="span7">
			<fieldset>
				<div style="float:left;">{net0}</div>
				<div id="net0box" class="netbox">
					{mac}
					{type}
					{vswitch}
				</div>
			</fieldset>

			<fieldset>
				<div style="float:left;">{net1}</div>
				<div id="net1box" class="netbox">
					{mac1}
					{type1}
					{vswitch1}
				</div>
			</fieldset>
			<script>
				if(document.getElementById('net1').checked == false) {
					document.getElementById('net1box').style.display = 'none';
				}
			</script>

			<fieldset>
				<div style="float:left;">{net2}</div>
				<div id="net2box" class="netbox">
					{mac2}
					{type2}
					{vswitch2}
				</div>
			</fieldset>
			<script>
				if(document.getElementById('net2').checked == false) {
					document.getElementById('net2box').style.display = 'none';
				}
			</script>

			<fieldset>
				<div style="float:left;">{net3}</div>
				<div id="net3box" class="netbox">
					{mac3}
					{type3}
					{vswitch3}
				</div>
			</fieldset>
			<script>
				if(document.getElementById('net3').checked == false) {
					document.getElementById('net3box').style.display = 'none';
				}
			</script>

			<fieldset>
				<div style="float:left;">{net4}</div>
				<div id="net4box" class="netbox">
					{mac4}
					{type4}
					{vswitch4}
				</div>
			</fieldset>
			<script>
				if(document.getElementById('net4').checked == false) {
					document.getElementById('net4box').style.display = 'none';
				}
			</script>
		</div>
	</fieldset>

	<fieldset>
		<legend>{lang_boot}</legend>
		{boot_local}
		<div>
			{boot_iso}
			{boot_iso_path}
			{browse_button}
			<div class="floatbreaker" style="line-height:0px;height:0px;clear:both;">&nbsp;</div>
		</div>
		{boot_net}
	</fieldset>

	<fieldset>
		<legend>{lang_vnc}</legend>
		<div style="float:left;">
			{vnc}
			{vncport}
			{vncserverip}
		</div>
		<div style="float:left; margin: 3px 0 0 15px;">
			<input type="button" id="passgenerate" onclick="passgen.generate(); return false;" class="password-button" value="{lang_password_generate}" style="display:none;">&#160;
			<input type="button" id="passtoggle" onclick="passgen.toggle(); return false;" class="password-button" value="{lang_password_show}" style="display:none;">
		</div>
		<div class="floatbreaker" style="line-height:0px;height:0px;clear:both;">&nbsp;</div>
	</fieldset>

	<div id="buttons">{submit}&#160;{cancel}</div>

</div>
</form>

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
document.getElementById('passgenerate').style.display = 'inline';
document.getElementById('passtoggle').style.display = 'inline';
</script>
