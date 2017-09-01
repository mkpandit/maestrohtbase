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


<h2 class="inner">{label}</h2>

<div class="row">
	
	<ul id="aws-menu">
		<li><a class="add btn-labeled fa fa-plus" href="/htvcenter/base/index.php?base=cloud&cloud_action=addawsinstance">Add EC2s</a></li>
		<li><a class="add btn-labeled fa fa-hdd-o " href="/htvcenter/base/index.php?base=cloud&cloud_action=awsinstance">List EC2s</a></li>
		<li><a class="add btn-labeled fa fa-hdd-o" href="/htvcenter/base/index.php?base=cloud&cloud_action=awsdisk">List Buckets</a></li>
		<li><a class="add btn-labeled fa fa-plus" href="/htvcenter/base/index.php?base=cloud&cloud_action=addawsstorage">Add Buckets</a></li>
		<li><a class="add btn-labeled fa fa-hdd-o" href="/htvcenter/base/index.php?base=cloud&cloud_action=awsvolumes">List Volumes</a></li>
		<li><a class="add btn-labeled fa fa-plus" href="/htvcenter/base/index.php?base=cloud&cloud_action=addawsvolume">Add Volumes</a></li>
		<li><a class="manage btn-labeled fa fa-cog" href="/htvcenter/base/index.php?base=cloud&cloud_action=configaws">Configure AWS</a></li>
	</ul>
	
	<div id="aws-log">
		{html_information}
	</div>
	
</div>

<div id="volumepopup" class="modal-dialog volumepopup3">
<div class="panel">
                    
                                <!-- Classic Form Wizard -->
                                <!--===================================================-->
                                <div id="demo-cls-wz">
                    
                                    <!--Nav-->
                                    <ul class="wz-nav-off wz-icon-inline wz-classic">
                                        <li class="col-xs-3 bg-info active">
                                            <a href="#demo-cls-tab1" data-toggle="tab" aria-expanded="true">
                                                <span class="icon-wrap icon-wrap-xs bg-trans-dark"><i class="fa fa-hdd-o"></i></span> New Storage
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