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

 to debug add {?}


-->

<script type="text/javascript">
var passgen = {
	generate : function() {
		pass = GeneratePassword();
		document.getElementById('cloud_user_password').value = pass;
	},
	toggle : function() {
		vnc = document.getElementById('cloud_user_password');
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
</script>

<h2>{title} <a href="{external_portal_name}" target="_blank" class="btn external">{external_portal_name}</a></h2>

<form action="{thisfile}">
	{form}
	<div class="row">
		
		<div class="tab-base span7">
          
                <!--Nav Tabs-->
                <ul class="nav nav-tabs">
                	<li class="active"><a data-toggle="tab" href="#demo-lft-tab-1">User Information</a></li>
                	<li><a data-toggle="tab" href="#demo-lft-tab-2">{cloud_user_address}</a></li>
                	<li><a data-toggle="tab" href="#demo-lft-tab-3">{cloud_user_data}</a></li>
                	<li><a data-toggle="tab" href="#demo-lft-tab-4">{cloud_user_permissions}</a></li>
               	</ul>
                <!--Tabs Content-->
            <div class="tab-content tabusercloud">
                  <div id="demo-lft-tab-1" class="tab-pane fade active in">
                    	<h3>{cloud_user_data}</h3>
						{cloud_user_forename}
						{cloud_user_lastname}
						{cloud_user_street}
						{cloud_user_city}
						{cloud_user_country}
						{cloud_user_lang}
                  </div>

                  <div id="demo-lft-tab-2" class="tab-pane fade">
                  		<h3>{cloud_user_address}</h3>
						{cloud_user_email}
						{cloud_user_phone}
				  </div>

				  <div id="demo-lft-tab-3" class="tab-pane fade">
				  		<h3>{cloud_user_data}</h3>
						<div class="row">
							<div class="span7">
								{cloud_user_name}
							</div>
						</div>

						<div class="row">
							<div class="span4">
								{cloud_user_password}
							</div>
							<div style="float: left;">
								<input type="button" id="passgenerate" onclick="passgen.generate(); return false;" value="{lang_password_generate}">&#160;
								<input type="button" id="passtoggle" onclick="passgen.toggle(); return false;" value="{lang_password_show}">
							</div>
						</div>
						{cloud_user_ccunits}
						{cloud_usergroup_id}
				  </div>

				  <div id="demo-lft-tab-4" class="tab-pane fade">
				  		<h3>{cloud_user_permissions}</h3>
			
						{cloud_user_resource_limit}
						{cloud_user_memory_limit}
						{cloud_user_disk_limit}
						{cloud_user_cpu_limit}
						{cloud_user_network_limit}
					
						<small>{cloud_user_limit_explain}</small>
				  </div>

				
			
			
			</div>

	<div id="buttonso">{submit}&#160;{cancel}</div>
	
 </div></div>
</form>

<script type="text/javascript">
	$( document ).ready(function() {
		$("li#tab_project_tab1 span a").click(function(){
			var aherf			= $("li#tab_project_tab1 span a").attr('href');
			var host_name		= document.location.hostname;
			window.location = 'http://'+host_name+'/htvcenter/base/'+aherf;
		});
	});
</script>