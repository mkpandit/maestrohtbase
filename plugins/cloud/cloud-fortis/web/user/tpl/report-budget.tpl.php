    <script>
    var nocontent = true;
    var budgetpage = true;
    var datepickeryep = true;
    </script>                         
                                               </div></div>



<div class="col-xs-12 col-sm-9 col-md-10 col-lg-10 windows_plane">
<div id="home_container">
<div class="row paddingrow">
<h2 class="redh2">Budget Planning  <a href="index.php?report=report_budget_create" class="btn btn-primary creatbudg"><i class="fa fa-plus"></i>  &nbsp;Create New</a></h2> 

</div>
   
       
     <br/> <br/>
   <div class="row">
        <div class="col-xs-6 col-sm-6 col-md-6 col-lg-6">
    
            <div class="jcarousel carbudget">
                <ul id="namespaces">
                </ul>
            </div>
            <div class="carbudgbtn" style="display: none;">
                <a class="btn buttoncarouselback btn-primary"><i class="fa fa-arrow-left"></i> Previous</a><a class="btn buttoncarousel btn-primary">Next <i class="fa fa-arrow-right"></i></a>
            </div>

        </div>

        <div class="col-xs-6 col-sm-6 col-md-6 col-lg-6">

            <div class="panel panel-primary infoblockbudget">
                    
                                <!--Panel heading-->
                                <div class="panel-heading">
                                    <div class="panel-control">
                    
                                        <!--Nav tabs-->
                                        <ul class="nav nav-tabs">
                                            <li class="active"><a href="#demo-tabs-box-1" data-toggle="tab">Resources</a>
                                            </li>
                                             <li><a href="#demo-tabs-box-2" data-toggle="tab">Alerts</a>
                                            </li>
                                            <li><a href="#demo-tabs-box-3" data-toggle="tab">Dates</a>
                                            </li>
                                        </ul>
                    
                                    </div>
                                    <h3 class="panel-title">Budget Details</h3>
                                </div>
                    
                                <!--Panel body-->
                                <div class="panel-body">
                    
                                    <!--Tabs content-->
                                    <div class="tab-content">
                                        <div class="tab-pane fade in active" id="demo-tabs-box-1">
                                            <h4 class="text-thin">Resources Price Limits</h4>
                                            <span id="resbudgetchoose">Choose Budget Block</span>
                                            <div id="resbudget">
                                               <table class="lowtable lowlow">
                                               <tr><td>
                                                <b>CPU</b>: </td><td> <span id="cpubd" class="editval"></span></td></tr>
                                               </table>
                                                <div class="slider"><input type="text" id="cpuedit" class="editright" /><br/></div>
                                                
                                               <table class="lowtable lowlow">
                                               <tr><td>
                                                <b>Memory</b>: </td><td> <span id="memorybd" class="editval"></span></td></tr>
                                               </table>
                                                 <div class="slider"><input type="text" id="memoryedit" class="editright" /><br/></div>

                                                 <table class="lowtable lowlow">
                                               <tr><td>
                                                <b>Storage</b>: </td><td><span id="storagebd" class="editval"></span></td></tr>
                                               </table>
                                                 <div class="slider"><input type="text" id="storageedit" class="editright" /><br/></div>
                                                <table class="lowtable lowlow">
                                               <tr><td>
                                                <b>Networking</b>: </td><td><span id="networkbd" class="editval"> </span></td></tr>
                                               </table>
                                                 <div class="slider"><input type="text" id="networkedit" class="editright" /><br/></div>
                                                <table class="lowtable lowlow">
                                               <tr><td>
                                                <b>Virtualization</b>: </td><td><span id="virtualbd" class="editval"></span></td></tr>
                                               </table>
                                                 <div class="slider"><input type="text" id="virtualedit" class="editright" /><br/></div>

                                               
                                            </div>
                                        </div>
                                        <div class="tab-pane fade" id="demo-tabs-box-2">
                                            <h4 class="text-thin">Alerts Information</h4>
                                            <p>
                                                <div class="alertsinfo">
                                                    <span class="noalerts">Have not got alerts<br/></span>
                                                    <table class="table table-bordered table-stripped table-hover table-alerts">
                                                        <tr class="header"><td class="text-center">% of budget</td><td class="text-center">Actions</td></tr>
                                                    </table>

                                                    <a id="addalert">Add Alert</a>
                                                    <div id="addalertslide">
                                                       <div class="left">Notify me when costs exceed <input type="text" id="percentbudg"> % of budgeted costs </div> <a class="btn btn-block btn-primary alertpricedit">Create Alert</a>
                                                    </div>

                                                </div>
                                            </p>
                                        </div>

                                           <div class="tab-pane fade" id="demo-tabs-box-3">
                                            <h4 class="text-thin">Dates Period</h4>
                                            <p>
                                                <div class="alertsinfo">
                                                    <span class="nodatalerts">No periods were set for this budget. This budget will be valid for an indefinite period of time<br/></span>
                                                  
                                                    <p id="timebudget">
                                                        <table class="lowtable">
                                                        <tr><td> <b>Start Date</b>: </td><td><span id="startdatebd"></span></td></tr>
                                                        <tr><td> <b>End Date</b>:  </td><td><span id="enddatebd"></span></td></tr>
                                                        </table>

                                                    </p>

                                                    <a id="datesedit">Edit Dates</a> <a id="removedates">Remove Dates</a>
                                                    <div id="editdatesdiv">
                                                <br/>
                                                <label>Start date: </label> 
                                                <div id="demo-dp-component">
                                                    <div class="input-group date">
                                                        <input type="text" id="budgeteditdatestart" class="form-control">
                                                        <span class="input-group-addon"><i class="fa fa-calendar fa-lg"></i></span>
                                                    </div>
                                                    
                                                </div>

                                                 <label>End date: </label> 
                                                <div id="demo-dp-component">
                                                    <div class="input-group date">
                                                        <input type="text" id="budgeteditdateend" class="form-control">
                                                        <span class="input-group-addon"><i class="fa fa-calendar fa-lg"></i></span>
                                                    </div>
                                                    
                                                </div>
                                              
                                                <a class="btn btn-primary" id="saveperiod">Save Period</a>
     
                                                    </div>
                                                </div>
                                            </p>
                                        </div>
                                    </div>
                                </div>
                            </div>
            
            </div>
        </div>
  

</div>
</div>



<div class="modal-dialog" id="popupremconfirm">
<div class="panel">
                    
                                <!-- Classic Form Wizard -->
                                <!--===================================================-->
                                <div id="demo-cls-wz">
                    
                                    <!--Nav-->
                                    <ul class="wz-nav-off wz-icon-inline wz-classic">
                                        <li class="col-xs-3 bg-info active">
                                            <a aria-expanded="true" data-toggle="tab" href="#demo-cls-tab1">
                                                <span class="icon-wrap icon-wrap-xs bg-trans-dark"><i class="fa fa-close"></i></span> Remove
                                            </a>
                                        </li>
                                        <div class="volumepopupclass"><a id="rempopupclose"><i class="fa fa-icon fa-close"></i></a></div>
                                        
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
                                                        <h2>Confirm Budget Alert Removal</h2>
                                                        
                                                        <strong>Id:</strong> <span class="remidplace"></span> <br/>
                                                        <strong>Name:</strong> <span class="remidname"></span>
                                                        <div class="rembnts">
                                                            <a class="btn btn-primary" id="remform">Yes</a>
                                                            <a class="btn btn-primary" id="closeremform">No</a>
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