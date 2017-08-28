<style>
    #project_tab_ui { display: none; }  /* hack for tabmenu issue */
</style>
<script src="/cloud-fortis/js/c3/d3.v3.min.js" type="text/javascript"></script>
<script src="/cloud-fortis/js/c3/c3.min.js" type="text/javascript"></script>
<script src="/cloud-fortis/js/chartjs/Chart.bundle.min.js" type="text/javascript"></script>
<script src="/cloud-fortis/js/chartjs/utils.js" type="text/javascript"></script>
<script src="/cloud-fortis/js/fetch-report.js" type="text/javascript"></script>
<script>
var nocontent = true;
var repflag = true;

function get_last_12_months_report(month, year) {

    var curr_month = new Date();
    curr_month.setDate(1);
    curr_month.setMonth(month);
    curr_month.setYear(year);

    var column_x_yearly  = ['x'];
    var total_monthly   = ['total'];
    var cpu_monthly     = ['cpu'];
    var storage_monthly = ['storage'];
    var memory_monthly  = ['memory'];
    var virtual_monthly = ['virtualization'];
    var network_monthly = ['networking'];
    var deferred = [];
    var loop_month = curr_month;

    for (var i = 11; i >= 0; i--) {
        loop_month.setMonth(loop_month.getMonth() - 1);
        loop_month.setDate(1);
        column_x_yearly.push(parseDate(loop_month,'Y-M-D'));
        deferred.push(get_monthly_data(parseDate(loop_month,'Y'), parseDate(loop_month,'m')));
    }

    $.when.apply($, deferred).done(function () {
        var objects=arguments;

         for (var j = 0; j < objects.length; j++) {
            var json = JSON.parse(objects[j][0]);

            total_monthly.push(to_num(json.all));
            cpu_monthly.push(to_num(json.cpu));
            storage_monthly.push(to_num(json.storage));
            memory_monthly.push(to_num(json.memory));
            virtual_monthly.push(to_num(json.virtualization));
            network_monthly.push(to_num(json.networking));
        }

        current_year_monthly_spent_by_resource("#current-year-monthly-spent-by-resource", [column_x_yearly, cpu_monthly, storage_monthly, memory_monthly, virtual_monthly, network_monthly]);
    });
}

$(document).ready(function() {

    get_last_12_months_report(7,2017);

});


</script>
<!--


                                               </div></div>



<div class="col-xs-12 col-sm-9 col-md-10 col-lg-10 windows_plane">
<div id="home_container">
<div class="printlogo">
    <img id="logo_cl_img" alt="htvcenter Enterprise Cloud" src="/cloud-fortis/img/fortis-logo.png">
</div>
    <h2 class="redh2">Report Bills</h2>

    {hidenuser}
                                                        <label class="shortlabel">Month:</label> <select id="reportmonth" class="shortselect">
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
                                                        <label class="shortlabel">Year:</label>  <select id="reportyear" class="shortselect">{reportyear}</select>

    <div class="reportbtnbill">
        <a class="btn btn-primary billcsvdownload"><i class="fa fa-download"></i> &nbsp; Download CSV</a>
        <a class="btn btn-primary printbill"><i class="fa fa-print"></i> &nbsp; Print</a>
    </div>

    <a class="gobackprint">Go back</a>

    <table class="table table-bordered table-hover table-stripped">
        <tr class="header"><td>Summary</td><td width="200px">Amount</td></tr>
        <tr><td>Cloud Services Charges</td><td class="total">0</td></tr>
        <tr><td><b>Total</b></td><td><b class="total">0</b></td></tr>
    </table>

    <h2 class="redh2">Details</h2>
    <table class="table table-bordered table-hover table-stripped">
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
-->

<div class="cat__content">
    <cat-page>
    <div class="row">
        <div class="col-sm-12">
            <section class="card">  
                <div class="card-header">
                    <span class="cat__core__title d-inline-block">
                        <strong>Service Usage Report</strong>
                    </span>
                    <div class="d-inline-block">
                        <label class="col-form-label col-sm-4">Month:</label> 
                        <select id="reportmonth" class="form-control col-sm-7 d-inline-block">
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
                    </div>
                    <div class="d-inline-block">
                        <label class="col-form-label col-sm-4">Year:</label> 
                        <select id="reportyear" class="form-control col-sm-7 d-inline-block">
                            <option value="2017">2017</option>
                        </select>
                    </div>
                </div>
                <div class="card-block">
                    <div class="row">
                        <div class="col-sm-6 dashboard">
                            <section class="card">  
                                <div class="card-header">
                                    <span class="cat__core__title">
                                        <strong>Last 12 months Spent Chart</strong>
                                    </span>
                                </div>
                                <div class="card-block">
                                    <div class="panel-heading">
                                        <div class="panel-control">
                                        </div>
                                        <h3 class="panel-title">&nbsp;</h3>
                                    </div>
                                    <div>
                                        <div id="current-year-monthly-spent-by-resource" style="height: 16rem;"></div>
                                    </div>
                                </div>
                            </section>
                        </div>
                        <div class="col-sm-6 dashboard">
                            <section class="card">  
                                <div class="card-header">
                                    <span class="cat__core__title">
                                        <strong>Spent Chart By Caterogy</strong>
                                    </span>
                                </div>
                                <div class="card-block">
                                    <div class="panel-heading">
                                        <div class="panel-control">
                                        </div>
                                        <h3 class="panel-title">&nbsp;</h3>
                                    </div>
                                    <div>
                                        <div id="current-three-months-spent" style="height: 16rem;"></div>
                                    </div>
                                </div>
                            </section>
                        </div>
                    </div>
                </div>
            </section>
        </div>
    </div>

    <div class="row">
        <div class="col-sm-12">
            <section class="card">  
                <div class="card-header">
                    <span class="cat__core__title">
                        <strong>Report Bills</strong>
                    </span>
                        <div class="reportbtnbill pull-right">
                            <a class="btn btn-primary billcsvdownload"><i class="fa fa-download"></i> &nbsp; Download CSV</a>
                            <a class="btn btn-primary printbill"><i class="fa fa-print"></i> &nbsp; Print</a>
                        </div>
                </div>
                <div class="card-block">
                    <div class="col-sm-12 dashboard">
                        <section class="card">  
                            <div id="current-year-monthly-spent"  style="height: 24rem;">
                                <table class="table table-bordered table-hover table-stripped">
                                    <tr class="header"><td>Details</td><td width="200px">Total</td></tr>
                                    <tr class="slideractive">
                                        <td><b>Cloud Services Charges <i class="fa fa-arrow-down slidedownfa"></i></b></td>
                                        <td><b class="total">0</b>
                                        </td></td>
                                    </tr>
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
                                    <tr>
                                        <td><b>Total</b></td>
                                        <td><b class="total">0</b></td>
                                    </tr>
                                </table>
                            </div>
                        </section>
                    </div>
                </div>
            </section>
        </div>
    </div>
    </cat-page>
</div>
