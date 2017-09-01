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

<form action="{thisfile}" method="POST">
{form}

<div class="row">
	<div class="span4">
		{ssh_key_file}
	</div>
	<div class="span2">
		<input type="button" id="ssh_key" onclick="filepicker.init('ssh_key_file'); return false;" class="browse-button" value="{lang_browse}" style="display:none;">
	</div>
</div>

<div class="row">
	<div class="span6">
		{submit}&#160;{cancel}
	</div>
</div>

	<div id="filepicker" style="display:none;position:absolute;top:15px;left:15px;"  class="function-box">
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

</form>


<script type="text/javascript">
MousePosition.init();
function tr_hover() {}
function tr_click() {}
var filepicker = {
        target : null,
        init : function(target) {
                this.target = target;
                mouse = MousePosition.get();
                document.getElementById('canvas').innerHTML = '<img src="{baseurl}/img/loading.gif" style="margin-top:150px;">';
                document.getElementById('filepicker').style.left = (mouse.x + -120)+'px';
                document.getElementById('filepicker').style.top  = (mouse.y - 180)+'px';
                document.getElementById('filepicker').style.display = 'block';
                $.ajax({
                        url: "{baseurl}/api.php?action=plugin&plugin=hybrid-cloud&controller=hybrid-cloud&path=/&{actions_name}=filepicker",
                        dataType: "text",
                        success: function(response) {
                                document.getElementById('canvas').innerHTML = response;
                        }
                });
        },
        browse : function(target) {
                document.getElementById('canvas').innerHTML = '<img src="{baseurl}/img/loading.gif" style="margin-top:150px;">';
                $.ajax({
                        url: "{baseurl}/api.php?action=plugin&plugin=hybrid-cloud&controller=hybrid-cloud&path="+target+"&{actions_name}=filepicker",
                        dataType: "text",
                        success: function(response) {
                                document.getElementById('canvas').innerHTML = response;
                        }
                });
        },
        insert : function(value) {
                document.getElementById(this.target).value = value;
                document.getElementById('filepicker').style.display = 'none';
        }
}
document.getElementById('ssh_key').style.display = 'inline';

</script>
