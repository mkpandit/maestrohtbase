function get_monthly_data(year_str, month_str) {
	var url = '/cloud-fortis/user/index.php?report=yes';
    var dataval = 'year='+year_str+'&month='+month_str+'&forbill=1';
    var category = '';
    
    var rtrn = $.ajax({
            url : url,
            type: "POST",
            data: dataval,
            cache: false,
            async: false,
            dataType: "html",
        });

    return rtrn;
}

function to_num(currency) { // convert $currency to number
    return Number(currency.replace(/[^0-9\.-]+/g,""));
}