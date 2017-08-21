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
				url: "{baseurl}/api.php?action=plugin&plugin=hyperv&controller=hyperv-vm&appliance_id={appliance_id}&path=C:/&{actions_name}=dirbrowser",
				dataType: "text",
				success: function(response) {
					document.getElementById('canvas').innerHTML = response;
				}
			});
		},
		browse : function(target) {
			document.getElementById('canvas').innerHTML = '<img src="{baseurl}/img/loading.gif" style="margin-top:150px;">';
			$.ajax({
				url: "{baseurl}/api.php?action=plugin&plugin=hyperv&controller=hyperv-vm&appliance_id={appliance_id}&path="+target+"&{actions_name}=dirbrowser",
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
<form action="{thisfile}" method="POST">
{form}
<div id="form">
	{name}
	{comment}
	<fieldset>
		<div>
			{path}{browse_button}
			<div class="floatbreaker" style="line-height:0px;height:0px;clear:both;">&nbsp;</div>
		</div>
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
</script>
