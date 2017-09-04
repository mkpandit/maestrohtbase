
			<script>
				//if (typeof(calendarevents) != 'undefined') {
					var eventsarr = {calendarevents};
					var todaydate = '{todaydate}';
					$('#demo-set-btn').hide();
				//}
			</script>

			<span id="storageidvolvol">{storageidvolvol}</span>
				<!--Navbar Dropdown-->
				<!--================================-->
				<div class="navbar-content clearfix">
					<ul class="nav navbar-top-links pull-left">

						<!--Navigation toogle button-->
						<!--~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~-->
						<li class="tgl-menu-btn">
							<a class="mainnav-toggle" href="#">
								<i class="fa fa-navicon fa-lg"></i>
							</a>
						</li>

						
						<!--~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~-->
						<!--End Navigation toogle button-->



						<!--Notification dropdown-->
						<!--~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~-->
						<li class="dropdown">
							<a href="#" data-toggle="dropdown" class="dropdown-toggle">
								<i class="fa fa-asterisk fa-lg"></i>
								<span class="badge badge-header badge-dangero"></span>
							</a>

							<!--Notification dropdown menu-->
							<div class="dropdown-menu dropdown-menu-md with-arrow">
								<div class="pad-all bord-btm">
									<p class="text-lg text-muted text-thin mar-no">Memory and Storage information:</p>
								</div>
								<div class="nano scrollable">
									<div class="nano-content">
										<ul class="head-list">

											<!-- Dropdown list-->
											<li>
												<a href="#">
													<div class="clearfix">
														<p class="pull-left">Storage</p>
														<p class="pull-right hddpercentli"></p>
													</div>
													<div class="progress progress-sm">
														<div style="" class="progress-bar prgrshdd">
															<span class="sr-only hsr-only"></span>
														</div>
													</div>
												</a>
											</li>

											<!-- Dropdown list-->
											<li>
												<a href="#">
													<div class="clearfix">
														<p class="pull-left">Memory</p>
														<p class="pull-right memorypercentli"></p>
													</div>
													<div class="progress progress-sm">
														<div style="" class="progress-bar progress-bar-warning prgrsmemory">
															<span class="sr-only msr-only"></span>
														</div>
													</div>
												</a>
											</li>
									
									
											<!-- Dropdown list-->
											<li id="fullhddlispace">
												<a href="#" class="media">
											<span class="badge badge-success pull-right hddpercentli">90%</span>
													<div class="media-left">
														<span class="icon-wrap icon-circle bg-danger">
															<i class="fa fa-hdd-o fa-lg"></i>
														</span>
													</div>
													<div class="media-body">
														<div class="text-nowrap">Storage is full</div>
														<small class="text-muted">Free storage space is very low</small>
													</div>
												</a>
											</li>

											<!-- Dropdown list-->
											<li id="fullmemorylispace">
												<a href="#" class="media">
											<span class="badge badge-success pull-right memorypercentli">90%</span>
													<div class="media-left">
														<span class="icon-wrap icon-circle bg-danger">
															<i class="fa fa-database fa-lg"></i>
														</span>
													</div>
													<div class="media-body">
														<div class="text-nowrap">Memory is full</div>
														<small class="text-muted">Free memory space is very low</small>
													</div>
												</a>
											</li>
									
									
										</ul>
									</div>
								</div>

								<!--Dropdown footer-->
								<div class="pad-all bord-top">
								
								</div>
							</div>
						</li>
						<!--~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~-->
						<!--End notifications dropdown-->


							<!--Localserver dropdown-->
						<!--~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~-->
						<li class="dropdown">
							<a href="#" data-toggle="dropdown" class="dropdown-toggle">
								<i class="fa fa-plus-square fa-lg"></i>
								
							</a>

							<!--Notification dropdown menu-->
							<div class="dropdown-menu dropdown-menu-md with-arrow">
								<div class="pad-all bord-btm">
									<p class="text-lg text-muted text-thin mar-no">Server Integration:</p>
								</div>
								<div class="nano scrollable">
									<div class="nano-content">
										<ul class="head-list serverul">

												<!-- Dropdown list-->
											<!--<li>
												<a class="citrixaddp">
													<img src="/htvcenter/base/img/citrix.jpg" class="headimg img-circle"/>
													<span class="headspan">Citrix</span>
												</a>
											</li>-->

												<li>
												<a class="showipform xen">
													<img src="/htvcenter/base/img/xen.png" class="headimg  img-circle "/>
													<span class="headspan">Xen</span>
												</a>
											</li>



											<!-- Dropdown list-->
											<li>
												<a class="vmwareaddp">
													<img src="/htvcenter/base/img/vmware.png" class="headimg img-circle "/>
													<span class="headspan">Vmware</span>
												</a>
											</li>

										
											
											<!-- Dropdown list-->
											<li>
												<a class="showipform kvm">
													<img src="/htvcenter/base/img/OCH.png" class="headimg img-circle "/>
													<span class="headspan">OCH</span>
												</a>
											</li>

											<li>
												<a class="hypervaddp">
													<img src="/htvcenter/base/img/windows.png" class="headimg img-circle "/>
													<span class="headspan">Hyper-V</span>
												</a>
											</li>


									
									
											<!-- Dropdown list-->
											<li id="fullhddlispace">
												<a href="#" class="media">
											<span class="badge badge-success pull-right hddpercentli">90%</span>
													<div class="media-left">
														<span class="icon-wrap icon-circle bg-danger">
															<i class="fa fa-hdd-o fa-lg"></i>
														</span>
													</div>
													<div class="media-body">
														<div class="text-nowrap">Storage is full</div>
														<small class="text-muted">Free storage space is very low</small>
													</div>
												</a>
											</li>

											<!-- Dropdown list-->
											<li id="fullmemorylispace">
												<a href="#" class="media">
											<span class="badge badge-success pull-right memorypercentli">90%</span>
													<div class="media-left">
														<span class="icon-wrap icon-circle bg-danger">
															<i class="fa fa-database fa-lg"></i>
														</span>
													</div>
													<div class="media-body">
														<div class="text-nowrap">Memory is full</div>
														<small class="text-muted">Free memory space is very low</small>
													</div>
												</a>
											</li>
									
									
										</ul>
									</div>
								</div>

								<!--Dropdown footer-->
								<div class="pad-all bord-top">
								
								</div>
							</div>
						</li>
						<!--~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~-->
						<!--End localserver dropdown-->


							<!--Localserver dropdown-->
						<!--~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~-->
						<li class="dropdown">
							<a href="#" data-toggle="dropdown" class="dropdown-toggle">
								<i class="fa fa-calendar-plus-o fa-lg"></i>
								
							</a>

							<!--Notification dropdown menu-->
							<div class="dropdown-menu dropdown-menu-md with-arrow">
								<div class="pad-all bord-btm">
									<p class="text-lg text-muted text-thin mar-no">Scheduler:</p>
								</div>
								<div class="nano scrollable">
									<div class="nano-content">
										<ul class="head-list serverul">

												<!-- Dropdown list-->
											<li class="shedulerli">
												<a id="shedulerstart">
													<i class="fa fa-play fa-2x"></i>
													<span class="headspan">Start VM</span>
												</a>
											</li>

											<li class="shedulerli">
												<a id="shedulerstop">
													<i class="fa fa-stop fa-2x"></i>
													<span class="headspan">Stop VM</span>
												</a>
											</li>

											<li class="shedulerli">
												<a id="shedulerremove">
													<i class="fa fa-trash fa-2x"></i>
													<span class="headspan">Remove VM</span>
												</a>
											</li>

											<li class="shedulerli">
												<a id="shedulersnapclone">
													<i class="fa fa-clone fa-2x"></i>
													<span class="headspan">VM Snapshot and Clone</span>
												</a>
											</li>

											

										</ul>
									</div>
								</div>

								<!--Dropdown footer-->
								<div class="pad-all bord-top">
									<i class="fa fa-calendar-o" style="color: #5fa2dd;"></i> <a href="/htvcenter/base/index.php?base=callendar" id="calendarlink"> Calendar page</a>
									
								</div>
							</div>
						</li>
						<!--~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~-->
						<!--End sheduler dropdown-->


							<!--report dropdown-->
						<!--~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~-->
						<li class="dropdown">
							<a href="#" data-toggle="dropdown" class="dropdown-toggle">
								<i class="fa fa-file-text-o fa-lg"></i>
								
							</a>

							<!--Notification dropdown menu-->
							<div class="dropdown-menu dropdown-menu-md with-arrow">
								<div class="pad-all bord-btm">
									<p class="text-lg text-muted text-thin mar-no">Report Features:</p>
								</div>
								<div class="nano scrollable">
									<div class="nano-content">
										<p id="fortisnonono">Fortis Billing not availiable</p>
										<ul class="head-list serverul billul">
                                            <li>
                                                <a class="dashboardreport" href="index.php?report=report_dashboard">
                                                    <i class="fa fa-circle-o-notch dropfa fadash fa-2x"></i>
                                                    <span class="headspan">Dashboard</span>
                                                </a>
                                            </li>

                                            <li>
                                                <a class="dashboardreport" href="index.php?report=report_bills">
                                                    <i class="fa fa-usd dropfa fabills fa-2x"></i>
                                                    <span class="headspan">Bills</span>
                                                </a>
                                            </li>

                                            <li>
                                                <a class="dashboardreport" href="index.php?report=report_explorer">
                                                    <i class="fa fa-search dropfa faexplorer fa-2x"></i>
                                                    <span class="headspan">Explorer</span>
                                                </a>
                                            </li>

                                        </ul>
									</div>
								</div>

								<!--Dropdown footer-->
								
							</div>
						</li>
						<!--~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~-->
						<!--End report dropdown-->

						<!--
						<li class="tgl-menu-btn">
							<a id="order">
								<i class="fa fa-file-text-o fa-lg"></i>
							</a>
						</li>
						-->



						
						<input id="username" type="hidden" value="{username}">
						<input id="userlang" type="hidden" value="{userlang}">
						 {language_select}
						
					</ul>

					<ul class="nav navbar-top-links pull-right">


						<!--Language selector-->
						<!--~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~-->
						<!-- <li class="dropdown">
							<a id="demo-lang-switch" class="lang-selector dropdown-toggle" href="#" data-toggle="dropdown">
								
							</a>

							<ul class="head-list dropdown-menu with-arrow langselectoul">
								<li>
									<a href="#" >
										<img class="lang-flag" src="img/flags/united-kingdom.png" alt="English">
										<span class="lang-id">EN</span>
										<span class="lang-name">English</span>
									</a>
								</li>
							
								<li>

									<a href="#" >
										<img class="lang-flag" src="img/flags/germany.png" alt="Germany">
										<span class="lang-id">DE</span>
										<span class="lang-name">Deutsch</span>
									</a>
								</li>
							
								<li>
									<a href="#">
										<img class="lang-flag" src="img/flags/spain.png" alt="Spain">
										<span class="lang-id">ES</span>
										<span class="lang-name">Espa&ntilde;ol</span>
									</a>
								</li>
							</ul>
						</li> -->
						<!--~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~-->
						<!--End language selector-->



						<!--User dropdown-->
						<!--~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~-->
						<li id="dropdown-user" class="dropdown">
							<a href="#" data-toggle="dropdown" class="dropdown-toggle text-right">
								<span class="pull-right">
									<img class="img-circle img-user media-object" src="img/av1.png" alt="Profile Picture">
								</span>
								<div class="username hidden-xs">{account}</div>
							</a>


							<div class="dropdown-menu dropdown-menu-md dropdown-menu-right with-arrow panel-default">

								<!-- Dropdown heading  -->
								<!--<div class="pad-all bord-btm">
									<p class="text-lg text-muted text-thin mar-btm">Account:</p>
									
								</div>-->
								<br/>

								<!-- User dropdown menu -->
								<ul class="head-list">
								
									
									<li>
										<a href="index.php?base=user">
											<i class="fa fa-gear fa-fw fa-lg"></i> Settings
										</a>
									</li>
									<li>
										<a id="infoshow">
											<i class="fa fa-question-circle fa-fw fa-lg"></i> Information
										</a>
									</li>
								
								</ul>

								<!-- Dropdown footer -->
								<div class="pad-all text-right">
									<a onclick="Logout(this);return false;" class="btn btn-primary" href="/htvcenter/" >
										<i class="fa fa-sign-out fa-fw"></i> Logout
									</a>
								</div>
							</div>
						</li>
						<!--~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~-->
						<!--End user dropdown-->
						
					</ul>
				</div>
				<!--================================-->
				<!--End Navbar Dropdown-->

				<div class="modal-dialog" id="infopopup">
									<div class="modal-content">
										<div class="modal-header">
											<button type="button" data-dismiss="modal" class="close"><span>×</span><span class="sr-only">Close</span></button>
											<h4 class="modal-title">About Maestro</h4>
										</div>
					
										<div class="modal-body">
											<img id="aboutimglogo" class="img-center" src="/htvcenter/base/img/logo.png"/><br/>
        <p>Maestro is developed by HTBase Corp.<br/><br/>
