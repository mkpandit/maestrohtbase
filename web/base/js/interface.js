var remid = '';
var globalid = '';
var bufid = '';
var vmz = ''
var poolremid = '';
var poolremidhost = '';


$('.shortselect').selectpicker();
$('#reportmonthdash').hide();
$('#reportyeardash').hide();
$('#reportuserdash').hide();


// scroll:
$("html").niceScroll();
$('#mainnav').niceScroll({autohidemode: "leave", hidecursordelay: 400});
$("#alltasks").niceScroll();
$(".warningeventwindow").find('.eventcontent').niceScroll();
$('.criticaleventwindow').find('.eventcontent').niceScroll();
$('.alleventswindow').find('.eventcontent').niceScroll();
$('#dnstextarea').niceScroll();
$('#dhcptextarea').niceScroll();
$('#lizardside').find('iframe').niceScroll();
$('iframe').niceScroll();
// --- end scroll ---


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
				blackalert('Have not got any data of this period');
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
				blackalert('Have not got any data of this period');
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
		blackalert('Only integer number of percent value and not bigger, than 100, please');
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
				blackalert('Have not got any data of this period');
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
						blackalert('Have not got any data of this period');
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
						blackalert('Have not got any data of this period');
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
						blackalert('Have not got any data of this period');
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
						blackalert('Have not got any data of this period');
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
						blackalert('Have not got any data of this period');
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
						blackalert('Removed succesfully');
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
						console.log(data);
						bds = JSON.parse(data);
					} else {
						blackalert('Have not got any data of this period');
					}
				}
			});


	var servers = '<li class="carstart">'; 
	var i = 0;
	$.each(bds, function(key, serv) {
		console.log(serv);
		i = i + 1;
		servers = servers + '<div class="panel panel-primary panel-colorful budgservn" num="'+i+'" remname="'+serv.name+'" remid="'+serv.id+'"><div class="panel-body text-center"><p class="text-uppercase mar-btm text-sm">'+serv.name+'</p><i class="fa fa-credit-card fa-3x"></i><hr><p class="h2 text-thin"><a class="budgremove"><i class="fa fa-close"></i> Remove</a></p></div></div>';
		
		if (i == 9) {
			i = 0;
			servers = servers + '</li>';
		}		
	});


		
	if (i > 0) {
		$('.carbudgbtn').show();
	}

	$('#namespaces').html(servers);
	$('.carbudget').show();
	$('.jcarousel').jcarousel();
	
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
						blackalert('Have not got any data of this period');
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
	blackalert('Only integer number of percent value and not bigger, than 100, please');
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
						blackalert('Something wrong');
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

$('#reportuserdash').change(function(){
	changedashboard();
});

$('#reportyeardash').change(function(){
	changedashboard();
});


if (typeof(datepickeryep) != 'undefined' && datepickeryep == true) {
	$('.date').datepicker();
}


$('#uzerexpo').change(function(){
	var uzr = $('#uzerexpo').val();
	var url = '/cloud-fortis/user/index.php?report=yes';
	var dataval = 'explorer=1&explorerajax=getservers&uzer='+uzr;
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
						blackalert('Have not got any data of this period');
					}
				}
			});
	var servers = '<li class="carstart">'; 
	var i = 0;
	$.each(vms, function(key, serv){
		i = i + 1;
		servers = servers + '<div class="panel panel-primary panel-colorful servn" num="'+i+'"><div class="panel-body text-center"><p class="text-uppercase mar-btm text-sm">'+serv.name+'</p><i class="fa fa-desktop fa-3x"></i><hr><p class="h2 text-thin">'+serv.price+'</p></div></div>';
			
		if (i == 9) {
			i = 0;
			servers = servers + '</li>';
		}
			
	});
	
	$('#namespaces').html(servers);
});



if ( (typeof(inactivespl) != 'undefined') && (inactivespl == true) ) {

	//var uzr = $('#uzerexpo').val();
	var url = '/htvcenter/base/index.php?inactive=yes';
	var dataval = 'inactivespl=1&explorerajax=getinactive';
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
						blackalert('Have not got any data of this period');
					}
				}
			});
	var servers = '<li class="carstart">'; 
	var i = 0;
	$.each(vms, function(key, serv){
		i = i + 1;
		servers = servers + '<div class="panel panel-primary panel-colorful servn" num="'+i+'"><div class="panel-body text-center"><p class="text-uppercase mar-btm text-sm">'+serv.name+'</p><i class="fa fa-server fa-3x"></i><hr><p class="h2 text-thin">'+serv.days+'</p></div><div class="trashservn"><a class="trashers" servid="'+serv.servid+'"><i class="fa fa-trash-o"></i></a></div></div>';
			
		if (i == 9) {
			i = 0;
			servers = servers + '</li>';
		}
			
	});
	
	
	$('#namespaces').html(servers);
	$('.jcarousel').jcarousel();
}


if ( (typeof(explorer) != 'undefined') && (explorer == true) ) {

	var uzr = $('#uzerexpo').val();
	var url = '/cloud-fortis/user/index.php?report=yes';
	var dataval = 'explorer=1&explorerajax=getservers&uzer='+uzr;
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
						//console.log(vms);
					} else {
						blackalert('Have not got any data of this period');
					}
				}
			});
	var servers = '<li class="carstart">'; 
	var i = 0;
	$.each(vms, function(key, serv){
		i = i + 1;
		servers = servers + '<div class="panel panel-primary panel-colorful servn" num="'+i+'"><div class="panel-body text-center"><p class="text-uppercase mar-btm text-sm">'+serv.name+'</p><i class="fa fa-desktop fa-3x"></i><hr><p class="h2 text-thin">'+serv.price+'</p></div></div>';
			
		if (i == 9) {
			i = 0;
			servers = servers + '</li>';
		}
			
	});
	
	$('#namespaces').html(servers);
	$('.jcarousel').jcarousel();
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

		if ( $('#statusexp').text() == 'inactive') {
			color = '#ff5800';
		}

		if ( $('#statusexp').text() == 'removed') {
			color = '#cc6165';
		}

		$('#statusexp').css('background-color', color);

		if (typeof(inactivespl) != 'undefined') {
			var butto = '<a class="btn btn-primary removeservina" idserv="'+vms[index].servid+'">Remove Server</a>';
			$('.removbtnplace').html('');
			$('.removbtnplace').html(butto);
		}
	}
});


$('#addnagiose').click(function(e){
	e.preventDefault();
		var text = $(this).text();
		
			var url = 'index.php?plugin=nagios3&controller=nagios3-services&nagios3_action=add';

	  		$('#storageformnagios').load(url+" form", function(){
	  			
	  			$('#storageformnagios select').selectpicker();
	  			$('#storageformnagios select').hide();
	  			$('#storageformnagios .selectpicker');
	  			$('.lead').hide();
	  			$('#volumepopupnagios').show();
	  		});  		
			
});

$('#volumepopupnagiosclose').click(function(){
	$('#volumepopupnagios').hide();
});

// CLOUD NEW

	$('.cloud').find('#tab_project_tab1').find('a').click(function(e){
		e.preventDefault();
		var text = $(this).text();
		if (text == 'New') {
			var url = 'index.php?project_tab=1&plugin=cloud&controller=cloud-usergroup&cloud_usergroup=insert';

	  		$('#storageformz').load(url+" form", function(){
	  			
	  			$('#storageformz select').selectpicker();
	  			$('#storageformz select').hide();
	  			$('#storageformz .selectpicker')
	  			$('.lead').hide();
	  			$('#volumepopupz').show();
	  		});  		
			
		}
	});


	$('.mailmenulink').click(function(e){
		e.preventDefault();
		
			var url = 'index.php?plugin=cloud&controller=cloud-mail';
			$('table').find("input[type=checkbox]").hide();
	  		$('#storageformzmail').load(url+" #themailform", function(){
	  			
	  			$('#storageformzmail select').selectpicker();
	  			$('#storageformzmail select').hide();
	  			$('#storageformzmail .selectpicker')
	  			$('.lead').hide();
	  			$('#volumepopupzmail').show();

	  		});  		
			
		
	});

// CLouD NEW END
	
	//Role Management Popup
	$('.role-admin-about').click(function(){
		var url = 'index.php?plugin=role-administration&controller=role-administration-about';
		$('table').find("input[type=checkbox]").hide();
	  	$('#storageformzmail').load(url+" #role_administration_about_tab0", function(){		
			$('#storageformzmail select').selectpicker();
			$('#storageformzmail select').hide();
			$('#storageformzmail .selectpicker')
			$('.lead').hide();
			$("#volumepopupzmail").find("ul.wz-classic li a").remove();
			$("#volumepopupzmail").find("ul.wz-classic li").html("<p>&nbsp;</p><p>&nbsp;</p>");
			$('#volumepopupzmail').show();
		});
		return false;
	});
	
	

$('#volumepopupclose').click(function(){
	$('#volumepopup').hide();
});

$('#volumepopupzclose').click(function(){
	$('#volumepopupz').hide();
});

$('#volumepopupclosewmware').click(function(){
	$('#volumepopupwmware').hide();
});


	$('#vmwaddvswitch').click(function(e){
		e.preventDefault();
		
			var url = $(this).find('a').attr('href');
			console.log(url);
	  		$('#storageformvmware').load(url + '#vmware_esx_vs_tab2 form', function(){
	  			$('#storageformvmware').addClass('hidefirstform');
	  			$('#storageformvmware select').selectpicker();
	  			$('#storageformvmware select').hide();
	  			$('#storageformvmware .selectpicker');
	  			$('.lead').hide();
	  			$('#volumepopupwmware').show();

	  		});  		
			
		
	});


	$('#vmwareaddportgroup').click(function(e){
			e.preventDefault();
		
			var url = $(this).find('a').attr('href');
			console.log(url);
	  		$('#storageformvmware').load(url + '#vmware_esx_vs_tab3 form', function(){
	  			$('#storageformvmware').addClass('hidefirstform');
	  			$('#storageformvmware select').selectpicker();
	  			$('#storageformvmware select').hide();
	  			$('#storageformvmware .selectpicker');
	  			$('.lead').hide();
	  			$('#volumepopupwmware').show();

	  		});  	
	});

$('#volumepopupzmailclose').click(function(){
	$('#volumepopupzmail').hide();
	$('table').find("input[type=checkbox]").show();
});

$('body').on('click', '.removeservina', function(e){
	e.preventDefault();
	var id = $(this).attr('idserv');
	var url = 'index.php?base=appliance&resource_filter=&appliance_action=select&resource_filter=&resource_type_filter=&appliance%5Bsort%5D=appliance_id&appliance%5Border%5D=ASC&appliance%5Boffset%5D=0&appliance%5Blimit%5D=20&appliance_identifier%5B%5D='+id+'&appliance_action%5Bremove%5D=remove';


	  		$('#storageform').load(url+" form", function(){
	  			
	  			$('#storageform select').selectpicker();
	  			$('#storageform select').hide();
	  			$('#storageform .selectpicker')
	  			$('#volumepopup').show();
	  		});  		
});


$('body').on('click', '.trashers', function(e){
	e.preventDefault();
	var id = $(this).attr('servid');
	var url = 'index.php?base=appliance&resource_filter=&appliance_action=select&resource_filter=&resource_type_filter=&appliance%5Bsort%5D=appliance_id&appliance%5Border%5D=ASC&appliance%5Boffset%5D=0&appliance%5Blimit%5D=20&appliance_identifier%5B%5D='+id+'&appliance_action%5Bremove%5D=remove';


	  		$('#storageform').load(url+" form", function(){
	  			
	  			$('#storageform select').selectpicker();
	  			$('#storageform select').hide();
	  			$('#storageform .selectpicker')
	  			$('#volumepopup').show();
	  		});  		
});


$('body').on('click', '.pooltrashers', function(e){
	e.preventDefault();

	var id = $(this).attr('servid');
	poolremid = id;
	var name = $(this).closest('.servnpool').find('.servnm').text();	
	$('#confirmid').text(id);
	$('#confirmname').text(name);
	
	
	$('#trasherzconfirm').show();
});

$('body').on('click', '.poolhosttrashers', function(e){
	e.preventDefault();

	var id = $(this).attr('servid');
	var poolid = $(this).attr('poolid');
	poolremidhost = id;
	poolremid = poolid;
	var name = $(this).closest('.servnpoolhost').find('.servnm').text();	
	$('#confirmidhost').text(id);
	$('#confirmidpool').text(poolid);
	$('#confirmnamehost').text(name);
	
	
	$('#trasherzconfirmhost').show();
});

$('.configpopupclose').click(function(){
	$('#configpopup').hide();
});

$('body').on('click', '.configpop', function(e){
		e.preventDefault();
			var url = $(this).attr('href');
			console.log(url);
	  		$('#configpopupform').load(url + ' #configure_plugin', function(){
	  			//$('#configpopupform').addClass('hidefirstform');
	  			$('#configpopupform select').selectpicker();
	  			$('#configpopupform select').hide();
	  			$('.lead').hide();
	  			$('#configpopupform').niceScroll();
	  			$('#configpopup').show();
	  		});  		
});



$('#remtrasherzbtn').click(function(e){
	e.preventDefault();
	wait();

	var url = '/htvcenter/base/index.php?base=appliance&hostpools=true&query=query';
	var dataval = 'action=rempool&id='+poolremid;
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
						
						blackalert('Removed successfully!');
						location.reload();
					}
				}
			});
	
	$('#trasherzconfirm').hide();
});

$('#remtrasherzbtnhost').click(function(e){
	e.preventDefault();
	wait();

	var url = '/htvcenter/base/index.php?base=appliance&hostpools=true&query=query';
	var dataval = 'action=remhostpool&poolid='+poolremid+'&hostid='+poolremidhost;
	
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
						
						blackalert('Removed successfully!');
						location.reload();
					}
				}
			});
	
	$('#trasherzconfirmhost').hide();
});

$('#noremtrasherzbtn').click(function(e){
	e.preventDefault();
	$('#trasherzconfirm').hide();
	$('#trasherzconfirmhost').hide();
});

$('#trasherzconfirmclose').click(function(e){
	e.preventDefault();
	$('#trasherzconfirm').hide();
	$('#trasherzconfirmhost').hide();
});

$('#noremtrasherzbtnhost').click(function(e){
	e.preventDefault();
	$('#trasherzconfirm').hide();
	$('#trasherzconfirmhost').hide();
});

$('#trasherzconfirmclosehost').click(function(e){
	e.preventDefault();
	$('#trasherzconfirm').hide();
	$('#trasherzconfirmhost').hide();
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
	var user = $('#reportuserdash').val();
	givedashboard(monthd, yeard, user);
}



$('.billcsvdownload').click(function(){
		
		var year = $('#yearzrep').val();
		var month = $('#monzrep').val();
		var user = $('#uzerrep').val();
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
						blackalert('Have not got any data of this period');
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
	$('.printlogo').show();
	$('.hideslider').show();
	$('#mainnav-container').hide();
	$('.navbar-content').hide();
	$('#aside').hide();
	$('#navbar').hide();
	$('#home_container').css('position', 'relative');
	$('#home_container').css('top', '-1px');
	$('#home_container').css('left', '-1px');
	$('.gobackprint').show();
	window.print();
	location.reload();
});

$('.printdash').click(function(){
	$('#cloud_top_menu').hide();
	$('.sidebar').hide();
	$('.navbar-content').hide();
	$('#aside').hide();
	$('#navbar').hide();
	$('.windows_plane').css('float','left');
	$('.windows_plane').css('width', '100%');
	$('#cloud-content').css('width', '100%');

	$('.reportbtnbill').hide();
	$('.printlogo').show();
	$('#mainnav-container').hide();
	$('#home_container').css('position', 'relative');
	$('#home_container').css('top', '-50px');
	$('#diagramsreport').css('width', '100%');
	$('#diagramsreport').css('top', '290px');
	$('#diagramsreport').css('position', 'absolute');
	$('.barchartsside').css('width', '40%');
	$('.donutside').css('width', '40%');
	$('.donutside').css('float', 'right');
	$('#home_container').css('left', '-120px');
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

 if (typeof(diagramshow) == 'undefined') {


	var month = $('#monzrep').val();
	var year = $('#yearzrep').val();
	var user = $('#uzerrep').val();
	

	if ($('.lead').is(':visible') == 'false') {
		wait();
	}
	
	var url = '/cloud-fortis/user/index.php?report=yes';
	var dataval = 'year='+year+'&month='+month+'&forbill=1&userdash='+user;
	
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
						blackalert('Have not got any data of this period');
					}
					
				}
			});
	}
});


