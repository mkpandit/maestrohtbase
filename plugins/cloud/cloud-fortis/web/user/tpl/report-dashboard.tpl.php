    <style>
        #project_tab_ui { display: none; }  /* hack for tabmenu issue */
    </style>
<script src="/cloud-fortis/js/c3/d3.v3.min.js" type="text/javascript"></script>
<script src="/cloud-fortis/js/c3/c3.min.js" type="text/javascript"></script>
<script src="/cloud-fortis/js/chartjs/Chart.bundle.min.js" type="text/javascript"></script>
<script src="/cloud-fortis/js/chartjs/utils.js" type="text/javascript"></script>
<script src="/cloud-fortis/js/fetch-report.js" type="text/javascript"></script>
<script>

/*
    var dashflag = true;
    var diagramshow = true;
    var d = new Date();
    var month = d.getMonth(); 
    var numyear = d.getYear();
    var year = d.getFullYear();
    var monthcurrentname = '';
    var monthlastname = '';
    var yearcurrent = '';
    var yearold = '';

givedashboard(month, year);

function givedashboard(month, year) {

   
    month = parseInt(month)

    switch(month) {
        case 0:
             monthcurrentname = 'January';
             monthlastname = 'December';
             monthcurrentnameajax = 'Jan';
             monthlastnameajax = 'Dec';
             yearcurrent = year;
             yearold = parseInt(year) - 1;
             
        break;
        
        case 1:
            console.log('here');
            monthcurrentname = 'February';
            monthlastname = 'January';
            monthcurrentnameajax = 'Feb';
            monthlastnameajax = 'Jan';
            yearcurrent = year;
            yearold = year;
        break;
        case 2:
            monthcurrentname = 'March';
            monthlastname = 'February';
            monthcurrentnameajax = 'Mar';
            monthlastnameajax = 'Feb';
            yearcurrent = year;
            yearold = year;
        break;
        case 3:
            monthcurrentname = 'April';
            monthlastname = 'March';
             monthcurrentnameajax = 'Apr';
            monthlastnameajax = 'Mar';
            yearcurrent = year;
            yearold = year;
        break;
        case 4:
            monthcurrentname = 'May';
            monthlastname = 'April';
            monthcurrentnameajax = 'May';
            monthlastnameajax = 'Apr';
            yearcurrent = year;
            yearold = year;
        break;
        case 5:
            monthcurrentname = 'June';
            monthlastname = 'May';
            monthcurrentnameajax = 'Jun';
            monthlastnameajax = 'May';
            yearcurrent = year;
            yearold = year;
        break;
        case 6:
            monthcurrentname = 'July';
            monthlastname = 'June';
            monthcurrentnameajax = 'Jul';
            monthlastnameajax = 'Jun';
            yearcurrent = year;
            yearold = year;
        break;
        case 7:
            monthcurrentname = 'August';
            monthlastname = 'July';
            monthcurrentnameajax = 'Aug';
            monthlastnameajax = 'Jul';
            yearcurrent = year;
            yearold = year;
        break;
        case 8:
            monthcurrentname = 'September';
            monthlastname = 'August';
            monthcurrentnameajax = 'Sep';
            monthlastnameajax = 'Aug';
            yearcurrent = year;
            yearold = year;
        break;
        case 9:
            monthcurrentname = 'October';
            monthlastname = 'September';
            monthcurrentnameajax = 'Oct';
            monthlastnameajax = 'Sep';
            yearcurrent = year;
            yearold = year;
        break;
        case 10:
            monthcurrentname = 'November';
            monthlastname = 'October';
            monthcurrentnameajax = 'Nov';
            monthlastnameajax = 'Oct';
            yearcurrent = year;
            yearold = year;
        break;
        case 11:
            monthcurrentname = 'December';
            monthlastname = 'November';
            monthcurrentnameajax = 'Dec';
            monthlastnameajax = 'Nov';
            yearcurrent = year;
            yearold = year;
        break;
    }

    renderdash();

}

function renderdash() {

    $('#donutrender').html('');
    $('#barcharts').html('');
    
    // $('#cloud-content').css('min-height', '400px');
    //var legendo = ''; 

    
    
    //var legendo = [{'label':'testone', 'value':60}, {'label':'testsecond', 'value':40}];

    // console.log(yearcurrent);
    // console.log(monthcurrentnameajax);
    var url = '/cloud-fortis/user/index.php?report=yes';
    var dataval = 'year='+yearcurrent+'&month='+monthcurrentnameajax+'&priceonly=0&detailcategory=1';
    var category = '';
    
        $.ajax({
                url : url,
                type: "POST",
                data: dataval,
                cache: false,
                async: false,
                dataType: "html",
                success : function (data) {
                    
                    if (data != 'none') {
                        category = data;
                    } 
                    
                }
            });  


        category = JSON.parse(category);

       
    var legendonut = [];
    legendonut.push({'label':'Networking', 'value':category.network});
    legendonut.push({'label':'Virtualisation', 'value':category.virtualisation});
    legendonut.push({'label':'Memory', 'value':category.memory});
    legendonut.push({'label':'CPU', 'value':category.cpu});
    legendonut.push({'label':'Storage', 'value':category.storage});
     
    
    var priceold = '';
    var pricethis = '';

    var url = '/cloud-fortis/user/index.php?report=yes';
    var dataval = 'year='+yearcurrent+'&month='+monthcurrentnameajax+'&priceonly=true';
        $.ajax({
                url : url,
                type: "POST",
                data: dataval,
                cache: false,
                async: false,
                dataType: "html",
                success : function (data) {
                    
                    if (data != 'none') {
                        pricethis = parseFloat(data);
                    } 
                    
                }
            });  

        var dataval = 'year='+yearold+'&month='+monthlastnameajax+'&priceonly=true';
        $.ajax({
                url : url,
                type: "POST",
                data: dataval,
                cache: false,
                async: false,
                dataType: "html",
                success : function (data) {
                    if (data != 'none') {
                        priceold = parseFloat(data);
                    } 
                }
        });

    var prognoseprice = pricethis;

    if ( pricethis < priceold ) {
        prognoseprice = (pricethis + priceold) / 2;  
    }

    if (category.network != 0 || category.virtualisation !=0 || category.memory !=0 || category.storage != 0 || category.cpu != 0) {
        Morris.Donut({
                        element: 'donutrender',
                        data: legendonut,
                        colors: [
                            '#a6c600',
                            '#177bbb',
                            '#afd2f0',
                            "#1fa67a", "#ffd055", "#39aacb", "#cc6165", "#c2d5a0", "#579575", "#839557", "#958c12", "#953579", "#4b5de4", "#d8b83f", "#ff5800", "#0085cc"
                        ],
                        resize:true
                    });
    } else {
        $('#donutrender').html('<p class="nodatadonut">No information available for these dates</p>');
    }


    console.log(priceold);
    console.log(pricethis);
    if (priceold !=0 || pricethis != 0) {
        Morris.Bar({
          barSizeRatio:0.3,
          element: 'barcharts',
          data: [
            { y: monthlastname, a: priceold },
            { y: monthcurrentname, a: pricethis },
            { y: 'Forecast', a: prognoseprice },
          ],
          barColors: ['#afd2f0', '#177bbb', '#a6c600'],
          xkey: 'y',
          ykeys: ['a'],
          labels: ['Price in $']
        });

    } else {

        $('#barcharts').html('<p class="nodatabars">No information available for these dates</p>');
    }

    $('td.storage').text(category.storage);
    $('td.cpu').text(category.cpu);
    $('td.memory').text(category.memory);
    $('td.networking').text(category.network);
    $('td.virtualisationb').text(category.virtualisation);
}

*/