This source code is released under the Maestro Server and Client License unless otherwise agreed with HTBase Corp. By using this software, you acknowledge having read this license and agree to be bound thereby.</p>
      
										</div>
					
										<div class="modal-footer">
											<button type="button" data-dismiss="modal" class="btn btn-default">Close</button>
										
										</div>
									</div>
								</div>

<div id="ajaxbuf"></div>


<div id="serverpopup" class="modal-dialog">
<div class="panel">
                    
                                <!-- Classic Form Wizard -->
                                <!--===================================================-->
                                <div id="demo-cls-wz">
                    
                                    <!--Nav-->
                                    <ul class="wz-nav-off wz-icon-inline wz-classic">
                                        <li class="col-xs-4 bg-info active">
                                            <a href="#demo-cls-tab1" data-toggle="tab" aria-expanded="true">
                                                <span class="icon-wrap icon-wrap-xs bg-trans-dark"><i class="fa fa-plus-square"></i></span> Server Integration
                                            </a>
                                        </li>
                                        <div class="volumepopupclass"><a id="serverpopupclose"><i class="fa fa-icon fa-close"></i></a></div>
                                        
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
                                                    <div id="serverform">
                                                    	<label>IP address:</label> <input type="text" id="ip" class="serveraddinput" /> <br/>
														<label>Server name:</label> <input type="text" id="name" class="serveraddinput" /> <br/>
														<label>Root Password:</label> <input type="text" id="pass" class="serveraddinput" /> <br/>
														<label>Network Interface:</label> <input type="text" id="iface" class="serveraddinput" /> <br/>
                                                    	<a class="btn btn-info" id="servintbtn">Integrate Server</a>
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





