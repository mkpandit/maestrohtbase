<script>
var nocontent = true;
var hostpools = true;
var donutpool = true;
</script>      

<style>
 #demo-set-btn {
 	display: none;
 }
</style>      
                                               



<div class="col-xs-12 col-sm-12 col-md-12 col-lg-12 windows_plane">
<div id="home_container">
<div class="row paddingrow">
    <h2 class="redh2">Resource Host Pool</h2>

   <a class="btn btn-primary" id="hostpoolcrt">Create Host Pool</a>
    </div>
    <div class="row" id="hostpoolcontent">
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
									<h3 class="panel-title">Pool Details</h3>
								</div>
					
								<!--Panel body-->
								<div class="panel-body">
					
									<!--Tabs content-->
									<div class="tab-content">
										<div class="tab-pane fade in active" id="demo-tabs-box-1">
											<h4 class="text-thin">Resources Information</h4>
											<p>
											   <table class="lowtable">
												<tr><td><b>CPU</b>: </td><td> <span id="cpuexp"></span></td></tr>
								    			<tr><td><b>Memory</b>: </td><td> <span id="memoryexp"></span></td></tr>
								    			</table>
                                                <a id="showpooldetail"><i class="fa fa-eye"></i> Pool Detail Information</a>
								    			<div class="removbtnplace"></div>
											</p>
										</div>
										<div class="tab-pane fade" id="demo-tabs-box-2">
											<h4 class="text-thin">Explorer</h4>
											<div id="hostsplace">
											</div>
										</div>
									</div>
								</div>
							</div>
    		
    		</div>
    	</div>

        <div class="row" id="poolsdashboard">
            <span id="closepooldetail"><i class="fa fa-close"></i> close</span>
            <br/><br/>
        <div class="col-xs-6 col-sm-8 col-md-8 col-lg-8">
            <div class="row">
            <div class="col-xs-12 col-sm-6 col-md-6 col-lg-6 dashcol dashcola">
                <div class="window storagemainwindow panel panel-bordered-primary">
                    <div class="panel-heading">
                        <h3 class="panel-title">Pool overview</h3>
                        <span class="badge badge-success bbadger"><a href="index.php?base=appliance" class="serversdetail">active</a></span>
                    </div>
                    <div class="panel-body">
                        <b>IP:</b> 127.0.0.1
                    </div>
                </div>
            </div>
            <div class="col-xs-12 col-sm-6 col-md-6 col-lg-6 dashcol dashcola">
                <div class="window storagemainwindow panel panel-bordered-primary">
                    <div class="panel-heading">
                        <h3 class="panel-title">Summary</h3>
                        
                    </div>
                    <div class="panel-body">
                          <div class="row toprow">
                         <h2 class="minih">Nodes:</h2>
                         <div class="col-xs-6 col-sm-6 col-md-6 col-lg-6">
                            <div class="esxileft">
                                <b>4</b> <br>
                                <span>active</span>
                            </div>
                         </div>
                       
                        </div>
                        <div class="row">
                        
                         <h2 class="minih">Virtual machines:</h2>
                        <div class="col-xs-6 col-sm-6 col-md-6 col-lg-6">
                            <div class="esxileft">
                                <b>0</b> <br>
                                <span>VMs</span>
                            </div>
                        </div>
                        <div class="col-xs-6 col-sm-6 col-md-6 col-lg-6 esxiright">

                            <div class="vmsside">
                                <b>0</b> active<br>
                                <b>0</b> stopped<br>
                                <b>0</b> shutoff
                                
                            </div>
                        </div>
                        </div>
                       

                    </div>
                </div>
            </div>
        </div>
        <div class="row">
        <div class="col-xs-12 col-md-12 col-lg-12 col-sm-12 dashcol dashcolanode">
                <div class="window storagemainwindow panel panel-bordered-primary">
                    <div class="panel-heading">
                        <h3 class="panel-title">Nodes</h3>
                    </div>
                    <div class="panel-body">
                        <table class="table table-striped table-bordered table-hover nodestable">
                            <tr class="success"><td align="center">Name</td><td align="center">Status</td><td align="center">CPU cores</td><td align="center">Memory</td></tr>
                            <tr><td align="center">Example</td><td align="center"><span class="badge badge-success bbadger"><a href="index.php?base=appliance" class="serversdetail">active</a></span></td><td align="center">6.93</td><td align="center">27.82</td></tr>

                            <tr><td align="center">Example</td><td align="center"><span class="badge badge-success bbadger"><a href="index.php?base=appliance" class="serversdetail">active</a></span></td><td align="center">6.93</td><td align="center">27.82</td></tr>

                            <tr><td align="center">Example</td><td align="center"><span class="badge badge-success bbadger"><a href="index.php?base=appliance" class="serversdetail">active</a></span></td><td align="center">6.93</td><td align="center">27.82</td></tr>

                            <tr><td align="center">Example</td><td align="center"><span class="badge badge-success bbadger"><a href="index.php?base=appliance" class="serversdetail">active</a></span></td><td align="center">6.93</td><td align="center">27.82</td></tr>
                        </table>
                    </div>
                </div>
        </div>
        </div>
    </div>
            <div class="col-xs-12 col-sm-4 col-md-4 col-lg-4 dashcol dashcola">
                <div class="window storagemainwindow panel panel-bordered-primary">
                    <div class="panel-heading">
                        <h3 class="panel-title">CPU usage</h3>
                    </div>
                    <div class="panel-body">
                        <div id="poolcpudash"></div>
                        <div id="poolinfa">
                             <div class="esxileft">
                                <b>135</b> cores <br>
                                <span class="prspano"><b>provisioned</b></span>
                            </div>

                            <div class="esxileft">
                                <b>6.43</b> <br>
                                <span class="prspano"><b>optimization ratio</b></span>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="window storagemainwindow panel panel-bordered-primary">
                    <div class="panel-heading">
                        <h3 class="panel-title">STORAGE usage</h3>
                    </div>
                    <div class="panel-body">
                        <div id="poolstoragedash"></div>
                        <div id="poolinfa">
                            <div class="esxileft">
                                <b>135</b> TB <br>
                                <span class="prspano"><b>provisioned</b></span>
                            </div>

                            <div class="esxileft">
                                <b>6.43</b> <br>
                                <span class="prspano"><b>optimization ratio</b></span>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="window storagemainwindow panel panel-bordered-primary">
                    <div class="panel-heading">
                        <h3 class="panel-title">MEMORY usage</h3>
                    </div>
                    <div class="panel-body">
                        <div id="poolmemorydash"></div>
                        <div id="poolinfa">
                            <div class="esxileft">
                                <b>6.25</b> GB <br>
                                <span class="prspano"><b>provisioned</b></span>
                            </div>

                            <div class="esxileft">
                                <b>6.43</b> <br>
                                <span class="prspano"><b>optimization ratio</b></span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
       
        </div>
    </div>                                          