function current_year_monthly_spent(bindto, data) {

    /* data = [
        ['x', '2017-01-01', '2017-02-01', '2017-03-01', '2017-04-01', '2017-05-01', '2017-06-01', '2017-07-01', '2017-08-01'],
        ['total',             2300, 2100, 2250, 2140, 2260, 2150, 2000, 2400],
    ]; */

    var chart = c3.generate({
        bindto: bindto,
        data: {
            x: 'x',
            columns: data,
            type: 'bar',
            color: function (color, d) {
                return seriesColors[d.index];
            },
        },
        axis: {
            x:  {
                type: 'timeseries',
                tick: {
                    format: '%m/%Y'
                }
            },
            y:  {
                label: {
                    text: 'total cost ($)'
                }
            }
        },
        grid: {
            y: {
                show: true
            }
        },
        legend: {
            show: false
        }  
    });
}

function current_year_three_months_spent(bindto, data) {

    var x_column = ['x', '2017-07-01', '2017-08-01', '2017-09-01'];
    var y_column = ['total', 750, 1200, 1080];
    // data = [x_column, y_column];

    var chart3 = c3.generate({
        bindto: bindto,
        data: {
            x: 'x',
            columns: data,
            type: 'bar',
            color: function (color, d) {
                return seriesColors[d.index];
            }
        },
        axis: {
            x: {
                type: 'timeseries',
                tick: {
                    format: '%Y-%b'
                }
            },
            y: {
                label: 'total cost ($)'
            }
        },
        bar: {
            width: {
                ratio: 0.5 // this makes bar width 50% of length between ticks
            }
      
        },
        grid: {
            y:  {
                show: true
            }
        },
        tooltip: {
            show: true,
            format: {
                value: function (value, ratio, id) {
                    var formatDecimalComma = d3.format(",.2f")
                    return "$" + formatDecimalComma(value); 
                }
            }
        },
        legend: {
            show: false
        } 
    });
}

