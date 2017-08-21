<?php
/*
    htvcenter Enterprise developed by HTBase Corp.

    All source code and content (c) Copyright 2015, HTBase Corp unless specifically noted otherwise.

    This source code is released under the htvcenter Enterprise Server and Client License, unless otherwise agreed with HTBase Corp.

    By using this software, you acknowledge having read this license and agree to be bound thereby.

                http://www.htbase.com

    Copyright 2015, HTBase Corp <contact@htbase.com>
*/
class htmlobject_preloader extends htmlobject
{
	function start($string = 'Loading ...') {
		echo '
		<div id="Loadbar" style="margin:40px 0 0 40px;display:none">
		<strong>'.$string.'</strong>
		</div>
		<script>
		document.getElementById("Loadbar").style.display = "block";
		</script>
		';
		flush();
	}

	function stop() {
		echo '
		<script>
			document.getElementById("Loadbar").style.display = "none";
		</script>
		';
	}
}