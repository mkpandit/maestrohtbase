var seriesColors = [
    '#dfdfdf',
    '#41bee9',
    chartColors.red,
    chartColors.yellow,
    chartColors.green,
    chartColors.teal,
    chartColors.orange,
    chartColors.moss,
    chartColors.blue,
    '#afd2f0',
    "#ffd055",
    chartColors.purple
];

function current_year_monthly_spent_by_resource(bindto, data) {

    // console.log(data);
    /* data = [
        ['x', '2017-01-01', '2017-02-01', '2017-03-01', '2017-04-01', '2017-05-01', '2017-06-01', '2017-07-01', '2017-08-01'],
        ['cpu',             300, 200, 250, 240, 260, 250, 200, 240],
        ['storage',         200, 130, 190, 240, 140, 220, 130, 230],
        ['memory',          300, 200, 210, 320, 250, 220, 200, 320],
        ['virtualization',  200, 130, 150, 240, 130, 210, 130, 250],
        ['networking',      130, 120, 150, 160, 170, 150, 120, 160],
    ]; */

    var chart2 = c3.generate({
        bindto: bindto,
        data: {
            x: 'x',
            columns: data,
            type: 'bar',
            colors: {
                cpu:            seriesColors[0],
                storage:        seriesColors[1],
                memory:         seriesColors[2],
                virtualization: seriesColors[3],
                networking:     seriesColors[4]
            },
            groups: [
                ['cpu','storage','memory','virtualization','networking']
            ]
        },
        axis: {
            x:  {
                type: 'timeseries',
                tick: {
                    format: '%Y-%b'
                }
            },
            y:  {
                label: {
                    text: 'total cost ($)'
                }
            }
        },
        grid: {
            y:  {
                show: true
            }
        }
    });
}

function get_monthly_data(year_str, month_str) {
    var url = '/cloud-fortis/user/index.php?report=yes';
    var dataval = 'year='+year_str+'&month='+month_str+'&forbill=1';
    var category = '';
    
    var rtrn = $.ajax({
            url : url,
            type: "POST",
            data: dataval,
            cache: false,
            async: true,
            dataType: "html",
        });

    return rtrn;
}

function parseDate(d, format) {

    if (format == 'Y') {
        return d.getFullYear();
    } else if (format == 'm') {
        return d.toLocaleString("en-us", {month: "numeric"});
    }  else if (format == 'mon') {
        return d.toLocaleString("en-us", {month: "short"});
    } else if (format == 'Y-M-D') {
        return d.getFullYear() + '-' + d.toLocaleString("en-us", {month: "2-digit"}) + '-' + d.toLocaleString("en-us", {day: "2-digit"});
    } else {
        return '';
    }
}

function to_num(currency) { // convert $currency to number
    return Number(currency.replace(/[^0-9\.-]+/g,""));
}