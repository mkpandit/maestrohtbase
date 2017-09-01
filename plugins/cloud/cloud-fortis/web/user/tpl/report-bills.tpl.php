<style>
    @media print {
        .card-header { font-size: 18px; }
    }

    #project_tab_ui { display: none; }  /* hack for tabmenu issue */

    hr { 
        display: block;
        margin-top: 0.5em;
        margin-bottom: 0.5em;
        margin-left: auto;
        margin-right: auto;
        border-style: solid;
        border-width: 2px;
        width:24px;
    }

    hr.cpu {
        border-color: #dfdfdf;
    }

    hr.storage {
        border-color: #41bee9 ;
    }

    hr.memory {
        border-color: rgb(255, 99, 132);
    }

    hr.virtualization {
        border-color: rgb(255, 205, 86);
    }

    hr.networking {
        border-color: rgb(75, 192, 192);
    }

    hr.total {
        border-color: rgb(153, 102, 255);
    }

    #current-month-spent-by-resource tr td div {
        text-align: center;
    }
</style>
<script src="/cloud-fortis/js/c3/d3.v3.min.js" type="text/javascript"></script>
<script src="/cloud-fortis/js/c3/c3.min.js" type="text/javascript"></script>
<script src="/cloud-fortis/js/chartjs/Chart.bundle.min.js" type="text/javascript"></script>
<script src="/cloud-fortis/js/chartjs/utils.js" type="text/javascript"></script>
<script src="/cloud-fortis/js/fetch-report.js" type="text/javascript"></script>
<script>
// var nocontent = true;
// var repflag = true;
var chart = null;

function get_last_12_months_report(month, year) {

    var column_x_yearly  = ['x'];
    var total_monthly   = ['total'];
    var cpu_monthly     = ['cpu'];
    var storage_monthly = ['storage'];
    var memory_monthly  = ['memory'];
    var virtual_monthly = ['virtualization'];
    var network_monthly = ['networking'];
    var deferred = [];
    var curr_month = new Date();
    curr_month.setYear(year);
    curr_month.setMonth(month);
    curr_month.setDate(1);

    $("#current-monthly-title").text(parseDate(curr_month, "mon") + " " + parseDate(curr_month, "Y"));

    for (var i = 0; i < 12; i++) {
        if (i > 0) {
            curr_month.setMonth(curr_month.getMonth() - 1);
            curr_month.setDate(1);
        } else {

        }
        column_x_yearly.push(parseDate(curr_month,'Y-M-D'));
        deferred.push(get_monthly_data(parseDate(curr_month,'Y'), parseDate(curr_month,'m')));
    }

    $("#current-year-monthly-title").text($("#current-monthly-title").text() + " ~ " + parseDate(curr_month, "mon") + " " + parseDate(curr_month, "Y"));

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

        $('#cpu-usage, .amount-cpu').empty().append("<span>$"+cpu_monthly[1]+"</span>");
        $('#storage-usage, .amount-storage').empty().append("<span>$"+storage_monthly[1]+"</span>");
        $('#memory-usage, .amount-memory').empty().append("<span>$"+memory_monthly[1]+"</span>");
        $('#virtualization-usage, .amount-virtualization').empty().append("<span>$"+virtual_monthly[1]+"</span>");
        $('#networking-usage, .amount-networking').empty().append("<span>$"+network_monthly[1]+"</span>");
        $('#total-usage, .amount-total').empty().append("<span>$"+total_monthly[1]+"</span>");

        if (chart) {
            chart.load({
                columns: [column_x_yearly, cpu_monthly, storage_monthly, memory_monthly, virtual_monthly, network_monthly] 
            });
        } else {
            chart = current_year_monthly_spent_by_resource("#current-year-monthly-spent-by-resource", [column_x_yearly, cpu_monthly, storage_monthly, memory_monthly, virtual_monthly, network_monthly]);
        }
    });
}