$('#report').click(function(){
	$('#reportdropdown').show();
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


	// close popup alert:

$('.msgBox .close').click(function(){
	$(this).closest('.msgBox').hide();
});

	// -- end close popup alert ---



$(document).ready(function() {


$('#OCH_edit').find('.migrate').click(function(e){
	e.preventDefault();

	var url = $(this).attr('href');

	  		$('#storageformmigr').load(url+" form", function(){
	  			$('#storageformmigr').addClass('hidefirstform');
	  			$('#storageformmigr select').selectpicker();
	  			$('#storageformmigr select').hide();
	  			$('#storageformmigr .selectpicker');
	  			$('.lead').hide();
	  			$('#volumepopupmigr').show();
	  		});  	

});

$('#volumepopupmigrclose').click(function(){
	$('#volumepopupmigr').hide();
});
	

//OCH:
$('.voladdoch a').click(function(e){
	e.preventDefault();
		
			var url = $(this).attr('href');

	  		$('#storageformochvolg').load(url+" form", function(){
	  			
	  			$('#storageformochvolg select').selectpicker();
	  			$('#storageformochvolg select').hide();
	  			$('#storageformochvolg .selectpicker');
	  			$('.lead').hide();
	  			$('#storageformoch').show();
	  		});  		
			
});

$('#ochvolgr input.submit').click(function(e){
	e.preventDefault();
	var text = $(this).val();
	var storageid = $('#storageid').text();
			
	var dsids = '';
	$('.checkbox').each(function(){
		if( $(this).is(":checked") == true) {
			var name = $(this).val();
			dsids = dsids + '&&OCH_identifier[]=' + name;
		}
	});
	var poststring = 'base=storage&storage_filter=&splugin=OCH&scontroller=OCH&storage_action=load&storage_id='+storageid+'&volgroup=storage1&OCH_action=volgroup'+dsids+'&OCH_action[remove]=remove';
	var urlstring = 'index.php';


			if (text == 'remove') {
				console.log('here');
		  		$('#storageformochremvolg').load(urlstring+" form", poststring, function(){
		  			
		  			$('#storageformochremvolg select').selectpicker();
		  			$('#storageformochremvolg select').hide();
		  			$('#storageformochremvolg .selectpicker');
		  			$('#storageformochremvolg .form-horizontal').remove();
		  			$('.lead').hide();
		  			$('#storageformochrem').show();
		  		});  		
	  		}
			
});

$('#storageformochclose').click(function(){
	$('#storageformoch').hide();
});

$('#storageformochremclose').click(function(){
	$('#storageformochrem').hide();
});

// --- end OCH;


// vmware start, stop, remove:

$('#RemRemRem').find("input[value='remove']").click(function(e){
	e.preventDefault();

	wait();
	var aplid = $('#aplidooo').text();
	
	var dsids = '';
	$('.checkbox').each(function(){
		if( $(this).is(":checked") == true) {
			var name = $(this).val();
			dsids = dsids + '&vmw_esx_id[]=' + name;
		}
	});

	var poststring = 'plugin=vmware-esx&controller=vmware-esx-vm&appliance_id='+aplid+'&vmware_esx_vm_action=edit'+dsids+'&vmware_esx_vm_action[remove]=remove';
	//var poststring = 'plugin=hyperv&controller=hyperv-vm&appliance_id='+aplid+'&hyperv_vm_action=edit'+dsids+'&hyperv_vm_action[stop]=stop';
	var urlstring = 'index.php';
	$('#storageformrem').load(urlstring+" form", poststring, function(){
		  			$('.lead').hide();
		  			$('#storageformrem select').selectpicker();
		  			$('#storageformrem select').hide();
	  				$('#volumepopuprem').show();
		 }); 
});



$('#RemRemRem').find("input[value='start']").click(function(e){
	e.preventDefault();

	wait();
	var aplid = $('#aplidooo').text();
	
	var dsids = '';
	$('.checkbox').each(function(){
		if( $(this).is(":checked") == true) {
			var name = $(this).val();
			dsids = dsids + '&vmw_esx_id[]=' + name;
		}
	});

	var poststring = 'plugin=vmware-esx&controller=vmware-esx-vm&appliance_id='+aplid+'&vmware_esx_vm_action=edit'+dsids+'&vmware_esx_vm_action[start]=start';
	//var poststring = 'plugin=hyperv&controller=hyperv-vm&appliance_id='+aplid+'&hyperv_vm_action=edit'+dsids+'&hyperv_vm_action[stop]=stop';
	var urlstring = 'index.php';
	$('#storageformrem').load(urlstring+" form", poststring, function(){
		  			$('.lead').hide();
		  			$('#storageformrem select').selectpicker();
		  			$('#storageformrem select').hide();
	  				$('#volumepopuprem').show();
		 }); 
});



$('#RemRemRem').find("input[value='stop']").click(function(e){
	e.preventDefault();

	wait();
	var aplid = $('#aplidooo').text();
	
	var dsids = '';
	$('.checkbox').each(function(){
		if( $(this).is(":checked") == true) {
			var name = $(this).val();
			dsids = dsids + '&vmw_esx_id[]=' + name;
		}
	});

	var poststring = 'plugin=vmware-esx&controller=vmware-esx-vm&appliance_id='+aplid+'&vmware_esx_vm_action=edit'+dsids+'&vmware_esx_vm_action[stop]=stop';
	//var poststring = 'plugin=hyperv&controller=hyperv-vm&appliance_id='+aplid+'&hyperv_vm_action=edit'+dsids+'&hyperv_vm_action[stop]=stop';
	var urlstring = 'index.php';
	$('#storageformrem').load(urlstring+" form", poststring, function(){
		  			$('.lead').hide();
		  			$('#storageformrem select').selectpicker();
		  			$('#storageformrem select').hide();
	  				$('#volumepopuprem').show();
		 }); 
});
// --- end vmware start, stop, remove ---


// hyperv list remove:
$('.listb').find("input[value='remove']").click(function(e){
	e.preventDefault();

	wait();
	var aplid = $('#appido').text();
	var volgripid = $('form').find("input[name='volgroup']").val();

	var dsids = '';
	$('.checkbox').each(function(){
		if( $(this).is(":checked") == true) {
			var name = $(this).val();
			dsids = dsids + '&hyperv_ds_id[]=' + name;
		}
	});

	
	var poststring = 'plugin=hyperv&controller=hyperv-ds&appliance_id='+aplid+'&volgroup='+volgripid+'&hyperv_ds_action=volgroup'+dsids+'&hyperv_ds_action[remove]=remove';
	var urlstring = 'index.php';
	$('#storageformrem').load(urlstring+" form", poststring, function(){
		  			$('.lead').hide();
		  			$('#storageformrem select').selectpicker();
		  			$('#storageformrem select').hide();
	  				$('#volumepopuprem').show();
		 }); 
});

$('#volumepopupremclose').click(function(){
	$('#volumepopuprem').hide();
});

// --- end hyperv list remove ---

$('.listhv').find("input[value='remove']").click(function(e){
	e.preventDefault();

	wait();
	var aplid = $('#aplidoo').text();
	
	var dsids = '';
	$('.checkbox').each(function(){
		if( $(this).is(":checked") == true) {
			var name = $(this).val();
			dsids = dsids + '&hyperv_id[]=' + name;
		}
	});

	
	var poststring = 'plugin=hyperv&controller=hyperv-vm&appliance_id='+aplid+'&hyperv_vm_action=edit'+dsids+'&hyperv_vm_action[remove]=remove';
	var urlstring = 'index.php';
	$('#storageformrem').load(urlstring+" form", poststring, function(){
		  			$('.lead').hide();
		  			$('#storageformrem select').selectpicker();
		  			$('#storageformrem select').hide();
	  				$('#volumepopuprem').show();
		 }); 
});


$('.listhv').find("input[value='start']").click(function(e){
	e.preventDefault();

	wait();
	var aplid = $('#aplidoo').text();
	
	var dsids = '';
	$('.checkbox').each(function(){
		if( $(this).is(":checked") == true) {
			var name = $(this).val();
			dsids = dsids + '&hyperv_id[]=' + name;
		}
	});

	
	var poststring = 'plugin=hyperv&controller=hyperv-vm&appliance_id='+aplid+'&hyperv_vm_action=edit'+dsids+'&hyperv_vm_action[start]=start';
	var urlstring = 'index.php';
	$('#storageformrem').load(urlstring+" form", poststring, function(){
		  			$('.lead').hide();
		  			$('#storageformrem select').selectpicker();
		  			$('#storageformrem select').hide();
	  				$('#volumepopuprem').show();
		 }); 
});


$('.listhv').find("input[value='stop']").click(function(e){
	e.preventDefault();

	wait();
	var aplid = $('#aplidoo').text();
	
	var dsids = '';
	$('.checkbox').each(function(){
		if( $(this).is(":checked") == true) {
			var name = $(this).val();
			dsids = dsids + '&hyperv_id[]=' + name;
		}
	});

	
	var poststring = 'plugin=hyperv&controller=hyperv-vm&appliance_id='+aplid+'&hyperv_vm_action=edit'+dsids+'&hyperv_vm_action[stop]=stop';
	var urlstring = 'index.php';
	$('#storageformrem').load(urlstring+" form", poststring, function(){
		  			$('.lead').hide();
		  			$('#storageformrem select').selectpicker();
		  			$('#storageformrem select').hide();
	  				$('#volumepopuprem').show();
		 }); 
});

// hyperv host start\stop\remove:



// --- end hyperv start\stop\remove ---

$('.clonera').click(function(){
	wait();
	var img = $(this).closest('tr').find('.imagebtn').text();
	var storageid = $('#storagekvmid').text();
	var urlstring = 'index.php?base=storage&storage_filter=&splugin=kvm&scontroller=kvm&storage_action=load&storage_id='+storageid+'&volgroup=storage1&kvm_action=clone&lvol='+img;

	$('#storageformvmf').load(urlstring+" form", function(){
		  			$('.lead').hide();
		  			$('#actionvmf').html('<div class="alaction"><label>Action:</label> <div class="alcontent"><i class="fa fa-clone"></i> Clone </div><br/></div>');
		  			$('#storageformvmf select').selectpicker();
		  			$('#storageformvmf select').hide();
	  				$('#volumepopupvmf').show();
		 }); 
});

$('.protera').click(function(){
	wait();
	var img = $(this).closest('tr').find('.imagebtn').text();
	var storageid = $('#storagekvmid').text();
	var urlstring = 'index.php?base=storage&storage_filter=&splugin=kvm&scontroller=kvm&storage_action=load&storage_id='+storageid+'&volgroup=storage1&kvm_action=snap&lvol='+img;

	$('#storageformvmf').load(urlstring+" form", function(){
		  			$('.lead').hide();
		  			$('#actionvmf').html('<div class="alaction"><label>Action:</label> <div class="alcontent"><i class="fa fa-files-o"></i> Snapshot </div><br/></div>');
		  			$('#storageformvmf select').selectpicker();
		  			$('#storageformvmf select').hide();
	  				$('#volumepopupvmf').show();
		 }); 
});



$('#volumepopupclosevmf').click(function(){
	$('#volumepopupvmf').hide();
});

$('#kvm_vm_tab1').find('td.action').find('a.clone').click(function(e){
	e.preventDefault();
	var aplid = $('#kvmkvmid').text();
	var vmname = $(this).closest('tr').find('.kvmkvmname').text();
	var mac = $(this).closest('tr').find('.kvmkvmmac').text();


	var urlstring = 'index.php?plugin=kvm&controller=kvm-vm&appliance_id='+aplid+'&kvm_vm_action=clone&vm='+vmname+'&mac='+mac;
	
	$('#storageform').load(urlstring+" form", function(){
		  			$('.lead').hide();
		  			$('#storageform select').selectpicker();
		  			$('#storageform select').hide();
	  				$('#volumepopup').show();
	}); 
});



$('#demo-set-btn').hide();


$('body').on('click', '#showpooldetail', function(){
	$('#hostpoolcontent').hide();
	$('#poolsdashboard').show();

	$('#poolstoragedash').html('');
	$('#poolcpudash').html('');
	$('#poolmemorydash').html('');
	
	// --- CPU POOL ---
			Morris.Donut({
				element: 'poolcpudash',
				data: [
					{label: "Free", value: 70},
					{label: "Used", value: 30}
					
				],
				colors: [
					'#a6c600',
					'#177bbb'
				],
				resize:true
			});

		// --- CPU POOL END ---

		// --- Memory POOL ---
			Morris.Donut({
				element: 'poolmemorydash',
				data: [
					{label: "Free", value: 70},
					{label: "Used", value: 30}
					
				],
				colors: [
					'#a6c600',
					'#177bbb'
				],
				resize:true
			});

		// --- memory POOL END ---


		// --- storage POOL ---
			Morris.Donut({
				element: 'poolstoragedash',
				data: [
					{label: "Free", value: 70},
					{label: "Used", value: 30}
					
				],
				colors: [
					'#a6c600',
					'#177bbb'
				],
				resize:true
			});

		// --- storage POOL END ---


});

$('body').on('click', '#closepooldetail', function(){
	$('#poolsdashboard').hide();
	$('#hostpoolcontent').show();
});

$('#crpoolbtn').click(function(){
	
	$('#schmultiselectpool_to').find('option').each(function(){
		$(this).prop('selected', true);
	});

	var action = 'addpool';
	var servers = $('#schmultiselectpool_to').val();
	var name = $('#poolnameinput').val();
	wait();

	if (servers != null) {
		var poststring = 'action='+action+'&servers='+servers+'&name='+name;
		
		var urlstring = '/htvcenter/base/index.php?base=appliance&hostpools=true&query=query';
		wait();
			
			$.ajax({
			  type: 'POST',
			  url: urlstring,
			  data: poststring,
		
			  success: function(data){
			  	$('.lead').hide();

			  
			  		alert(data);
			  		$('#poolservpopup').hide();
			  		location.reload(); 
			  		//blackalert('Some errors, please, try again or read error logs!');
			  
			    
			  }
			});
	}

});



if ( (typeof(hostpools) != 'undefined') && (hostpools == true) ) {
	$('#hostpoolcontent').show();
	//var uzr = $('#uzerexpo').val();
	var url = '/htvcenter/base/index.php?base=appliance&hostpools=true&query=query';
	var dataval = 'action=getpools';
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
						//console.log(vms);
						vmz = vms;
					} else {
						blackalert('Have not got any data of this period');
					}
				}
			});
	var servers = '<li class="carstart">'; 
	var i = 0;
	$.each(vms, function(key, serv){
		i = i + 1;
		servers = servers + '<div class="panel panel-primary panel-colorful servnpool" num="'+i+'"><div class="panel-body text-center"><p class="text-uppercase mar-btm text-sm servnm">'+serv.name+'</p><i class="fa fa-connectdevelop fa-3x"></i><hr><p class="h2 text-thin">'+serv.count+'</p></div><div class="trashservnpool"><a class="pooltrashers" servid="'+serv.id+'"><i class="fa fa-trash-o"></i></a></div></div>';
			
		if (i == 9) {
			i = 0;
			servers = servers + '</li>';
		}
			
	});

	

	
	
	$('#namespaces').html(servers);
	$('.jcarousel').jcarousel();
}

var vmzname = '';

$('body').on('click', '.servnpool', function(){
	if (typeof(budgetpage) == 'undefined') {
		$('.servnpool').css('border', 'none');
		$(this).css('border', '3px solid #cc6165');
		var index = $(this).attr('num');
		$('#cpuexp').html(vmz[index].cpu);
		$('#memoryexp').html(vmz[index].ram);
		$('#statusexp').html(vmz[index].status);
		$('#storageexp').html(vmz[index].storage);
		$('#creationexp').html(vmz[index].created);
		$('#timeexp').html(vmz[index].worked);
		$('#creationexp').html(vmz[index].created);
		$('#totalexp').html(vmz[index].price);

		vmzname = vmz[index].name;
			var butto = '<a class="btn btn-primary removepoolina" servid="'+vmz[index].id+'">Remove Host Pool</a>';
			$('.removbtnplace').html('');
			$('.removbtnplace').html(butto);
			$('#showpooldetail').show();


		
		
		var a =1;
		var b = vmz[index].count;
		b = parseInt(b);
		var hostsh = '';
		for (a = 1; a<=b; a++) {
			var memh = vmz[index].hosts[a].memory;
			var cpuh = vmz[index].hosts[a].cpu;
			var nameh = vmz[index].hosts[a].name;
			var virth = vmz[index].hosts[a].hypervisor;
			var hostsid = vmz[index].hosts[a].idhost;
			var poolidh = vmz[index].id;

			if (cpuh == null) {
				cpuh = 0;
			}

			hostsh = hostsh + '<div class="panel panel-primary panel-colorful servnpoolhost" num="'+a+'"><div class="panel-body text-center"><p class="text-uppercase mar-btm text-sm servnm">'+nameh+'</p><i class="fa fa-server fa-3x"></i><hr><p class="h22 text-thin"> CPU: '+cpuh+' <br/> Memory: '+memh+' MB<br/> Hypervisor: '+virth+'</p></div><div class="trashservnpool"><a class="poolhosttrashers" servid="'+hostsid+'" poolid="'+poolidh+'"><i class="fa fa-trash-o"></i></a></div></div>';
		
		}
		

		$('#hostsplace').html(hostsh);
	}
});



