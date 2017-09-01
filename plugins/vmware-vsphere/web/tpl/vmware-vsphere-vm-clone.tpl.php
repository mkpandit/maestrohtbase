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
		{clone_name}
	</fieldset>

	<fieldset>
		<legend>{lang_hardware}</legend>
		{resourcepool}
		{datastore}
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
				<div style="float:left;">{lang_net_0}</div>
				<div id="net0box" class="netbox">
					{mac}
					{type}
					{vswitch}
				</div>
			</fieldset>

		</div>
	</fieldset>




	<fieldset>
		<legend>{lang_vnc}</legend>
		<div style="float:left;">
			{vnc}
			{vncport}
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

<script type="text/javascript">
tmp = document.getElementById('browsebutton');
if(tmp) {
	tmp.style.display = 'inline';
}
document.getElementById('passgenerate').style.display = 'inline';
document.getElementById('passtoggle').style.display = 'inline';
</script>
