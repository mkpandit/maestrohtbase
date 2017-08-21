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


<h2>{cloud_documentation_soap_title}</h2>
	
<div class="row">
	<div class="tab-base span7">
					
								<!--Nav Tabs-->
								<ul class="nav nav-tabs">
									<li class="active">
										<a data-toggle="tab" href="#demo-lft-tab-1">{cloud_documentation_soap_design_title}</a>
									</li>
									<li>
										<a data-toggle="tab" href="#demo-lft-tab-2">{cloud_documentation_soap_admin_label}</a>
									</li>

									<li>
										<a data-toggle="tab" href="#demo-lft-tab-3">SOAP WebService for the Cloud Users</a>
									</li>
								</ul>
	<div class="tab-content">
		<div id="demo-lft-tab-1" class="tab-pane fade active in">
			 <h3>{cloud_documentation_soap_design_title}</h3>
		    {cloud_documentation_soap_design}
		</div>

		<div id="demo-lft-tab-2" class="tab-pane fade">
			 <h3>{cloud_documentation_soap_admin_label}</h3>
			<p>{cloud_documentation_soap_admin_functions}</p>
			<pre>{cloud_documentation_soap_admin_function_list}</pre>
			<p>{cloud_documentation_soap_admin_wsdl}</p>
		</div>

		<div id="demo-lft-tab-3" class="tab-pane fade">
			 <h3>SOAP WebService for the Cloud Users</h3>
		    <p>{cloud_documentation_soap_user_functions}</p>
		    <pre>{cloud_documentation_soap_user_function_list}</pre>

		    <p>{cloud_documentation_soap_user_wsdl}</p>
		</div>



	    
	</div>
</div>
</div>

