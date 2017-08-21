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
<h2>{label}</h2>

<div class="row">
	<div class="tab-base span7">
					
								<!--Nav Tabs-->
								<ul class="nav nav-tabs">
									<li class="active">
										<a data-toggle="tab" href="#demo-lft-tab-1">{introduction_title}</a>
									</li>
									<li>
										<a data-toggle="tab" href="#demo-lft-tab-2">{provides_title}</a>
									</li>
									<li>
										<a data-toggle="tab" href="#demo-lft-tab-3">{requirements_title}</a>
									</li>
									<li>
										<a data-toggle="tab" href="#demo-lft-tab-3">{type_title}</a>
									</li>
									<li>
										<a data-toggle="tab" href="#demo-lft-tab-5">{tested_title}</a>
									</li>
								</ul>
	<div class="tab-content">
		<div id="demo-lft-tab-1" class="tab-pane fade active in">
			<h3>{introduction_title}</h3>
			{introduction_content}
		</div>
		
		<div id="demo-lft-tab-2" class="tab-pane fade">
			<h3>{provides_title}</h3>
			{provides_list}
		</div>
		
		<div id="demo-lft-tab-3" class="tab-pane fade">
			<h3>{requirements_title}</h3>
			{requirements_list}
		</div>

	
		<div id="demo-lft-tab-4" class="tab-pane fade">
			<h3>{type_title}</h3>
			<span class="pill">{type_content}</span>
		</div>
	
		<div id="demo-lft-tab-5" class="tab-pane fade">
			<h3>{tested_title}</h3>
			{tested_content}
		</div>
	</div>
</div>
</div>




