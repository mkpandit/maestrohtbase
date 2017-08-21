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
console.log('loo');
var filepicker = {
	init : function( element ) {
		this.element = element;
		mouse = MousePosition.get();
		document.getElementById('canvas').innerHTML = '<img src="{baseurl}/img/loading.gif" style="margin-top:150px;">';
		document.getElementById('filepicker').style.left = (mouse.x + -210)+'px';
		document.getElementById('filepicker').style.top  = (mouse.y - 270)+'px';
		document.getElementById('filepicker').style.display = 'block';
		$.ajax({
			url: "{baseurl}/api.php?action=plugin&plugin=kvm&controller=kvm-vm&path=/&appliance_id={appliance_id}&{actions_name}=filepicker",
			dataType: "text",
			success: function(response) {
				document.getElementById('canvas').innerHTML = response;	
			}
		});
	},
	browse : function(target) {
		document.getElementById('canvas').innerHTML = '<img src="{baseurl}/img/loading.gif" style="margin-top:150px;">';
		$.ajax({
			url: "{baseurl}/api.php?action=plugin&plugin=kvm&controller=kvm-vm&path="+target+"&appliance_id={appliance_id}&{actions_name}=filepicker",
			dataType: "text",
			success: function(response) {
				document.getElementById('canvas').innerHTML = response;	
			}
		});
	},
	insert : function(value) {
		document.getElementById(this.element).value = value;
		document.getElementById('filepicker').style.display = 'none';
	}
}

