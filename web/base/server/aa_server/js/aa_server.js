/* handle IE missing map function */
if (!Array.prototype.map)
{
	Array.prototype.map = function(fun)
	{
		var len = this.length;
		if (typeof fun != "function")
			throw new TypeError();
		var res = new Array(len);
		var thisp = arguments[1];
		for (var i = 0; i < len; i++)
		{
			if (i in this)
			res[i] = fun.call(thisp, this[i], i, this);
		}
		return res;
	};
}


$(document).ready(function(){
	
	var seriesColors = ['#a6c600', '#177bbb', '#afd2f0', "#1fa67a",  "#ffd055", "#39aacb", "#cc6165", "#c2d5a0", "#579575", "#839557", "#958c12", "#953579", "#4b5de4", "#d8b83f", "#ff5800", "#0085cc"];
			
	// options for dummy donut charts
	var donutOptions = {
		height: 200,
		title: {
			text: '',
			textColor: '#353535'
		},
		seriesColors: seriesColors,
		
		seriesDefaults: {
			renderer:$.jqplot.DonutRenderer,
			rendererOptions:{
				sliceMargin: 5,
				shadow: false,
				startAngle: -90,
				showDataLabels: false
			}
		},
		legend: { 
			show: false
		},
		grid: {
			drawGridLines: false,
			background: '#ffffff',
			borderWidth: 0,
			shadow: false,
			renderer: $.jqplot.CanvasGridRenderer,
			rendererOptions: {}
		}
	};
	

	function renderDonutLegend(values) {
		var legend = [];

		$.each(values, function(k,v) {
			if (v[0] == 'KVM Host') {
				v[0] = 'OCH Host';
			}

			if (v[0] == 'KVM VM') {
				v[0] = 'OCH VM';
			}
			legend.push({label: v[0], value: v[1]});
		})
		return legend;
	}


	function renderDonutLabelsLegend(values) {
		var legend = $('<ul>');
		$.each(values, function(k,v) {
			
			legend.append(
				$('<li>').append(
					$('<div>').addClass('legend-tile').attr('style', 'background:' + seriesColors[k])
				).append( v[0])
			);
		})
		return legend;
	}


	/**
	 * Build server donut chart. Does not use jqplots build-in 
	 * legend due to lack of positioning options
	 */
	function server_donut() {
		var server_list = htvcenter.get_server_list();
		var server_values = [];
		var server_values2 = [];
		var virtualization, virtualization_list = [];
		var hist = {};
		
		if(server_list != false && $('#chartdiv-inventory-server').length) {
			try{
				// remove "no data" message
				$('#chartdiv-inventory-server .no-data-available').remove();
			
				donutOptions.title.text = lang_inventory_servers;
			
				$.each(server_list, function(k,server){
					virtualization_list.push(server['appliance_virtualization']);
				});
				virtualization_list.map( function (a) { if (a in hist) hist[a] ++; else hist[a] = 1; } );
				$.each(hist, function(k,v){
					if (k == 'KVM VM (localboot)') {
						k = 'KVM VM';
					}

					if (k == 'ESX VM (localboot)') {
						k = 'ESX VM';
					}
					server_values.push([k ,v]);
					if (k == 'KVM Host') {
						k = 'OCH Host';
					}

					if (k == 'KVM VM') {
						k = 'OCH VM';
					}
					server_values2.push([k + ' (' +v+ ')',v]);
				})
				//$.jqplot('chartdiv-inventory-server', [server_values], donutOptions);
				var legend = renderDonutLegend(server_values);
				var legend2 = renderDonutLabelsLegend(server_values2);
				$('#server-donut').show();
	Morris.Donut({
		element: 'server-donut',
		data: legend,
		colors: [
			'#a6c600',
			'#177bbb',
			'#afd2f0',
			"#1fa67a", "#ffd055", "#39aacb", "#cc6165", "#c2d5a0", "#579575", "#839557", "#958c12", "#953579", "#4b5de4", "#d8b83f", "#ff5800", "#0085cc"
		],
		resize:true
	});



	//console.log(server_values);


				$('#chartdiv-inventory-server-legend').append(legend2);
			} catch(e) { }
		}
	}

	/**
	 * Build storage donut chart. Does not use jqplots build-in 
	 * legend due to lack of positioning options
	 */
	function storage_donut() {
		var storage_list = htvcenter.get_storage_list();
		var storage_values = [];
		var storage_values2 = [];
		var deploment, deployment_list = [];
		var hist = {};
		//console.log('here1');
		if(storage_list != false && $('#chartdiv-inventory-storage').length) {
			console.log('here2');
			try{
				// remove "no data" message
				$('#chartdiv-inventory-storage .no-data-available').remove();

				donutOptions.title.text = lang_inventory_storages;
				donutOptions.seriesDefaults.rendererOptions.startAngle = 0;
					
				$.each(storage_list, function(k,storage){
					deployment_list.push(storage['storage_type']);
				});
				deployment_list.map( function (a) { if (a in hist) hist[a] ++; else hist[a] = 1; } );
				$.each(hist, function(k,v){
					storage_values.push([k ,v]);
					storage_values2.push([k + ' (' +v+ ')',v]);
				})
				//$.jqplot('chartdiv-inventory-storage', [storage_values], donutOptions);
				
				var legend = renderDonutLegend(storage_values);
				var legend2 = renderDonutLabelsLegend(storage_values2);
				
				$('#storage-donut').show();

	Morris.Donut({
		element: 'storage-donut',
		data: legend,
		colors: [
			'#a6c600',
			'#177bbb',
			'#afd2f0',
			"#1fa67a",  "#ffd055", "#39aacb", "#cc6165", "#c2d5a0", "#579575", "#839557", "#958c12", "#953579", "#4b5de4", "#d8b83f", "#ff5800", "#0085cc"
		],
		resize:true
	});
				$('#chartdiv-inventory-server-storage').append(legend2);
			} catch(e) { }
		}
	}
	
	/**
	 *	Populate new laod data to load chart and redraw chart canvas
	 */
	function updateLoadChart() {
		var stats = htvcenter.get_datacenter_load();
		//console.log("HTV");
		//console.log(stats);
		var dc_load = [[],[],[]];	
		var xaxis_labels = []; 
		var idx;
		
		if(stats != null && $('#chartdiv-load').length) {
			
			$.each(stats, function(k,v) {
				idx = parseInt(k)+1;
				xaxis_labels.push(idx%5 > 0 ? (' ') : (parseInt(k)+1));
				
				dc_load[0].push( [idx, parseFloat(v['datacenter_load_overall'] )] );
				dc_load[1].push( [idx, parseFloat(v['datacenter_load_server'] )] );
				dc_load[2].push( [idx, parseFloat(v['datacenter_load_storage'] )] );
			});
			
		//console.log(dc_load[0]);
			
		if ( typeof(dc_load[0][59]) != 'undefined') {
			var serverp = dc_load[1][59];
			var storagep = dc_load[2][59];
			var datacenterp = dc_load[0][59];
		} else {
			var serverp = dc_load[1][0];
			var storagep = dc_load[2][0];
			var datacenterp = dc_load[0][0];
		}

		if (typeof(datacenterp) == 'undefined') {
			var datacenterp = [0, 0, 0];
		}


		if (typeof(serverp) == 'undefined') {
			var serverp = [0, 0, 0];
		}


		if (typeof(storagep) == 'undefined') {
			var storagep = [0, 0, 0];
		}

			var datacento = (datacenterp[1]+storagep[1])/2;
			datacento = Math.round(datacento * 100) / 100;
			datacento = datacento + '%';
			$('.datacenterp').text(datacento);
			$('.serverp').text(serverp[1]+'%');
			$('.storagep').text(storagep[1]+'%');

			$('.datacenterpbar').css('width', datacenterp[1]+'%');
			$('.serverpbar').css('width', serverp[1]+'%');
			$('.storagepbar').css('width', storagep[1]+'%');

			$('#chartdiv-load *').remove();
			var plot1 = $.jqplot('chartdiv-load', dc_load, 
				{
					seriesColors: seriesColors,
					showMarker:false,
					seriesDefaults: {
						linewidth: 1,
						showMarker: false
					},
					axesDefaults: {
						min: 0
					},
					axes:{
						xaxis:{
							ticks: xaxis_labels.reverse(),
							renderer: $.jqplot.CategoryAxisRenderer,
							tickOptions: {
								showGridline: false
							}
						}
					},
					grid: {
						drawGridLines: false,
						gridLineColor: '#1fa67a',
						background: '#ffffff',
						borderWidth: 0,
						shadow: false,
						renderer: $.jqplot.CanvasGridRenderer,
						rendererOptions: {}
					}
				}
			);
		}

	var i = 0;
	var day_data = [];
	for ( i=0; i < 60; i++ ) {
		//console.log(dc_load[0][i][1]);
		var first = 0;
		var second = 0;
		 if ( typeof dc_load[0][i] !== 'undefined') {
			if ( typeof dc_load[0][i][1] !== 'undefined') {
				first = dc_load[0][i][1];
			}

			if ( typeof dc_load[2][i][1] !== 'undefined') {
				second = dc_load[2][i][1];
			}
		 }

		day_data.push({"elapsed": i, "value": first, "b":second});
	}
	
	var chart = Morris.Area({
		element: 'morris-chart-network',
		data: day_data,
		axes:false,
		xkey: 'elapsed',
		ykeys: ['value', 'b'],
		//labels: ['Load', 'Minute'],
		//yLabelFormat :function (y) { return y.toString() + ' minutes'; },
		gridEnabled: false,
		gridLineColor: 'transparent',
		lineColors: ['#8eb5e3','#1b72bc'],
		lineWidth:0,
		pointSize:0,
		pointFillColors:['#3e80bd'],
		pointStrokeColors:'#3e80bd',
		fillOpacity:.7,
		gridTextColor:'#999',
		parseTime: false,
		resize:true,
		behaveLikeLine : true,
		hideHover: 'auto'
	});
	}


	function updateEventSection() {
		var events = htvcenter.get_event_list();
		
		
		if(events) {
			// delete tbody content 
			$('.eventtable tbody').html('');
			
			// add updated events
			$.each(events, function(k,event){
				var evento = 'null';
				
				if (event['event_source'] == 'htvcenter_lock_queue') {
					evento = 'htvcenter_lock_queue';
				} else {
					evento = event['event_source'];
					var newString = evento.replace('htvcenter', 'htvcenter');
					evento = newString;
				}

				
				var event_time = new Date((parseInt(event['event_time'])*1000));
				$('.eventtable tbody').append(
					$('<tr>')
						.append($('<td>').html(htvcenter.formatDate(event_time, '%Y/%M/%d %H:%m:%s')))
						.append($('<td>').html(
							$('<span>').attr('class','pill ' + htvcenter.getEventStatus(event['event_priority']))
						))
						.append($('<td>').html(evento))
						.append(
							$('<td>')
								.attr('title', event['event_description'])
								.html(
									htvcenter.crop(event['event_description'], 50)
								)
						)
				);
			});
		}
	}

	function updateLoadSection() {
		var status = htvcenter.get_datacenter_status();
		
		if(status != null) {
			$('.bar-01 .bar').attr('style','width:' + (status[0]*10) + '%');
			$('.bar-01 .bar label').html(status[0]);
			
			$('.bar-02 .bar').attr('style','width:' + (status[3]*10) + '%');
			$('.bar-02 .bar label').html(status[3]);
			$('.bar-02 .peak').attr({'style' : 'left: ' + (status[4]*10) + '%'});
	
			$('.bar-03 .bar').attr('style','width:' + (status[1]*10) + '%');
			$('.bar-03 .bar label').html(status[1]);
			$('.bar-03 .peak').attr({'style' : 'left: ' + (status[2]*10) + '%'});
		}
	}

	server_donut();
	storage_donut();

	updateLoadChart();
	updateLoadSection();
	updateEventSection();

	
	// Init refresh interval for datacenter load section and chart, 
	// event list section
	setInterval(function (){
	
		updateLoadChart();
		updateLoadSection();
		updateEventSection();
		
	}, 5000);
	
	
	// add refresh events to widget buttons
	$('.refresh-load-current').click( function() {
		updateLoadSection();
	});
	$('.refresh-load-chart').click( function() {
		updateLoadChart();
	});
	$('.refresh-events').click( function() {
		updateEventSection();
	});
	
	
});
