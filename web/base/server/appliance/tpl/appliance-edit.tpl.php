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
<div id="appliance_edit" class="row">
	<form action="{thisfile}" method="POST">
	<div class="span6">
		{form}
		{virtualization}
		{resource}
		{image}
		{kernel}

		{cpus}
		{cpuspeed}
		{cpumodel}
		{nics}
		{memory}
		{swap}
		{capabilities}
		{comment}
	</div>

	<div id="pluginsboxer" class="span2">
		<fieldset>
			
			{myplugins}
		</fieldset>
	</div>

	<div class="floatbreaker">&#160;</div>
	<div id="buttons">{submit}&#160;{cancel}</div>

</div>

<!--

	<div class="dirbox span3">
		<h3 onclick="$('#list1').slideToggle('slow');"><img src="{baseurl}/img/ha.png" alt="ha"> {lang_ha}</h3>
		<div class="dirlist" id="list1">
			{plugin_ha}
		</div>
		<script type="text/javascript">$('#list1').slideToggle('fast');</script>

		<h3 onclick="$('#list2').slideToggle('slow');"><img src="{baseurl}/img/datacenter.png" alt="network"> {lang_net}</h3>
		<div class="dirlist" id="list2">
			{plugin_net}
		</div>
		<script type="text/javascript">$('#list2').slideToggle('fast');</script>

		<h3 onclick="$('#list3').slideToggle('slow');"><img src="{baseurl}/img/user.png" alt="management"> {lang_mgmt}</h3>
		<div class="dirlist" id="list3">
			{plugin_mgmt}
		</div>
		<script type="text/javascript">$('#list3').slideToggle('fast');</script>

		<h3 onclick="$('#list4').slideToggle('slow');"><img src="{baseurl}/img/monitoring.png" alt="monitoring"> {lang_moni}</h3>
		<div class="dirlist" id="list4">
			{plugin_moni}
		</div>
		<script type="text/javascript">$('#list4').slideToggle('fast');</script>

		<h3 onclick="$('#list5').slideToggle('slow');"><img src="{baseurl}/img/manage.png" alt="misc"> {lang_dep}</h3>
		<div class="dirlist" id="list5">
			{plugin_dep}
		</div>
		<script type="text/javascript">$('#list5').slideToggle('fast');</script>

		<h3 onclick="$('#list6').slideToggle('slow');"><img src="{baseurl}/img/manage.png" alt="misc"> {lang_misc}</h3>
		<div class="dirlist" id="list6">
			{plugin_misc}
		</div>
		<script type="text/javascript">$('#list6').slideToggle('fast');</script>

		<h3 onclick="$('#list7').slideToggle('slow');"><img src="{baseurl}/img/enterprise.png" alt="misc"> {lang_enter}</h3>
		<div class="dirlist" id="list7">
			{plugin_enter}
		</div>
		<script type="text/javascript">$('#list7').slideToggle('fast');</script>
	</div>

//-->


<div id="volumepopup" class="modal-dialog">
<div class="panel">
                    
                                <!-- Classic Form Wizard -->
                                <!--===================================================-->
                                <div id="demo-cls-wz">
                    
                                    <!--Nav-->
                                    <ul class="wz-nav-off wz-icon-inline wz-classic">
                                        <li class="col-xs-3 bg-info active">
                                            <a href="#demo-cls-tab1" data-toggle="tab" aria-expanded="true">
                                                <span class="icon-wrap icon-wrap-xs bg-trans-dark" id="iconk"></span> Server Edit
                                            </a>
                                        </li>
                                        <div class="volumepopupclass"><a id="volumepopupclose"><i class="fa fa-icon fa-close"></i></a></div>
                                        
                                    </ul>
                    
                                    <!--Progress bar-->
                                    <div class="progress progress-sm progress-striped active">
                                        <div class="progress-bar progress-bar-info" style="width: 100%;"></div>
                                    </div>
                    
                    
                                    <!--Form-->
                                    <form class="form-horizontal mar-top">
                                        <div class="panel-body">
                                            <div class="tab-content">
                    
                                                <!--First tab-->
                                                <div class="tab-pane active in" id="demo-cls-tab1">
                                                    <div id="storageform">
                                                    aa
                                                    </div>
                                                </div>
                    
                                                
                                            </div>
                                        </div>
                    
                    
                                    </form>
                                </div>
                                <!--===================================================-->
                                <!-- End Classic Form Wizard -->
                    
                            </div>
</div>






