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


{js_formbuilder}
{js_use_api}

$(document).ready(function(){
	$('select').change(function(){
			cloud_cost_calculator();
	});

	$('#cloud_ha_select').click(function(){
			cloud_cost_calculator();
	});

	var maxval = {maxmaxmax};
	var i = 0;

	for (i=1; i<5; i++) {
		if (i > maxval) {
			var sel = '.sel'+i;
			$(sel).remove();
		}
	}

});

	window.onload = function() {
		// add event handler 
		var inputs = $( "#components_form :input" );
		for(i=0;i<inputs.length;i++) {
			$(inputs[i].id).change(function () {
					cloud_cost_calculator();
				});
		}

		

		// remove selected ip from the other selects
		$("#cloud_ip_select_0").change(function() {
			var sid = $("#cloud_ip_select_0 option:selected").val();
			if ((sid != -1) && (sid != -2)) {
				$("#cloud_ip_select_1 option:[value=" + sid + "]").remove();
				$("#cloud_ip_select_2 option:[value=" + sid + "]").remove();
				$("#cloud_ip_select_3 option:[value=" + sid + "]").remove();
			}
		})
		$("#cloud_ip_select_1").change(function() {
			var sid = $("#cloud_ip_select_1 option:selected").val();
			if ((sid != -1) && (sid != -2)) {
				$("#cloud_ip_select_0 option:[value=" + sid + "]").remove();
				$("#cloud_ip_select_2 option:[value=" + sid + "]").remove();
				$("#cloud_ip_select_3 option:[value=" + sid + "]").remove();
			}
		})
		$("#cloud_ip_select_2").change(function() {
			var sid = $("#cloud_ip_select_2 option:selected").val();
			if ((sid != -1) && (sid != -2)) {
				$("#cloud_ip_select_1 option:[value=" + sid + "]").remove();
				$("#cloud_ip_select_0 option:[value=" + sid + "]").remove();
				$("#cloud_ip_select_3 option:[value=" + sid + "]").remove();
			}
		})
		$("#cloud_ip_select_3").change(function() {
			var sid = $("#cloud_ip_select_3 option:selected").val();
			if ((sid != -1) && (sid != -2)) {
				$("#cloud_ip_select_1 option:[value=" + sid + "]").remove();
				$("#cloud_ip_select_2 option:[value=" + sid + "]").remove();
				$("#cloud_ip_select_0 option:[value=" + sid + "]").remove();
			}
		})

		// preset ip selects to 1 nic
		$('#cloud_ip_select_0').removeAttr("disabled");
		$('#cloud_ip_select_1').attr('disabled', 'true');
		$('#cloud_ip_select_2').attr('disabled', 'true');
		$('#cloud_ip_select_3').attr('disabled', 'true');

		// load costs
		cloud_cost_calculator();
	};

	var resultsum = [0, 0, 0, 0, 0];

	function cloud_cost_calculator() {

		
		
		
		var this_cloud_id = 0;
		var virtualization = $("select[name=cloud_virtualization_select]").val();
		var kernel = $("select[name=cloud_kernel_select]").val();
		//var memory = $("select[name=cloud_memory_select]").val();
		//var cpu = $("select[name=cloud_cpu_select]").val();
		//var disk = $("select[name=cloud_disk_select]").val();
		var network = $("select[name=cloud_network_select]").val();

		var disk = $('#valll').text();
		disk = parseInt(disk);
		var memory = $('#valll1').text();
		memory = parseInt(memory);
		var cpu = $('#valll2').text();
		cpu = parseInt(cpu);
		

		var ha = 0;
		if ($("input[name=cloud_ha_select]").is(":checked")) {
			var ha = 1;
		}

		var inputs = $( "#applications_list :input" );
		var apps = '';
		var j = 0;
		for(i=0;i<inputs.length;i++) {
			if($(inputs[i]).is(":checked")) {
				if(j == 0) {
					var apps = $(inputs[i]).val();
					j++;
				} else {
					var apps = apps+','+$(inputs[i]).val();
				}
			}
		}

		// enable/disable ip selects
		// adjust ip selects according to the nic count
		switch (network) {
			case '1':
				$('#cloud_ip_select_0').removeAttr("disabled");
				$('#cloud_ip_select_1').attr('disabled', 'true');
				$('#cloud_ip_select_2').attr('disabled', 'true');
				$('#cloud_ip_select_3').attr('disabled', 'true');
				break;
			case '2':
				$('#cloud_ip_select_0').removeAttr("disabled");
				$('#cloud_ip_select_1').removeAttr("disabled");
				$('#cloud_ip_select_2').attr('disabled', 'true');
				$('#cloud_ip_select_3').attr('disabled', 'true');
				break;
			case '3':
				$('#cloud_ip_select_0').removeAttr("disabled");
				$('#cloud_ip_select_1').removeAttr("disabled");
				$('#cloud_ip_select_2').removeAttr("disabled");
				$('#cloud_ip_select_3').attr('disabled', 'true');
				break;
			case '4':
				$('#cloud_ip_select_0').removeAttr("disabled");
				$('#cloud_ip_select_1').removeAttr("disabled");
				$('#cloud_ip_select_2').removeAttr("disabled");
				$('#cloud_ip_select_3').removeAttr("disabled");
				break;
			default:
				$('#cloud_ip_select_0').attr('disabled', 'false');
				$('#cloud_ip_select_1').attr('disabled', 'false');
				$('#cloud_ip_select_2').attr('disabled', 'false');
				$('#cloud_ip_select_3').attr('disabled', 'false');
				break;
		}

		
			resultsum = [0, 0, 0, 0, 0];
			$('#moredisktbl').find('td.size').each(function(){
				ccuval = $(this).text();
				ccuval = parseInt(ccuval);
				calculatevolume(ccuval, virtualization);
			});
			console.log(resultsum);

		if( use_api == true ) {
			// send ajax request to calculator
			// this connects via soap to the specific cloud-zone server to get the costs for the request
			var url = "/cloud-fortis/user/api.php?action=calculator&virtualization=" + virtualization;
			url = url + "&kernel=" + kernel;
			url = url + "&memory=" + memory;
			url = url + "&cpu=" + cpu;
			url = url + "&disk=" + disk;
			url = url + "&network=" + network;
			url = url + "&ha=" + ha;
			url = url + "&apps=" + apps;
			result = [];
			prefix = [];
			var cur = 'USD';
			$.ajax({
				url : url,
				type: "POST",
				cache: false,
				async: false,
				dataType: "html",
				success : function (data) {
							console.log(data);
					var costs = data.split(";");
					
					for( i in costs ) {
						tmp = costs[i].split('=');
						prefix[i] = tmp[0];
						//$("#price_"+tmp[0]).text(tmp[1]);
						if (typeof(tmp[1]) != 'undefined') {
						var spliter = tmp[1].split(' ');
						cur = spliter[0];
						var spl = spliter[1];
						var num = 0;
						if (typeof(spl) == 'undefined') {
							spl = spliter[0].replace(',','.');
							num = parseFloat(spl);
						} else {
								var spl2 = spl.replace(',','.');
								num = parseFloat(spl2);
						}

						if (num == 'NaN') {
							num = 0;
						}
						result[i]=num;
					}
					}
					resultsum[0] = resultsum[0] + result[0];
					resultsum[1] = resultsum[1] + result[1];
					resultsum[2] = resultsum[2] + result[2];
					resultsum[3] = resultsum[3] + result[3];
					resultsum[4] = resultsum[4] + result[4];

					resultsum[0] = resultsum[0].toFixed(2);
					resultsum[1] = resultsum[1].toFixed(2);
					resultsum[2] = resultsum[2].toFixed(2);;
					resultsum[3] = resultsum[3].toFixed(2);
					resultsum[4] = resultsum[4].toFixed(2);
					
					$("#price_"+prefix[0]).text(resultsum[0]);
					$("#price_"+prefix[2]).text(cur+' '+resultsum[2]);
					$("#price_"+prefix[3]).text(cur+' '+resultsum[3]);
					$("#price_"+prefix[4]).text(cur+' '+resultsum[4]);
					
					
				}
			});
		}
	}

	function calculatevolume(disk, virtualization) {
		// send ajax request to calculator
			// this connects via soap to the specific cloud-zone server to get the costs for the request
			var url = "/cloud-fortis/user/api.php?action=calculator&virtualization=" + virtualization;
			//url = url + "&kernel=" + kernel;
			//url = url + "&memory=" + memory;
			//url = url + "&cpu=" + cpu;
			url = url + "&disk=" + disk;
			//url = url + "&network=" + network;
			//url = url + "&ha=" + ha;
			//url = url + "&apps=" + apps;
			var result = new Array(4);
			$.ajax({
				url : url,
				type: "POST",
				cache: false,
				async: false,
				dataType: "html",
				success : function (data) {
					console.log(data);
					var costs = data.split(";");
					
					for( i in costs ) {
						tmp = costs[i].split('=');
						//$("#price_"+tmp[0]).text(tmp[1]);
						if (typeof(tmp[1]) != 'undefined') {
						var spliter = tmp[1].split(' ');
						var spl = spliter[1];
						var num = 0;
						if (typeof(spl) == 'undefined') {
							spl = spliter[0].replace(',','.');
							num = parseFloat(spl);
						} else {
								var spl2 = spl.replace(',','.');
								num = parseFloat(spl2);
						}

						if (num == 'NaN') {
							num = 0;
						}
						result[i]=num;
					}
					}
					resultsum[0] = resultsum[0] + result[0];
					resultsum[1] = resultsum[1] + result[1];
					resultsum[2] = resultsum[2] + result[2];
					resultsum[3] = resultsum[3] + result[3];
					resultsum[4] = resultsum[4] + result[4];
				}
			});
	}

	function init_image() {
		// add resources with js
		selected = document.getElementById('cloud_virtualization_select').options.selectedIndex;
		$('#cloud_virtualization_select').html('');
		for( i in formbuilder.resources ) {
			option = document.createElement("option");
			option.value = formbuilder.resources[i][0];
			option.text = formbuilder.resources[i][1];
			if(i == selected) {
				option.selected = 'selected';
			}
			document.getElementById('cloud_virtualization_select').appendChild(option);
		}
		$('#cloud_virtualization_select').change(function () {
					change_image(this);
					cloud_cost_calculator();
				});
		change_image(document.getElementById('cloud_virtualization_select'));
	}

	function change_image(element) {
		select   = document.getElementById('cloud_image_select');
		try {
			selected = select.options[select.options.selectedIndex].value;
			tag      = formbuilder.resources[element.options.selectedIndex][2];
			type     = formbuilder.resources[element.options.selectedIndex][3];
			$('#cloud_image_select').html('');
			for( i in formbuilder.images ) {
				if(formbuilder.images[i][2] == tag) {
					option = document.createElement("option");
					option.value = formbuilder.images[i][0];
					option.text = formbuilder.images[i][1];
					if(formbuilder.images[i][0] == selected) {
						option.selected = 'selected';
					}
					select.appendChild(option);
				}
			}
			console.log(type);
			if(type == 'vm-net') {
				document.getElementById('cloud_kernel_select_box').style.visibility = 'visible';
			} else {
				document.getElementById('cloud_kernel_select_box').style.visibility = 'hidden';
			}
		} catch(e) { }

		if(select.length == 0) {
			option = document.createElement("option");
			option.value = '';
			option.text = ' ';
			select.appendChild(option);
		}
	}

