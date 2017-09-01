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


<h2>{title} <a href={external_portal_name} target="_blank" class="btn pull-right">{external_portal_name}</a></h2>

<div class="row">
	

	<div class="tab-base span7">
					
								<!--Nav Tabs-->
								<ul class="nav nav-tabs">
									<li class="active">
										<a data-toggle="tab" href="#demo-lft-tab-1">{cloud_documentation_label}</a>
									</li>
									<li>
										<a data-toggle="tab" href="#demo-lft-tab-2">{cloud_documentation_setup_title}</a>
									</li>
									<li>
										<a data-toggle="tab" href="#demo-lft-tab-3">{cloud_documentation_users}</a>
									</li>
									<li>
										<a data-toggle="tab" href="#demo-lft-tab-4">{cloud_documentation_ip_management}</a>
									</li>

									<li>
										<a data-toggle="tab" href="#demo-lft-tab-5">{cloud_documentation_soap}</a>
									</li>

									<li>
										<a data-toggle="tab" href="#demo-lft-tab-6">{cloud_documentation_lockfile}</a>
									</li>

									<li>
										<a data-toggle="tab" href="#demo-lft-tab-7">Type</a>
									</li>

									
									

									

									
								</ul>
<div class="tab-content">
	<div id="demo-lft-tab-1" class="tab-pane fade active in">
		<h3>{cloud_documentation_label}</h3>
		{cloud_documentation_intro}
	</div>

	<div id="demo-lft-tab-2" class="tab-pane fade">
		<h3>{cloud_documentation_setup_title}</h3>
		{cloud_documentation_setup}
		{cloud_documentation_setup_steps}
	</div>

	<div id="demo-lft-tab-3" class="tab-pane fade">
		<h3>{cloud_documentation_users}</h3>
		{cloud_documentation_create_user}
	</div>

	<div id="demo-lft-tab-4" class="tab-pane fade">
		<h3>{cloud_documentation_ip_management}</h3>
		{cloud_documentation_ip_management_setup}
	</div>

	<div id="demo-lft-tab-5" class="tab-pane fade">
		<h3>{cloud_documentation_soap}</h3>
		{cloud_documentation_api}
	</div>


	<div id="demo-lft-tab-6" class="tab-pane fade">
	<h3>{cloud_documentation_lockfile}</h3>
		{cloud_documentation_lockfile_details}
	</div>

	<div id="demo-lft-tab-7" class="tab-pane fade">
		<h3>{cloud_documentation_type_title}</h3>
		{cloud_documentation_type_content}
		
	</div>





</div>
</div>
												
	</div>

	



