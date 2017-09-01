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

<h2>{title}</h2>
<form action="{thisfile}">
{form}
<div class="tab-base spanooo">
          
                <!--Nav Tabs-->
                <ul class="nav nav-tabs">
                <li class="active"><a data-toggle="tab" href="#demo-lft-tab-1">Admin</a></li>
                <li><a data-toggle="tab" href="#demo-lft-tab-2">Limits</a></li>
                <li><a data-toggle="tab" href="#demo-lft-tab-3">Network</a></li>
                <li><a data-toggle="tab" href="#demo-lft-tab-4">Users</a></li>
                <li><a data-toggle="tab" href="#demo-lft-tab-5">Billing</a></li>
                <li><a data-toggle="tab" href="#demo-lft-tab-6">Performance</a></li>
                <li><a data-toggle="tab" href="#demo-lft-tab-7">Cloud-Nephos</a></li>
                <li><a data-toggle="tab" href="#demo-lft-tab-8">Misc</a></li>
                </ul>
                <!--Tabs Content-->
                <div class="tab-content">
                  <div id="demo-lft-tab-1" class="tab-pane fade active in">
                  		<h3>Admin</h3>
						{cloud_enabled}
						{cloud_admin_email}
						{auto_provision}
						{external_portal_url}
						{request_physical_systems}
						{default_clone_on_deploy}
						{auto_create_vms}
						{public_register_enabled}
                  </div>

                  <div id="demo-lft-tab-2" class="tab-pane fade">
                  		<h3>Limits</h3>
						{max_apps_per_user}
						{max_disk_size}
						{max_memory}
						{max_cpu}
						{max_network}
						{max_network_interfaces}
						{resource_pooling}
                  </div>

                  <div id="demo-lft-tab-3" class="tab-pane fade">
                  		<h3>Network</h3>
						{ip-management}
						{cloud_nat}
                  </div>

                  <div id="demo-lft-tab-4" class="tab-pane fade">
                  		<h3>Users</h3>
						{show_collectd_graphs}
						{show_disk_resize}
						{show_private_image}
						{show_ha_checkbox}
						{show_puppet_groups}
						{show_sshterm_login}
						{appliance_hostname}

						{cloud_selector}
                  </div>

                  <div id="demo-lft-tab-5" class="tab-pane fade">
                  		<h3>Billing</h3>
						{cloud_billing_enabled}
						{cloud_currency}
						{cloud_1000_ccus}
						{auto_give_ccus}
						{deprovision_warning}
						{deprovision_pause}
                  </div>

                  <div id="demo-lft-tab-6" class="tab-pane fade">
                  			<h3>Performance</h3>
							{vm_provision_delay}
							{vm_loadbalance_algorithm}
							{max_resources_per_cr}
							{max-parallel-phase-one-actions}
							{max-parallel-phase-two-actions}
							{max-parallel-phase-three-actions}
							{max-parallel-phase-four-actions}
							{max-parallel-phase-five-actions}
							{max-parallel-phase-six-actions}
							{max-parallel-phase-seven-actions}

                  </div>

                  <div id="demo-lft-tab-7" class="tab-pane fade">
                  			<h3>cloud-nephos</h3>
							{cloud_zones_client}
							{cloud_zones_master_ip}
							{cloud_external_ip}
                  </div>

                  <div id="demo-lft-tab-8" class="tab-pane fade">
                  				<h3>Misc</h3>
								{allow_vnc_access}
                  </div>

	</div>
</div>
	

	
	

	







<div class="floatbreaker">&#160;</div>

	<div id="buttons">{submit}</div>

</form>