function current_month_spent_by_resource(bindto, data) {

    var numbers = [240,230,320,250,160];
    var labels = ["cpu","storage","memory","virtualization","networking"];
    //data = [labels,numbers];

    var color = Chart.helpers.color;
    var config = {
        data: {
            datasets: [{
                data: data[1],
                backgroundColor: [
                    color(seriesColors[0]).rgbString(),
                    color(seriesColors[1]).rgbString(),
                    color(seriesColors[2]).rgbString(),
                    color(seriesColors[3]).rgbString(),
                    color(seriesColors[4]).rgbString()
                ],
                label: 'dollars ($)' // for legend
            }],
            labels: data[0]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            legend: {
                display: true,
                position: 'bottom'
            },
            title: {
                display: false,
                // text: 'Mon'
            },
            scale: {
                display: true,
                reverse: false,
                ticks: {
                    callback: function(value, index, values) {
                        return '$ '+value;
                    }
                }
            },
            animation: {
                animateRotate: true,
                animateScale: true
            },
            layout: {
                padding: {
                    left: 5,
                    right: 5,
                    top: 5,
                    bottom: 5
                }
                
            },
            tooltips: {
                callbacks: {
                    label: function(tooltipItems, data) {
                        return data.labels[tooltipItems.index] +': $' + tooltipItems.yLabel;
                    }
                }
            }
        }
    };
    Chart.defaults.global.legend.labels.boxWidth = 12;
    var ctx = document.getElementById(bindto);
    window.myPolarArea = Chart.PolarArea(ctx, config);
    /* document.getElementById('js-legend').innerHTML = myPolarArea.generateLegend(); */
}

function lifetime_spent(bindto, objects) {
    var value = ['lifetime spending', 0];
    var max_val = value[1] * 2; /* show 50% gauge */

    var chart4 = c3.generate({
        bindto: bindto,
        data: {
            columns: [ value ],
            labels: true,
            type: 'gauge',
            onclick: function (d, i) { /* console.log("onclick", d, i); */ },
            onmouseover: function (d, i) { /* console.log("onmouseover", d, i); */},
            onmouseout: function (d, i) { /* console.log("onmouseout", d, i); */}
        },
        gauge: {
            max: max_val,
            label: {
                format: function(value, ratio) {
                    return '$ '+value;
                },
                show: true
            }
        },
        color: {
            pattern: [seriesColors[1]], // the three color levels for the percentage values.
        },
        legend: {
            show: true,
            position: 'bottom',
            format: function(value, ratio) {
                return '$ '+value;
            },
        },    
        transition: {
            duration: 1500
        },
        tooltip: {
            show: true,
            format: {
                value: function (value, ratio, id, index) { return value; }
            }
        }
    });
}

