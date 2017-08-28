<!--
/*
    htvcenter Enterprise developed by HTBase Corp.

    All source code and content (c) Copyright 2015, HTBase Corp unless specifically noted otherwise.

    This source code is released under the htvcenter Enterprise Server and Client License, unless otherwise agreed with HTBase Corp.

    By using this software, you acknowledge having read this license and agree to be bound thereby.

                http://www.htbase.com

    Copyright 2015, HTBase Corp <contact@htbase.com>
*/
//-->

<script>



function filterevents() {
		var filtsel = $('#events_filter select').val();
		
		window.location.replace('/htvcenter/base/index.php?base=event&event_filter='+filtsel);
}
</script>

<h2 class="inner">{label}</h2>

<br/>
<div class="panel">
								<div class="panel-heading">
									<h3 class="event-panel-title">Maestro Events Table</h3>
								</div>


					
								<!--Hover Rows-->
								<!--===================================================-->
								<div class="panel-body">
									<form id="eventfrm" action="/htvcenter/base/index.php?base=event" method="POST">
									<div class="head-tbl-data"></div>
									{table}
									
								</div>
								<!--===================================================-->
								<!--End Hover Rows-->
					
							</div>