<div id="schedulerpopup" class="modal-dialog">
<div class="panel">
                    
                                <!-- Classic Form Wizard -->
                                <!--===================================================-->
                                <div id="demo-cls-wz">
                    
                                    <!--Nav-->
                                    <ul class="wz-nav-off wz-icon-inline wz-classic">
                                        <li class="col-xs-4 bg-info active">
                                            <a href="#demo-cls-tab1" data-toggle="tab" aria-expanded="true">
                                                <span class="icon-wrap icon-wrap-xs bg-trans-dark"><i class="fa fa-calendar-plus-o"></i></span> Scheduler
                                            </a>
                                        </li>
                                        <div class="volumepopupclass"><a id="schedulerpopupclose"><i class="fa fa-icon fa-close"></i></a></div>
                                        
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
                                                    <div id="schedulerform">
                                                    	<div class="row">
															  <div class="col-xs-5">
															  
															    <select name="from" id="schmultiselect" class="form-control notselectpicker" size="8" multiple="multiple">
															      {scheduleroptions}
															    </select>
															  </div>
															  <div class="col-xs-2">
															    <button type="button" id="schmultiselect_rightAll" class="btn btn-block"><i class="fa fa-long-arrow-right fa-2x"></i></button>
															    <button type="button" id="schmultiselect_rightSelected" class="btn btn-block"><i class="fa fa-long-arrow-right fa-lg"></i></button>
															    <button type="button" id="schmultiselect_leftSelected" class="btn btn-block"><i class="fa fa-long-arrow-left fa-lg"></i></button>
															    <button type="button" id="schmultiselect_leftAll" class="btn btn-block"><i class="fa fa-long-arrow-left fa-2x"></i></button>
															  </div>
															  <div class="col-xs-5">
															    <select name="to" id="schmultiselect_to" class="form-control notselectpicker" size="8" multiple="multiple">
															    </select>
															  </div>
															</div>
															<br/>
													<div class="row">
														<div class="col-xs-6 col-sm-6 col-xs-6 col-lg-6">

															<div id="demo-dp-component">
																<div class="input-group date">
																	<input type="text" class="form-control" id="schdate">
																	<span class="input-group-addon"><i class="fa fa-calendar fa-lg"></i></span>
																</div>
															</div>

														</div>

														<div class="col-xs-6 col-sm-6 col-xs-6 col-lg-6">
														<div class="input-group date">
															<input type="text" class="form-control" id="demo-tp-com">
															<span class="input-group-addon"><i class="fa fa-clock-o fa-lg"></i></span>
														</div>
														</div>
													</div>		<br/>
															
														<a href="/htvcenter/base/index.php?base=callendar" id="calendarlink"><i class="fa fa-calendar-o"></i> Calendar page</a>

                                                    	<a class="btn btn-info" id="schedulertbtn">Create Scheduler Rule</a>
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