var passgen = {
	generate : function() {
		pass = GeneratePassword();
		document.getElementById('vnc').value = pass;
		document.getElementById('vnc_1').value = pass;
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
	document.getElementById('name').value = 'kvm'+name;
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

<div id="form">
<div class="tab-base">
					
								<!--Nav Tabs-->
								<ul class="nav nav-tabs">
									<li class="active">
										<a href="#demo-lft-tab-1" data-toggle="tab">{lang_basic}</a>
									</li>
									<li>
										<a href="#demo-lft-tab-2" data-toggle="tab">{lang_hardware}</a>
									</li>
									<li>
										<a href="#demo-lft-tab-3" data-toggle="tab">{lang_virtual_disk}</a>
									</li>
									<li>
										<a href="#demo-lft-tab-4" data-toggle="tab">{lang_net}</a>
									</li>

									<li>
										<a href="#demo-lft-tab-5" data-toggle="tab">{lang_boot}</a>
									</li>

									<li>
										<a href="#demo-lft-tab-6" data-toggle="tab">{lang_vnc}</a>
									</li>

									

									

									
								</ul>
	<form action="{thisfile}" method="GET">
	{form}
<div class="tab-content">
	<div class="tab-pane fade active in fixm " id="demo-lft-tab-1">
										
	<fieldset>
	
			<div class="span8">
				{name}
			</div>
	</fieldset>
	</div>

	<div class="tab-pane fade" id="demo-lft-tab-2">
	<fieldset>
			<div class="span8">
			{cpus}
			{memory}
		</div>
	</fieldset>
	</div>

	<div class="tab-pane fade fixeeeer" id="demo-lft-tab-3">
	<fieldset>
		
			<div class="span8">
				<div style="float:left;">
					{localboot_image}
					{netboot_image}
					{disk_interface}
				</div>
				<div style="float:left; width: 250px; margin: 3px 0 0 15px">
					{add_vm_image} <!-- <a class="add btn-labeled fa fa-gear" id="managedisks"> Additional Disks</a> -->
				</div>
				<div class="floatbreaker" style="line-height:0px;height:0px;clear:both;">&nbsp;</div>
					{cdrom_iso_path}
					{cdrom_button}
				<div class="floatbreaker" style="line-height:0px;height:0px;clear:both;">&nbsp;</div>
				<div id="morediskdiv">
				<span id="freemb">{freembplain}</span>
					
				</div>
			</div>
	</fieldset>
	</div>
	
	<div class="tab-pane fade" id="demo-lft-tab-4">
	<fieldset>
		
		<div class="span8">
			<fieldset>
				<div style="float:left;">{net0}</div>
				<div id="net0box" class="netbox">
					{mac}
					{bridge}
					<!-- {ip_network} -->
					{nic}
				</div>
			</fieldset>

			<fieldset>
				<div style="float:left;">{net1}</div>
				<div id="net1box" class="netbox">
					{mac1}
					{bridge1}
					{nic1}
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
					{bridge2}
					{nic2}
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
					{bridge3}
					{nic3}
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
					{bridge4}
					{nic4}
				</div>
			</fieldset>
			<script>
				if(document.getElementById('net4').checked == false) {
					document.getElementById('net4box').style.display = 'none';
				}
			</script>
		</div>
	</fieldset>
	</div>

	<div class="tab-pane fade boooter" id="demo-lft-tab-5">
	<fieldset>
		
		<div class="span8">
			{boot_cd}
			<div>
				{boot_iso}
				{boot_iso_path}
				{browse_button}
				<div class="floatbreaker" style="line-height:0px;height:0px;clear:both;">&nbsp;</div>
			</div>
			{boot_net}
			{boot_local}
		</div>
	</fieldset>
	</div>

	<div class="tab-pane fade" id="demo-lft-tab-6">
	<fieldset>
		<div class="span8">
			<div style="float:left;">
				{vnc}
				{vnc_1}
				{vnc_keymap}
			</div>
			<div style="float:left; width: 250px; margin: 3px 0 0 15px">
				<input type="button" id="passgenerate" onclick="passgen.generate(); return false;" class="password-button" value="{lang_password_generate}" style="display:none;"><br>
				<input type="button" id="passtoggle" onclick="passgen.toggle(); return false;" class="password-button" value="{lang_password_show}" style="display:none;">
			</div>
			<div class="floatbreaker" style="line-height:0px;height:0px;clear:both;">&nbsp;</div>
		</div>
	</fieldset>
	</div></div></div>



	<div id="modal-volumeadd" class="modal fade">
  <div class="modal-dialog">
    <div class="modal-content">
      <div class="modal-header">
        <button aria-label="Close" data-dismiss="modal" class="close" type="button"><span aria-hidden="true">×</span></button>
        <h4 class="modal-title">Add one more volume</h4>
      </div>
      <div class="modal-body">
      		<p> You can add few volumes here. Available space for it is <span id="freembsp">{freemb}</span> </p>
				 	<div class="moredisk">
				 		<span>Input volume information:</span><br/><br/>
				 		<input  type="hidden" id="namevolumeinput"/><br/>
				 		<div id="selecto">
				 		<select  id="typevolumeselect">
				 			
				 			<option value="raw">raw</option>
				 		</select></div><br/>
				 		<label>Size:</label> <input  type="text" id="sizevolumeinput"/><br/>
				 	</div>
      </div>
      <div class="modal-footer">
        <button data-dismiss="modal" class="btn btn-default" type="button">Close</button>
        <button data-dismiss="modal" class="btn btn-success" type="button" id="addvolumebtn">Add</button>
      </div>
    </div><!-- /.modal-content -->
  </div><!-- /.modal-dialog -->
</div>

<div id="edit-modal" class="modal fade">
  <div class="modal-dialog">
    <div class="modal-content">
      <div class="modal-header">
        <button aria-label="Close" data-dismiss="modal" class="close" type="button"><span aria-hidden="true">×</span></button>
        <h4 class="modal-title">Edit volume</h4>
      </div>
      <div class="modal-body">
      		<p> You can edit the volume here. Available space for it is <span id="freembspedit">{freemb}</span> </p>
				 	<div class="moredisk">
				 		<span>Input volume information:</span><br/><br/>
				 		 <input  type="hidden" id="nameeditvolumeinput"/><br/>
				 		<div id="selecto">
				 		<select id="typeeditvolumeselect">
				 			
				 			<option value="raw">raw</option>
				 		</select></div><br/>
				 		<label>Size:</label> <input  type="text" id="sizeeditvolumeinput"/><br/>
				 		<span id="storageidedit"></span>
				 	</div>
      </div>
      <div class="modal-footer">
        <button data-dismiss="modal" class="btn btn-default" type="button">Close</button>
        <button data-dismiss="modal" class="btn btn-success" type="button" id="editvolumebtn">Save</button>
      </div>
    </div><!-- /.modal-content -->
  </div><!-- /.modal-dialog -->
</div>

<div id="modal-volume" class="modal fade">
  <div class="modal-dialog">
    <div class="modal-content">
      <div class="modal-header">
        <button aria-label="Close" data-dismiss="modal" class="close" type="button"><span aria-hidden="true">×</span></button>
        <h4 class="modal-title">Disks</h4>
      </div>
      <div class="modal-body">
      		<a class="btn btn-primary" id="addmoredisks"><i class="fa fa-plus"></i> New Disk</a>
				 	<table class="table table-striped table-hover" id="moredisktbl">
				 	<tr class="warning">
				 		<td>Size</td><td class="text-center">Edit</td><td class="text-center">Remove</td>
				 	</tr>
				 	
				 	</table>
      </div>
      <div class="modal-footer">
        <button data-dismiss="modal" class="btn btn-default" type="button">Close</button>
      </div>
    </div><!-- /.modal-content -->
  </div><!-- /.modal-dialog -->
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
if(document.getElementById('browsebutton')) {
	document.getElementById('browsebutton').style.display = 'inline';
}
if(document.getElementById('cdrom_button')) {
	document.getElementById('cdrom_button').style.display = 'inline';
}
document.getElementById('passgenerate').style.display = 'inline';
document.getElementById('passtoggle').style.display = 'inline';
</script>