$('body').on('click', '.removepoolina', function(e){
	e.preventDefault();

	var id = $(this).attr('servid');
	poolremid = id;
	var name = vmzname;	
	$('#confirmid').text(id);
	$('#confirmname').text(name);
	
	
	$('#trasherzconfirm').show();
});

$('#hostpoolcrt').click(function(){
	$('#poolservpopup').show();
});

$('#poolserverpopupclose').click(function(){
	$('#poolservpopup').hide();
});

$('#schmultiselectpool').multiselect();



$('.editippopupo').click(function(e){
	e.preventDefault();
		var urlstring = $(this).attr('href');
		$('#storageform').load(urlstring+" form", function(){
					$('#storageform').addClass('hidefirstform');
		  			$('.lead').hide();
		  			$('#storageform select').selectpicker();
		  			$('#storageform select').hide();
	  				$('#volumepopup').show();
		 }); 
})



	$('#hyperv_ds_tab2').find('td.action').find('a.clone').click(function(e){
		e.preventDefault();
		var urlstring = $(this).attr('href');
		$('#storageform').load(urlstring+" form", function(){
		  			$('.lead').hide();
		  			$('#storageform select').selectpicker();
		  			$('#storageform select').hide();
	  				$('#volumepopup').show();
		 }); 
	});
	
	var width = $(document).width();
	

	if (width > 1309) {
		$('.shorto').css('width', '80px');
	}
	$('.shorto').change(function(){
		var user = $('#reportuserdashmain').val();
		var yeard = $('#reportyeardashmain').val();
		var monthd = $('#reportmonthdashmain').val();

		
		var selecttext = $(this).find("option:selected").text();

		if ( $(this).attr('id') == 'reportuserdashmain') {
			$("button").each(function(){
				if ( $(this).attr('data-id') == "reportuserdashmain") {
					$(this).find('.filter-option').text(selecttext);
				}
			});
		}

		if ( $(this).attr('id') == 'reportmonthdashmain') {
			$("button").each(function(){
				if ( $(this).attr('data-id') == "reportmonthdashmain") {
					$(this).find('.filter-option').text(selecttext);
				}
			});
		}

		if ( $(this).attr('id') == 'reportyeardashmain') {
			$("button").each(function(){
				if ( $(this).attr('data-id') == "reportyeardashmain") {
					$(this).find('.filter-option').text(selecttext);
				}
			});
		}

			
			var month = '';
			if (monthd == '0') {
				var month = 'Jan';
			}

			if (monthd == '1') {
				var month = 'Feb';
			}

			if (monthd == '2') {
				var month = 'Mar';
			}

			if (monthd == '3') {
				var month = 'Apr';
			}

			if (monthd == '4') {
				var month = 'May';
			}

			if (monthd == '5') {
				var month = 'Jun';
			}

			if (monthd == '6') {
				var month = 'Jul';
			}

			if (monthd == '7') {
				var month = 'Aug';
			}

			if (monthd == '8') {
				var month = 'Sep';
			}

			if (monthd == '9') {
				var month = 'Oct';
			}

			if (monthd == '10') {
				var month = 'Nov';
			}

			if (monthd == '11') {
				var month = 'Dec';
			}
			var url = '/cloud-fortis/user/index.php?report=yes';
			var dataval = 'year='+yeard+'&month='+month+'&forbill=1&userdash='+user;
			
	
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
						$('#mval').text(data.all);
						$('#totalamauntmain').show();
					} else {
						blackalert('Have not got any data of this period');
					}
					
				}
			});


		$('#donutrendermaino').html('');
		givedashboard(monthd, yeard, user);


		
	});

	setInterval(function(){ 

													$("button[data-id='reportuserdashmain']").each(function(i){
													  if (i > 0) {
													    $(this).remove();
													  }
													});
													

										
													$("button[data-id='reportmonthdashmain']").each(function(j){
													  if (j > 0) {
													    $(this).remove();
													  }
													});
											

											
													$("button[data-id='reportyeardashmain']").each(function(k){
													  if (k > 0) {
													    $(this).remove();
													  }
													});

													

												 }, 2000);

	



$('table#cloud_images').addClass('Tabelle');
$('#cloud_appliances tr').find('.pill').each(function(){
		color = '#5fa2dd';
		vlasso = 'panel-primary';
		
		if ( $(this).text() == 'active' ) {
			color = '#9cc96b';
			vlasso = 'panel-success';
		}

		
		$(this).closest('tr').addClass('panel-bordered panel '+vlasso);
		$(this).css('background-color', color);
		
});

// fortis order:

var detailtable = '';

$('#reportuser').selectpicker();
$('#reportmonth').selectpicker();
$('#reportyear').selectpicker();

$('#reportuser').hide();
$('#reportmonth').hide();
$('#reportyear').hide();


$('#orderreport').click(function(){
	
	var user = $(this).closest('#popupform').find('#reportuser').val();
	var month = $(this).closest('#popupform').find('#reportmonth').val();
	var year = $(this).closest('#popupform').find('#reportyear').val();


	if ($('.lead').is(':visible') == 'false') {
		wait();
	}
	
	var url = 'index.php?report=yes';
	var dataval = 'year='+year+'&month='+month+'&user='+user;
	$('#popup').hide();
	$('.lead').hide();
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
						blackalert('Have not got any data of this period');
					}
					
				}
			});
});

$('body').on('click', '#detailreport', function(){
	$('.lead').hide();
	$('#popup').hide();
	$('#popuptableform').html(detailtable);
	$('#popuptable').show();
});

$('#order').click(function(){
	$('#popup').show();
});

$('#popupclose').click(function(){
	$('.lead').hide();
	$('#popup').hide();
});

$('#popuptableclose').click(function(){
	$('.lead').hide();
	$('#popuptable').hide();
});

// --- end fortis order ---


$('#kvm_tab2').find('#Tabelle').find('.actiontable').find('.submit').click(function(e){

	e.preventDefault();
		var identifiers = '';
  		var first = true;
  		var checkboxyes = false;
  		$('.checkbox').each(function(){
  			if( $(this).is(":checked") == true) {
  				checkboxyes = true;
  				if (first != true) {
  					identifiers = identifiers + '&';
  				}
  				var aplid = $(this).val();
  				console.log(aplid);
  				identifiers = identifiers + 'kvm_identifier%5B%5D='+aplid;
  				first = false;
  			}
  		});

  		if (checkboxyes == true) {
		    var storageid = $('#storageid').text();
		    var volgroup = $('#storagecount').text();
			var urlstring = 'index.php?base=storage&kvm_action=volgroup&kvm_action%5Bremove%5D=delete&'+identifiers+'&scontroller=kvm&splugin=kvm&storage_action=load&storage_id='+storageid+'&volgroup='+volgroup;
			
			$('#storageform').load(urlstring+" #kvm_tab3", function(){
		  			$('.lead').hide();
		  			$('#storageform select').selectpicker();
		  			$('#storageform select').hide();
	  				$('#volumepopup').show();
		  		}); 
		} else {
			blackalert('Select volume');
		}
});


$('.thetableee').find('.actiontable').find('.submit').click(function(e){ 
				var count = $('.checkbox:checked').length;
				
				e.preventDefault();
				$('.lead').hide();
		  		var actionz = $(this).attr('value');
		  		var idsz = '';
		  		$('.checkbox:checked').each(function(i){
		  				var id = $(this).val();
		  				idsz = idsz + '&cloud_request_id[]='+id;
		  			
		  			 if (i+1 === count) {
      					other(idsz, actionz);
    				}
		  		});
				
				
		  		
		});

function other(idsz, actionz) {
	console.log(idsz);
	if (idsz != '') {
		  		

		  		var storagelink = 'index.php?plugin=cloud&controller=cloud-request&cloud_request_action='+actionz+idsz;
		 		console.log(storagelink);
		 		$('#storageform').load(storagelink+" form", function(){
		  			$('.lead').hide();
		  			$('#storageform select').selectpicker();
		  			$('#storageform select').hide();
	  				$('#volumepopup').show();
		  		});  	
		  		} else {
		  			blackalert('Select Instance');
		  		}
}

var volg = $('#volgroupupdatewrap');
if (typeof(volg) != 'undefined') {
		timeout = setTimeout(reloadChat, 1000);
		var storid = $('#storageid').text();
		var urlvolg = 'index.php?base=storage&storage_filter=&splugin=kvm&scontroller=kvm&storage_action=load&storage_id='+storid+'&kvm_action=volgroup&volgroup=storage1' ;
}