<div id="schedulerconfirmpopup" class="modal-dialog">
<div class="panel">
                    
                                <!-- Classic Form Wizard -->
                                <!--===================================================-->
                                <div id="demo-cls-wz">
                    
                                    <!--Nav-->
                                    <ul class="wz-nav-off wz-icon-inline wz-classic">
                                        <li class="col-xs-4 bg-info active">
                                            <a href="#demo-cls-tab1" data-toggle="tab" aria-expanded="true">
                                                <span class="icon-wrap icon-wrap-xs bg-trans-dark"><i class="fa fa-calendar-plus-o"></i></span> Scheduler
                                            </a>
                                        </li>
                                        <div class="volumepopupclass"><a class="schremoveclose"><i class="fa fa-icon fa-close"></i></a></div>
                                        
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
                                                    <div id="removeschedulerform">
                                                    	<p><b>Do you want to remove this rule from scheduler:</b><br/><br/></p>
                                                    		<label><b>Id:</b></label><span id="removesid"></span><br/><br/>
                                                    		<label><b>Name:</b></label><span id="removesname"></span><br/>
                                                    	
                                                    	<a class="btn btn-info" id="schremove">Remove Scheduler Rule</a>
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



<div id="integratepopup" class="modal-dialog">
<div class="panel">
                    
                                <!-- Classic Form Wizard -->
                                <!--===================================================-->
                                <div id="demo-cls-wz">
                    
                                    <!--Nav-->
                                    <ul class="wz-nav-off wz-icon-inline wz-classic">
                                        <li class="col-xs-4 bg-info active">
                                            <a href="#demo-cls-tab1" data-toggle="tab" aria-expanded="true">
                                                <span class="icon-wrap icon-wrap-xs bg-trans-dark"><i class="fa fa-plus-square"></i></span> Integrate
                                            </a>
                                        </li>
                                        <div class="volumepopupclass"><a class="integratepopupclose"><i class="fa fa-icon fa-close"></i></a></div>
                                        
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
                                                    <div id="integratepopupform">
                                                    	
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




