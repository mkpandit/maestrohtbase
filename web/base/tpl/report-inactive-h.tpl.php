<script>
var nocontent = true;
var inactivespl = true;
</script>      

<style>
 #demo-set-btn {
 	display: none;
 }
</style>      
                                               



<div class="col-xs-12 col-sm-12 col-md-12 col-lg-12 windows_plane">
<div id="home_container">
<div class="row paddingrow">
    <h2 class="redh2">Inactive Virtual Machines</h2>

    
    </div>
    <div class="row">
    	<div class="col-xs-6 col-sm-6 col-md-6 col-lg-6">
    
    		<div class="jcarousel carcarcar">
    			<ul id="namespaces">
    			</ul>
    		</div>
    		<div class="carcarbtn">
    			<a class="btn buttoncarouselback btn-primary"><i class="fa fa-arrow-left"></i> Previous</a><a class="btn buttoncarousel btn-primary">Next <i class="fa fa-arrow-right"></i></a>
    		</div>

    	</div>

    	<div class="col-xs-6 col-sm-6 col-md-6 col-lg-6">

    		<div class="panel panel-primary">
					
								<!--Panel heading-->
								<div class="panel-heading">
									<div class="panel-control">
					
										<!--Nav tabs-->
										<ul class="nav nav-tabs tabzo">
											<li class="active"><a href="#demo-tabs-box-1" data-toggle="tab">Resources</a>
											</li>
											<li><a href="#demo-tabs-box-2" data-toggle="tab">Explorer</a>
											</li>
										</ul>
					
									</div>
									<h3 class="panel-title">Instance Details</h3>
								</div>
					
								<!--Panel body-->
								<div class="panel-body">
					
									<!--Tabs content-->
									<div class="tab-content">
										<div class="tab-pane fade in active" id="demo-tabs-box-1">
											<h4 class="text-thin">Resources Information</h4>
											<p>
                                            <table class="lowtable">
												<tr><td><b>CPU</b>:</td><td> <span id="cpuexp"></span></td></tr>
								    			<tr><td><b>Memory</b>:</td><td> <span id="memoryexp"></span></td></tr>
								    			<tr><td><b>Storage</b>: </td><td><span id="storageexp"></span></td></tr>
								    			<tr><td><b>Status</b>: </td><td><span id="statusexp"></span></td></tr>
                                            </table>
											</p>
											<div class="removbtnplace"></div>
										</div>
										<div class="tab-pane fade" id="demo-tabs-box-2">
											<h4 class="text-thin">Explorer</h4>
											<p>
                                              <table class="lowtable">
												<tr><td><b>Last Start Date</b>: </td><td><span id="creationexp"></span></td></tr>
	    										<tr><td><b>Last Working Time</b>: </td><td><span id="timeexp"></span></td></tr>
                                                </table>
	    										<div class="removbtnplace"></div>
											</p>
										</div>
									</div>
								</div>
							</div>
    		
    		</div>
    	</div>
    </div>                                          
</div>

<div class="modal-dialog" id="volumepopup">
<div class="panel">
                    
                                <!-- Classic Form Wizard -->
                                <!--===================================================-->
                                <div id="demo-cls-wz">
                    
                                    <!--Nav-->
                                    <ul class="wz-nav-off wz-icon-inline wz-classic">
                                        <li class="col-xs-3 bg-info active">
                                            <a aria-expanded="true" data-toggle="tab" href="#demo-cls-tab1">
                                                <span class="icon-wrap icon-wrap-xs bg-trans-dark"><i class="fa fa-server"></i></span> Server Alert
                                            </a>
                                        </li>
                                        <div class="volumepopupclass"><a id="volumepopupclose"><i class="fa fa-icon fa-close"></i></a></div>
                                        
                                    </ul>
                    
                                    <!--Progress bar-->
                                    <div class="progress progress-sm progress-striped active">
                                        <div style="width: 100%;" class="progress-bar progress-bar-info"></div>
                                    </div>
                    
                    
                                    <!--Form-->
                                    <div class="form-horizontal mar-top">
                                        <div class="panel-body">
                                            <div class="tab-content">
                    
                                                <!--First tab-->
                                                <div id="demo-cls-tab1" class="tab-pane active in">
                                                    <div id="storageform"></div>
                                                </div>
                    
                                                
                                            </div>
                                        </div>
                    
                    
                                    </div>
                                </div>
                                <!--===================================================-->
                                <!-- End Classic Form Wizard -->
                    
                            </div>
</div>