function reloadChat() {
	volg.load(urlvolg + ' #volgroupupdate',function () {
		$(this).unwrap();
		console.log('here');
	});
}

		$('#cloud_requests').find('.cr_details').find('a').click(function(e){ 
			if ($(this).attr('title') == 'Pause') {
				
				e.preventDefault();
				$('.lead').hide();
		  		
		  		var storagelink = $(this).attr('href');
		 		
		 		$('#storageform').load(storagelink+" form", function(){
		  			$('.lead').hide();
		  			$('#storageform select').selectpicker();
		  			$('#storageform select').hide();
	  				$('#volumepopup').show();
		  		});  	
		  	}
		  		
		});

	$('#cloud_resource_limit_table tr.odd').addClass('panel-primary');
	$('#cloud_resource_limit_table tr.even').addClass('panel-primary');
	$('#cloud_resource_pool_table tr.odd').addClass('panel-primary');
	$('#cloud_resource_pool_table tr.even').addClass('panel-primary');
	$('#cloud_power_saver_table tr.odd').addClass('panel-primary');
	$('#cloud_power_saver_table tr.even').addClass('panel-primary');
	
	$('#hpselect').multiselect();
	$('#piselect').multiselect();
	
	

	$('.gaugestorage').find('.label-primary').each(function(){
		if ($(this).text() == '') {
			$(this).text(0);
		}
	});

	// cloud-nephos works:
		var tttx = $('#tab_cloud_nephos_tab1 a').text();
		if (tttx == 'New') {
			$('#tab_cloud_nephos_tab1 a').addClass('btn btn-labeled fa fa-plus btn-primary newbtncss');
		}

		var ttxx = $('#tab_cloud_nephos_user_tab1 a').text();
		if (ttxx == 'New') {
			$('#tab_cloud_nephos_user_tab1 a').addClass('btn btn-labeled fa fa-plus btn-primary newbtzcss');
		}

		var ttxx = $('#tab_cloud_nephos_usergroup_tab1 a').text();
		if (ttxx == 'New') {
			$('#tab_cloud_nephos_usergroup_tab1 a').addClass('btn btn-labeled fa fa-plus btn-primary newbtzcss');
		}

		var ttxx = $('#tab_cloud_nephos_limits_tab1 a').text();
		if (ttxx == 'New') {
			$('#tab_cloud_nephos_limits_tab1 a').addClass('btn btn-labeled fa fa-plus btn-primary newbtzcss');
		}

		$('#tab_cloud_nephos_limits_tab1 a').click(function(e){ 
		
		var ttxx = $('#tab_cloud_nephos_limits_tab1 a').text();
			if (ttxx == 'New') {
				e.preventDefault();
				$('.lead').hide();
		  		
		  		var storagelink = $(this).attr('href');
		 		
		 		$('#storageform').load(storagelink+" #cloud_nephos_limits_tab1", function(){
		  			$('.lead').hide();
		  			$('#storageform select').selectpicker();
		  			$('#storageform select').hide();
	  				$('#volumepopup').show();
		  		});  	
		  	}		
		});


		
		
		$('#tab_cloud_nephos_user_tab1 a').click(function(e){ 
		
		var ttxx = $('#tab_cloud_nephos_user_tab1 a').text();
			if (ttxx == 'New') {
				e.preventDefault();
				$('.lead').hide();
		  		
		  		var storagelink = $(this).attr('href');
		 		
		 		$('#storageform').load(storagelink+" #cloud_nephos_user_tab1", function(){
		  			$('.lead').hide();
		  			$('#storageform select').selectpicker();
		  			$('#storageform select').hide();
	  				$('#volumepopup').show();
		  		});  	
		  	}		
		});

		$('#tab_cloud_nephos_usergroup_tab1 a').click(function(e){ 
		
		var ttxx = $('#tab_cloud_nephos_usergroup_tab1 a').text();
			if (ttxx == 'New') {
				e.preventDefault();
				$('.lead').hide();
		  		
		  		var storagelink = $(this).attr('href');
		 		
		 		$('#storageform').load(storagelink+" #cloud_nephos_usergroup_tab1", function(){
		  			$('.lead').hide();
		  			$('#storageform select').selectpicker();
		  			$('#storageform select').hide();
	  				$('#volumepopup').show();
		  		});  	
		  	}		
		});

		$('.cloud_nephos_usergroup_table tr.odd').addClass('panel panel-bordered panel-primary');
		$('.cloud_nephos_usergroup_table tr.even').addClass('panel panel-bordered panel-primary');
		$('.cloud_nephos_userlimits_table tr.even').addClass('panel panel-bordered panel-primary');
		$('.cloud_nephos_userlimits_table tr.odd').addClass('panel panel-bordered panel-primary');

		$('.cloud_nephos_user_table').find('td.edit').find('a.edit').click(function(e){
				e.preventDefault();
				$('.lead').hide();
		  		
		  		var storagelink = $(this).attr('href');
		 		
		 		$('#storageform').load(storagelink+" #cloud_nephos_user_tab1", function(){
		  			$('.lead').hide();
		  			$('#storageform select').selectpicker();
		  			$('#storageform select').hide();
	  				$('#volumepopup').show();
		  		});  	
		});

		$('.cloud_nephos_usergroup_table ').find('td.edit').find('a.edit').click(function(e){
				e.preventDefault();
				$('.lead').hide();
		  		
		  		var storagelink = $(this).attr('href');
		 		
		 		$('#storageform').load(storagelink+" #cloud_nephos_usergroup_tab1", function(){
		  			$('.lead').hide();
		  			$('#storageform select').selectpicker();
		  			$('#storageform select').hide();
	  				$('#volumepopup').show();
		  		});  	
		});

			$('.cloud_nephos_userlimits_table').find('td.edit').find('a.edit').click(function(e){
				e.preventDefault();
				$('.lead').hide();
		  		
		  		var storagelink = $(this).attr('href');
		 		
		 		$('#storageform').load(storagelink+" #cloud_nephos_limits_tab1", function(){
		  			$('.lead').hide();
		  			$('#storageform select').selectpicker();
		  			$('#storageform select').hide();
	  				$('#volumepopup').show();
		  		});  	
		});

		var rplc = $('td.cloud_nephos_user_ident').html();
		$('td.cloud_nephos_user_ident').remove();
		$('td.cloud_nephos_user_status').append(rplc);

		var rrrp = $('td.cloud_nephos_group_ident').html();
		$('td.cloud_nephos_group_ident').remove();
		$('.cloud_nephos_usergroup_table').find('.appnamer').append(rrrp);

		var rrrp = $('td.cloud_nephos_limits_ident').html();
		$('td.cloud_nephos_limits_ident').remove();
		$('.cloud_nephos_userlimits_table').find('.appnamer').append(rrrp);

	// --- end cloud nephos ---

	// update to resource pools, host limit, power saver in the popups:
	$('#cloud_resource_pool_table').find('td.appliance_actions').find('a.edit').click(function(e){ 
		e.preventDefault();
		$('.lead').hide();
	  		var storagelink = $(this).attr('href');
	 		$('#storageform').load(storagelink+" #project_tab1", function(){
	 			$('#hpselect').multiselect();
	  			$('.lead').hide();
	  			$('#cloud_resource_pool_assign').attr('multiple', 'multiple');
	  			$('#cloud_resource_pool_assign').hide();
	  			//$('#storageform select').selectpicker();
	  			//$('#storageform select').hide();
  				$('#volumepopup').show();
	  		});  		
	});

	$('body').on('click', '#hpselect_rightAll',function(){
		hpvalue();
	});

	$('body').on('click', '#hpselect_rightSelected',function(){
		hpvalue();
	});

	$('body').on('click', '#hpselect_leftSelected',function(){
		hpvalue();
	});

	$('body').on('click', '#hpselect_leftAll',function(){
		hpvalue();
	});

	$('body').on('click', '#hpselect_leftAll',function(){
		volvalue();
	});



	

	function hpvalue() {
		allvals = [];
		$('#hpselect_to').find('option').each(function(){
			var atr = $(this).attr('value');
			allvals.push(atr);
		});
		console.log(allvals);
		$("#cloud_resource_pool_assign").val(allvals);
		
		
		console.log($('#cloud_resource_pool_assign').val());
	}

	$('#cloud_resource_limit_table').find('td.appliance_actions').find('a.edit').click(function(e){ 
		e.preventDefault();
		$('.lead').hide();
	  		var storagelink = $(this).attr('href');
	 		$('#storageform').load(storagelink+" #project_tab1", function(){
	  			$('.lead').hide();
	  			$('#storageform select').selectpicker();
	  			$('#storageform select').hide();
  				$('#volumepopup').show();
	  		});  		
	});


	$('#cloud_power_saver_table').find('td.appliance_actions').find('a.edit').click(function(e){ 
		e.preventDefault();
		$('.lead').hide();
	  		var storagelink = $(this).attr('href');
	 		$('#storageform').load(storagelink+" #project_tab1", function(){
	  			$('.lead').hide();
	  			$('#storageform select').selectpicker();
	  			$('#storageform select').hide();
  				$('#volumepopup').show();
	  		});  		
	});

	$('#cloud_usergroup_table').find('td.cg_actions').find('a.edit').click(function(e){ 
		e.preventDefault();
		$('.lead').hide();
	  		var storagelink = $(this).attr('href');
	 		$('#storageform').load(storagelink+" #project_tab1", function(){
	  			$('.lead').hide();
	  			$('#storageform select').selectpicker();
	  			$('#storageform select').hide();
  				$('#volumepopup').show();
	  		});  		
	});

	

	$('#cloud_private_image_table').find('td.image_actions').find('a.edit').click(function(e){ 
		e.preventDefault();
		$('.lead').hide();
	  		var storagelink = $(this).attr('href');
	 		$('#storageform').load(storagelink+" #project_tab1", function(){
	 			$('#piselect').multiselect();
	  			$('.lead').hide();
	  			$('#cloud_private_image_assign').attr('multiple', 'multiple');
	  			$('#cloud_private_image_assign').hide();
	  			//$('#storageform select').selectpicker();
	  			//$('#storageform select').hide();
  				$('#volumepopup').show();
	  		});  		
	});

	$('body').on('click', '#piselect_rightAll',function(){
		pivalue();
	});

	$('body').on('click', '#piselect_rightSelected',function(){
		pivalue();
	});

	$('body').on('click', '#piselect_leftSelected',function(){
		pivalue();
	});

	$('body').on('click', '#piselect_leftAll',function(){
		pivalue();
	});

	

	function pivalue() {
		allvals = [];
		$('#piselect_to').find('option').each(function(){
			var atr = $(this).attr('value');
			allvals.push(atr);
		});
		console.log(allvals);
		$("#cloud_private_image_assign").val(allvals);
		
		
		console.log($('#cloud_private_image_assign').val());
	}
	

	


	
	// --- end update ---

	// new server popup:

	$('#servadddd').click(function(e){
		e.preventDefault();
		$('.lead').hide();
	  		var storagelink = $(this).find('a.add').attr('href');
	 		$('#storageformaddn').load(storagelink+" #step1", function(){
	  			$('.lead').hide();
	  			$('#storageformaddn select').selectpicker();
	  			$('#storageformaddn select').hide();
	  			var heder = $('#appliance_tab0').find('h2').text();

				if (heder == 'ServerAdd a new Server') {
					$('#storageformaddn').find('#name').css('left','-20px');
				}
				$('#storageformaddn').find('#info').remove();
  				$('#volumepopupaddn').show();
	  		});  			
	});

	$('#volumepopupcloseaddn').click(function(){
		$('#volumepopupaddn').hide();
	});

	$('.shortcut-grid').click(function(e){
		var hrefo = $(this).attr('href');
		if (hrefo == '/htvcenter/base/index.php?base=appliance&resource_filter=&appliance_action=step1') {
			e.preventDefault();
			$('.lead').hide();
	  		
	 		$('#storageformaddn').load(hrefo+" #step1", function(){
	  			$('.lead').hide();
	  			$('#storageformaddn select').selectpicker();
	  			$('#storageformaddn select').hide();
	  			$('label').each(function(){
	  				if ($(this).attr('for') == 'comment') {
	  					$(this).remove();
	  				}
	  			});

	  			var heder = $('#appliance_tab0').find('h2').text();

				if (heder == 'ServerAdd a new Server') {
					$('#storageformaddn').find('#name').css('left','-20px');
				}

				$('#storageformaddn').find('#info').remove();
  				$('#volumepopupaddn').show();
	  		});  	
		}
	});

	// --- end new server popup ---

	// puppet edit to popup:

	$('#puppet_tab0').find('td.action').find('a.edit').click(function(e){
		e.preventDefault();
		$('.lead').hide();
	  		var storagelink = $(this).attr('href');
	 		$('#storageform').load(storagelink+" form", function(){
	  			$('.lead').hide();
	  			$('#storageform select').selectpicker();
	  			$('#storageform select').hide();
	  			$('#storageform .selectpicker');
  				$('#volumepopup').show();
	  		});  			

	});
	// --- end puppet edit ---

	// cloud users:

	$('#cloud_users').find('.pill').each(function(){
		
		if ( $(this).text() == 'Enabled' ) {
			color = '#9cc96b';
			vlasso = 'panel-success';
		} else {
			color = '#5fa2dd';
			vlasso = 'panel-primary';
		}

		$(this).closest('tr').addClass('panel-bordered panel '+vlasso);
		$(this).closest('tr').css('border','1px solid '+color);
		$(this).css('background-color', color);
	});
	// --- end cloudusers ---

	// ip-mgmt update popup:

	$('#ip_mgmt_tab0').find('td.edit').find('a.edit').click(function(e) {
		e.preventDefault();
		$('.lead').hide();

  			
  			
	  		var storagelink = $(this).attr('href');
	 		$('#storageform').load(storagelink+" form", function(){
	  			$('.lead').hide();
	  			$('#storageform select').selectpicker();
	  			$('#storageform select').hide();
	  			$('#storageform .selectpicker');
  				$('#volumepopup').show();
	  		});  			

	});

		$('#ip_mgmt_tab0').find('a.add').click(function(e) {
		e.preventDefault();
		$('.lead').hide();
		
  			
  			
	  		var storagelink = $(this).attr('href');
	 		$('#storageform').load(storagelink+" form", function(){
	  			$('.lead').hide();
	  			$('#storageform select').selectpicker();
	  			$('#storageform select').hide();
	  			$('#storageform .selectpicker');
  				$('#volumepopup').show();
	  		});  			

	});

	// --- end ip mgmt popup ---

	// ipmgmt actions for fortis update:

	$('td.ip_mgmt_actions').find('a.edit').click(function(e){
		e.preventDefault();
		

  			
  			
	  		var storagelink = $(this).attr('href');
	 		$('#storageform').load(storagelink+" form", function(){
	  			$('.lead').hide();
	  			$('#storageform select').selectpicker();
	  			$('#storageform select').hide();
	  			$('#storageform .selectpicker');
  				$('#volumepopup').show();
	  		});  			

	}); 

	// --- end ipmgmt ---

	// edit highavailability to popup:
		$('#highavailability_tab0').find('td.edit').find('a.edit').click(function(e){
			e.preventDefault();
		
	  		var storagelink = $(this).attr('href');
	  		console.log(storagelink);
	 		$('#storageform').load(storagelink+" form", function(){
	  			$('.lead').hide();
	  			$('#storageform select').selectpicker();
	  			$('#storageform select').hide();
	  			$('#storageform .selectpicker');
  				$('#volumepopup').show();
	  		});

		});
	// --- end edit highavailability to popup ---


	$('#stvolumesbtn').click(function(e){
		e.preventDefault();
		var hrefo = $(this).attr('href');
		console.log(hrefo);
		if ( hrefo == 'index.php?base=storage&storage_filter=&storage_action=load&splugin=kvm&kvm_action=edit&storage_id=') {
			blackalert('Please, create KVM storage first');
		} else {
			document.location = hrefo;
		}
	});

	if (typeof(okchart) != 'undefined' && okchart == 'okkk') {
		var arrayspark = [physicalpercent, bridgepercent];
		var netw = $('.networkpie');
		
	
						$("#demo-sparkline-pie").sparkline(arrayspark, {
							type: 'pie',
							width: '100',
							height: '100',
							tooltipContainer: netw,
							tooltipValueLookups: {
								'offset': {
									0: 'Physical',
									1: 'Bridge'
								}
							},
							tooltipChartTitle: '',
							tooltipFormat: '{{offset:offset}} ({{percent.1}}%)',
							
							sliceColors: ['#2d4859','#fe7211'],
						});
	}
					
/*// main storages:
var sfree = $('#lizzard').find('.availZ').text();

var stotal = $('#lizzard').find('.totalZ').text();
console.log(sfree);
var sfrarr = sfree.split(" ");
console.log(sfrarr);
var frnum = sfrarr[0];
var val = sfrarr[1];
console.log(val);
var stotarr = stotal.split(' ');
var stnum = stotarr[0];

sfree = '<b>'+sfree+'</b>';
$('.sfree').html(sfree);
$('.stotal').text(stotal);

frnum = parseFloat(frnum);
stnum = parseFloat(stnum);

var sused = stnum - frnum;
console.log(sused);
var susedtext = toString(sused);
console.log(susedtext);
var valtext = toString(val);
//susedtext = susedtext + val;
$('.sused').text(susedtext);


// -- end main storages---
*/

$('#showstoragesbtn').click(function(){
	$('#lizardside').hide();
	$('#storageside').show();
	$('#showstoragesbtn').hide();
	$('#showlizardbtn').show();
	//$('.newstoragepop').show();
	//$('.newstoragepop').after("<br class='break'><br class='break'>");
});

$('#showlizardbtn').click(function(){
	$('#lizardside').show();
	$('#storageside').hide();
	$('#showstoragesbtn').show();
	$('#showlizardbtn').hide();
	$('.newstoragepop').hide();
	$('.break').remove();
});

if($(document).width() <=1200) {
	$("div#btnsideee .break").css('display','none');
} else {
	$("div#btnsideee .break").css('display','block');
}
 
//dns and dhcp config:
$('#dnstextareasave').click(function(){
	var text = $('#dnstextarea').val();
	console.log(text);
	var urlstring = 'index.php?plugin=dns&controller=dns-about&textarea=save';
	var poststring = 'text='+text;
	$.ajax({
			  type: 'POST',
			  url: urlstring,
			  data: poststring,
		
			  success: function(data){
			  		blackalert('Config file was saved successfully!');
			  }
			});
});

$('#dhcptextareasave').click(function(){
	var text = $('#dhcptextarea').val();
	console.log(text);
	var urlstring = 'index.php?plugin=dhcpd&controller=dhcpd-about&textarea=save';
	var poststring = 'text='+text;
	$.ajax({
			  type: 'POST',
			  url: urlstring,
			  data: poststring,
		
			  success: function(data){
			  		alert('Config file was saved successfully!');
			  }
			});
});

$("#ip_mgmt_gateway").on('click', function(){
	var text_val = $("#ip_mgmt_network_2").val();
	$("#ip_mgmt_gateway").val(text_val);
});
// --- end dns and dhcp config ---

//image remove in popup:
 

  		
    	$('.image').find('a.remove').click(function(e) {
  			e.preventDefault();
  			$('.lead').hide();

  			var imageid = $(this).closest('tr').find('.imageid').text();
  			
	  		var storagelink = '/htvcenter/base/index.php?base=image&image_filter=&image_action=remove&image_identifier='+imageid+'&image[sort]=image_id&image[order]=ASC&image[limit]=20&image[offset]=0';
	 		$('#storageform').load(storagelink+" form", function(){
	  			
	  			$('#storageform select').selectpicker();
	  			$('#storageform select').hide();
	  			$('#storageform .selectpicker');
	  			$('#iconk').html('<i class="fa fa-eye"></i>');
  				$('#volumepopup').show();
	  		});  			
  		});

  
// --- end image remove in popup ---

//scheduler js:

$('#addschvol').click(function(){
	var volopt = $('#volopt').html();
	$('#volschmultiselect').html(volopt);
	$('#volschedulerpopup').show();
});

$('#shedulersnapclone').click(function(){
	var volopt = $('#volopt').html();
	$('#volschmultiselect').html(volopt);
	$('#volschedulerpopup').show();
});

$('#volschedulerpopupclose').click(function(){
	$('#volschedulerpopup').hide();
});

$('#volschedulertbtn').click(function(){
			
		
			var action = $('#volactionselect').val();
			var date = $('#volschdate').val();
			var time = $('#voldemo-tp-com').val();

			allvals = [];
			$('#volschmultiselect_to').find('option').each(function(){
				var atr = $(this).attr('value');
				allvals.push(atr);
			});
		
			$("#volschmultiselect_to").val(allvals);
			var volumes = $('#volschmultiselect_to').val();
			var storageid = $('#storageid').text();
			var volgroup = $('#storagecount').text();
			var resido = $('#resido').text();

			if (storageid == '') {
				storageid = $('#storageidvolvol').text();
			}

			if (volgroup == '') {
				volgroup = 'storage1';
			}

			if (resido == '') {
				resido = '0';
			}
			
			var poststring = 'action='+action+'&volumes='+volumes+'&date='+date+'&time='+time+'&volgroup='+volgroup+'&storageid='+storageid+'&resido='+resido;
			
			var urlstring = '/htvcenter/base/index.php?base=callendar&action=volquery';
			wait();
			
			$.ajax({
			  type: 'POST',
			  url: urlstring,
			  data: poststring,
		
			  success: function(data){
			  	$('.lead').hide();

			  	if (data == 'ok') {
			  		blackalert('Scheduler rule added successfully!');
			  		window.location.href='/htvcenter/base/index.php?base=callendar';
			  	} else {
			  		alert(data);
			  		//alert('Some errors, please, try again or read error logs!');
			  	}

			    $('#schedulerpopup').hide();
			  }
			});

});


if ( typeof(callendar) != 'undefined' && callendar == true) {

	

	//$('#demo-calendar').fullCalendar();

	// initialize the external events
	// -----------------------------------------------------------------
	$('#demo-external-events .fc-event').each(function() {
		// store data so the calendar knows to render an event upon drop
		$(this).data('event', {
			title: $.trim($(this).text()), // use the element's text as the event title
			stick: true, // maintain when user navigates (see docs on the renderEvent method)
			className : $(this).data('class')
		});


		// make the event draggable using jQuery UI
		$(this).draggable({
			zIndex: 99999,
			revert: true,      // will cause the event to go back to its
			revertDuration: 0  //  original position after the drag
		});
	});


	// Initialize the calendar
	// -----------------------------------------------------------------
	$('#demo-calendar').fullCalendar({
		header: {
			left: 'prev,next today',
			center: 'title',
			right: 'month,agendaWeek,agendaDay'
		},
		editable: false,
		droppable: true, // this allows things to be dropped onto the calendar
		drop: function() {
			// is the "remove after drop" checkbox checked?
			if ($('#drop-remove').is(':checked')) {
				// if so, remove the element from the "Draggable Events" list
				$(this).remove();
			}
		},
		defaultDate: todaydate,
		eventLimit: true, // allow "more" link when too many events
		events: eventsarr
	});

	// -----------------------------------
}