</div>


<div id="poolservpopup" class="modal-dialog">
<div class="panel">
                    
                                <!-- Classic Form Wizard -->
                                <!--===================================================-->
                                <div id="demo-cls-wz">
                    
                                    <!--Nav-->
                                    <ul class="wz-nav-off wz-icon-inline wz-classic">
                                        <li class="col-xs-4 bg-info active">
                                            <a href="#demo-cls-tab1" data-toggle="tab" aria-expanded="true">
                                                <span class="icon-wrap icon-wrap-xs bg-trans-dark"><i class="fa fa-connectdevelop"></i></span> Host Pool
                                            </a>
                                        </li>
                                        <div class="volumepopupclass"><a id="poolserverpopupclose"><i class="fa fa-icon fa-close"></i></a></div>
                                        
                                    </ul>
                    
                                    <!--Progress bar-->
                                    <div class="progress progress-sm progress-striped active">
                                        <div class="progress-bar progress-bar-info" style="width: 100%;"></div>
                                    </div>
                    
                    
                                    <!--Form-->
                                    <div class="form-horizontal mar-top">
                                        <div class="panel-body">
                                            <div class="tab-content">
                    
                                                <!--First tab-->
                                                <div class="tab-pane active in" id="demo-cls-tab1">
                                                    <div id="servpopupform">
                                                    	<div class="row">
															  <div class="col-xs-5">
															  
															    <select name="from" id="schmultiselectpool" class="form-control notselectpicker" size="8" multiple="multiple">
															      {hostpoolserveroptions}
															      
															    </select>
															  </div>
															  <div class="col-xs-2">
															    <button type="button" id="schmultiselectpool_rightAll" class="btn btn-block"><i class="fa fa-long-arrow-right fa-2x"></i></button>
															    <button type="button" id="schmultiselectpool_rightSelected" class="btn btn-block"><i class="fa fa-long-arrow-right fa-lg"></i></button>
															    <button type="button" id="schmultiselectpool_leftSelected" class="btn btn-block"><i class="fa fa-long-arrow-left fa-lg"></i></button>
															    <button type="button" id="schmultiselectpool_leftAll" class="btn btn-block"><i class="fa fa-long-arrow-left fa-2x"></i></button>
															  </div>
															  <div class="col-xs-5">
															    <select name="to" id="schmultiselectpool_to" class="form-control notselectpicker" size="8" multiple="multiple">
															    </select>
															  </div>
															</div>
															<br/>
												
															
													    <div class="row">
														
															
																	<label id="poollabel">Host Pool Name:</label>
																	<input type="text" class="form-control" id="poolnameinput">
															

														</div>

                                                    	<a class="btn btn-info" id="crpoolbtn">Create Host Pool</a>
                                                    </div>
                                                </div>
                    
                                                
                                            </div>
                                        </div>
                    
                    
                                    </div>
                                </div>
                                <!--===================================================-->
                                <!-- End Classic Form Wizard -->
                    
                            </div>