$(document).ready(function() {

    var today = new Date();
    $("#reportmonth").val(parseDate(today,"mon"));
    $("#reportyear").val(parseDate(today,"Y"));

    get_last_12_months_report(today.getMonth(),today.getFullYear());

    $("#reportyear, #reportmonth").change(function() {

        var year = $("#reportyear").val();
        var month = $("#reportmonth option:selected").data("val");
        get_last_12_months_report(month,year);
    });
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
    <div class="row" id="chart-row">
        <div class="col-sm-12">
            <section class="card">  
                <div class="card-header">
                    <span class="cat__core__title d-inline-block">
                        <strong>Service Usage Report</strong>
                    </span>
                    <div class="d-inline-block">
                        <label class="col-form-label col-sm-4">Month:</label> 
                        <select id="reportmonth" class="form-control col-sm-7 d-inline-block">
                            <option value="Jan" data-val="0">January</option>
                            <option value="Feb" data-val="1">February</option>
                            <option value="Mar" data-val="2">March</option>
                            <option value="Apr" data-val="3">April</option>
                            <option value="May" data-val="4">May</option>
                            <option value="Jun" data-val="5">June</option>
                            <option value="Jul" data-val="6">July</option>
                            <option value="Aug" data-val="7">August</option>
                            <option value="Sep" data-val="8">September</option>
                            <option value="Oct" data-val="9">October</option>
                            <option value="Nov" data-val="10">November</option>
                            <option value="Dec" data-val="11">December</option>
                        </select>
                    </div>
                    <div class="d-inline-block">
                        <label class="col-form-label col-sm-4">Year:</label> 
                        <select id="reportyear" class="form-control col-sm-7 d-inline-block">
                            <option value="2017">2017</option>
                            <option value="2016">2016</option>
                            <option value="2015">2015</option>
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
                                            <h3 class="panel-title" id="current-year-monthly-title">&nbsp;</h3>
                                        </div>
                                    </div>
                                    <div>
                                        <div id="current-year-monthly-spent-by-resource" style="height: 18rem;"></div>
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
                                             <h3 class="panel-title" id="current-monthly-title">&nbsp;</h3>
                                        </div>
                                    </div>
                                    <div>
                                        <div id="current-month-spent-by-resource" style="height: 18rem;">
                                            <table class="table table-bordered table-hover table-stripped">
                                                <tr>
                                                    <td width="50%">
                                                        <div><strong>CPU</strong></div>
                                                        <div id="cpu-usage"></div>
                                                        <hr class="cpu">
                                                    </td>
                                                    <td width="50%">
                                                        <div><strong>Storage</strong></div>
                                                        <div id="storage-usage"></div>
                                                        <hr class="storage">
                                                    </td>
                                                </tr>
                                                <tr>
                                                    <td width="50%">
                                                        <div><strong>Memory</strong></div>
                                                        <div id="memory-usage"></div>
                                                        <hr class="memory">
                                                    </td>
                                                    <td width="50%">
                                                        <div><strong>Virtualization</strong></div>
                                                        <div id="virtualization-usage"></div>
                                                        <hr class="virtualization">
                                                    </td>
                                                </tr>
                                                <tr>
                                                    <td width="50%">
                                                        <div><strong>Networking</strong></div>
                                                        <div id="networking-usage"></div>
                                                        <hr class="networking">
                                                    </td>
                                                    <td width="50%">
                                                        <div><strong>Total</strong></div>
                                                        <div id="total-usage"></div>
                                                        <hr class="total">
                                                    </td>
                                                </tr>
                                            </table>
                                        </div>
                                    </div>
                                </div>
                            </section>
                        </div>
                    </div>
                </div>
            </section>
        </div>
    </div>
    <a class="gobackprint">Go back</a>
    <div class="row">
        <div class="col-sm-12">
            <section class="card">  
                <div class="card-header">
                    <span class="cat__core__title">
                        <strong>Report Bills</strong>
                    </span>
                        <div class="reportbtnbill pull-right">
                            <a class="btn btn-primary btn-sm billcsvdownload"><i class="fa fa-download"></i> &nbsp; Download CSV</a>
                            <a class="btn btn-primary btn-sm printbill"><i class="fa fa-print"></i> &nbsp; Print</a>
                        </div>
                </div>
                <div class="card-block">
                    <!-- <div class="col-sm-12 dashboard"> -->
                        <div id="current-monthly-spent"  style="height: 25rem;">
                            <table class="table table-bordered table-hover table-stripped">
                                <tr class="header">
                                    <td>Details</td>
                                    <td width="200px">Total</td>
                                </tr>
                                <tr class="slideractive">
                                    <td><b>Cloud Services Charges <i class="fa fa-arrow-down slidedownfa"></i></b></td>
                                    <td><b class="amount-total">0</b>
                                    </td></td>
                                </tr>
                                <tr class="hideslider">
                                    <td class="value">CPU</td>
                                    <td class="amount-cpu">0</td>
                                </tr>
                                <tr class="hideslider">
                                    <td class="value">Storage</td>
                                    <td class="amount-storage">0</td>
                                </tr>
                                <tr class="hideslider">
                                    <td class="value">Memory</td>
                                    <td class="amount-memory">0</td>
                                </tr>
                                <tr class="hideslider">
                                    <td class="value">Virtualisation</td>
                                    <td class="amount-virtualization">0</td>
                                </tr>
                                 <tr class="hideslider">
                                    <td class="value">Networking</td>
                                    <td class="amount-networking">0</td>
                                </tr>
                                <tr>
                                    <td><b>Total</b></td>
                                    <td><b class="amount-total">0</b></td>
                                </tr>
                            </table>
                        </div>
                    <!-- </div> -->
                </div>
            </section>
        </div>
    </div>
    </cat-page>
</div>