// server remove from calendar:

	var removeservid = null;
	var volumer = false;
	$('body').on('click', '.fc-event', function(){
		removeservid = null;
		var serv = $(this).attr('class');
		var matches = serv.match(/id_(\d+)/);
		var id = matches[0].match(/(\d+)/);
		removeservid = id[0];

		volumer = false;
		var volumerlocal = serv.match(/volumecal/);
		if (volumerlocal == 'volumecal') {
			volumer = true;
		}


		var names = $(this).text();

		$('#removesid').text(removeservid);
		$('#removesname').text(names);
		$('#schedulerconfirmpopup').show();
	});


	$('#schremove').click(function(){
		var poststring = 'serverid='+removeservid;
		var urlstring = '/htvcenter/base/index.php?base=callendar&action=remove';

		if (volumer == true) {
			var poststring = 'serverid='+removeservid+'&volumer=true';
		}

			wait();
			$.ajax({
			  type: 'POST',
			  url: urlstring,
			  data: poststring,
		
			  success: function(data){
			  	$('.lead').hide();

			  	if (data == 'ok') {
			  		blackalert('Scheduler rule removed successfully!');
			  		window.location.href='/htvcenter/base/index.php?base=callendar';
			  	} else {
			  		alert(data);
			  		//alert('Some errors, please, try again or read error logs!');
			  	}

			    $('#schedulerconfirmpopup').hide();
			  }
			});
	});

	$('.schremoveclose').click(function(){
		 $('#schedulerconfirmpopup').hide();
	});

// --- end server removing ---

// ip management delete

$('#ip_mgmt_tab0').find("input[value='Delete']").click(function(e){
	e.preventDefault();

	wait();
	
	var dsids = '';
	$('.checkbox').each(function(){
		if( $(this).is(":checked") == true) {
			var name = $(this).val();
			dsids = dsids + '&ip_mgmt_id[]=' + name;
		}
	});

	
	var poststring = 'plugin=ip-mgmt&controller=ip-mgmt&ip_mgmt=select'+dsids+'&ip_mgmt[delete]=Delete';
	var urlstring = 'index.php';
	$('#storageformrem').load(urlstring+" form", poststring, function(){
		  			$('.lead').hide();
		  			$('#storageformrem select').selectpicker();
		  			$('#storageformrem select').hide();
	  				$('#volumepopuprem').show();
		 }); 
});


$('#volumepopupcloserem').click(function(){
	$('#volumepopuprem').hide();
});

// --- end ip management delete ---

var schaction = null;

$('#schmultiselect').multiselect();
$('#volschmultiselect').multiselect();

		
$('#demo-dp-component .input-group.date').datepicker({autoclose:true});
$('#voldemo-dp-component .input-group.date').datepicker({autoclose:true});
$('#demo-tp-com').timepicker();
$('#voldemo-tp-com').timepicker();

$('#shedulerstart').click(function(){
	$('#schedulerpopup').show();
	schaction = 'start';
});

$('#shedulerstop').click(function(){
	$('#schedulerpopup').show();
	schaction = 'stop';
});

$('#shedulerremove').click(function(){
	$('#schedulerpopup').show();
	schaction = 'remove';
});

$('#schedulerpopupclose').click(function(){
	$('#schedulerpopup').hide();
});

$('#schedulertbtn').click(function(){
	var action = schaction;
	var servers = $('#schmultiselect_to').val();
	var date = $('#schdate').val();
	var time = $('#demo-tp-com').val();
	wait();

	if ( (action != null) && (servers != null) && (date != null) && (time != null) ) {

			
			var poststring = 'action='+action+'&servers='+servers+'&date='+date+'&time='+time;
			var urlstring = '/htvcenter/base/index.php?base=callendar&action=query';
			wait();
			$.ajax({
			  type: 'POST',
			  url: urlstring,
			  data: poststring,
		
			  success: function(data){
			  	$('.lead').hide();

			  	if (data == 'ok') {
			  		blackalert('Scheduler rule added successfully!');
			  		window.location.href='/htvcenter/base/index.php?base=callendar';
			  	} else {
			  		alert(data);
			  		//alert('Some errors, please, try again or read error logs!');
			  	}

			    $('#schedulerpopup').hide();
			  }
			});

		
			
		
		} else {
			alert ('Error: Servers are empty!');
			$('#schedulerpopup').hide();
			$('.lead').hide();
		}

});

// --- end scheduler js ---

// server implementation js:


// add citrix:

		$('.citrixaddp').click(function(e) {
			console.log('aaaaa');
  			e.preventDefault();
  			$('.lead').hide();
  			var hrefo = 'index.php?plugin=citrix&controller=citrix-discovery&citrix_discovery_action=add';
	  		var storagelink = hrefo;
	 		$('#integratepopupform').load(storagelink+" #citrix_discovery_tab1", function(){
	  			$('#integratepopupform select').selectpicker();
	  			$('#integratepopupform select').hide();
	  			$('#integratepopupform .selectpicker');
	  			$('#iconk').html('<i class="fa fa-plus-square"></i>');
  				$('#integratepopup').show();
	  		});  		
  		});
		
		$('.integratepopupclose').click(function(){
			$('#integratepopup').hide();
		});
// --- end citrix ---


// add vmware:
	$('.vmwareaddp').click(function(e) {
			console.log('aaaaa');
  			e.preventDefault();
  			$('.lead').hide();
  			var hrefo = 'index.php?plugin=vmware-esx&controller=vmware-esx-discovery&vmware_esx_discovery_action=add';
	  		var storagelink = hrefo;
	 		$('#integratepopupform').load(storagelink+" #vmware_esx_discovery_tab1", function(){
	  			$('#integratepopupform select').selectpicker();
	  			$('#integratepopupform select').hide();
	  			$('#integratepopupform .selectpicker');
	  			$('#iconk').html('<i class="fa fa-plus-square"></i>');
  				$('#integratepopup').show();
	  		});  		
  		});
	
// --- end add vmware ---


// add hyperv:
	
	$('.hypervaddp').click(function(e) {
			console.log('aaaaa');
  			e.preventDefault();
  			$('.lead').hide();
  			var hrefo = 'index.php?plugin=hyperv&controller=hyperv-discovery&hyperv_discovery_action=add';
	  		var storagelink = hrefo;
	 		$('#integratepopupform').load(storagelink+" #hyperv_discovery_tab1", function(){
	  			$('#integratepopupform select').selectpicker();
	  			$('#integratepopupform select').hide();
	  			$('#integratepopupform .selectpicker');
	  			$('#iconk').html('<i class="fa fa-plus-square"></i>');
  				$('#integratepopup').show();
	  		});  		
  		});	
  		

// --- end add hyperv ---
	
	var what = null;
	$('.showipform').click(function(){
		what = null;
	
		if ($(this).hasClass('kvm')) {
			what = 'kvm';
		}

		if ($(this).hasClass('xen')) {
			what = 'xen';
		}		

		$('#serverpopup').show();
	});

	$('#serverpopupclose').click(function(){
		$('#serverpopup').hide();
	});

	$('#servintbtn').click(function(){

		var ip = null;
		var name = null;
		var pass = null;
		var iface = null;

		var ip = $('#serverform').find('#ip').val();
		var name = $('#serverform').find('#name').val();
		var pass = $('#serverform').find('#pass').val();
		var iface = $('#serverform').find('#iface').val();

		if ( (ip != '') && (name != '') && (pass != '') && (iface != '') ) {

			
			var poststring = 'ip='+ip+'&name='+name+'&pass='+pass+'&iface='+iface;
			
			var urlstring = '/htvcenter/base/index.php?plugin=local-server&controller=local-server-nfsip&local_server_nfsip_action=integrate';
			wait();
			$.ajax({
			  type: 'POST',
			  url: urlstring,
			  data: poststring,
		
			  success: function(data){
			  	$('.lead').hide();
			  	var n = data.length;
			  	if (n < 256) {
			  		alert(data);
			  	} else {
			  		alert ('Please, install local-server and KVM plugins first!');
			  	}
			  	$('#serverpopup').hide();
			  }
			});
			

		} else {
			alert ('Please, input all form\'s fields');
		}
	});

// --- end server implementation ---

$('#vmware_esx_ds_tab1 .storageboxx tr.even').addClass('panel panel-bordered panel-info');
$('#vmware_esx_ds_tab1 .storageboxx tr.odd').addClass('panel panel-bordered panel-info');
	// server enable button popup:

		$('.appliance').find('a.enable').click(function(e) {
			if ($(this).text() == 'start') {
	  			e.preventDefault();
	  			$('.lead').hide();
	  			var hrefo = $(this).attr('href');
		  		var storagelink = hrefo;
		 		$('#storageform').load(storagelink+" form", function(){
		  			$('#storageform').addClass('hidefirstform');
		  			$('#storageform select').selectpicker();
		  			$('#storageform select').hide();
		  			$('#storageform .selectpicker');
		  			$('#iconk').html('<i class="fa fa-eye"></i>');
	  				$('#volumepopup').show();
		  			
		  		});  		
	  		}
  		});

	// --- end server enable button popup ---


	// hyperv datastore popup:

	
	$('#dsadder').click(function(e){
		e.preventDefault();
	  			$('.lead').hide();
	  			var appido = $('#appido').text();
	  			
		  		var storagelink = 'index.php?plugin=hyperv&controller=hyperv-ds&appliance_id='+appido+'&hyperv_ds_action=add_pool';
		  	
		 		$('#storageform').load(storagelink+" form", function(){
		  			$('#browsebutton').show();
		  			
		  			$("input[name='appliance_id']").val(appido);
		  			$('#storageform select').selectpicker();
		  			$('#storageform select').hide();
		  			$('#storageform .selectpicker');
		  			$('#iconk').html('<i class="fa fa-plus-square"></i>');
	  				$('#volumepopup').show();
		  			
		  		});  		
	});
	


	
	$('#hyperv_ds_tab1').find('td.hyperv_pool_action').find('a.remove').click(function(e){
		e.preventDefault();
		var appido = $('#appido').text();
		var volgripo = $(this).closest('tr').find('td.hyperv_pool_id').text();
		var storagelink = 'index.php?plugin=hyperv&controller=hyperv-ds&appliance_id='+appido+'&hyperv_ds_action=remove_pool&volgroup='+volgripo;
		  	
		 		$('#storageform').load(storagelink+" form", function(){
		  			$('#browsebutton').show();
		  			$('#browsebutton').css('display', 'block');
		  			$("input[name='appliance_id']").val(appido);
		  			$('#storageform select').selectpicker();
		  			$('#storageform select').hide();
		  			$('#storageform .selectpicker');
		  			$('#iconk').html('<i class="fa fa-plus-square"></i>');
	  				$('#volumepopup').show();
		  			
		  		});  		
		
	});

	$('#hyperv_ds_tab2').find('a.add').click(function(e){
		e.preventDefault();
		
		var storagelink = $(this).attr('href');
		
				$('#storageform').load(storagelink+" #form", function(){
		  			$('#browsebutton').show();
		  			
		  			
		  			$('#storageform select').selectpicker();
		  			$('#storageform select').hide();
		  			$('#storageform .selectpicker');
		  			$('#iconk').html('<i class="fa fa-plus-square"></i>');
	  				$('#volumepopup').show();
		  			
		  		});  		
		
	});


	

	// --- end hyperv popup ---

	$('#addvso').click(function(e){
		e.preventDefault();
	  			$('.lead').hide();
	  			var appido = $('#vsoid').text();
		  		var storagelink = 'index.php?plugin=hyperv&controller=hyperv-vs&appliance_id='+appido+'&hyperv_vs_action=add';
		  		
		 		$('#storageform').load(storagelink+" form", function(){
		  			$('#browsebutton').show();
		  			
		  			$('#storageform select').selectpicker();
		  			$('#storageform select').hide();
		  			$('#storageform .selectpicker');
		  			$('#iconk').html('<i class="fa fa-plus-square"></i>');
	  				$('#volumepopup').show();
		  			
		  		});  		

	});

	// server disable button popup:

		$('.appliance').find('a.disable').click(function(e) {
  			e.preventDefault();
  			$('.lead').hide();
  			var hrefo = $(this).attr('href');
	  		var storagelink = hrefo;
	 		$('#storageform').load(storagelink+" form", function(){
	  			$('#storageform').addClass('hidefirstform');
	  			$('#storageform select').selectpicker();
	  			$('#storageform select').hide();
	  			$('#storageform .selectpicker');
	  			$('#iconk').html('<i class="fa fa-eye"></i>');
  				$('#volumepopup').show();
	  			
	  		});  		
  		});

	// --- end server disable button popup ---




  var notpicker = false;

    //kvm clone popup:

    	var cloner = $('#tblbtn').find('a.clone');

    
    	
    	$('body').on('click', '#tblbtn a.clone', function(e){
    		console.log('eeeee');
  			e.preventDefault();
  			$('.lead').hide();
  			var hrefo = $(this).attr('href');
  			
	  		var storagelink = hrefo;
	 		$('#storageform').load(storagelink+" #kvm_tab3 form", function(){
	  			
	  			$('#storageform select').selectpicker();
	  			$('#storageform select').hide();
	  			$('#storageform .selectpicker');
	  			$('#iconk').html('<i class="fa fa-eye"></i>');
  				$('#volumepopup').show();
	  			
	  		});  		
  		});

  		$('body').on('click', '.divtxt div.clone', function(e){
    		console.log('eeeee');
  			e.preventDefault();
  			$('.lead').hide();
  			var hrefo = $(this).attr('href');
  			
	  		var storagelink = hrefo;
	 		$('#storageform').load(storagelink+" #kvm_tab3 form", function(){
	  			
	  			$('#storageform select').selectpicker();
	  			$('#storageform select').hide();
	  			$('#storageform .selectpicker');
	  			$('#iconk').html('<i class="fa fa-eye"></i>');
  				$('#volumepopup').show();
	  			
	  		});  		
  		});

  // --- end kvm clone popup ---

  //kvm snap popup:
		
  	
    		$('body').on('click', '#tblbtn a.snap', function(e){
    		console.log('bbbbbb');
  			e.preventDefault();
  			$('.lead').hide();
  			var hrefo = $(this).attr('href');
  			
	  		var storagelink = hrefo;
	 		$('#storageform').load(storagelink+" #kvm_tab3 form", function(){
	  			
	  			$('#storageform select').selectpicker();
	  			$('#storageform select').hide();
	  			$('#storageform .selectpicker');
	  			$('#iconk').html('<i class="fa fa-eye"></i>');
  				$('#volumepopup').show();
	  			
	  		});  		
  		});

    		$('body').on('click', '.divtxt div.snap', function(e){
    		console.log('bbbbbb');
  			e.preventDefault();
  			$('.lead').hide();
  			var hrefo = $(this).attr('href');
  			
	  		var storagelink = hrefo;
	 		$('#storageform').load(storagelink+" #kvm_tab3 form", function(){
	  			
	  			$('#storageform select').selectpicker();
	  			$('#storageform select').hide();
	  			$('#storageform .selectpicker');
	  			$('#iconk').html('<i class="fa fa-eye"></i>');
  				$('#volumepopup').show();
	  			
	  		});  		
  		});

  // --- end kvm snap popup ---

   //add new NAS popup:

  
    		$('#addnewnas').click(function(e) {
  			e.preventDefault();
  			$('.lead').hide();
  			var hrefo = $(this).find('a').attr('href');
	  		var storagelink = hrefo;
	 		$('#storageform').load(storagelink+" form", function(){
	  			
	  			$('#storageform select').selectpicker();
	  			$('#storageform select').hide();
	  			$('#storageform .selectpicker');
	  			$('#iconk').html('<i class="fa fa-eye"></i>');
  				$('#volumepopup').show();
	  			
	  		});  		
  		});

  // --- add new NAS popup ---

  //add new ISCSI popup:

  
    		$('#addnewiscsi').click(function(e) {
  			e.preventDefault();
  			$('.lead').hide();
  			var hrefo = $(this).find('a').attr('href');
	  		var storagelink = hrefo;
	 		$('#storageform').load(storagelink+" form", function(){
	  			
	  			$('#storageform select').selectpicker();
	  			$('#storageform select').hide();
	  			$('#storageform .selectpicker');
	  			$('#iconk').html('<i class="fa fa-eye"></i>');
  				$('#volumepopup').show();
	  			
	  		});  		
  		});

  // --- add new ISCSI popup ---


   //nfs storage auth popup:

  
    		$('#nfs_tab1').find('td.auth').find('a').click(function(e) {
  			e.preventDefault();
  			$('.lead').hide();
  			var hrefo = $(this).attr('href');
	  		var storagelink = hrefo;
	 		$('#storageform').load(storagelink+" form", function(){
	  			
	  			$('#storageform select').selectpicker();
	  			$('#storageform select').hide();
	  			$('#storageform .selectpicker');
	  			$('#iconk').html('<i class="fa fa-eye"></i>');
  				$('#volumepopup').show();
	  			
	  		});  		
  		});

  // --- end nfs storage auth popup ---


    //lvm storage add new volume popup:

  
    		$('#lvmadd').click(function(e) {
  			e.preventDefault();
  			$('.lead').hide();
  			var lvmidnum = $('#lvmstid').text();
	  		var storagelink = 'index.php?plugin=lvm-storage&storage_id='+lvmidnum+'&lvm_storage_action=addvg';
	 		$('#storageform').load(storagelink+" form", function(){
	  			
	  			$('#storageform select').selectpicker();
	  			$('#storageform select').hide();
	  			$('#storageform .selectpicker');
	  			$('#iconk').html('<i class="fa fa-eye"></i>');
  				$('#volumepopup').show();
	  			
	  		});  			
  		});

  // --- end lvm storage add new volume popup ---

    //nfs storage add new volume popup:

  
    		$('#nfsadd').click(function(e) {
  			e.preventDefault();
  			$('.lead').hide();
  			var nfsidnum = $('#nfsid').text();
	  		var storagelink = 'index.php?plugin=nfs-storage&storage_id='+nfsidnum+'&nfs_storage_action=add';
	 		$('#storageform').load(storagelink+" form", function(){
	  			
	  			$('#storageform select').selectpicker();
	  			$('#storageform select').hide();
	  			$('#storageform .selectpicker');
	  			$('#iconk').html('<i class="fa fa-eye"></i>');
  				$('#volumepopup').show();
	  			
	  		});  			
  		});

  // --- end nfs storage add new volume popup ---

  // server edit ipmgmt
  		$('#ipmgmtedit').click(function(e) {
  			e.preventDefault();
  			$('.lead').hide();
  			var aplianceidnum = $('#appidval').text();
	  		var storagelink = 'index.php?base=appliance&appliance_action=load_edit&aplugin=ip-mgmt&acontroller=ip-mgmt&appliance_id='+aplianceidnum+'&ip_mgmt=configure&ip_mgmt_id[]='+aplianceidnum;
	 		$('#storageform').load(storagelink+" form", function(){
	  			
	  			$('#storageform select').selectpicker();
	  			$('#storageform select').hide();
	  			$('#storageform .selectpicker');
	  			$('#iconk').html('<i class="fa fa-sitemap"></i>');
  				$('#volumepopup').show();
	  			
	  		});  			
  		});
  // --- end server edit ipmgmt ---

  //server edit nagios:

  
    		$('#nagiosedit').click(function(e) {
  			e.preventDefault();
  			$('.lead').hide();
  			var aplianceidnum = $('#appidval').text();
	  		var storagelink = 'index.php?base=appliance&appliance_action=load_edit&aplugin=nagios3&appliance_id='+aplianceidnum+'&nagios3_action=edit';
	 		$('#storageform').load(storagelink+" form", function(){
	  			
	  			$('#storageform select').selectpicker();
	  			$('#storageform select').hide();
	  			$('#storageform .selectpicker');
	  			$('#iconk').html('<i class="fa fa-eye"></i>');
  				$('#volumepopup').show();
	  			
	  		});  			
  		});

  // --- end server edit nagios ---


    //server edit puppet:

  
    		$('#puppetedit').click(function(e) {
  			e.preventDefault();
  			$('.lead').hide();
  			var aplianceidnum = $('#appidval').text();
	  		var storagelink = 'index.php?base=appliance&appliance_action=load_edit&aplugin=puppet&puppet_action=edit&appliance_id='+aplianceidnum;
	 		$('#storageform').load(storagelink+" #puppet_groups", function(){
	  			
	  			$('#storageform select').selectpicker();
	  			$('#storageform select').hide();
	  			$('#storageform .selectpicker');
	  			$('#iconk').html('<i class="fa fa-gear"></i>');
  				$('#volumepopup').show();
	  			
	  		});  			
  		});

  // --- end server edit nagios ---

  //server edit network manager:

  $('#nwaddbtn').click(function(e){
  		e.preventDefault();
  			$('.lead').hide();
  			var aplianceidnum = $('#appidval').text();
	  		var storagelink = 'index.php?base=appliance&appliance_id='+aplianceidnum+'&resource_filter=&aplugin=network-manager&acontroller=network-manager&appliance_action=load_edit&network_manager_action=add';
	 		$('#storageform').load(storagelink+" #form", function(){
	  			
	  			$('#storageform select').selectpicker();
	  			$('#storageform select').hide();
	  			$('#storageform .selectpicker');
	  			$('#iconk').html('<i class="fa fa-globe"></i>');
  				$('#volumepopup').show();
	  			
	  		});  			
  });

  // --- end server edit network manager ---



  // server edit template
  	$('#templateedit').click(function(e){
  		e.preventDefault();
  			$('.lead').hide();
  			var aplianceidnum = $('#appidval').text();
	  		var storagelink = 'index.php?plugin=template&appliance_id='+aplianceidnum+'&template_action=edit';
	 		$('#storageform').load(storagelink+" form", function(){
	  			
	  			$('#storageform select').selectpicker();
	  			$('#storageform select').hide();
	  			$('#storageform .selectpicker');
	  			$('#iconk').html('<i class="fa fa-image"></i>');
	  			$('.edit').addClass('btn-labeled fa fa-pencil');
	  			$('.edit').css('margin-top','10px');
  				$('#volumepopup').show();
	  			
	  		});  			
  	});

  // --- end server edit template ---