$(document).ready(function () {

    var this_month = new Date();
    var last_month = new Date();
    var next_month = new Date();
    this_month.setDate(1);
    last_month.setDate(1);
    last_month.setMonth(this_month.getMonth()-1);
    next_month.setDate(1);
    next_month.setMonth(this_month.getMonth()+1);

    var column_x_yearly  = ['x'];
    var column_x_3months = ['x'];
    var total_monthly   = ['total'];
    var cpu_monthly     = ['cpu'];
    var storage_monthly = ['storage'];
    var memory_monthly  = ['memory'];
    var virtual_monthly = ['virtualization'];
    var network_monthly = ['networking'];
    
    var deferred = [];

    var current_month = new Date();
    for (var i = 0; i <= this_month.getMonth(); i++) {
        current_month.setMonth(i);
        current_month.setDate(1);
        column_x_yearly.push(parseDate(current_month,'Y-M-D'));
        deferred.push(get_monthly_data(parseDate(current_month,'Y'), parseDate(current_month,'m')));
    }
    column_x_3months.push(parseDate(last_month,'Y-M-D'), parseDate(this_month,'Y-M-D'), parseDate(next_month,'Y-M-D'));

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

        current_year_monthly_spent("#current-year-monthly-spent", [column_x_yearly, total_monthly]);
        
        current_year_monthly_spent_by_resource("#current-year-monthly-spent-by-resource", [column_x_yearly, cpu_monthly, storage_monthly, memory_monthly, virtual_monthly, network_monthly]);
        
        // future data supposed to come from same source?
        current_year_three_months_spent("#current-three-months-spent", [column_x_3months, ['total', total_monthly.slice(-3)[0],total_monthly.slice(-2)[0],total_monthly.slice(-1)[0]]]);
        
        current_month_spent_by_resource("chartdiv-this-month-chart", [[cpu_monthly[0], storage_monthly[0], memory_monthly[0],virtual_monthly[0], network_monthly[0]], [cpu_monthly.slice(-1)[0], storage_monthly.slice(-1)[0], memory_monthly.slice(-1)[0], virtual_monthly.slice(-1)[0], network_monthly.slice(-1)[0]]]);
        lifetime_spent("#lifetime-spent-gauge", objects); 
    });
});
</script>



<!--
                                               </div></div>



<div class="windows_plane">
<div id="home_container">
<div class="printlogo">
    <img id="logo_cl_img" alt="htvcenter Enterprise Cloud" src="/cloud-fortis/img/fortis-logo.png">
</div>
  <div class="row paddingrow">
<h2 class="redh2">Report Dashboard</h2>
    {hidenuser}
                                                        <label class="shortlabel">Month:</label> <select id="reportmonthdash" class="shortselect">
                                                            <option value="0">January</option>
                                                            <option value="1">February</option>
                                                            <option value="2">March</option>
                                                            <option value="3">April</option>
                                                            <option value="4">May</option>
                                                            <option value="5">June</option>
                                                            <option value="6">July</option>
                                                            <option value="7">August</option>
                                                            <option value="8">September</option>
                                                            <option value="9">October</option>
                                                            <option value="10">November</option>
                                                            <option value="11">December</option>
                                                            
                                                        </select>
                                                        <label class="shortlabel">Year:</label>  <select id="reportyeardash" class="shortselect">{reportyear}</select>

    <div class="reportbtnbill">
        <a class="btn btn-primary printdash"><i class="fa fa-print"></i> &nbsp; Print</a>
    </div>

    <a class="gobackprint">Go back</a>