<div id="volschedulerpopup" class="modal-dialog">
<div class="panel">
                    
                                <!-- Classic Form Wizard -->
                                <!--===================================================-->
                                <div id="demo-cls-wz">
                    
                                    <!--Nav-->
                                    <ul class="wz-nav-off wz-icon-inline wz-classic">
                                        <li class="col-xs-4 bg-info active">
                                            <a href="#demo-cls-tab1" data-toggle="tab" aria-expanded="true">
                                                <span class="icon-wrap icon-wrap-xs bg-trans-dark"><i class="fa fa-calendar-plus-o"></i></span> Scheduler
                                            </a>
                                        </li>
                                        <div class="volumepopupclass"><a id="volschedulerpopupclose"><i class="fa fa-icon fa-close"></i></a></div>
                                        
                                    </ul>
                    
                                    <!--Progress bar-->
                                    <div class="progress progress-sm progress-striped active">
                                        <div class="progress-bar progress-bar-info" style="width: 100%;"></div>
                                    </div>
                    
                    
                                    <!--Form-->
                                    <div class="form-horizontal mar-top">
                                        <div class="panel-body">
                                            <div class="tab-content">

                                            	<div id="volactionsch">
                                            		<select id="volactionselect">
                                            				<option>Clone</option>
                                            				<option>Snap</option>
                                            		</select>
                                            	</div>
                    
                                                <!--First tab-->
                                                <div class="tab-pane active in" id="demo-cls-tab1">
                                                    <div id="volschedulerform">
                                                    	<div class="row">
															  <div class="col-xs-5">
															  
															    <select name="from" id="volschmultiselect" class="form-control notselectpicker" size="8" multiple="multiple">
															      	{voloptions}
															    </select>
															  </div>
															  <div class="col-xs-2">
															    <button type="button" id="volschmultiselect_rightAll" class="btn btn-block"><i class="fa fa-long-arrow-right fa-2x"></i></button>
															    <button type="button" id="volschmultiselect_rightSelected" class="btn btn-block"><i class="fa fa-long-arrow-right fa-lg"></i></button>
															    <button type="button" id="volschmultiselect_leftSelected" class="btn btn-block"><i class="fa fa-long-arrow-left fa-lg"></i></button>
															    <button type="button" id="volschmultiselect_leftAll" class="btn btn-block"><i class="fa fa-long-arrow-left fa-2x"></i></button>
															  </div>
															  <div class="col-xs-5">
															    <select name="to" id="volschmultiselect_to" class="form-control notselectpicker" size="8" multiple="multiple">
															    </select>
															  </div>
															</div>
															<br/>
													<div class="row">
														<div class="col-xs-6 col-sm-6 col-xs-6 col-lg-6">

															<div id="voldemo-dp-component">
																<div class="input-group date">
																	<input type="text" class="form-control" id="volschdate">
																	<span class="input-group-addon"><i class="fa fa-calendar fa-lg"></i></span>
																</div>
															</div>

														</div>

														<div class="col-xs-6 col-sm-6 col-xs-6 col-lg-6">
														<div class="input-group date">
															<input type="text" class="form-control" id="voldemo-tp-com">
															<span class="input-group-addon"><i class="fa fa-clock-o fa-lg"></i></span>
														</div>
														</div>
													</div>		<br/>
															
														<a href="/htvcenter/base/index.php?base=callendar" id="calendarlink"><i class="fa fa-calendar-o"></i> Calendar page</a>

                                                    	<a class="btn btn-info" id="volschedulertbtn">Create Scheduler Rule</a>
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


