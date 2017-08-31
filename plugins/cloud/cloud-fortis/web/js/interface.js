$(document).ready(function(){

if (typeof(dashflag) !='undefined' && dashflag == true) {
	changedashboard();
}

if (typeof(repflag) !='undefined' && repflag == true) {
	changereporte();
}



var remid = '';
var globalid = '';
var bufid = '';

$('body').on('click', '.budgremove', function(){
	var id = $(this).closest('.budgservn').attr('remid');
	var name = $(this).closest('.budgservn').attr('remname');
	remid = id;
	$('.remidplace').text(id);
	$('.remidname').text(name);
	$('#popupremconfirm').show();
});


$('body').on('click', '.rempercent', function(){
	var percval = $(this).closest('tr').find('.valperc').text();
	var row = $(this).closest('tr').remove();

	var url = '/cloud-fortis/user/index.php?budget=yes';
	var dataval = 'editbudgets=1&place=percremove&globalid='+globalid+'&editval='+percval;
	var bds = '';
			$.ajax({
				url : url,
				type: "POST",
				data: dataval,
				cache: false,
				async: false,
				dataType: "html",
				success : function (data) {
					$('.lead').hide();
					if (data != 'none') {
						$('.lead').hide();
						console.log(data);
						bds = JSON.parse(data);
					} else {
						alert('Have not got any data of this period');
					}
				}
			});
});

function refreshalerts(num) {
		var bds = '';
			$.ajax({
				url : url,
				type: "POST",
				data: dataval,
				cache: false,
				async: false,
				dataType: "html",
				success : function (data) {
					$('.lead').hide();
					if (data != 'none') {
						$('.lead').hide();
						bds = JSON.parse(data);
					} else {
						alert('Have not got any data of this period');
					}
				}
			});


	if (bds[num].havealerts == 1) {
		$('.newrow').remove();
		$('.noalerts').hide();
		$.each(bds[num]['alerts'], function(key, val){
			var row = '<tr class="newrow"><td class="text-center valperc">'+val+'</td><td class="text-center"><a class="rempercent"><i class="fa fa-close"></i> Remove</a></td></tr>';
			$('.table-alerts').append(row);
			$('.table-alerts').show();
		});
	} else {
		$('.table-alerts').hide();
		$('.noalerts').show();
	}

	
}

$('.alertpricedit').click(function(){
		var val = $('#percentbudg').val();
		val = parseInt(val);

		if ( (val > 100) || (isNaN(val) == true) ) {
			alert('Only integer number of percent value and not bigger, than 100, please');
		} else {
				
				var row = '<tr class="newrow"><td class="text-center valperc">'+val+'</td><td class="text-center"><a class="rempercent"><i class="fa fa-close"></i> Remove</a></td></tr>';
				$('.table-alerts').append(row);
				$('.table-alerts').show();
		}

	var url = '/cloud-fortis/user/index.php?budget=yes';
	var dataval = 'editbudgets=1&place=percadd&globalid='+globalid+'&editval='+val;
	var bds = '';
			$.ajax({
				url : url,
				type: "POST",
				data: dataval,
				cache: false,
				async: false,
				dataType: "html",
				success : function (data) {
					$('.lead').hide();
					if (data != 'none') {
						$('.lead').hide();
						console.log(data);
						bds = JSON.parse(data);
					} else {
						alert('Have not got any data of this period');
					}
				}
			});

	$('.noalerts').hide();
});

$('#addalert').click(function(){

  if ( $('#addalertslide').is(':visible') == true ) {
  		$('#addalertslide').slideUp();
  } else {
  		$('#addalertslide').slideDown();
  }
	
});

$('#rempopupclose').click(function(){
	$('#popupremconfirm').hide();
});

$('#closeremform').click(function(){
	$('#popupremconfirm').hide();
});

$('#datesedit').click(function(){

  if ( $('#editdatesdiv').is(':visible') == true ) {
  	 $('#editdatesdiv').slideUp();
  } else {
  	 $('#editdatesdiv').slideDown();
  	 var oldstart = $('#startdatebd').text();
  	 var oldend = $('#enddatebd').text();
  	 $('#budgeteditdatestart').val(oldstart);
  	 $('#budgeteditdateend').val(oldend);
  }
	
});


$('#removedates').click(function(){
	var url = '/cloud-fortis/user/index.php?budget=yes';
	var dataval = 'editbudgets=1&place=datestartedit&globalid='+globalid+'&editval=""';
	console.log(dataval);
	var bds = '';
			$.ajax({
				url : url,
				type: "POST",
				data: dataval,
				cache: false,
				async: false,
				dataType: "html",
				success : function (data) {
					$('.lead').hide();
					if (data != 'none') {
						$('.lead').hide();
						console.log(data);
						bds = JSON.parse(data);
					} else {
						alert('Have not got any data of this period');
					}
				}
			});

	var url = '/cloud-fortis/user/index.php?budget=yes';
	var dataval = 'editbudgets=1&place=dateendedit&globalid='+globalid+'&editval=""';
	var bds = '';
			$.ajax({
				url : url,
				type: "POST",
				data: dataval,
				cache: false,
				async: false,
				dataType: "html",
				success : function (data) {
					$('.lead').hide();
					if (data != 'none') {
						$('.lead').hide();
						console.log(data);
						bds = JSON.parse(data);
					} else {
						alert('Have not got any data of this period');
					}
				}
			});

			$('#timebudget').hide();
			$('.nodatalerts').show();
			$('#removedates').hide();
});

$('#saveperiod').click(function(){
	var dateend = $('#budgeteditdateend').val();
	console.log(dateend);
	var datestart = $('#budgeteditdatestart').val();
	$('#startdatebd').text(datestart);
	$('#enddatebd').text(dateend);
	$('.nodatalerts').hide();
	$('#timebudget').show();
	$('#editdatesdiv').slideUp();

	console.log(datestart);
	console.log(dateend);

	var url = '/cloud-fortis/user/index.php?budget=yes';
	var dataval = 'editbudgets=1&place=datestartedit&globalid='+globalid+'&editval="'+datestart+'"';
	console.log(dataval);
	var bds = '';
			$.ajax({
				url : url,
				type: "POST",
				data: dataval,
				cache: false,
				async: false,
				dataType: "html",
				success : function (data) {
					$('.lead').hide();
					if (data != 'none') {
						$('.lead').hide();
						console.log(data);
						bds = JSON.parse(data);
					} else {
						alert('Have not got any data of this period');
					}
				}
			});

	var url = '/cloud-fortis/user/index.php?budget=yes';
	var dataval = 'editbudgets=1&place=dateendedit&globalid='+globalid+'&editval="'+dateend+'"';
	var bds = '';
			$.ajax({
				url : url,
				type: "POST",
				data: dataval,
				cache: false,
				async: false,
				dataType: "html",
				success : function (data) {
					$('.lead').hide();
					if (data != 'none') {
						$('.lead').hide();
						console.log(data);
						bds = JSON.parse(data);
					} else {
						alert('Have not got any data of this period');
					}
				}
			});
});

$('.editright').focusout(function(){
	console.log('save');
	var val = $(this).val();
	val = parseInt(val);
	var dispval = '';

	if ( (val == 0) || (val == 'NaN') || (isNaN(val) == true) ) {
		val = 0;
		dispval = 'Unlimited';
	} else {
		dispval = '$'+val;
	}
	
	var idplace = $(this).attr('id');

	if ( idplace == 'cpuedit' ) {
		$('#cpubd').text(dispval);
	}

	if ( idplace == 'memoryedit' ) {
		$('#memorybd').text(dispval);
	}

	if ( idplace == 'storageedit' ) {
		$('#storagebd').text(dispval);
	}

	if ( idplace == 'networkedit' ) {
		$('#networkbd').text(dispval);
	}

	if ( idplace == 'virtualedit' ) {
		$('#virtualbd').text(dispval);
	}

	$(this).closest('.slider').hide();

	var url = '/cloud-fortis/user/index.php?budget=yes';
	var dataval = 'editbudgets=1&place='+idplace+'&globalid='+globalid+'&editval='+val;
	var bds = '';
			$.ajax({
				url : url,
				type: "POST",
				data: dataval,
				cache: false,
				async: false,
				dataType: "html",
				success : function (data) {
					$('.lead').hide();
					if (data != 'none') {
						$('.lead').hide();
						console.log(data);
						bds = JSON.parse(data);
					} else {
						alert('Have not got any data of this period');
					}
				}
			});

});



$('#remform').click(function(){
	var url = '/cloud-fortis/user/index.php?budget=yes';
	var dataval = 'rembudgets=1&remid='+remid;
	var bds = '';
			$.ajax({
				url : url,
				type: "POST",
				data: dataval,
				cache: false,
				async: false,
				dataType: "html",
				success : function (data) {
					$('.lead').hide();
					if (data == 'remdone') {
						$('.lead').hide();
						alert('Removed succesfully');
						$('#popupremconfirm').hide();
						location.reload();
					} 
				}
			});
});

if ( ( typeof(budgetpage) != 'undefined' ) && ( budgetpage == true) ) {
	var url = '/cloud-fortis/user/index.php?budget=yes';
	var dataval = 'getbudgets=1';
	var bds = '';

	$.ajax({
		url : url,
		type: "POST",
		data: dataval,
		cache: false,
		async: false,
		dataType: "html",
		success : function (data) {
			$('.lead').hide();
			if (data != 'none') {
				$('.lead').hide();
				//console.log(data);
				bds = JSON.parse(data);
			} else {
				alert('Have not got any data of this period');
			}
		}
	});

	//var servers = '<li class="carstart">'; 
	var i = 0;
	var budgets = [];

	$.each(bds, function(key, serv) {
		
		if (i == 0) {
			$('#budget-name').html('<strong>' + serv.name + '</strong>&nbsp;&nbsp;(' + serv.date_start + ' ~ ' + serv.date_end + ')').attr("rel",serv.id);
		}

		var html =	'<table id="' +  serv.id + '" class="table table-hover table-stripped"' + (i > 0 ? ' style="display:none;" ' : '')   + '><tbody>';
		html	+=		'<tr><td style="width: 40%"><strong>' + "CPU:" + 			'</strong></td><td>' + serv.cpu + 		'</td></tr>';
		html	+=		'<tr><td style="width: 40%"><strong>' + "Storage:" + 		'</strong></td><td>' + serv.storage + 	'</td></tr>';
		html	+=		'<tr><td style="width: 40%"><strong>' + "Memory:" + 		'</strong></td><td>' + serv.memory + 	'</td></tr>';
		html	+=		'<tr><td style="width: 40%"><strong>' + "Virtualization:" +	'</strong></td><td>' + serv.vm + 		'</td></tr>';
		html	+=		'<tr><td style="width: 40%"><strong>' + "Network:" +		'</strong></td><td>' + serv.network + 	'</td></tr>';
		html	+=	'</tbody></table>';
	
		$("#budgets-setting").append(html);

		var html_alerts = '';

		if (serv.havealerts) {
			html_alerts =	'<table id="alerts-' +  serv.id + '" class="table table-hover table-stripped"' + (i > 0 ? '' : '')   + '>';
			html_alerts +=		'<tbody><tr><td style="width: 50%"><strong>' + "% of Budget" + '</strong></td><td><strong>' + "Action" + '</strong></td></tr>';

			for (j = 0; j < serv.alerts.length; j++) {
				html_alerts +=	'<tr><td style="width: 50%">' + serv.alerts[j] + '</td><td><a href="#"><i class="fa fa-minus-circle" style="color:red"></i>&nbsp;Remove</a></td></tr>';
			}
			html_alerts	+=	'</tbody></table>';
		}

		$("#budgets-alert").append(html_alerts);

		i++;
	});

	if (i > 0) {
		$('.carbudgbtn').show();
	}

	$('#namespaces').html(servers);
	$('.carbudget').show();

	// if ($('.jcarousel').length > 0) {
	//		$('.jcarousel').jcarousel();
	// }
}




$('body').on('click', '.budgservn', function(){

	var url = '/cloud-fortis/user/index.php?budget=yes';
	var dataval = 'getbudgets=1';
	var bds = '';
			$.ajax({
				url : url,
				type: "POST",
				data: dataval,
				cache: false,
				async: false,
				dataType: "html",
				success : function (data) {
					$('.lead').hide();
					if (data != 'none') {
						$('.lead').hide();
						bds = JSON.parse(data);
					} else {
						alert('Have not got any data of this period');
					}
				}
			});

	
	$('.budgservn').css('border', 'none');
	$(this).css('border', '3px solid #cc6165');
	var num = $(this).attr('num');
	num = parseInt(num);
	
	$('.infoblockbudget').show();
	$('#resbudgetchoose').hide();
	
	var cpuval = '';
	var memoryval = '';
	var storageval = '';
	var networkval = '';
	var virtval = '';
	
	if (bds[num].memory == 0 ) {
        memoryval = 'Unlimited';
	} else {
		memoryval = '$'+bds[num].memory;
	}

	if (bds[num].storage == 0 ) {
        storageval = 'Unlimited';
	} else {
		storageval = '$'+bds[num].storage;
	}

	if (bds[num].cpu == 0 ) {
        cpuval = 'Unlimited';
	} else {
		cpuval = '$'+bds[num].cpu;
	}

	if (bds[num].network == 0 ) {
        networkval = 'Unlimited';
	} else {
		networkval = '$'+bds[num].network;
	}

	if (bds[num].vm == 0 ) {
        virtval = 'Unlimited';
	} else {
		virtval = '$'+bds[num].vm;
	}

	globalid = bds[num].id;

	
	$('#cpubd').text(cpuval);
	$('#memorybd').text(memoryval);
	$('#storagebd').text(storageval);
	$('#networkbd').text(networkval);
	$('#virtualbd').text(virtval);


	if ( (bds[num].date_start == 'unlim') || (bds[num].date_end == 'unlim') ) {
		$('#timebudget').hide();
		$('.nodatalerts').show();
		$('#timebudget').hide();
		$('#removedates').hide();
	} else {
		$('#startdatebd').text(bds[num].date_start);
		$('#enddatebd').text(bds[num].date_end);
		$('#timebudget').show();
		$('.nodatalerts').hide();
		$('#removedates').show();
	}

	if (bds[num].havealerts == 1) {
		$('.newrow').remove();
		$('.noalerts').hide();
		$.each(bds[num]['alerts'], function(key, val){
			var row = '<tr class="newrow"><td class="text-center valperc">'+val+'</td><td class="text-center"><a class="rempercent"><i class="fa fa-close"></i> Remove</a></td></tr>';
			$('.table-alerts').append(row);
			$('.table-alerts').show();
		});
	} else {
		$('.table-alerts').hide();
		$('.noalerts').show();
	}

	$('#resbudget').show();
});




$('.alertprice').click(function(){
	var percent = $('#percentbudg').val();
	percent = parseInt(percent);
	if ( (percent != 'NaN') && (percent <= 100) ) {
	var row = '<tr><td class="perval">'+percent+'</td><td> <a class="rempercent"><i class="fa fa-close"></i> Remove</a></td></tr>';
	$('.table-alerts').append(row);
} else {
	alert('Only integer number of percent value and not bigger, than 100, please');
}
$('.table-alerts').show();

});

$('body').on('click','.rempercent', function(){
	$(this).closest('tr').remove();
});

$('.submitallprice').click(function(){
	$('.lead').show();
	var name = $('#budgetname').val();
	var date_start = $('#budgetdatestart').val();
	var date_end = $('#budgetdateend').val();
	var cpu = $('#budgetcpu').val();
	var memory = $('#budgetmemory').val();
	var storage = $('#budgetstorage').val();
	var networking = $('#budgetnetwork').val();
	var vm = $('#budgetvm').val();

	var perval = Array();
	$('.perval').each(function(i){
		perval[i] = $(this).text();
	});


	

	cpu = parseInt(cpu);
	if (cpu == 'NaN') {
		cpu = 0;
	}

	memory = parseInt(memory);
	if (memory == 'NaN') {
		memory = 0;
	}

	storage = parseInt(storage);
	if (storage == 'NaN') {
		storage = 0;
	}

	networking = parseInt(networking);
	if (networking == 'NaN') {
		networking = 0;
	}

	vm = parseInt(vm);
	if (vm == 'NaN') {
		vm = 0;
	}

	var url = '/cloud-fortis/user/index.php?budget=yes';
	var dataval = 'create=1&name='+name+'&limit='+perval+'&date_start="'+date_start+'"&date_end="'+date_end+'"&cpu='+cpu+'&memory='+memory+'&storage='+storage+'&networking='+networking+'&vm='+vm;
	
			$.ajax({
				url : url,
				type: "POST",
				data: dataval,
				cache: false,
				async: false,
				dataType: "html",
				success : function (data) {
					$('.lead').hide();
					if (data != 'none') {
						$('.lead').hide();
						if (data == 'works') {
							location.href='index.php?report=report_budget';
						} else {
							alert(data);
						}
					} else {
						alert('Something wrong');
					}
				}
			});



});



$('#content_container').click(function(){
	$('.reportdropdown').hide();
});

$('#home_container').click(function(){
	$('.reportdropdown').hide();
});

$('.sidebar').click(function(){
	$('.reportdropdown').hide();
});

$('#reportmonthdash').change(function(){
	changedashboard();
});

$('#reportyeardash').change(function(){
	changedashboard();
});


if (typeof(datepickeryep) != 'undefined' && datepickeryep == true) {
	$('.date').datepicker();
}





if ((typeof(explorer) != 'undefined') && (explorer == true)) {
	var url = '/cloud-fortis/user/index.php?report=yes';
	var dataval = 'explorer=1&explorerajax=getservers';
	var vms = '';
	$.ajax({
		url : url,
		type: "POST",
		data: dataval,
		cache: false,
		async: false,
		dataType: "html",
		success : function (data) {
			$('.lead').hide();
			if (data != 'none') {
				$('.lead').hide();
				vms = JSON.parse(data);
			} else {
				alert('Have not got any data of this period');
			}
		}
	});
	//var servers = '<li class="carstart">'; 
	var servers = '<table class="table table-hover nowrap dataTable dtr-inline" id="score_cloud_appliances_table" role="grid" style="width: 100%;"><thead><tr>';
	var servers = servers + '<tr><th>VM Name</th><th>VM IP</th><th>CPU</th><th>Memory</th><th>Storage</th><th>Status</th><th>Creation time</th><th>Working time</th><th>Cost</th>';
	var servers = servers + '</tr></thead><tbody>';
	var i = 0;
	$.each(vms, function(key, serv){
		i = i + 1;
		//servers = servers + '<div class="panel panel-primary panel-colorful servn" num="'+i+'"><div class="panel-body text-center"><p class="text-uppercase mar-btm text-sm">'+serv.name+'</p><i class="fa fa-desktop fa-3x"></i><hr><p class="h2 text-thin">'+serv.price+'</p></div></div>';
		servers = servers + '<tr><td>'+serv.name+'</td>';
		servers = servers + '<td>'+serv.ip+'</td>';
		servers = servers + '<td>'+serv.cpu+'</td>';
		servers = servers + '<td>'+serv.ram+'</td>';
		servers = servers + '<td>'+serv.storage+'</td>';
		servers = servers + '<td>'+serv.status+'</td>';
		servers = servers + '<td>'+serv.created+'</td>';
		servers = servers + '<td>'+serv.worked+'</td>';
		servers = servers + '<td>'+serv.price+'</td></tr>';
		if (i == 9) {
			i = 0;
			//servers = servers + '</li>';
		}
	});
	var servers = servers + '</tbody></table>';
	$('#namespaces').html(servers);
	//$('.jcarousel').jcarousel();
	$("#server-cost-table-data").html(servers);
}

$('#cpubd').click(function() {
	if ($('#cpuedit').closest('.slider').is(':visible') == false) {
		$('#cpuedit').closest('.slider').slideDown();
	} else {
		$('#cpuedit').closest('.slider').slideUp();
	}
});

$('#memorybd').click(function() {
	if ($('#memoryedit').closest('.slider').is(':visible') == false) {
		$('#memoryedit').closest('.slider').slideDown();
	} else {
		$('#memoryedit').closest('.slider').slideUp();
	}
});

$('#storagebd').click(function() {
	if ($('#storageedit').closest('.slider').is(':visible') == false) {
		$('#storageedit').closest('.slider').slideDown();
	} else {
		$('#storageedit').closest('.slider').slideUp();
	}	
});

$('#networkbd').click(function() {
	if ($('#networkedit').closest('.slider').is(':visible') == false) {
		$('#networkedit').closest('.slider').slideDown();
	} else {
		$('#networkedit').closest('.slider').slideUp();
	}
});

$('#virtualbd').click(function() {
	if ($('#virtualedit').closest('.slider').is(':visible') == false) {
		$('#virtualedit').closest('.slider').slideDown();
	} else {
		$('#virtualedit').closest('.slider').slideUp();
	}
});

$('body').on('click', '.servn', function(){
	if (typeof(budgetpage) == 'undefined') {
		$('.servn').css('border', 'none');
		$(this).css('border', '3px solid #cc6165');
		var index = $(this).attr('num');
		$('#cpuexp').html(vms[index].cpu);
		$('#memoryexp').html(vms[index].ram);
		$('#statusexp').html(vms[index].status);
		$('#storageexp').html(vms[index].storage);
		$('#creationexp').html(vms[index].created);
		$('#timeexp').html(vms[index].worked);
		$('#creationexp').html(vms[index].created);
		$('#totalexp').html(vms[index].price);

		var color = '#5fa2dd';

		if ( $('#statusexp').text() == 'active') {
			color = '#8cc63f';
		}

		if ( $('#statusexp').text() == 'stopped') {
			color = '#ff5800';
		}

		if ( $('#statusexp').text() == 'removed') {
			color = '#cc6165';
		}

		$('#statusexp').css('background-color', color);
	}
});


$('.selectype').find('.panel-body').click(function() {
	$('.selectype .panel-body').css('border', 'none');
	$(this).css('border','3px solid #cc6165');
});
          
        

$('.buttoncarousel').click(function(){
	$('.jcarousel').jcarousel('scroll', '+=1');
});

$('.buttoncarouselback').click(function(){
	$('.jcarousel').jcarousel('scroll', '-=1');
});




function changedashboard() {
	var monthd = $('#reportmonthdash').val();
	var yeard = $('#reportyeardash').val();
	givedashboard(monthd, yeard);
}



$('.billcsvdownload').click(function(){
		
		var year = $('#reportyear').val();
		var month = $('#reportmonth').val();
		var user = $('#reportuser').val();
		var url = '/cloud-fortis/user/index.php?report=yes';
		var dataval = 'year='+year+'&month='+month+'&forbill=1&forcsv=1&userdash='+user;
	
			$.ajax({
				url : url,
				type: "POST",
				data: dataval,
				cache: false,
				async: false,
				dataType: "html",
				success : function (data) {
					$('.lead').hide();
					if (data != 'none') {
						$('.lead').hide();
						location.href=data;
					} else {
						alert('Have not got any data of this period');
					}
				}
			});
});

$('.printbill').click(function(){
	$('#cloud_top_menu').hide();
	$('.sidebar').hide();
	
	$('.windows_plane').css('float','left');
	$('.windows_plane').css('width', '100%');
	$('#cloud-content').css('width', '100%');

	$('.reportbtnbill').hide();
	$('#chart-row').hide();
	$('.printlogo').show();
	$('.hideslider').show();
	$('#home_container').css('position', 'relative');
	$('#home_container').css('top', '-50px');
	$('.gobackprint').show();
	window.print();
});

$('.printdash').click(function(){
	$('#cloud_top_menu').hide();
	$('.sidebar').hide();
	
	$('.windows_plane').css('float','left');
	$('.windows_plane').css('width', '100%');
	$('#cloud-content').css('width', '100%');

	$('.reportbtnbill').hide();
	$('.printlogo').show();
	$('#home_container').css('position', 'relative');
	$('#home_container').css('top', '-50px');
	$('#diagramsreport').css('width', '100%');
	$('#diagramsreport').css('top', '290px');
	$('#diagramsreport').css('position', 'absolute');
	$('.barchartsside').css('width', '40%');
	$('.donutside').css('width', '40%');
	$('.donutside').css('float', 'right');

	$('#donutrender').css('right', '100px');
	$('#donutrender').css('position', 'relative');
	$('.gobackprint').show();
	$('.morris-hover').hide();

	window.print();
});

$('.gobackprint').click(function(){
	location.reload();
});

$('.shortselect').change(function() {
	changereporte();
});

function changereporte() {
	 if (typeof(diagramshow) == 'undefined') {
	var month = $('#reportmonth').val();
	var year = $('#reportyear').val();
	var user = $('#hiddenname').val();

	if ($('.lead').is(':visible') == 'false') {
		wait();
	}
	
	var url = '/cloud-fortis/user/index.php?report=yes';
	var dataval = 'year='+year+'&month='+month+'&forbill=1';
	
		$.ajax({
				url : url,
				type: "POST",
				data: dataval,
				cache: false,
				async: false,
				dataType: "html",
				success : function (data) {
					$('.lead').hide();
					if (data != 'none') {
						$('.lead').hide();
						data = JSON.parse(data);
						var total = data.all;
						$('td.storage').text(data.storage);
						$('td.cpu').text(data.cpu);
						$('td.memory').text(data.memory);
						$('td.networking').text(data.networking);
						$('td.virtualization').text(data.virtualization);
						$('b.total').text(total);
						$('td.total').text(total);


					} else {
						alert('Have not got any data of this period');
					}
					
				}
			});
	}
}

$('#report').click(function(){
	if($("#reportdropdown").is(':visible') == true){
		$('#reportdropdown').hide();
	} else {
		$('#reportdropdown').show();
	}
});

$('#reportdropdown').focusout(function(){
	$(this).hide();
});


$('.slideractive').click(function() {
	if ($('.hideslider').is(':visible') == true) {
		$('.hideslider').hide();
	} else {
		$('.hideslider').show();
	}
});

if ( (typeof(nocontent) != 'undefined') && (nocontent == true) ) {
	
	$('#cloud-content').css('min-height', '200px');
}


$('#diagramclose').click(function(){
	$('#diagramsreportpopup').hide();
});

var detailtable = '';

$('#orderreport').click(function(){
	//var user = $(this).closest('#popupform').find('#hiddenname').val();
	var month = $(this).closest('#popupform').find('#reportmonth').val();
	var year = $(this).closest('#popupform').find('#reportyear').val();

	if ($('.lead').is(':visible') == 'false') {
		wait();
	}
	
	var url = '/cloud-fortis/user/index.php?report=yes';
	var dataval = 'year='+year+'&month='+month;
	$('#popup').hide();
		$.ajax({
				url : url,
				type: "POST",
				data: dataval,
				cache: false,
				async: false,
				dataType: "html",
				success : function (data) {
					$('.lead').hide();
					if (data != 'none') {
						$('.lead').hide();
						$('.modal-overlay').hide();
						$('#popupform').html(data);
						$('#popup').show();
						detailtable = $('#popupform').find('#detailtable').html();
					} else {
						alert('Have not got any data of this period');
					}
					
				}
			});
});

$('body').on('click', '#detailreport', function(){
	$('#popup').hide();
	$('#popuptableform').html(detailtable);
	$('#popuptable').show();
});

$('#order').click(function(){
	$('#popup').show();
	$('#reportdropdown').hide();
});

$('#popupclose').click(function(){
	$('#popup').hide();
});

$('#popuptableclose').click(function(){
	$('#popuptable').hide();
});

var vwidth = $(window).width();
	if (vwidth < 1300) {
		//$('#menubutton').addClass('menubutton2');
		//$('#fortis5').find('img').addClass('little');
	}

$('#cloud_appliances').find('.action').find('a').click(function(e) {
	
	
	e.preventDefault();
	var hrefo = $(this).attr('href');

		 		$('#storageform').load(hrefo+" form", function(){
		  			$('.lead').hide();
		  			$('#storageform').find('form').each(function(){
		  				if ($(this).attr('method') == 'get') {
		  					$(this).remove();
		  				}
		  			});
		  			//$('#storageform select').selectpicker();
		  			//$('#storageform select').hide();
		  			//$('#storageform').find('.submit').addClass('');
	  				$('#volumepopup').show();
		  		});  	
	


});
// popup works:


	$('#volumepopupclose').click(function(){
		$('#volumepopup').hide();
		$('.modal-overlay').hide();
	});

// --- end popup works ---

// authorisation works:

document.execCommand('ClearAuthenticationCache', 'false');
var height = $(window).height();
height = (height - 100)/2;
height = height + 'px';
//$('#loginwindow').css('top', height);
	

// Create Base64 Object
var Base64={_keyStr:"ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz0123456789+/=",encode:function(e){var t="";var n,r,i,s,o,u,a;var f=0;e=Base64._utf8_encode(e);while(f<e.length){n=e.charCodeAt(f++);r=e.charCodeAt(f++);i=e.charCodeAt(f++);s=n>>2;o=(n&3)<<4|r>>4;u=(r&15)<<2|i>>6;a=i&63;if(isNaN(r)){u=a=64}else if(isNaN(i)){a=64}t=t+this._keyStr.charAt(s)+this._keyStr.charAt(o)+this._keyStr.charAt(u)+this._keyStr.charAt(a)}return t},decode:function(e){var t="";var n,r,i;var s,o,u,a;var f=0;e=e.replace(/[^A-Za-z0-9\+\/\=]/g,"");while(f<e.length){s=this._keyStr.indexOf(e.charAt(f++));o=this._keyStr.indexOf(e.charAt(f++));u=this._keyStr.indexOf(e.charAt(f++));a=this._keyStr.indexOf(e.charAt(f++));n=s<<2|o>>4;r=(o&15)<<4|u>>2;i=(u&3)<<6|a;t=t+String.fromCharCode(n);if(u!=64){t=t+String.fromCharCode(r)}if(a!=64){t=t+String.fromCharCode(i)}}t=Base64._utf8_decode(t);return t},_utf8_encode:function(e){e=e.replace(/\r\n/g,"\n");var t="";for(var n=0;n<e.length;n++){var r=e.charCodeAt(n);if(r<128){t+=String.fromCharCode(r)}else if(r>127&&r<2048){t+=String.fromCharCode(r>>6|192);t+=String.fromCharCode(r&63|128)}else{t+=String.fromCharCode(r>>12|224);t+=String.fromCharCode(r>>6&63|128);t+=String.fromCharCode(r&63|128)}}return t},_utf8_decode:function(e){var t="";var n=0;var r=c1=c2=0;while(n<e.length){r=e.charCodeAt(n);if(r<128){t+=String.fromCharCode(r);n++}else if(r>191&&r<224){c2=e.charCodeAt(n+1);t+=String.fromCharCode((r&31)<<6|c2&63);n+=2}else{c2=e.charCodeAt(n+1);c3=e.charCodeAt(n+2);t+=String.fromCharCode((r&15)<<12|(c2&63)<<6|c3&63);n+=3}}return t}}


		function make_base_auth(user, password) {
		  var tok = user + ':' + password;
		  var hash = Base64.encode(tok);
		  return "Basic " + hash;
		}

// == Login ==
function cloudScoreLogin(){
	var username = $("input#userlogin").val();
	var password = $("input#userpassword").val();
	if(username == ""){
		var errmsg = "Username can not be empty";
	}
	if(password == ""){
		var errmsg = "Password can not be empty";
	}
	if(username == "" && password == ""){
		var errmsg = "Username and Password can not be empty";
	}
	var full = location.protocol+'//'+location.hostname+(location.port ? ':'+location.port: '');
	var domainpart = location.hostname+(location.port ? ':'+location.port: '');
	var ajaxurl = full + "/cloud-fortis/user/";	

	$.ajax({
		type: "GET",
		url: ajaxurl,
		headers: {"Authorization": "Basic " + btoa(username + ":" + password)},
		beforeSend: function (xhr){xhr.setRequestHeader('Authorization', make_base_auth(username, password));},
		success: function(){
			var domain = domainpart;
			location.href = 'http://'+domain+'/cloud-fortis/user/';
			//location.href = 'http://'+username+':'+password+'@'+domain+'/cloud-fortis/user/';
			//setTimeout(function() { window.location = '/cloud-fortis/user/'; }, 2000);
		},
		error: function(){
			if(errmsg){
				alert(errmsg);
			} else {
				alert('Credentials provided is not valid');
			}
		}
	});
}
$('#btnlogin').click(function(){
	cloudScoreLogin();
});
$("#userpassword").keypress(function(e){
	if(e.which == 13){
		cloudScoreLogin();
	}
});
$("#userlogin").keypress(function(e){
	if(e.which == 13){
		cloudScoreLogin();
	}
});

// --- end authorisation works ---



	//network frontend development part:

		var networkvalues = '';
		$('#cloud_network_select').find('option').each(function(){
			var val = $(this).val();
			networkvalues = val;
		});

		for ( i=1; i<5; i++ ) {
			if (i > networkvalues) {
				var sel = '.sel'+i;
				$(sel).hide();
			}

		}

		$('#cloud_network_select').val(1);

		$('.netcheck1').find('input').click(function() {
			alert('One network should to be always, you can\'t switch it off' );
		});

		$('.form-checkbox').find('input').click(function(){
			
			if ($(this).closest('.form-checkbox').hasClass('netcheck1') == false) {
				
				var numc = $(this).closest('.form-checkbox').attr('netcheck');
				

				var prevnum = numc - 1;
				var prevselect = '.netcheck'+prevnum;
				var prevactive = false;
				if ($(prevselect).hasClass('active')) {
					prevactive = true;
				}

				var valuen = $('#cloud_network_select').val();

				if ($(this).closest('.form-checkbox').hasClass('active') == true) {
					//selected
					if (prevactive == true && valuen == numc) {
						$(this).closest('.form-checkbox').removeClass('active');
						var numselect = numc - 1;
						var sel = '#cloud_ip_select_' + numselect;
						$(sel).prop('disabled', true);
						$('#cloud_network_select').val(numselect);
						//$(this).find('input').prop('checked', true);

					} else {
						alert('Please, select network interfaces by order');
					}
				} else {
					//not selected
					if (prevactive == true) {
						$(this).closest('.form-checkbox').addClass('active');
						var numselect = numc - 1;
						var sel = '#cloud_ip_select_' + numselect;
						$(sel).prop('disabled', false);
						$('#cloud_network_select').val(numc);
						
					} else {
						alert('Please, select network interfaces by order');
					}
				}
			}
		});
	// --- end network part ---

	// profiles selection:
	$('#profiles_select').change(function(){
		var hrefo = $(this).val();
		location.href=hrefo;
	});
			
			
	// --- end profiles selection ---

	
	$('#register_tab').find('ul').addClass('mainnav-menu');
	// volume edit:
	var hostnameglobal = '';
	var globalnum = '';
		$('.editvolumesmpopup').click(function(){
			$('#moredisktbl').find('.content').remove();
			wait();
			var hostname = $(this).closest('tr').find('.hostnamee').text();
			hostnameglobal = hostname;
		

			var url = "/cloud-fortis/user/index.php?cloud_ui=appliances&action=volumedata&hostname=" + hostname;
			
			$.ajax({
				url : url,
				type: "GET",
				cache: false,
				async: false,
				dataType: "html",
				success : function (data) {
					$('#moredisktbl').find('.content').remove();
					$('#moredisktbl').append(data);
					$('.lead').hide();
					$('.modal-overlay').hide();
					$('#modal-volume').modal();
				}
			});
			
		});








		$('body').on('click','.voldel', function() {
			wait();
		
			var num = $(this).closest('tr').attr('num');
			globalnum = num;
			var url = "/cloud-fortis/user/index.php?cloud_ui=appliances&action=volumedatadel&hostname=" + hostnameglobal +"&num="+num;
			$('#moredisktbl').find('.content').remove();
			var linktext = $(this).text();
			$.ajax({
				url : url,
				type: "GET",
				cache: false,
				async: false,
				dataType: "html",
				success : function (data) {
					$('#moredisktbl').find('.content').remove();
					$('#moredisktbl').append(data);
					$('.lead').hide();
					$('.modal-overlay').hide();
					$('#modal-volume').modal('hide');
					alert('Volume removed succesful');
				}
			});
			//setInterval(updatevolumes(linktext), 20000);

			
		});

		var globallinktext = '';
		$('body').on('click','.voladd', function() {
			var num = $(this).closest('tr').attr('num');
			globalnum = num;
			$('#modal-volumeadd').modal();
			var linktext = $(this).text();
			globallinktext = linktext;
		});

		$('#addvolumebtnvv').click(function(){
			wait();

			var sizevol = $('#volumeselect').val();
			var url = "/cloud-fortis/user/index.php?cloud_ui=appliances&action=volumedataadd&hostname=" + hostnameglobal +"&num="+globalnum+"&sizevol="+sizevol;
			$('#moredisktbl').find('.content').remove();
			var linktext = globallinktext;
			$.ajax({
				url : url,
				type: "GET",
				cache: false,
				async: false,
				dataType: "html",
				success : function (data) {
					if (data == 'no disk space') {
						alert('You have not got free space for this volume creation');
						$('.lead').hide();
						$('.modal-overlay').hide();
						$('#modal-volumeadd').modal('hide');
						$('#modal-volume').modal('hide');
						return;
					}
					$('#moredisktbl').find('.content').remove();
					$('#moredisktbl').append(data);
					$('.lead').hide();
					$('.modal-overlay').hide();
					$('#modal-volumeadd').modal('hide');
					$('#modal-volume').modal('hide');
					alert('Volume created succesful');
				}
			});
		 	//setInterval(updatevolumes(linktext), 20000);
		});

	/*function updatevolumes(linktext) {
		console.log('first = '+linktext);
		var flag = false;
		var url = "/cloud-fortis/user/index.php?cloud_ui=appliances&action=volumedata&hostname=" + hostnameglobal;
			
			$.ajax({
				url : url,
				type: "GET",
				cache: false,
				async: false,
				dataType: "html",
				success : function (data) {
					$('#moredisktbl').find('.content').remove();
					$('#moredisktbl').append(data);

					$('#moredisktbl').find('.content').each(function(){
						var attrnum = $(this).attr('num');
						
						if ( attrnum == globalnum) {
							var newlink = $(this).find('a').text();
							console.log(newlink);
							console.log(linktext);
							if (newlink != linktext) {
								$('#moredisktbl').find('.content').remove();
								$('#moredisktbl').append(data);
								$('.lead').hide();
								$('.modal-overlay').hide();
								flag = true;
							}
						}
					});
				}
			});
			if (flag == false) {
				updatevolumes(linktext);
			}
	}
	*/
	// --- end volume edit ---

	var hostnameglobal = '';
	var cdromlist = [];
	//cdrom:
		$('.cdrom').click(function(){
			var hostname = $(this).closest('tr').find('.hostnamee').text();
			hostnameglobal = hostname;
			if ( $(this).text() == 'Insert CD' ) {
				var action = 'getlist';
				cdromsender(hostname, action);
				
				$('#isofilez').html('');
				for( i in cdromlist ) {
					var file = cdromlist[i];
					var filerow = '<tr class="htmlobject_tr odd last"><td class="htmlobject_td name"><a href="#" class="file">'+file+'</a></td></tr>';
					$('#isofilez').append(filerow);
				}
				
				$('#filepicker').show();
			}

		});

		$('.cdromeject').click(function() {
			var hostname = $(this).closest('tr').find('.hostnamee').text();
			hostnameglobal = hostname;
			if ( $(this).text() == 'Eject CD' ) {
				var action = 'eject';
				cdromsender(hostname, action);
			}
		});

		$('.filepickclose').click(function(){
			hostnameglobal = '';
		});


		$('body').on('click', '.file', function() {
			var filetext = $(this).text();
			cdromsender(hostnameglobal, 'insert', filetext);
		});

	function cdromsender(hostname, action, isofile) {
		
			var url = "/cloud-fortis/user/index.php?cloud_ui=appliances&action=cdrom&hostname=" + hostname;
			url = url + "&cdaction=" + action;
			url = url + "&isofile=" + isofile;
			var row = '';
			$('.hostnamee').each(function(){
				if ( $(this).text() == hostname ) {
					row = $(this).closest('tr');
				}
			});
			
			$.ajax({
				url : url,
				type: "GET",
				cache: false,
				async: false,
				dataType: "html",
				success : function (data) {
					if (action == 'getlist') {
						list = data.split(';');
						for (i in list) {
							cdromlist[i] = list[i];
						}
					}

					if (action == 'insert') {
						if (data == 'Insert succesful') {
							alert(data);
							$('#filepicker').hide();
							row.find('.cdrom').hide();
							row.find('.cdromeject').show();
						} else {
							alert('Can\'t insert iso file, please, read documentation first');
							$('#filepicker').hide();
						}
						
					}

					if (action == 'eject') {
						if (data == 'Eject succesful') {
							alert(data);
							row.find('.cdromeject').hide();
							row.find('.cdrom').show();
						} else {
							alert('Can\'t eject cd');
						}
					}	
				}
			});
	}
	// --- end cdrom ---

	

	// add more disk js:
	var morediskcount = 0;
	var availablespace = 99999999999999999999;//$('#freemb').text();
	var allsizes = 0;
	availablespace = parseInt(availablespace);
	var availablespacefirst = availablespace;

	$('#managedisks').click(function(){
		var cc = 0;
		$('#moredisktbl td.size').each(function(){
			cc = cc + parseInt($(this).text());
		});

		allsizes = cc;
		availablespace = availablespacefirst - allsizes;

		$('#modal-volume').modal();
	});

	$('#addmoredisks').click(function() {
		if (morediskcount < 11) {
			var hostname = $('#cloud_hostname_input').val();
			if (hostname != '') {
				$('#freembsp').text(availablespace+'MB');
				$('#modal-volume').modal('hide');
				$('#modal-volumeadd').modal();
				$('#namevolumeinput').val(hostname+'vol'+morediskcount);
			} else {
				alert('Please, insert hostname first');
			}
		} else {
			alert('You can add maximum 10 volumes');
		}
	});



	$('#addvolumebtn').click(function() {

		var allcount = 0;
		var storagenameval = $('#namevolumeinput').val();
		var storagesizeval = $('#volumeselect').val();
		var ccuval = 0;

		storagesizeval = parseInt(storagesizeval);
		var storagetypeval = $('#typevolumeselect').val();
		var storageid = 0;
		$('#morediskdiv').show();
		
				if( isNaN(storagesizeval) == true) {
					alert('Size should to be an integer value');
				} else {
					morediskcount = morediskcount + 1;
					storageid = morediskcount;
					if (morediskcount < 11) {
						var moredisk = '<tr class="storagerow" storageid="'+storageid+'"><!--<td class="type">'+storagetypeval+'--> <input type="hidden" name="storagetype[]" value="'+storagetypeval+'" /><!--</td><td class="name">'+storagenameval+'--> <input class="inputname" type="hidden" name="storagename[]" value="'+storagenameval+'" /><!--</td>--><td class="size">'+storagesizeval+'<input type="hidden" name="storagesize[]" value="'+storagesizeval+'" /></td><td class="text-center"><a class="storagedit"><i class="fa fa-pencil"></i></a></td><td class="text-center"><a class="storageremove"><i class="fa fa-close"></i></a></td></tr>';
						allsizes = allsizes + parseInt(storagesizeval);
					} 


				
					if (allsizes > availablespacefirst) {
						alert('You do not have enough disk space available. Available space is: '+ availablespace+' MB');
						allsizes = allsizes - parseInt(storagesizeval);
					} else {
						$('#moredisktbl').append(moredisk);
						availablespace = availablespacefirst - allsizes;
					}
				}

	
		cloud_cost_calculator();
		
	});

 $('body').on('click', '.storageremove', function() {
 		var stid = $(this).closest('tr').attr('storageid');
 		var sizest = $(this).closest('tr').find('td.size').text();
 		sizest = parseInt(sizest);
		allsizes = allsizes - sizest;
		availablespace = availablespacefirst - allsizes;
		morediskcount = morediskcount - 1;
		$(this).closest('tr').remove();
		
		cloud_cost_calculator();
 });

 $('body').on('click', '.storagedit', function(){
 		var stid = $(this).closest('tr').attr('storageid');
 		var sizest = $(this).closest('tr').find('td.size').text();
 		var namest = $(this).closest('tr').find('.inputname').val();
 		var typest = $(this).closest('tr').find('td.type').text();
 		sizest = parseInt(sizest);
		allsizes = allsizes - sizest;
		availablespace = availablespacefirst - allsizes;
		$('#freembspedit').text(availablespace+'MB');
		$('#sizeeditvolumeselect').val(sizest);
		$('#nameeditvolumeinput').val(namest);
		$('#typeeditvolumeselect select').val(typest);
		$('#storageidedit').text(stid);
		$('#modal-volume').modal('hide');
		$('#edit-modal').modal();
 });


	$('#editvolumebtn').click(function(){

		var storagenameval = $('#nameeditvolumeinput').val();
		var storagesizeval = $('#sizeeditvolumeselect').val();
		storagesizeval = parseInt(storagesizeval);

		var storagetypeval = $('#typeeditvolumeselect').val();
		var storageid = $('#storageidedit').text();
		
		$('#morediskdiv').show();
		
				if( isNaN(storagesizeval) == true) {
					alert('Size should to be an integer value');
				} else {
					
					
						var moredisk = '<!--<td class="type">'+storagetypeval+' --><input type="hidden" name="storagetype[]" value="'+storagetypeval+'" /><!--</td><td class="name">'+storagenameval+'--> <input type="hidden" name="storagename[]" value="'+storagenameval+'" /><!--</td>--><td class="size">'+storagesizeval+'<input type="hidden" name="storagesize[]" value="'+storagesizeval+'" /></td><td class="text-center"><a class="storagedit"><i class="fa fa-pencil"></i></a></td><td class="text-center"><a class="storageremove"><i class="fa fa-close"></i></a></td>';
						allsizes = allsizes + parseInt(storagesizeval);
					


				
					if (allsizes > availablespacefirst) {
						alert('You do not have enough disk space available. Available space is: '+ availablespace+' MB');
						allsizes = allsizes - parseInt(storagesizeval);
					} else {
						var rowedit = $('#moredisktbl').find('tr[storageid='+storageid+']');
						$(rowedit).html(moredisk);
						availablespace = availablespacefirst -allsizes;
					}
				}

			
			cloud_cost_calculator();
	});
// --- end storage add ---

	//var flago = false;
	var sidew = 0;
	var planew = 0;
	var menuarr = new Array();
	var flago = false;
	var cookflag = $.cookie('flago');

	// menu icons:

	var tab0 = $('#tab_register_tab0 a').html();
	$('#tab_register_tab0 a').html('<i class="fa fa-sign-in"></i>' + tab0);

	var tab1 = $('#tab_register_tab1 a').html();
	$('#tab_register_tab1 a').html('<i class="fa fa-plus"></i>' + tab1);

	var tab2 = $('#tab_register_tab2 a').html();
	$('#tab_register_tab2 a').html('<i class="fa fa-unlock-alt"></i>' + tab2);

	var tab3 = $('#tab_register_tab3 a').html();
	$('#tab_register_tab3 a').html('<i class="fa fa-key"></i>' + tab3);

	var tab0 = $('#tab_project_tab_ui0 a').html();
	$('#tab_project_tab_ui0 a').html('<i class="fa fa-home"></i>' + tab0);

	var tab1 = $('#tab_project_tab_ui1 a').html();
	$('#tab_project_tab_ui1 a').html('<i class="fa fa-sitemap"></i>' + tab1);

	var tab2 = $('#tab_project_tab_ui2 a').html();
	$('#tab_project_tab_ui2 a').html('<i class="fa fa-user"></i>' + tab2);

	var tab3 = $('#tab_project_tab_ui3 a').html();
	$('#tab_project_tab_ui3 a').html('<i class="fa fa-plus"></i>' + tab3);

	// --- end menu ---

	
	if (cookflag == 'true') {
			sidew = $('.sidebar').width();
			planew = $('.windows_plane').width();
			sidew = parseInt(sidew);
			planew = parseInt(planew);
			var width = planew + sidew;
		$('.sidebar').css('width','60px');
		$('.windows_plane').css('width',width+'px');
		

			$('#register_tab ul li a').each(function(e){
				menuarr[e] = $(this).html();
				var mstr = $(this).html();
				var mre = /(<i.+?\/i>).+/ig;
				var mnewstr = mstr.replace(mre, "$1", "");
				$(this).html(mnewstr);
			});
			
			$('#project_tab_ui ul li a').each(function(e){
				menuarr[e] = $(this).html();
				var mstr = $(this).html();
				var mre = /(<i.+?\/i>).+/ig;
				var mnewstr = mstr.replace(mre, "$1", "");
				$(this).html(mnewstr);
			});

			$('#register_tab ul li i.fa').css('font-size', '25px');
		
			$('#logo_cl_img').css('height', '40px');
			$('#cloud_logo').css('padding', '10px 0 10px 20px');
			$('.sidebar').css('top', '60px');
			$('#register_tab').css('width','60px');
			$('#project_tab_ui').css('width','60px');
			
			flago = true;
	}
	
	// menu hide:
	
	$('#menubutton').click(function(){
		
		if (flago != true) {
			$('#topspan').hide();
		$('.sidebar').animate({'width':'60px'}, 500);
			sidew = $('.sidebar').width();
			planew = $('.windows_plane').width();
			sidew = parseInt(sidew);
			planew = parseInt(planew);
			var width = planew + sidew;

			$('#register_tab ul li a').each(function(e){
				menuarr[e] = $(this).html();
				var mstr = $(this).html();
				var mre = /(<i.+?\/i>).+/ig;
				var mnewstr = mstr.replace(mre, "$1", "");
				$(this).html(mnewstr);
			});
			
			$('#project_tab_ui ul li a').each(function(e){
				menuarr[e] = $(this).html();
				var mstr = $(this).html();
				var mre = /(<i.+?\/i>).+/ig;
				var mnewstr = mstr.replace(mre, "$1", "");
				$(this).html(mnewstr);
			});

			$('#register_tab ul li i.fa').css('font-size', '25px');
		
			
			$('.windows_plane').animate({'width':width+'px'}, 500);
			$('#logo_cl_img').css('height', '40px');
			$('#cloud_logo').css('padding', '10px 0 10px 20px');
			$('.sidebar').css('top', '60px');
			$('#register_tab').css('width','60px');
			$('#project_tab_ui').css('width','60px');
			$.cookie('flago', true);
			flago = true;
		} else {
			$('#topspan').show();
			$('#register_tab ul li a').each(function(e){
				 $(this).html(menuarr[e]);
			});

			$('#project_tab_ui ul li a').each(function(e){
				 $(this).html(menuarr[e]);
			});
			$('#cloud_logo').css('padding', '');

			$('#logo_cl_img').css('height', '');
			$('#menubutton').css('top','');
			$('.sidebar').css('top', '');
			$('.sidebar').css('width','');
			$('.windows_plane').css('width', '');
			$('#register_tab ul li i.fa').css('font-size', '16px');
			$('#register_tab').css('width','');
			$('#project_tab_ui').css('width','');
			$.cookie('flago', false);
			flago = false;
		}

	});

	// --- end menu hide ---

	// modal with info popup:
	
	$('.headinfo').click(function(){
		$('#modal-infoserv').modal();
	});
		
	// --- end modal with info popup ---

	// language flag changing:
	var lang = $('#langselect').val();
    
    switch(lang) {
    	case 'de':
    		$('#flagzone').attr('src', '/htvcenter/base/img/germany.png');
    	break;

    	case 'en':
    		$('#flagzone').attr('src', '/htvcenter/base/img/usa.png');
    	break;

    	case 'es':
    		$('#flagzone').attr('src', '/htvcenter/base/img/spain.png');
    	break;

    	case 'fr':
    		$('#flagzone').attr('src', '/htvcenter/base/img/france.png');
    	break;

    	case 'it':
    		$('#flagzone').attr('src', '/htvcenter/base/img/italia.png');
    	break;

    	case 'nl':
    		$('#flagzone').attr('src', '/htvcenter/base/img/niderland.png');
    	break;

    	default:
    		$('#flagzone').attr('src', '/htvcenter/base/img/no.png');
    	break;
    }

	// -- end language flag changing --

	 $('#avatarside').click(function(){
	 	 if ($('.dropdownwin').is(':visible') == true) {
	 	 	$('.dropdownwin').fadeOut();
	 	 } else {
	 	 	$('.dropdownwin').fadeIn();
	 	 }
	 });

	 $('.msgBox').click(function(){
	 	$(this).fadeOut();
	 });

	 var width = $(window).width()-$('.msgBox').width()-$('.sidebar').width()/2;

	 $('.msgBox').css('right', '-'+width+'px');


	 // range sliders:

		// disk:
		    
		var diskvalues = [];
		var diskselect = [];

		$('#cloud_disk_select option').each(function(){
			var value = $(this).val();
			var label = $(this).attr('label');

			diskvalues[value] = label;
			diskselect.push(value);
		});	

		var inpt = diskvalues[diskselect[0]];
		$('#valllgb').text(inpt);


		var l = diskselect.length;
		l = l - 1;
		var percent = (diskselect[l] - diskselect[0])/100;
		var ranger = {};

		diskselect.forEach(function(v, num){
			var localpercent = (v - diskselect[0])/percent;
			localpercent = Math.round(localpercent);
			v = parseInt(v);
			if (num == 0) {
				ranger['min'] = v;
			} else {
				if (num == l) {
					ranger['max'] = v;
				} else {
					var localrow = localpercent+'%';
					ranger[localrow] = v;
				}
			}
		});


		var startSlider = document.getElementById('sliderrr');

		if ($("#sliderrr").length > 0) {

			$("#sliderrr").noUiSlider({
				start: [ 20 ],
				connect : 'lower',
				snap: true,
				orientation: 'vertical',
				direction: 'rtl',
				range: ranger
			}).Link('lower').to($("#valll"));
			/*$(".demo-pips").noUiSlider_pips({
				mode: 'range',
				density: 5000
			});*/

			$("#sliderrr").on('slide', function(){
				var texto = $("#valll").text();
			    texto = parseInt(texto);
			    var labelo = diskvalues[texto];
			    $("#valllgb").text(labelo);
			    $('#cloud_disk_select_box').val(texto);
			    $('#cloud_disk_select').val(texto);
			    cloud_cost_calculator();
			});
		}
		// --- end disk ---

		// memory:
		    
		var diskvalues1 = [];
		var diskselect1 = [];

		$('#cloud_memory_select option').each(function(){
			var value = $(this).val();
			var label = $(this).attr('label');

			diskvalues1[value] = label;
			diskselect1.push(value);
		});	

		
		var inpt = diskvalues1[diskselect1[0]];
		$('#valllgb1').text(inpt);


		var l = diskselect1.length;
		l = l - 1;
		var percent1 = (diskselect1[l] - diskselect1[0])/100;
		var ranger1 = {};
		

		diskselect1.forEach(function(v, num){
			var localpercent = (v - diskselect1[0])/percent1;
			localpercent = Math.round(localpercent);
			v = parseInt(v);
			if (num == 0) {
				ranger1['min'] = v;
			} else {
				if (num == l) {
					ranger1['max'] = v;
				} else {
					var localrow = localpercent+'%';
					ranger1[localrow] = v;
				}
			}
		});

		
		var startSlider1 = document.getElementById('sliderrr1');

		if ($("#sliderrr1").length > 0) {

			$("#sliderrr1").noUiSlider({
					start: [ 0 ],
					connect : 'lower',
					snap: true,
					orientation: 'vertical',
					direction: 'rtl',
					range: ranger1
				}).Link('lower').to($("#valll1"));
			/*$(".demo-pips1").noUiSlider_pips({
				mode: 'range',
				density: 1000
			});*/

			$("#sliderrr1").on('slide', function(){
				var texto = $("#valll1").text();
			    texto = parseInt(texto);
			    var labelo = diskvalues1[texto];
			    $("#valllgb1").text(labelo);
			    $('#cloud_memory_select_box').val(texto);
			     $('#cloud_memory_select').val(texto);
			    cloud_cost_calculator();
			});
		}
		// --- end memory ---

		// cpu:
		    
		var diskvalues2 = [];
		var diskselect2 = [];

		$('#cloud_cpu_select option').each(function(){
			var value = $(this).val();
			var label = $(this).attr('label');

			diskvalues2[value] = label;
			diskselect2.push(value);
		});	

		var inpt = diskvalues2[diskselect2[0]];
		$('#valllgb2').text(inpt);


		var l = diskselect2.length;
		l = l - 1;
		var percent2 = (diskselect2[l] - diskselect2[0])/100;
		var ranger2 = {};

		diskselect2.forEach(function(v, num){
			var localpercent = (v - diskselect2[0])/percent2;
			localpercent = Math.round(localpercent);
			v = parseInt(v);
			if (num == 0) {
				ranger2['min'] = v;
			} else {
				if (num == l) {
					ranger2['max'] = v;
				} else {
					var localrow = localpercent+'%';
					ranger2[localrow] = v;
				}
			}
		});

		var startSlider2 = document.getElementById('sliderrr2');

		if ($("#sliderrr2").length > 0) {

			$("#sliderrr2").noUiSlider({
					start: [ 1 ],
					connect : 'lower',
					orientation: 'vertical',
					direction: 'rtl',
					snap: true,
					range: ranger2,
				}).Link('lower').to($("#valll2"));
			/*$(".demo-pips2").noUiSlider_pips({
				mode: 'range',
				density: 1000
			});*/

			$("#sliderrr2").on('slide', function(){
				var texto = $("#valll2").text();
			    texto = parseInt(texto);
			    var labelo = diskvalues2[texto];
			    $("#valllgb2").text(labelo);
			    $('#cloud_cpu_select_box').val(texto);
			    $('#cloud_cpu_select').val(texto);
			    cloud_cost_calculator();
			});
		}	


		// --- end cpu ---

// --- end range sliders ---
	 
});
