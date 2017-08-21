    <script>
    var nocontent = true;
    var datepickeryep = true;
    </script>                         
                                               </div></div>



<div class="col-xs-12 col-sm-9 col-md-10 col-lg-10 windows_plane">
<div id="home_container">
<div class="row paddingrow">
<h2 class="redh2">Budget Planning</h2>
</div>
<div class="budgetform">

  <div class="row">
      <div class="col-xs-4 col-sm-4 col-md-4 col-lg-4 budgetnamemain">
        <label>Name: </label> 
              <div id="demo-dp-component">
                                                    <div class="input-group budgetname">
                                                        <input type="text" class="form-control" id="budgetname">
                                                    </div>
                                                    
                                                </div>
      </div>
      <div class="col-xs-4 col-sm-4 col-md-4 col-lg-4 datedatedate">
         <label>Start date: </label> 
                                                <div id="demo-dp-component">
                                                    <div class="input-group date">
                                                        <input type="text" class="form-control" id="budgetdatestart">
                                                        <span class="input-group-addon"><i class="fa fa-calendar fa-lg"></i></span>
                                                    </div>
                                                    
                                                </div>
      </div>
      <div class="col-xs-4 col-sm-4 col-md-4 col-lg-4 datedatedate">
         <label>End date: </label>
          <div id="demo-dp-component">
                                                    <div class="input-group date">
                                                        <input type="text" class="form-control" id="budgetdateend">
                                                        <span class="input-group-addon"><i class="fa fa-calendar fa-lg"></i></span>
                                                    </div>
                                                    
                                                </div>
      </div>
  </div>
  <br/>
  <div class="row">
                                <div class="col-sm-2 col-md-2 col-lg-2 col-xs-2 selectype">
                                    <div class="panel plan">
                                        <div class="panel-body">
                                            <span class="plan-title">CPU</span>
                                           
                                            <div class="plan-icon">
                                                <i class="fa fa-desktop"></i>
                                            </div>
                                            
                                            <p class="text-muted pad-btm">
                                                Monthly Price Limit in $: <br/><input type="text" name="input" id="budgetcpu"> <br/>
                                            </p>
                                           
                                            
                                        </div>
                                    </div>
                                </div>

                                <div class="col-sm-2 col-md-2 col-lg-2 col-xs-2 selectype">
                                    <div class="panel plan">
                                        <div class="panel-body">
                                            <span class="plan-title">Memory</span>
                                           
                                            <div class="plan-icon">
                                                <i class="fa fa-database"></i>
                                            </div>


                                            <p class="text-muted pad-btm">
                                                Monthly Price Limit in $: <br/><input type="text" name="input" id="budgetmemory"> <br/>
                                            </p>
                                          
                    
                                            
                                        </div>
                                    </div>
                                </div>

                                <div class="col-sm-2 col-md-2 col-lg-2 col-xs-2 selectype">
                                    <div class="panel plan">
                                        <div class="panel-body">
                                            <span class="plan-title">Storage</span>
                                           
                                            <div class="plan-icon">
                                                <i class="fa fa-hdd-o"></i>
                                            </div>


                                            <p class="text-muted pad-btm">
                                                Monthly Price Limit in $: <br/><input type="text" name="input" id="budgetstorage"> <br/>
                                            </p>
                                            
                    
                                            
                                        </div>
                                    </div>
                                </div>

                                <div class="col-sm-2 col-md-2 col-lg-2 col-xs-2 selectype">
                                    <div class="panel plan">
                                        <div class="panel-body">
                                            <span class="plan-title">Network</span>
                                           
                                            <div class="plan-icon">
                                                <i class="fa fa-globe"></i>
                                            </div>


                                            <p class="text-muted pad-btm">
                                                Monthly Price Limit in $: <br/><input type="text" name="input" id="budgetnetwork"> <br/>
                                            </p>
                                        
                    
                                            
                                        </div>
                                    </div>
                                </div>
                                <div class="col-sm-2 col-md-2 col-lg-2 col-xs-2 selectype">
                                    <div class="panel plan">
                                        <div class="panel-body">
                                            <span class="plan-title">VM</span>
                                           
                                            <div class="plan-icon">
                                                <i class="fa fa-cloud"></i>
                                            </div>
                                        

                                            <p class="text-muted pad-btm">
                                                Monthly Price Limit in $: <br/><input type="text" name="input" id="budgetvm"> <br/>
                                            </p>
                                            
                                            
                                        </div>
                                    </div>
                                </div>
  </div>
     
</div>


<div class="row paddingrow">
<h2 class="redh2">Alerts</h2>
</div>
<div class="left">Notify me when costs exceed <input type="text" id="percentbudg"> % of budgeted costs </div> <a class="btn btn-block btn-primary alertprice">Create Alert</a>
<br/><br/><br/>

<table class="table table-bordered table-stripped table-hover table-alerts">
    <tr class="header"><td>% of budget</td><td>Action</td></tr>
    
 
</table>

<a class="btn btn-block btn-primary submitallprice">Submit</a>
</div>
</div>