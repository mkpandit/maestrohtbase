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

<div id="serverpanel">
                        <div class="col-sm-12 col-md-4 col-lg-4 col-sm-4">
                            <a href="/htvcenter/base/index.php?base=image">
                         <div class="panel media pad-all ">
                              
                                <div class="media-body">
                                    <p class="text-2x mar-no text-thin"><span class="icon-wrap icon-wrap-sm icon-circle bg-success">
                                    <i class="fa fa-upload"></i>
                                    </span>Images</p>
                                </div>

                            </div>
                            </a>
                         </div>

                        <div class="col-sm-12 col-md-4 col-lg-4 col-sm-4">
                            <a href="/htvcenter/base/index.php?base=resource">
                            <div class="panel media pad-all ">
                               
                                <div class="media-body">
                                    <p class="text-2x mar-no text-thin"><span class="icon-wrap icon-wrap-sm icon-circle bg-warning">
                                    <i class="fa fa-database"></i>
                                    </span>Resource</p>
                                </div>
                            </div>
                            </a>
                        </div>

                        <div class="col-sm-12 col-md-4 col-lg-4 col-sm-4">
                            <a href="/htvcenter/base/index.php?base=storage">
                            <div class="panel media pad-all ">
                               
                                <div class="media-body">
                                   
                                    <p class="text-2x mar-no text-thin"><span class="icon-wrap icon-wrap-sm icon-circle bg-danger">
                                    <i class="fa fa-hdd-o"></i>
                                    </span>Storage</p>
                                </div>
                            </div>
                            </a>
                        </div>
</div>


<div class="row">
	
	<div class="row">
		<div id="azure-configuration">
			<form action="{thisfile}" method="GET">
			{form}
			{subscription_id}
			{client_id}
			{secret_key}
			{tenant_id}
			<div id="buttons">{submit}&#160;{cancel}</div>
			</form>
		</div>
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