</script>


</div></div>
<div class="col-xs-12 col-sm-8 col-md-9 col-lg-9 windows_plane">
<div id="content_container">
<span id="storageidedit"></span>
	<h1>{label}</h1>





	<div class="col-xs-12 col-sm-8 col-md-8 col-lg-8">
		<div id="error_list" style="display:{display_error};">
			{error}
		</diV>
		<div id="freemb" style="display:none">0</div>
		<div id="components_list" style="display:{display_component_table};">
		<form action="{thisfile}" id="components_form">
			{form}
		<div id="hardware_slot" class="panel panel-primary niftypanel">
						<div class="panel-heading">
							<h3 class="panel-title">Profiles</h3>
						</div>
						<div class="panel-body">
			<div id="profiles_sloter">
			
			<div id="profiles_list" class="col-xs-9 col-md-9 col-sm-9 col-lg-9">
				<div id="cloud_profile_select_box" class="htmlobject_box">
					<div class="left"><label for="cloud_profile_select">Profiles</label></div>
					<div class="right">
					{profiles}
					</div>
					<div class="floatbreaker" style="line-height:0px;height:0px;clear:both;">&nbsp;</div>
					</div>
			</div>
			<div id="manage_list" class="col-xs-3 col-md-3 col-sm-3 col-lg-3">
				<ul>
					
					<li>{profiles_link}</li>
				</ul>
				<div class="floatbreaker" style="clear:both;">&#160;</div>
			</div>
			</div>
			</div>
			</div>


			<div id="hardware_slot" class="panel panel-primary niftypanel">
						<div class="panel-heading">
							<h3 class="panel-title">Virtual Machine</h3>
						</div>
						<div class="panel-body">
				{cloud_virtualization_select}
				{cloud_image_select}
				{cloud_kernel_select}
						</div>
				<script type="text/javascript"> init_image(); </script>
			</div>

			<div id="hardware_slot" class="panel panel-primary niftypanel">
				<div class="panel-heading">
							<h3 class="panel-title">Resources</h3>
						</div>
						<div class="panel-body">
				<div class="row">
				
				<div id="cloud_disk_select_box_slide" class="htmlobject_box col-xs-4 col-md-4 col-sm-4 col-lg-4">
				<div class="left"><label for="cloud_disk_select">Disk *</label></div>
				<div class="rightero right">
						<div id="sliderrr" class="demo-pips range-vertical pips"></div>
						<div id="valllgb"></div>
						<div id="valll"></div>
				</div>
				<div class="floatbreaker" style="line-height:0px;height:0px;clear:both;">&nbsp;</div>
				</div>

				<div id="cloud_cpu_select_box_slide" class="htmlobject_box col-xs-4 col-md-4 col-sm-4 col-lg-4">
				<div class="left"><label for="cloud_cpu_select">CPU *</label></div>
				<div class="rightero right">
						<div id="sliderrr2" class="demo-pips2 range-vertical pips"></div>
						<div id="valllgb2"></div>
						<div id="valll2"></div>

				</div>
				<div class="floatbreaker" style="line-height:0px;height:0px;clear:both;">&nbsp;</div>
				</div>

				<div id="cloud_memory_select_box_slide" class="htmlobject_box col-xs-4 col-md-4 col-sm-4 col-lg-4">
				<div class="left"><label for="cloud_memory_select">Memory *</label></div>
				<div class="rightero right">
						<div id="sliderrr1" class="demo-pips1 range-vertical pips"></div>
						<div id="valllgb1"></div>
						<div id="valll1"></div>
				</div>
				<div class="floatbreaker" style="line-height:0px;height:0px;clear:both;">&nbsp;</div>
				</div>

			

			
				</div>

				{cloud_disk_select} 
				<br/>
				<div id="disks_box" class="htmlobject_box"  style="display:none">

				<div class="left"><label for="cloud_memory_select">Additional Disks</label></div>
				<div class="right">
					<a class="btn btn-primary" id="managedisks">Manage</a>
				</div>
				<div class="floatbreaker" style="line-height:0px;height:0px;clear:both;">&nbsp;</div>
				</div>
				
				{cloud_memory_select}
				{cloud_cpu_select}
				
				</div>
			</div>

			<div id="applications_slot" class="panel panel-primary niftypanel">
				<div class="panel-heading">
							<h3 class="panel-title">Network</h3>
						</div>
						<div class="panel-body">
						{cloud_network_select}
						
						<div class="input-group mar-btm sel1">
											<span class="input-group-addon">
												<label class="form-checkbox form-icon netcheck1 active" netcheck="1">
													<input type="checkbox" checked="">
												</label>
											</span>
											{cloud_ip_select_0}
											</select>
						</div>
						<div class="input-group mar-btm sel2">
											<span class="input-group-addon">
												<label class="form-checkbox form-icon netcheck2" netcheck="2">
													<input type="checkbox" checked="">
												</label>
											</span>
											{cloud_ip_select_1}
											</select>
						</div>
						<div class="input-group mar-btm sel3">
											<span class="input-group-addon">
												<label class="form-checkbox form-icon netcheck3" netcheck="3">
													<input type="checkbox" checked="">
												</label>
											</span>
											{cloud_ip_select_2}
											</select>
						</div>
						<div class="input-group mar-btm sel4">
											<span class="input-group-addon">
												<label class="form-checkbox form-icon netcheck4" netcheck="4">
													<input type="checkbox" checked="">
												</label>
											</span>
											{cloud_ip_select_3}
											</select>
						</div>																		
						
				
			
				</div>
				</div>

			<div id="applications_slot" class="panel panel-primary niftypanel">
				<div class="panel-heading">
							<h3 class="panel-title">Application Market</h3>
						</div>
						<div class="panel-body">
				<div id="applications_list">
				<div class="jcarousel-wrapper">
                <div class="jcarousel">
                    <ul>
					{cloud_applications}
					{cloud_ha_select}
					 </ul>
                </div>

                <a href="#" class="jcarousel-control-prev">&lsaquo;</a>
                <a href="#" class="jcarousel-control-next">&rsaquo;</a>

                <p class="jcarousel-pagination"></p>
            	</div>
				</div>



				
                        
                   


				</div>
			</div>

			<div id="capabilities_slot" class="panel panel-primary niftypanel">
				<div class="panel-heading">
							<h3 class="panel-title">Hostname</h3>
						</div>
				<div class="panel-body">
				{cloud_hostname_input}
				{cloud_appliance_capabilities}
				{submit}
				</div>
			</div>

			<div id="misc_slot" class="panel panel-primary niftypanel">
				<div class="panel-heading">
							<h3 class="panel-title">Profile</h3>
						</div>
						<div class="panel-body">
				{cloud_profile_name}
				<input type="submit" value="Save Profile" name="response[submit]" class="submit btn btn-primary" data-message="">
				<div class="floatbreaker" style="clear:both;">&#160;</div>
			</div>
			</div>
			<br/>

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
				 		<input  type="text" class="btn btn-primary" id="namevolumeinput"/><br/>
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
				 		<input  type="text" class="btn btn-primary" id="nameeditvolumeinput"/><br/>
				 		<div class="selecto">
				 		<select  id="typeeditvolumeselect">
				 			<!-- <option value="qcow2">qcow2</option> -->
				 			<option value="raw">raw</option>
				 		</select></div><br/>
				 		<div class="selecto">
				 		<label>Size:</label> {volumeselectedit}<br/>
				 		</div><br/><br/><br/>
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
				 	<tr class="myinfocolor">
				 		<td style="display:none">Type</td><td style="display:none">Name</td><td>Size</td><td class="text-center">Edit</td><td class="text-center">Remove</td>
				 	</tr>
				 	
				 	</table>
      </div>
      <div class="modal-footer">
        <button data-dismiss="modal" class="btn btn-default" type="button">Close</button>
      </div>
    </div><!-- /.modal-content -->
  </div><!-- /.modal-dialog -->
</div>
		
				
		</form>
		</div>
	
</div>

<div class="col-xs-12 col-sm-4 col-md-4 col-lg-4">
			
		
		<div id="price_list" style="display:{display_price_list};">
			<table>
			<tr>
				<td><b>{ccu_per_hour}:</b></td><td id="price_summary" class="price">&#160;</td>
			</tr><tr>
				<td><b>{price_hour}:</b></td><td id="price_hour" class="price">&#160;</td>
			</tr><tr>
				<td><b>{price_day}:</b></td><td id="price_day" class="price">&#160;</td>
			</tr><tr>
				<td><b>{price_month}:</b></td><td id="price_month" class="price">&#160;</td>
			</tr>
			</table>
			<div class="floatbreaker" style="clear:both;">&#160;</div>
		</div>

		
		
			
</div>
		<div class="floatbreaker" style="clear:both;">&#160;</div>
	</div>
</div>


</div>
