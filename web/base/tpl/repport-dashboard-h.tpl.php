   <style>
        #demo-set-btn {
            display: none;
        }
   </style>

    <script>
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
function givedashboard(month, year, user) {

   
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

    renderdash(user);

 }


 function renderdash(user) {

    $('#donutrender').html('');
    $('#barcharts').html('');
    
    $('#cloud-content').css('min-height', '400px');
    //var legendo = ''; 

    
    
    //var legendo = [{'label':'testone', 'value':60}, {'label':'testsecond', 'value':40}];

    console.log(yearcurrent);
    console.log(monthcurrentnameajax);
    var url = '/cloud-fortis/user/index.php?report=yes';
    var dataval = 'year='+yearcurrent+'&month='+monthcurrentnameajax+'&priceonly=0&detailcategory=1&userdash='+user;
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
    var dataval = 'year='+yearcurrent+'&month='+monthcurrentnameajax+'&priceonly=true&userdash='+user;
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


var realResize = false;
setTimeout(function() { realResize = true; }, 500);

$(window).on('resize', function () {
    if(realResize){
		$("#container #aside-container").css('display', 'none');
	}
});

</script>                         



<div class="col-xs-12 col-sm-12 col-md-12 col-lg-12 windows_plane">
<div id="home_container">
<div class="printlogo">
    <img id="logo_cl_img" alt="htvcenter Enterprise Cloud" src="/htvcenter/base/img/logo.png">
</div>
  <div class="row paddingrow">
<h2 class="redh2">Report Dashboard</h2>
<label class="shortlabel">User:</label> <select id="reportuserdash" class="shortselect">
                                                            
                                                            {hidenuser}
                                                        </select>
    
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

                                                                <table class="table table-bordered table-hover table-stripped fordonuto whiteback">
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