// add more disks for kvm vm:

 	// add more disk js:
	var morediskcount = 0;
	var availablespace = $('#freemb').text();
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

var volnamerr = null;

	$('#addmoredisks').click(function(){
		
		var name = $('#name_box').find('input').val();
		volnamerr = name+'vol'+morediskcount;
		$('#namevolumeinput').val(volnamerr);
		if (morediskcount < 11) {
			if (name != '') {
			$('#freembsp').text(availablespace+'MB');
			$('#modal-volume').modal('hide');
			$('#modal-volumeadd').modal();
			} else {
				blackalert('Enter name of vm first!');
			}
		} else {
			blackalert('You can add maximum 10 volumes');
		}
	});

	$('#addvolumebtn').click(function() {
		var allcount = 0;
		var storagenameval = volnamerr;
		var storagesizeval = $('#sizevolumeinput').val();
		storagesizeval = parseInt(storagesizeval);
		var storagetypeval = 'raw';
		var storageid = 0;
		$('#morediskdiv').show();
		
				if( isNaN(storagesizeval) == true) {
					blackalert('Size should to be an integer value');
				} else {
					morediskcount = morediskcount + 1;
					storageid = morediskcount;
					if (morediskcount < 11) {
						var moredisk = '<tr class="storagerow" storageid="'+storageid+'"><td class="type" style="display:none">'+storagetypeval+' <input type="hidden" name="storagetype[]" value="'+storagetypeval+'" /></td><td class="name" style="display:none">'+storagenameval+' <input type="hidden" name="storagename[]" value="'+storagenameval+'" /></td><td class="size">'+storagesizeval+'<input type="hidden" name="storagesize[]" value="'+storagesizeval+'" /></td><td class="text-center"><a class="storagedit"><i class="fa fa-pencil"></i></a></td><td class="text-center"><a class="storageremove"><i class="fa fa-close"></i></a></td></tr>';
						allsizes = allsizes + parseInt(storagesizeval);
					} 

					
				
					if (allsizes > availablespacefirst) {
						blackalert('You do not have enough disk space available. Available space is: '+ availablespace+' MB');
						allsizes = allsizes - parseInt(storagesizeval);
					} else {
						$('#moredisktbl').append(moredisk);
						availablespace = availablespacefirst - allsizes;
					}
				}
		
	});

 $('body').on('click', '.storageremove', function() {
 		var stid = $(this).closest('tr').attr('storageid');
 		var sizest = $(this).closest('tr').find('td.size').text();
 		sizest = parseInt(sizest);
		allsizes = allsizes - sizest;
		availablespace = availablespacefirst - allsizes;
		morediskcount = morediskcount - 1;
		$(this).closest('tr').remove();
 });

 $('body').on('click', '.storagedit', function(){
 		var stid = $(this).closest('tr').attr('storageid');
 		var sizest = $(this).closest('tr').find('td.size').text();
 		var namest = $(this).closest('tr').find('td.name').text();
 		var typest = $(this).closest('tr').find('td.type').text();
 		sizest = parseInt(sizest);
		allsizes = allsizes - sizest;
		availablespace = availablespacefirst - allsizes;
		$('#freembspedit').text(availablespace+'MB');
		$('#sizeeditvolumeinput').val(sizest);
		$('#nameeditvolumeinput').val(namest);
		$('#typeeditvolumeselect select').val(typest);
		$('#storageidedit').text(stid);
		$('#modal-volume').modal('hide');
		$('#edit-modal').modal();
 });


	$('#editvolumebtn').click(function(){

		var storagenameval = $('#nameeditvolumeinput').val();
		var storagesizeval = $('#sizeeditvolumeinput').val();
		storagesizeval = parseInt(storagesizeval);
		var storagetypeval = $('#typeeditvolumeselect').val();
		var storageid = $('#storageidedit').text();
		$('#morediskdiv').show();
		
				if( isNaN(storagesizeval) == true) {
					blackalert('Size should to be an integer value');
				} else {
					
					
						var moredisk = '<td class="type" style="display:none">'+storagetypeval+' <input type="hidden" name="storagetype[]" value="'+storagetypeval+'" /></td><td class="name" style="display:none">'+storagenameval+' <input type="hidden" name="storagename[]" value="'+storagenameval+'" /></td><td class="size">'+storagesizeval+'<input type="hidden" name="storagesize[]" value="'+storagesizeval+'" /></td><td class="text-center"><a class="storagedit"><i class="fa fa-pencil"></i></a></td><td class="text-center"><a class="storageremove"><i class="fa fa-close"></i></a></td>';
						allsizes = allsizes + parseInt(storagesizeval);
					


				
					if (allsizes > availablespacefirst) {
						blackalert('You do not have enough disk space available. Available space is: '+ availablespace+' MB');
						allsizes = allsizes - parseInt(storagesizeval);
					} else {
						var rowedit = $('#moredisktbl').find('tr[storageid='+storageid+']');
						$(rowedit).html(moredisk);
						availablespace = availablespacefirst -allsizes;
					}
				}
	});


 

// --- end add more disks ---

//resource popup functions:

$('#resource_tab0 .submit').click(function(e){
  	
  		e.preventDefault();

  		var identifiers = '';
  		var first = true;
  		var checkboxyes = false;
  		$('.checkbox').each(function(){
  			if( $(this).is(":checked") == true) {
  				checkboxyes = true;
  				if (first != true) {
  					identifiers = identifiers + '&';
  				}
  				var aplid = $(this).val();
  				console.log(aplid);
  				identifiers = identifiers + 'resource_identifier%5B%5D='+aplid;
  				first = false;
  			}
  		})

  		if (checkboxyes == true) {

  		if ( $(this).val() == 'remove' ) {
  			var action = 'remove';
  		}

  		if ( $(this).val() == 'reboot' ) {
  			var action = 'reboot';
  		}

  		if ( $(this).val() == 'poweroff' ) {
  			var action = 'poweroff';
  		}

  		

  		var string = 'index.php?base=resource&resource%5Blimit%5D=20&resource%5Boffset%5D=0&resource%5Border%5D=ASC&resource%5Bsort%5D=resource_id&resource_action=select&resource_action%5B'+action+'%5D='+action+'&'+identifiers;
  		console.log(string);
  		$('#storageform').load(string+" #resource_tab1", function(){
	  			
	  			$('#storageform select').selectpicker();
	  			$('#storageform select').hide();
	  			$('#storageform .selectpicker')
	  			$('#volumepopup').show();
	  		});  		
  		

	  	} else {
	  		blackalert('nothing selected');
	  	}
  		
  	
  });


// --- end resource popup functions ---

// server popup functions:

	
  $('#appliance_tab0 .submit').click(function(e){
  	
  		e.preventDefault();

  		var identifiers = '';
  		var first = true;
  		var checkboxyes = false;
  		$('.checkbox').each(function(){
  			if( $(this).is(":checked") == true) {
  				checkboxyes = true;
  				if (first != true) {
  					identifiers = identifiers + '&';
  				}
  				var aplid = $(this).val();
  				identifiers = identifiers + 'appliance_identifier%5B%5D='+aplid;
  				first = false;
  			}
  		})

  		if (checkboxyes == true) {

  		if ( $(this).val() == 'remove' ) {
  			var serveraction = 'remove';
  		}

  		if ( $(this).val() == 'stop' ) {
  			var serveraction = 'stop';
  		}

  		if ( $(this).val() == 'start' ) {
  			var serveraction = 'start';
  		}


  			$('.lead').hide();
	  		var storagelink = 'index.php?base=appliance&resource_filter=&appliance_action=select&resource_filter=&resource_type_filter=&appliance%5Bsort%5D=appliance_id&appliance%5Border%5D=ASC&appliance%5Boffset%5D=0&appliance%5Blimit%5D=20&'+identifiers+'&appliance_action%5B'+serveraction+'%5D='+serveraction;
	  		
	  		$('#storageform').load(storagelink+" form", function(){
	  			$('#storageform').addClass('hidefirstform');
	  			$('#storageform select').selectpicker();
	  			$('#storageform select').hide();
	  			$('#storageform .selectpicker');
	  			$('#volumepopup').show();
	  		});  		
	  	} else {
	  		blackalert('nothing selected');
	  	}
  		
  	
  });