</div>


<div id="trasherzconfirm" class="modal-dialog">
<div class="panel">
                    
                                <!-- Classic Form Wizard -->
                                <!--===================================================-->
                                <div id="demo-cls-wz">
                    
                                    <!--Nav-->
                                    <ul class="wz-nav-off wz-icon-inline wz-classic">
                                        <li class="col-xs-4 bg-info active">
                                            <a href="#demo-cls-tab1" data-toggle="tab" aria-expanded="true">
                                                <span class="icon-wrap icon-wrap-xs bg-trans-dark"><i class="fa fa-trash-o"></i></span> Remove Pool
                                            </a>
                                        </li>
                                        <div class="volumepopupclass"><a id="trasherzconfirmclose"><i class="fa fa-icon fa-close"></i></a></div>
                                        
                                    </ul>
                    
                                    <!--Progress bar-->
                                    <div class="progress progress-sm progress-striped active">
                                        <div class="progress-bar progress-bar-info" style="width: 100%;"></div>
                                    </div>
                    
                    
                                    <!--Form-->
                                    <div class="form-horizontal mar-top">
                                        <div class="panel-body">
                                            <div class="tab-content">
                    
                                                <!--First tab-->
                                                <div class="tab-pane active in" id="demo-cls-tab1">
                                                    <div id="servpopupform">
                                                    	 Do you want to Remove this Host Pool? <br/><br/>

                                                    	<table class="lowtable">
												<tr><td><b>Id</b>: </td><td> <span id="confirmid"></span></td></tr>
								    			<tr><td><b>Name</b>: </td><td> <span id="confirmname"></span></td></tr>
								    			</table>

                                                    	<div class="rembtnoo">
                                                    	<a class="btn btn-info" id="remtrasherzbtn">Yes</a> <a class="btn btn-info" id="noremtrasherzbtn">No</a>
                                                    	</div>
                                                    </div>
                                                </div>
                    
                                                
                                            </div>
                                        </div>
                    
                    
                                    </div>
                                </div>
                                <!--===================================================-->
                                <!-- End Classic Form Wizard -->
                    
                            </div>
</div>


<div id="trasherzconfirmhost" class="modal-dialog">
<div class="panel">
                    
                                <!-- Classic Form Wizard -->
                                <!--===================================================-->
                                <div id="demo-cls-wz">
                    
                                    <!--Nav-->
                                    <ul class="wz-nav-off wz-icon-inline wz-classic">
                                        <li class="col-xs-4 bg-info active">
                                            <a href="#demo-cls-tab1" data-toggle="tab" aria-expanded="true">
                                                <span class="icon-wrap icon-wrap-xs bg-trans-dark"><i class="fa fa-trash-o"></i></span> Remove Host
                                            </a>
                                        </li>
                                        <div class="volumepopupclass"><a id="trasherzconfirmclosehost"><i class="fa fa-icon fa-close"></i></a></div>
                                        
                                    </ul>
                    
                                    <!--Progress bar-->
                                    <div class="progress progress-sm progress-striped active">
                                        <div class="progress-bar progress-bar-info" style="width: 100%;"></div>
                                    </div>
                    
                    
                                    <!--Form-->
                                    <div class="form-horizontal mar-top">
                                        <div class="panel-body">
                                            <div class="tab-content">
                    
                                                <!--First tab-->
                                                <div class="tab-pane active in" id="demo-cls-tab1">
                                                    <div id="servpopupform">
                                                         Do you want to Remove this Host from Host Pool? <br/><br/>

                                                        <table class="lowtable">
                                                <tr><td><b>Host Id</b>: </td><td> <span id="confirmidhost"></span></td></tr>
                                                <tr><td><b>Pool Id</b>: </td><td> <span id="confirmidpool"></span></td></tr>
                                                <tr><td><b>Name</b>: </td><td> <span id="confirmnamehost"></span></td></tr>
                                                </table>

                                                        <div class="rembtnoo">
                                                        <a class="btn btn-info" id="remtrasherzbtnhost">Yes</a> <a class="btn btn-info" id="noremtrasherzbtnhost">No</a>
                                                        </div>
                                                    </div>
                                                </div>
                    
                                                
                                            </div>
                                        </div>
                    
                    
                                    </div>
                                </div>
                                <!--===================================================-->
                                <!-- End Classic Form Wizard -->
                    
                            </div>
</div>