</div>

                                                    <div id="diagramsreport">
                                                        <div class="row">
                                                            <div class="col-xs-6 col-sm-6 col-md-6 col-lg-6 barchartsside">
                                                                <div class="header">
                                                                    Spend summary
                                                                </div>

                                                                <div id="barcharts">
                                                                    <p class="nodatabars">No information available for these dates</p>
                                                                </div>
                                                            </div>

                                                            <div class="col-xs-6 col-sm-6 col-md-6 col-lg-6 donutside">
                                                                <div class="header">
                                                                    Month-to-date Spend by Service
                                                                </div>
                                                                <div id="donutrender">
                                                                    <p class="nodadonut">No information available for these dates</p>
                                                                </div>

                                                                <table class="table table-bordered table-hover table-stripped fordonuto ">
                                                                <tr class="header">
                                                                    <td>Month-to-date Top Services by spend</td><td>Amount</td>
                                                                </tr>
                                                                
                                                                <tr>
                                                                    <td class="value">Storage</td>
                                                                    <td class="amount storage text-center">0</td>
                                                                </tr>
                                                                <tr>
                                                                    <td class="value">CPU</td>
                                                                    <td class="amount cpu text-center">0</td>
                                                                </tr>
                                                                <tr>
                                                                    <td class="value">Memory</td>
                                                                    <td class="amount memory text-center">0</td>
                                                                </tr>
                                                                <tr>
                                                                    <td class="value">Networking</td>
                                                                    <td class="amount networking text-center">0</td>
                                                                </tr>
                                                                <tr>
                                                                    <td class="value">Virtualisation</td>
                                                                    <td class="amount virtualisationb text-center">0</td>
                                                                </tr>
                                                                </table>
                                                            </div>
                                                        </div>
                                                    </div>
                                                    
                                                    </div>
                                                    </div>
-->
<div class="cat__content">
    <cat-page>
    <div class="row">
        <div class="col-sm-12">
            <section class="card">  
                <div class="card-header">
                    <span class="cat__core__title">
                        <strong>Score Report Dashboard</strong>
                    </span>
                </div>
                <div class="card-block">
                    <div class="row">
                        <div class="col-sm-6 dashboard">
                            <section class="card">  
                                <div class="card-header">
                                    <span class="cat__core__title">
                                        <strong>{currentyear} Total Spent</strong>
                                    </span>
                                </div>
                                <div class="card-block">
                                    <div class="panel-heading">
                                        <div class="panel-control">
                                        </div>
                                        <h3 class="panel-title">&nbsp;</h3>
                                    </div>
                                    <div>
                                        <div id="current-year-monthly-spent"  style="height: 16rem;"></div>
                                    </div>
                                </div>
                            </section>
                        </div>
                        <div class="col-sm-6 dashboard">
                            <section class="card">  
                                <div class="card-header">
                                    <span class="cat__core__title">
                                        <strong>Monthly Projection</strong>
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
                    <div class="row">
                        <div class="col-sm-6 dashboard">
                            <section class="card">  
                                <div class="card-header">
                                    <span class="cat__core__title">
                                        <strong>{currentyear} Total Spent By Resource</strong>
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
                        <div class="col-sm-3 dashboard">
                            <section class="card">  
                                <div class="card-header">
                                    <span class="cat__core__title">
                                        <strong>Current Spending</strong>
                                    </span>
                                </div>
                                <div class="card-block">
                                    <div class="panel-heading">
                                        <div class="panel-control">
                                        </div>
                                        <h3 class="panel-title">&nbsp;</h3>
                                    </div>
                                    <div style="height: 16rem;">
                                        <canvas id="chartdiv-this-month-chart"></canvas>
                                    </div>
                                     <!-- <div id="js-legend" class="chart-legend"></div> -->
                                </div>
                            </section>
                        </div>
                        <div class="col-sm-3 dashboard">
                            <section class="card">  
                                <div class="card-header">
                                    <span class="cat__core__title">
                                        <strong>Lifetime Spending</strong>
                                    </span>
                                </div>
                                <div class="card-block">
                                    <div class="panel-heading">
                                        <div class="panel-control">
                                        </div>
                                        <h3 class="panel-title">&nbsp;</h3>
                                    </div>
                                    <div>
                                        <div id="lifetime-spent-gauge" style="height: 16rem;"></div>
                                    </div>
                                </div>
                            </section>
                        </div>
                    </div>
                </div>
            </section>
        </div>
    </div>
    </cat-page>
</div>

