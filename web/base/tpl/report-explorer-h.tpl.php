<script>
var nocontent = true;
var explorer = true;
var realResize = false;
setTimeout(function() { realResize = true; }, 500);

$(window).on('resize', function () {
    if(realResize){
		$("#container #aside-container").css('display', 'none');
	}
});
</script>      

<style>
 #demo-set-btn {
 	display: none;
 }
</style>      
                                               



<div class="col-xs-12 col-sm-12 col-md-12 col-lg-12 windows_plane">
<div id="home_container">
<div class="row paddingrow">
    <h2 class="redh2">Cost Explorer</h2>

    <label class="shortlabel">User:</label> <select class="shortselect bs-select-hidden" id="uzerexpo">
                                                            {hidenuser}
                                                            
                                                        </select>
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
												<tr><td><b>CPU</b>: </td><td> <span id="cpuexp"></span></td></tr>
								    			<tr><td><b>Memory</b>: </td><td> <span id="memoryexp"></span></td></tr>
								    			<tr><td><b>Storage</b>: </td><td> <span id="storageexp"></span></td></tr>
								    			<tr><td><b>Status</b>: </td><td> <span id="statusexp"></span></td></tr>
								    			</table>
											</p>
										</div>
										<div class="tab-pane fade" id="demo-tabs-box-2">
											<h4 class="text-thin">Explorer</h4>
											<p>
											  <table class="lowtable">
												<tr><td><b>Creation Date</b>: </td><td> <span id="creationexp"></span></td></tr>
	    										<tr><td><b>Working Time</b>: </td><td> <span id="timeexp"></span></td></tr>
	    										<tr><td><b>Total Cost</b>: </td><td> <span id="totalexp"></span></td></tr>
	    									  </table>
											</p>
										</div>
									</div>
								</div>
							</div>
    		
    		</div>
    	</div>
    </div>                                          
</div>