<div class="modal-dialog" id="popup">
<div class="panel">
                    
                                <!-- Classic Form Wizard -->
                                <!--===================================================-->
                                <div id="demo-cls-wz">
                    
                                    <!--Nav-->
                                    <ul class="wz-nav-off wz-icon-inline wz-classic">
                                        <li class="col-xs-3 bg-info active">
                                            <a aria-expanded="true" data-toggle="tab" href="#demo-cls-tab1">
                                                <span class="icon-wrap icon-wrap-xs bg-trans-dark"><i class="fa fa-file-text-o"></i></span> Report
                                            </a>
                                        </li>
                                        <div class="volumepopupclass"><a id="popupclose"><i class="fa fa-icon fa-close"></i></a></div>
                                        
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
                                                    <div id="popupform">
                                                        <h2>Cloud Billing Report</h2>
                                                        <label>User:</label>  <select id="reportuser" class="notselectpicker">{cloudusers}</select>
														<label>Month:</label> <select id="reportmonth" class="notselectpicker">
															<option value="Jan">January</option>
															<option value="Feb">February</option>
															<option value="Mar">March</option>
															<option value="Apr">April</option>
															<option value="May">May</option>
															<option value="Jun">June</option>
															<option value="Jul">July</option>
															<option value="Aug">August</option>
															<option value="Sep">September</option>
															<option value="Oct">October</option>
															<option value="Nov">November</option>
															<option value="Dec">December</option>
															
														</select>
														<label>Year:</label>  <select id="reportyear" class="notselectpicker">{reportyear}</select>
														<div id="buttons"><input type="submit" value="Report" class="submit" id="orderreport"></div>
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

