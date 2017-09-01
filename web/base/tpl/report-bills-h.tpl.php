<script>
var nocontent = true;
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
<div class="printlogo">
    <img id="logo_cl_img" alt="htvcenter Enterprise Cloud" src="/htvcenter/base/img/logo.png">
</div>
    <h2 class="redh2">Report Bills</h2>
    <label class="shortlabel">User:</label> <select id="uzerrep" class="shortselect">
                                                            {hidenuser}
                                                            
                                                        </select>
                                                       

    
                                                        <label class="shortlabel">Month:</label> <select id="monzrep" class="shortselect">
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
                                                        <label class="shortlabel">Year:</label>  <select id="yearzrep" class="shortselect">{reportyear}</select>

    <div class="reportbtnbill">
        <a class="btn btn-primary billcsvdownload"><i class="fa fa-download"></i> &nbsp; Download CSV</a>
        <a class="btn btn-primary printbill"><i class="fa fa-print"></i> &nbsp; Print</a>
    </div>

    <a class="gobackprint">Go back</a>

    <table class="table table-bordered table-hover table-stripped whiteback">
        <tr class="header"><td>Summary</td><td width="200px">Amount</td></tr>
        <tr><td>Cloud Services Charges</td><td class="total">0</td></tr>
        <tr><td><b>Total</b></td><td><b class="total">0</b></td></tr>
    </table>

    <h2 class="redh2">Details</h2>
    <table class="table table-bordered table-hover table-stripped whiteback">
        <tr class="header"><td>Details</td><td width="200px">Total</td></tr>
        <tr class="slideractive"><td><b>Cloud Services Charges <i class="fa fa-arrow-down slidedownfa"></i></b></td><td><b class="total">0</b></td></td></tr>

        
                                                                <tr class="hideslider">        
                                                                    <td class="value">Storage</td>
                                                                    <td class="amount storage">0</td>
                                                                </tr>
                                                                <tr class="hideslider">
                                                                    <td class="value">CPU</td>
                                                                    <td class="amount cpu">0</td>
                                                                </tr>
                                                                <tr class="hideslider">
                                                                    <td class="value">Memory</td>
                                                                    <td class="amount memory">0</td>
                                                                </tr>
                                                                <tr class="hideslider">
                                                                    <td class="value">Networking</td>
                                                                    <td class="amount networking">0</td>
                                                                </tr>
                                                                <tr class="hideslider">
                                                                    <td class="value">Virtualisation</td>
                                                                    <td class="amount virtualization">0</td>
                                                                </tr>
        

        <tr><td><b>Total</b></td><td><b class="total">0</b></td></tr>
    </table>
    <a class="gobackprint">Go back</a>
</div>
</div>