// --- end server popup functions ---
  

  // storages popup in the menu items:

  $('a.add').click(function(e){
  	var texte = $(this).text();
  	if (texte == 'New Storage') {
  		e.preventDefault();
  		$('.lead').hide();
  		var storagelink = '/htvcenter/base/index.php?base=storage&storage_action=add';
  		
  		$('#storageform').load(storagelink+" #form", function(){

  			$('#storageform select').selectpicker();
  			$('#storageform select').hide();
  			$('#storageform .selectpicker')
  			$('#volumepopup').show();
  		});  		
  	}
  });

  // --- end storages popup in the menu items ---

  // td replacement
  	$('#kvm_tab2 #Tabelle td.kvm_identifier').each(function(){
  		var content = $(this).html();
  		var prev = $(this).prev('td.action');
  		var contentprev = prev.html();
  		$(this).html(contentprev);
  		prev.html(content);
  	});
  // --- end td replacement ---

  // new storage popup:
  	$('.newstoragepop').click(function(e){
  		e.preventDefault();

  		$('.lead').hide();
  		var storagelink = '/htvcenter/base/index.php?base=storage&storage_filter=&storage_action=add&storage[sort]=storage_id&storage[order]=ASC&storage[limit]=20&storage[offset]=0';
  		
  		$('#storageform').load(storagelink+" #form", function(){

  			$('#storageform select').selectpicker();
  			$('#storageform select').hide();
  			$('#storageform .selectpicker')
  			$('#volumepopup').show();
  		});  		
  	});
  // --- end new storage popup ---


  // volumepopup:
  	$('#volumepopupbtn a').click(function(e){
  		e.preventDefault();
  		$('.lead').hide();
  		var storageid = $('#storageid').text();
  		var storagelink = '/htvcenter/base/index.php?base=storage&storage_filter=&splugin=kvm&scontroller=kvm&storage_action=load&storage_id='+storageid+'&kvm_action=addvg';
  		$('#storageform').load(storagelink+" #form", function(){

  			$('#storageform select').selectpicker();
  			$('#storageform select').hide();
  			//$('#storageform .selectpicker')
  			$('#volumepopup').show();
  		});  		
  		
  	});

  	$('.popupvolumebtn a').click(function(e){
  		e.preventDefault();
  		$('.lead').hide();
  		var storageid = $('#storageid').text();
  		var storagecount = $('#storagecount').text();
  		var storagelink = '/htvcenter/base/index.php?base=storage&storage_filter=&splugin=kvm&scontroller=kvm&storage_action=load&storage_id='+storageid+'&volgroup='+storagecount+'&kvm_action=add';
  		$('#storageform').load(storagelink+" #form", function(){

  			$('#storageform select').selectpicker();
  			$('#storageform select').hide();
  			//$('#storageform .selectpicker')
  			$('#volumepopup').show();
  		});  		
  	});

  	$('#volumepopupclose').click(function(){
  		$('#volumepopup').hide();
  	});
  // --- end volume popup ---

	// resource add:
	var resad = $('.resadd');
	
	if (typeof(resad) != 'undefined' && resad.length != 0) {
		notpicker = true;
	}

	
		$('#buttons').show();
		$('.resadd').click(function(e){
			$('.resadd').removeClass('borderchoose');
			var ids = $(this).attr('ids');
			$('#resource').val(ids);
			$(this).addClass('borderchoose');
			//console.log(ids);
			//console.log($('#resource').val());

			//$('#buttons').find('input.submit').trigger('click');
			
			//$('#step2 form').submit();
		});

	// --- end resource add ---

	//$('#cloud_resource_pool_assign').attr('multiple', 'multiple');
	// hosts widjets:

	$('.hosterr tr.even').addClass('panel');
	$('.hosterr tr.even').addClass('widjet');
	$('.hosterr tr.odd').addClass('panel');
	$('.hosterr tr.odd').addClass('widjet');

	$('.hosterr .widget-header').each(function(){
		var pill = $(this).closest('tr').find('.pill');

		if ( pill.hasClass('active')) {
				color = '#9cc96b';
				vlasso = 'bg-success';
			}

			if (pill.hasClass('done')) {
				color = '#5fa2dd';
				vlasso = 'bg-primary';
			}

			if (pill.hasClass('no-res')) {
				color = '#ebaa4b';
				vlasso = 'bg-warning';
			}

			if (pill.hasClass('inactive')) {
				color = '#5fa2dd';
				vlasso = 'bg-primary';
			}
			
			if (pill.hasClass('notice')) {
				color = '#ebaa4b';
				vlasso = 'bg-warning';
			}

			if (pill.hasClass('transition')) {
				color = '#ebaa4b';
				vlasso = 'bg-warning';
			}

			if (pill.hasClass('off')) {
				color = '#4ebcda';
				vlasso = 'bg-info';
			}

			if (pill.hasClass('idle')) {
				color = '#ebaa4b';
				vlasso = 'bg-warning';
			}

			$(this).addClass(vlasso);
			pill.css('background-color', 'transparent');
			$(this).closest('tr').find('.widget-img').addClass(vlasso);


	});

	// --- end hosts widjet ---




	$('.OCHtbl .widget-header').each(function(){
		var pill = $(this).closest('tr').find('.pill');

		if ( pill.hasClass('active')) {
				color = '#9cc96b';
				vlasso = 'bg-success';
			}

			if (pill.hasClass('done')) {
				color = '#5fa2dd';
				vlasso = 'bg-primary';
			}

			if (pill.hasClass('no-res')) {
				color = '#ebaa4b';
				vlasso = 'bg-warning';
			}

			if (pill.hasClass('inactive')) {
				color = '#5fa2dd';
				vlasso = 'bg-primary';
			}
			
			if (pill.hasClass('notice')) {
				color = '#ebaa4b';
				vlasso = 'bg-warning';
			}

			if (pill.hasClass('transition')) {
				color = '#ebaa4b';
				vlasso = 'bg-warning';
			}

			if (pill.hasClass('off')) {
				color = '#4ebcda';
				vlasso = 'bg-info';
			}

			if (pill.hasClass('idle')) {
				color = '#ebaa4b';
				vlasso = 'bg-warning';
			}

			$(this).addClass(vlasso);
			pill.css('background-color', color);
			$(this).closest('tr').find('.widget-img').addClass(vlasso);


	});

	// --- end xen widjet ---

	

	$('#support_tab0 #passtogle').show();
	$('#support_tab0 #passgenerate').show();
	$('#support_tab0 #passgenerate').closest('div').show();

	$('#Tabelle').addClass('table table-hover table-vcenter');
	$('#Tabelle1').addClass('table table-hover table-vcenter');
	$('#Tabelle_2').addClass('table table-hover table-vcenter');
	$('#Tabelle_1').addClass('table table-hover table-vcenter');
	$('#cloud_selector_table').addClass('table table-hover table-vcenter');
	$('#cloud_transaction_table').addClass('table table-hover table-vcenter');
	$('#cloud_transaction_failed_table').addClass('table table-hover table-vcenter');
	$('#cloud_ip_mgmt_table').addClass('table table-hover table-vcenter');



$('.cloud a').each(function(){
	if ($(this).text() == 'New') {
		$(this).addClass('btn-labeled fa fa-plus btn-new');
	}
})


$('textarea').addClass('form-control');
$('#cdrom_button').addClass('btn-labeled fa fa-search'); 
$('td a.resize').addClass('btn-labeled fa fa-expand'); 
$('td.ha a.enable').addClass('btn-labeled fa fa-plus'); 
$('td a.clone').addClass('btn-labeled fa fa-clone'); 
$('#template_tab1 .span3 a.clone').addClass('btn-labeled fa fa-clone'); 
$('#template_tab1 .span3 a.console').addClass('btn-labeled fa fa-linux'); 
$('td a.snap').addClass('btn-labeled fa fa-mouse-pointer'); 
$('#browsebutton').addClass('btn-labeled fa fa-search');
$('a.edit').addClass('btn-labeled fa fa-pencil');
$('#htmlobject_box_add_storage a').addClass('btn-labeled fa fa-plus');
$('.cloud a.edit_nojs').addClass('btn-labeled fa fa-info');
$('a.disable').addClass('btn-labeled fa fa-close');
$('a.manage').addClass('btn-labeled fa fa-cog');
$('a.remove').addClass('btn-labeled fa fa-close');
$('#Tabellerr a.enable').addClass('btn-labeled fa fa-plus');	
$('a.add').addClass('btn-labeled fa fa-plus');	

if (notpicker != true) {
	$('select').each(function(){
		if($(this).hasClass('notselectpicker') == false) {
			$(this).addClass('selectpicker');
		}
	});
}
//$('.selectpicker').selectpicker();




	var tbldat = $('.htmlobject_td.head').html();
	tbldat = tbldat +  $('.htmlobject_td.pageturn_head').html();
	$('#Tabelle .htmlobject_td.head').remove(); 
	$('#Tabelle .htmlobject_td.pageturn_head').remove();

	$('.head-tbl-data').html(tbldat);

	$('.tab-content-area a.enable').addClass('btn-labeled fa fa-plus');
	$('.tab-content-area a.disable').addClass('btn-labeled fa fa-minus');
	$('.tab-content-area a.start').addClass('btn-labeled fa fa-play-circle');
	$('.tab-content-area a.stop').addClass('btn-labeled fa fa-power-off');
	$('.tab-content-area a.manage').addClass('btn-labeled fa fa-cog');
	// plugin manager interface:


	var sell = [];
	var pluginnav = '';
	$('select[name=plugin_filter] option').each(function(i) {
		var selval = $(this).attr('value');
		if (selval != 'storage') {
			sell.push(selval);
			pluginnav = pluginnav + '<li><a href="#demo-lft-tab-1" data-toggle="tab">'+selval+'</a></li>';
		}
	});

	$('#pluginnav').html(pluginnav);
	var labl = $('.label-purple:first').text();
	
	$('#pluginnav li').each(function(){
		if ($(this).text() == labl) {
			$(this).addClass('active');
		}
	})

	$('body').on('click', '#pluginnav li', function() {
		$('#pluginnav li').removeClass('active');
		$(this).addClass('active');
		var plugintype = $(this).text();

		$('.tab-content-area').load('/htvcenter/base/index.php .tab-content-area', 
			{
				'aa_plugins_action': 'select',
				'plugin':'aa_plugins',
				'plugin_filter': plugintype,	
				'plugins[limit]': 0,
				'plugins[offset]': 0,
				'plugins[order]': 'ASC',
				'plugins[sort]': 'name',
			}, function(){
					$('.tab-content-area a.enable').addClass('btn-labeled fa fa-plus');
					$('.tab-content-area a.disable').addClass('btn-labeled fa fa-minus');
					$('.tab-content-area a.start').addClass('btn-labeled fa fa-play-circle');
					$('.tab-content-area a.stop').addClass('btn-labeled fa fa-power-off');
					$('.tab-content-area a.manage').addClass('btn-labeled fa fa-cog');

					$('.tab-content-area td.configure').each(function(){
							if ($(this).text() != 'configure') {
								$(this).hide();
							}
					});


					$("body").tooltip({ selector: '[data-toggle="tooltip"]' });
					$('.tab-content-area .type').hide();
			});
	});
	

	// --- end plugin manager interface ---


		// close popup alert:

	$('.msgBox .close').click(function(){
		$(this).closest('.msgBox').hide();
	});

	// -- end close popup alert ---


	$('#aside-container').css('right', '-220px');

	// close event sidebar:

	$('.sidebarallink .btn-danger').click(function(){
			$('#content-container').css('width', '100%');
			$('#aside-container').css('right', '-220px');
	});

	// --- end close event sidebar ---

	// events and right sidebar

	$('#warningeventbox').click(function(){
		$('#content-container').css('width', '83%');
		$('#aside-container').css('right', '0px');
	});

	$('#messageeventbox').click(function(){
		$('#content-container').css('width', '83%');
		$('#aside-container').css('right', '0px');
		$('#demo-asd-tab-1').toggleClass('active in');
		$('#demo-asd-tab-3').toggleClass('active in');
		$('#asideul li').removeClass('active');
		$('#asideul li.third').addClass('active');
	});

	$('#erroreventbox').click(function(){
		$('#content-container').css('width', '83%');
		$('#aside-container').css('right', '0px');
		$('#demo-asd-tab-1').toggleClass('active in');
		$('#demo-asd-tab-2').toggleClass('active in');
		$('#asideul li').removeClass('active');
		$('#asideul li.second').addClass('active');
	});

	var eventsall = $('#preeventsall').html();
	var eventserror = $('#preeventserror').html();
	var eventswarning = $('#preeventsnotice').html();

	$('#sidebarallevents').html(eventsall);
	$('#sidebarallwarnings').html(eventswarning);
	$('#sidebarallerrors').html(eventserror);

	// --- end events and right sidebar ---

	// todo list

	$('#addtask').click(function(){
		todoadd();
		$('#newtaskinput').focus();
		$('#newtaskinput').val('');
	});

	$('#newtaskinput').on('keypress', function (event) {
         if(event.which === 13){
         	todoadd();
			$('#newtaskinput').focus();
			$('#newtaskinput').val('');
         }
   });

	$('#newtaskinput').click(function(){
		$(this).val('');
	});

	function todoadd() {
		var tasktext = $('#newtaskinput').val();
		var task = '<li class="list-group-item"><label class="form-checkbox form-icon form-text"><input type="checkbox"><span>'+tasktext+'</span></label></li>';
		$('#alltasks').append(task);
		tododatabasesave(tasktext);
	}

	function tododatabasesave(textero) {
		$.ajax({
		  url: '/htvcenter/base/index.php?action=todo&method=save&tasktext='+textero,
		});
	}

	function removetaskbytext(textero) {
		textinurl = textero.replace(' ', '%20');
		$.ajax({
		  url: '/htvcenter/base/index.php?action=todo&method=removebytext&tasktext='+textinurl,
		});
	}

	var click = 0;
	$('body').on('click', '#alltasks label', function() {
		
		if (click != 1) {
			if ($(this).hasClass('active')) {
				$(this).removeClass('active');
				var textero = $(this).find('span').text();
				
				tododatabasesave(textero);
			} else {
				$(this).addClass('active');
				var textero = $(this).find('span').text();
				removetaskbytext(textero);
			}
		}
		click = 1;
	});

	window.setInterval(function(){
	  click = 0;
	}, 100);



	// --- end todo list ---

	// language select:

	var lango = $('#Language_select').val();
	
	if (lango == 'de') {
		delang = 'Deutsch';
		var langdata = '<span class="lang-selected"><img alt="Deutsch" src="img/flags/germany.png" class="lang-flag"><span class="lang-id">DE</span><span class="lang-name">'+delang+'</span></span>';
	}

	if (lango == 'es') {
		delang = 'Espa&ntilde;ol';
		var langdata = '<span class="lang-selected"><img alt="Deutsch" src="img/flags/spain.png" class="lang-flag"><span class="lang-id">ES</span><span class="lang-name">'+delang+'</span></span>';
	}

	if (lango == 'en') {
		delang = 'English';
		var langdata = '<span class="lang-selected"><img alt="Deutsch" src="img/flags/united-kingdom.png" class="lang-flag"><span class="lang-id">EN</span><span class="lang-name">'+delang+'</span></span>';
	}

	$('#demo-lang-switch').html(langdata);

	$('.langselectoul a').click(function(){
		
		var switchlang = $(this).find('.lang-id').text();
		//alert(switchlang);
		

		if ( switchlang == 'DE' ) {
			$('#Language_select').val('de');
			set_language();
		}

		if ( switchlang == 'EN' ) {
			$('#Language_select').val('en');
			set_language();
		}

		if ( switchlang == 'ES' ) {
			$('#Language_select').val('es');
			set_language();
		}
	});
	// --- end language select ---


	// hddprogress and memoryprogress in header

		var hddp = $('#storagearea .progress-bar').attr('style');

		var mmp = $('.memoryprogress .progress-bar').attr('style');

		if (typeof(hddp) == 'undefined') {
			$('#ajaxbuf').load('/htvcenter/base/index.php?base=aa_server&controller=datacenter .memoryprogress', function(){
						mmp = $('.memoryprogress .progress-bar').attr('style');
						$('#ajaxbuf').load('/htvcenter/base/index.php?base=aa_server&controller=datacenter .hddprogress', function(){
							hddp = $('.hddprogress .progress-bar').attr('style');
							//console.log(hddp);
							//console.log(mmp);
							$('#ajaxbuf').remove();
							headprogress(hddp, mmp);
						});
						
			});
	
		} else {
			headprogress(hddp, mmp);
		}

		

		function headprogress(hddp, mmp) {
		
		$('.prgrshdd').attr('style', hddp);
		$('.prgrsmemory').attr('style', mmp);

		var mper = mmp.split(' ');
		var mpercent = mper[1].split(';');
		mper = mpercent[0];

		var hper = hddp.split(' ');

		var hpercent = hper[1].split(';');
		hper = hpercent[0];

		$('.hddpercentli').text(hper);
		$('.memorypercentli').text(mper);

		var text = mper + 'used';
		$('.msr-only').text(text);

		var text = hper + 'used';
		$('.hsr-only').text(text);

		var hpp = hper.split('%');
		var mpp = mper.split('%');

		hpp = parseInt(hpp[0]);
		mpp = parseInt(mpp[0]);



		var errorhead = 0;

		if (hpp > 80) {
			$('#fullhddlispace').show();
			errorhead = errorhead + 1;
		}

		if (mpp > 80) {
			$('#fullmemorylispace').show();
			errorhead = errorhead + 1;
		}

		if (errorhead > 0) {
			$('.dropdown-toggle .badge-dangero').text(errorhead);
			$('.dropdown-toggle .badge-dangero').show();
		} else {
			$('.dropdown-toggle .badge-dangero').hide();
		}

		

	}

	// --- end hddprogress and memoryprogress in header ---



	// esx main page content
	$('#chartdiv-inventory-server-legend ul li').each(function(){
		var esxnutanix = $(this).text();
		if ( esxnutanix.indexOf('ESX Host') + 1) {
			$(this).addClass('esxnutanixlink');
		}
	});

	$('body').on( 'click', '.esxnutanixlink', function(){
		$('#prenutanix').hide();
		$('#nutanix').show();
		 $(window).scrollTop(0);
	});

	$('#infoshow').click(function(){
		$('#infopopup').show();

	});

	$('#infopopup .close').click(function(){
		$('#infopopup').hide();
		
	});

	$('#infopopup button').click(function(){
		$('#infopopup').hide();
		
	});


	$('.jqplot-title').each(function(){
		if ($(this).text() == 'Server by type') {
			$(this).text('Hosts');
		}

		if ($(this).text() == 'Storage Pool') {
			$(this).text('Storage');
		}
	});
	
	$('#closenutanix').click(function(){
		$('#nutanix').hide();
		$('#prenutanix').show();
		 $(window).scrollTop(0);
	});

	// --- end of esx frontend --- 

	var psw1 = $('input[name=image_password]');
	var psw2 = $('input[name=image_password_2]');

	if (psw1.length === 0) {
		$('#passgenerate').closest('div').hide();
	}

	if (psw2.length == 0) {
		$('#passtoggle').closest('div').hide();
	}

	var hh = $('#hybrid_cloud_tab1 h2').text();
	if ( hh.indexOf('Add new account') + 1) {		
		$('.custom_tab input.submit').remove();
	}
	

	var hh = $('#hybrid_cloud_vm_tab1 h2').text();
	if ( hh.indexOf('Instances for account') + 1) {		
		$('.custom_tab input.submit').remove();
	}
	

	var hh = $('#hybrid_cloud_keypair_tab1 h2').text();
	if ( hh.indexOf('Keypairs for account') + 1) {		
		$('.custom_tab input.submit').remove();
	}
	

	var hh = $('#hybrid_cloud_ami_tab1 h2').text();
	if ( hh.indexOf('Add/remove Image for AMIs') + 1) {		
		$('.custom_tab input.submit').remove();
	}
	

	var hh = $('#hybrid_cloud_s3_tab1 h2').text();
	if ( hh.indexOf('S3 Buckets for account') + 1) {		
		$('.custom_tab input.submit').remove();
	}
	

	var hh = $('#hybrid_cloud_volume_tab1 h2').text();
	if ( hh.indexOf('EBS Volumes for Account') + 1) {		
		$('.custom_tab input.submit').remove();
	}

	var hh = $('#hybrid_cloud_snapshot_tab1 h2').text();
	if ( hh.indexOf('Snapshots for Account') + 1) {		
		$('.custom_tab input.submit').remove();
	}

	var hh = $('#hybrid_cloud_tab1 h2').text();
	if ( hh.indexOf('Add new account') + 1) {		
		$('#region').hide();
	}

	var hh = $('#hybrid_cloud_vm_tab2 h2').text();
	if ( hh.indexOf('Add new Instance') + 1) {		
		$('.custom_tab input.submit').remove();
	}

	var hh = $('#hybrid_cloud_keypair_tab1 h2').text();
	if ( hh.indexOf('Keypairs for account  HTBase_AWS') + 1) {		
		$('#region').css('margin-top', '0px');
	}

	var hh = $('#hybrid_cloud_s3_tab2 h2').text();
	if ( hh.indexOf('Create S3 Bucket') + 1) {		
		$('.custom_tab input.submit').remove();
		$('#region').css('margin-top', '-80px');
	}

	var hh = $('#hybrid_cloud_volume_tab2 h2').text();
	if ( hh.indexOf('Add new EBS Volume') + 1) {		
		$('.custom_tab input.submit').remove();
	}

	
	

	
		


	 

	

	// modal with info popup:
		$('.headinfo').click(function(){
			$('#modal-infoserv').modal();
			//$('#modal-infoserv').show();
		});
		// --- end modal with info popup ---

	// menu hide:

	$('.private_windows_plane').toggleClass('col-sm-9 col-sm-offset-3 col-md-10 col-md-offset-2');
	$('.private_windows_plane').toggleClass('col-sm-12 col-md-12 col-lg-12 col-xs-12');	
	$('#menubutton').click(function(){
		if( $('.sidebar').is(':visible') == true) {
			$('.sidebar').hide();
			$(this).html('<i class="fa fa-plus"></i> Show Menu');
			$('#windows_plane').toggleClass('col-sm-9 col-sm-offset-3 col-md-10 col-md-offset-2');
			$('#windows_plane').toggleClass('col-sm-12 col-md-12 col-lg-12 col-xs-12');
			$('.private_windows_plane').toggleClass('col-sm-9 col-sm-offset-3 col-md-10 col-md-offset-2');
			$('.private_windows_plane').toggleClass('col-sm-12 col-md-12 col-lg-12 col-xs-12');
		} else {
			$('.sidebar').show();
			$(this).html('<i class="fa fa-minus"></i> Hide Menu');
			$('#windows_plane').toggleClass('col-sm-9 col-sm-offset-3 col-md-10 col-md-offset-2');
			$('#windows_plane').toggleClass('col-sm-12 col-md-12 col-lg-12 col-xs-12');
			$('.private_windows_plane').toggleClass('col-sm-9 col-sm-offset-3 col-md-10 col-md-offset-2');
			$('.private_windows_plane').toggleClass('col-sm-12 col-md-12 col-lg-12 col-xs-12');
		}
	});

	// --- end menu hide ---

	// refreshlink:

	$('.refreshlink').click(function(){
		$(this).closest('form').submit();
	});
	// --- end refresh link ---

	// Table box design colors actions:
	$('.appliance_state').each(function(){
		colorborder($(this));
	});


	$('#cloud_users .htmlobject_tr').each(function(){
		colorborder($(this));
	});

	$('#cloud_appliances .htmlobject_tr').each(function(){
		colorborder($(this));
	});

	$('#cloud_requests .htmlobject_tr').each(function(){
		colorborder($(this));
	});

	$('#cloud_requests tr.even').each(function(){
		colorborder($(this));
	});





	

	

	$('.resource_state').each(function(){
		colorborder($(this));
	});
	
	
	var storagelast = $('.storage_state').filter(":last");
	var lastcheckoid = storagelast.closest('tr').find('.storage_identifier').find('input').attr('id');
	
	$('.storage_state').each(function(){
		colorborder($(this));
		var id = $(this).closest('tr').find('.storage_identifier').find('input').attr('id');
		if (lastcheckoid == id) {
			$('#storageside').hide();
		}
		
	});

	$('#cloud_private_image_table tr').each(function(){
		colorborder($(this));
	});


	$('.image_isactive').each(function(){
		colorborder($(this));
	});

	// --- end table box design actions ---

	// language flag changing:
	var lang = $('#Language_select').val();
    
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
	



	//checkbox position in boxes

	$('.resource_identifier').each(function(){
		var wind = $(this).closest('.htmlobject_tr');
		var bef = $(wind).find('.data');
		$(this).insertBefore(bef);
		$(this).addClass('checko');
	});

	$('#cloud_users .cloud_user_id').each(function(){
		var wind = $(this).closest('.htmlobject_tr');
		var bef = $(wind).find('.info');
		$(this).insertBefore(bef);
		$(this).addClass('checko');
	});


	$('#cloud_usergroup_table .cloud_usergroup_id').each(function(){
		var wind = $(this).closest('.htmlobject_tr');
		var bef = $(wind).find('.cg_id');
		$(this).insertBefore(bef);
		$(this).addClass('checko');
	});

	$('table#cloud_requests .cloud_request_id').each(function(){
		var wind = $(this).closest('.htmlobject_tr');
		var bef = $(wind).find('.info');
		$(this).insertBefore(bef);
		$(this).addClass('checko');
	});

	$('.storage_identifier').each(function(){
		var wind = $(this).closest('.htmlobject_tr');
		var bef = $(wind).find('.data');
		$(this).insertBefore(bef);
		$(this).addClass('checko2');
	});

	

	// --- end checkbox positions in boxes


	// Plugin-manager-interface things

	$('#Tabellerplug tr.odd td').each(function(){
		var texto = $(this).text();
		var size = texto.length;
		if (size == 1) {
			$(this).hide();
		}
	});

	$('#Tabellerplug tr.even td').each(function(){
		var texto = $(this).text();
		var size = texto.length;
		if (size == 1) {
			$(this).hide();
		}
	});

	$('#Tabellerplug td.type').each(function(){
		var wind = $(this).closest('.htmlobject_tr');
		var bef = $(wind).find('.name');
		$(this).insertBefore(bef);
	});

	$('#Tabellerplug td.description').each(function(){
		$(this).hide();
	});


	var eventh = $('.eventbox').width() + 20;
	var eventwind = $('.eventwindow').height() + eventh + 40;
	$('.eventbox').css('height', eventh+'px');
	$('.eventwindow').css('height', eventwind+'px');

	// --- end plugin manager interface ---




	 
});

	$('#vmware_esx_discovery_tab0 tr.odd').addClass('panel panel-bordered panel-info');
	$('#vmware_esx_discovery_tab0 tr.even').addClass('panel panel-bordered panel-info');


	colorborder($('#vmware_esx_discovery_tab0 tr.even .vmw_esx_ad_state'));
	colorborder($('#vmware_esx_discovery_tab0 tr.odd .vmw_esx_ad_state'));

	$('.kernel tr.odd').addClass('panel panel-bordered panel-info');
	$('.kernel tr.even').addClass('panel panel-bordered panel-info');

	$('.resource tr.odd').addClass('panel panel-bordered');
	$('.resource tr.even').addClass('panel panel-bordered');
	colorborder($('.resource tr.even .resource_state'));
	colorborder($('.resource tr.odd .resource_state'));

	$('.image tr.odd').addClass('panel panel-bordered');
	$('.image tr.even').addClass('panel panel-bordered');
	colorborder($('.image tr.even .resource_state'));
	colorborder($('.image tr.odd .resource_state'));

	$('.storage tr.odd').addClass('panel panel-bordered');
	$('.storage tr.even').addClass('panel panel-bordered');
	colorborder($('.storage tr.even .resource_state'));
	colorborder($('.storage tr.odd .resource_state'));

	$('.cloud_nephos_user_table tr.odd').addClass('panel panel-bordered');
	$('.cloud_nephos_user_table tr.even').addClass('panel panel-bordered');
	colorborder($('.cloud_nephos_user_table tr.even .appnamer'));
	colorborder($('.cloud_nephos_user_table tr.odd .appnamer'));

	$('.cloud tr.odd').addClass('panel panel-bordered');
	$('.cloud tr.even').addClass('panel panel-bordered');
	colorborder($('.cloud tr.even .resource_state'));
	colorborder($('.cloud tr.odd .resource_state'));

	$('#cloud_private_image_table tr.odd').addClass('panel panel-bordered');
	$('#cloud_private_image_table tr.even').addClass('panel panel-bordered');
	//colorborder($('#cloud_private_image_table tr.even .appnamer'));
	//
	$('#cloud_private_image_table tr.odd .appnamer').each(function(){
		colorborder($(this));
	});

	$('#cloud_private_image_table tr.even .appnamer').each(function(){
		colorborder($(this));
	});

	$('.clreqtbl').find('td.cr_status').each(function(){
		console.log($(this));
		colorborder($(this));
	});

	$('.thetableee').find('.actiontable').closest('tr').css('width', '2000px !important');

