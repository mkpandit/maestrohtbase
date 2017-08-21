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

var seriesColors = [ '#a6c600',
					'#177bbb',
					'#afd2f0',"#cccccc", "#8cc63f", "#ff5800", "#223e99", "#579575", "#839557", "#958c12", "#953579", "#4b5de4", "#d8b83f", "#ff5800", "#0085cc"];

// options for dummy donut charts
var donutOptions = {
	height: 200,
	width: 200,
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

function get_limits() {
	var data = $.ajax({
		url : "api.php?action=limits",
		type: "POST",
		cache: false,
		async: false,
		dataType: "json",
		success : function () { }
	}).responseText;
	var response = $.parseJSON(data);
	make_donut('systems', response.systems_list);
	make_donut('disk', response.disk_list);
	make_donut('memory', response.memory_list);
	make_donut('cpu', response.cpu_list);
	make_donut('network', response.network_list);
}

function renderDonutLegend(values) {
	var legend = $('<ul>');
	$.each(values, function(k,v) {
		legend.append(
			$('<li>').append(
				$('<div>').addClass('legend-tile').attr('style', 'background:' + seriesColors[k])
			).append( v[0] )
		);
	})
	return legend;
}

function renderdon(values) {
		var legend = [];

		$.each(values, function(k,v) {
			var spliters = v[0].split(' ');
			var last = '';
			spliters.forEach(function(entry){
				last = entry;
			});
			
			var val = v[1];
			if(val > 1000) {
				val = val/1000;
			}

			var res = Math.round(val);
			legend.push({label: last, value: res});
		})
		return legend;
	}

function make_donut(mode,list) {
	var values = [];
	if(list != false && $('#chartdiv-inventory-'+mode).length) {
		try{
			donutOptions.title.text == '';
			lang = 'lang_'+mode;
			donutOptions.title.text = eval(lang);
			var titlo = eval(lang);
			for(i in list) {
				k = list[i][0];
				v = list[i][1];
				if(mode == 'disk' || mode == 'memory') {
					if(v < 1000) {
						var res = Math.round(v);
						size = res+ ' MB '+k;
					} else {
						v = v/1000;
						var res = Math.round(v);
						size = (res)+ ' GB '+k;
					}
					var res = Math.round(v);
					values.push([size, res]);
				} else {
					values.push([v+ ' '+k, v]);
				}
			}
			
			var legendo = renderdon(values);
			$('#chartdiv-inventory-'+mode).html('');
			 
			Morris.Donut({
				element: 'chartdiv-inventory-'+mode,
				data: legendo,
				colors: [
					'#a6c600',
					'#177bbb',
					'#afd2f0',
					"#1fa67a", "#ffd055", "#39aacb", "#cc6165", "#c2d5a0", "#579575", "#839557", "#958c12", "#953579", "#4b5de4", "#d8b83f", "#ff5800", "#0085cc"
				],
				resize:true
			});

			$('#chartdiv-inventory-'+mode).closest('.dashboard').find('.panel-title').text(titlo);
			var legend = renderDonutLegend(values);

			$('#chartdiv-inventory-'+mode+'-legend').html('');
			$('#chartdiv-inventory-'+mode+'-legend').append(legend);
		} catch(e) { alert(e); }
	}

}

$(document).ready(function(){
	get_limits();
	refresh = window.setInterval(function() {get_limits();}, 5000);
});