<div class="modal-dialog" id="popuptable">
<div class="panel">
                    
                                <!-- Classic Form Wizard -->
                                <!--===================================================-->
                                <div id="demo-cls-wz">
                    
                                    <!--Nav-->
                                    <ul class="wz-nav-off wz-icon-inline wz-classic">
                                        <li class="col-xs-3 bg-info active">
                                            <a aria-expanded="true" data-toggle="tab" href="#demo-cls-tab1">
                                                <span class="icon-wrap icon-wrap-xs bg-trans-dark"><i class="fa fa-file-text-o"></i></span> Report
                                            </a>
                                        </li>
                                        <div class="volumepopupclass"><a id="popuptableclose"><i class="fa fa-icon fa-close"></i></a></div>
                                        
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
                                                    <div id="popuptableform">
                                                        
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

<script>
$(document).ready(function(){
					var billreport = '{configbill}';

					if (billreport == 'false') {
						$('.billul').hide();
						$('#fortisnonono').show();
					} else {
						$('.billul').show();
						$('#fortisnonono').hide();
					}
				});
			</script>





<div id="volumepopupzmail" class="modal-dialog">
<div class="panel">
                    
                                <!-- Classic Form Wizard -->
                                <!--===================================================-->
                                <div id="demo-cls-wz">
                    
                                    <!--Nav-->
                                    <ul class="wz-nav-off wz-icon-inline wz-classic">
                                        <li class="col-xs-3 bg-info active">
                                            <a href="#demo-cls-tab1" data-toggle="tab" aria-expanded="true">
                                                <span class="icon-wrap icon-wrap-xs bg-trans-dark"><i class="fa fa-envelope-o"></i></span> Mail
                                            </a>
                                        </li>
                                        <div class="volumepopupclass"><a id="volumepopupzmailclose"><i class="fa fa-icon fa-close"></i></a></div>
                                        
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
                                                    <div id="storageformzmail">
                                                 
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


<div id="volopt">
		{voloptions}
</div>

<div class="msgBox" id="blackalert"><span class="textalert"></span><br></div>

<script type="text/javascript">
	function blackalert (stralert) {
		$('#blackalert').find('.textalert').text(stralert);
		$('#blackalert').show();
		setInterval(blackalerthide, 6000);
	}

	function blackalerthide() {
		$('#blackalert').hide();
	}
</script>