var butto = $('#project_tab0 #form #buttons').html();
$('#project_tab0 #form #buttons').hide();
$('#project_tab0 form').append(butto);
// function border boxes color changing:
function colorborder(objc) {
		var wind = objc.closest('tr');
		
		
		if ($(wind).is(':visible') == true) {
			
			var color = '#5fa2dd';
			var	vlasso = 'panel-primary';

			if (objc.find('.pill').hasClass('active')) {
				color = '#9cc96b';
				vlasso = 'panel-success';
				console.log('aaaaa');
			}

			if (objc.find('.pill').hasClass('error')) {
				color = '#f76c51';
				vlasso = 'panel-danger';
			}

			if (objc.find('.pill').hasClass('done')) {
				color = '#5fa2dd';
				vlasso = 'panel-primary';
			}

			if (objc.find('.pill').hasClass('no-res')) {
				color = '#ebaa4b';
				vlasso = 'panel-warning';
			}

			if (objc.find('.pill').hasClass('deprovision')) {
				color = '#ebaa4b';
				vlasso = 'panel-warning';
			}

			if (objc.find('.pill').hasClass('new')) {
				color = '#ebaa4b';
				vlasso = 'panel-warning';
			}

			if (objc.find('.pill').hasClass('approve')) {
				color = '#ebaa4b';
				vlasso = 'panel-warning';
			}

			if (objc.find('.pill').hasClass('deny')) {
				color = '#ebaa4b';
				vlasso = 'panel-warning';
			}

			if (objc.find('.pill').hasClass('inactive')) {
				color = '#5fa2dd';
				vlasso = 'panel-primary';
			}

			if (objc.find('.pill').hasClass('idle')) {
				color = '#5fa2dd';
				vlasso = 'panel-primary';
			}
			
			

			

			if (objc.find('.pill').hasClass('notice')) {
				color = '#ebaa4b';
				vlasso = 'panel-warning';
			}

			if (objc.find('.pill').hasClass('transition')) {
				color = '#ebaa4b';
				vlasso = 'panel-warning';
			}

			if (objc.find('.pill').hasClass('off')) {
				color = '#4ebcda';
				vlasso = 'panel-info';
			}

			
			
			var csska = '1px solid ' + color + ' !important';
		
			
			var sel = $(wind).find('.appnamer').closest('tr');


			

			$(sel).addClass('panel-bordered');
			$(sel).addClass('panel');
			$(sel).css('border', csska);
			$(sel).removeClass('panel-info');
			$(sel).removeClass('panel-danger');
			$(sel).removeClass('panel-warning');
			
			$(sel).addClass(vlasso);
			$(sel).find('.pill').css('background-color', color);

			sel = $(sel).find('.panel-title');
			var checkr = $(wind).find('.appliance_identifier');

			var chch = $(wind).find('.identifier').html();
			
			if (typeof(chch) != 'undefined') {
				if (chch.length != 0) {
					$(wind).find('.checkooo').html(chch);
					//$('.identifier').hide();
				}
			}
		
			if ($(checkr).find('input').length == 0 ) {
				var idp = $(wind).find('.appnamer').attr('appid');
				var selidp = $(sel).closest('tr').attr('onclick');
				var lll = selidp.split(' ');
				var ll = lll[1].split('\'');
				selidp = ll[1];

				var l = '<input type="checkbox" value="'+idp+'" name="appliance_identifier[]" id="'+selidp+'" class="checkbox ololocheck">';
				if (checkr.length == 0) {
					var checkr = $(wind).find('.appliance_identifier');

				} else {
					$(checkr).html(l);
					$(sel).closest('tr').find('.pill').css('top', '60px');
				}

				checkr.insertBefore(sel);
			} else {
				$(wind).find('.appnamer').append(checkr);
			}

		

			$(wind).find('.appliance_state').insertAfter(sel);

		

	}

	var plugcount = 0;
	$('#vmware_esx_ds_tab2 td.state .pill').each(function(){
		colorpill($(this));
	});

	$('#cloud_images td.state .pill').each(function(){
		colorpill($(this));
	});
	$('#Tabelle td.state .pill').each(function(){
		colorpill($(this));
	});

	$('#Tabelle td.appliance_state .pill').each(function(){
		colorpill($(this));
	});

	$('#Tabelle td.vmw_esx_ad_state .pill').each(function(){
		colorpill($(this));
	});

	$('.divtxt .pill').each(function(){
		colorpill($(this));
	});
	

	function colorpill(obj) {
		var text = obj.text();
		var color = null;

		if (text == 'active') {
			color = '#9cc96b';
		}

		if (text == 'done') {
			color = '#5fa2dd';
		}

		if (text == 'no-res') {
			color = '#ebaa4b';
		}

			

		if (text == 'inactive') {
			color = '#5fa2dd';
		}

		if (text == 'unaligned') {
			color = '#5fa2dd';
		}

		

		if (text == 'paused') {
			color = '#5fa2dd';
		}

		if (text == 'notice') {
			color = '#ebaa4b';
		}

		if (text == 'transition') {
			color = '#ebaa4b';
		}
			
		if (text == 'off') {
			color = '#4ebcda';
		}

		if (text == 'idle') {
			color = '#4ebcda';
		}

		
		obj.css('background-color', color);
		
	}


	// inactive fixes:
		$('#appliance_tab0 #Tabellerr tr.even').each(function(){
			if ($(this).find('.pill').text() == 'inactive') {
				inactivefix($(this));
			}
			
		});

		$('#appliance_tab0 #Tabellerr tr.odd').each(function(el){
			if ($(this).find('.pill').text() == 'inactive') {
				inactivefix($(this));
			}
		});

		function inactivefix(el) {
			
			var pill = $(el).find('.pill');
			var checkp = $(el).find('.checkbox');
			pill.css('top','11px');
			checkp.addClass('inalign');
		}


	var trl = $('#appliance_tab0').find('tr.last');
	//console.log(trl);

	if (trl.find('.pill').hasClass('inactive')) {
		
		trl.find('.checkbox').removeClass('inalign');

		if (trl.hasClass('even')) {
			trl.find('.checkbox').addClass('checkfix');
		}
	}

	// --- end inactive fixes ---
	

$('.gaugetable').find('#Tabelle').removeClass('table-hover');

}
// --- end color change functionality ---

// header align in plugins popup:
function headfix() {
	if ($('#aa_plugins_tabmsgBox').is(':visible') == true) {
		if($('#navbar').offset().top < 0) {
			$('#navbar').css('top','50px');
			$('#container.mainnav-lg #mainnav-container').css('top','50px');
			$('#aa_plugins_tab0').css('top', '50px');
		}
	}
}

/*if ($('#aa_plugins_tabmsgBox').is(':visible') == true) {
	setInterval(headfix, 2500);
	setInterval(headfix, 4000);
}*/

Pace.on('done', headfix);
$('#appliance_tab0 a.enable').each(function(){
	if ($(this).text() == 'noVNC') {
		$(this).text('Svaccess');
	}
})
	// -- end header align --