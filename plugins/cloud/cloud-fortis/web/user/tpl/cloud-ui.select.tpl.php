<!--
/*
    htvcenter Enterprise developed by htvcenter Enterprise GmbH.

    All source code and content (c) Copyright 2014, htvcenter Enterprise GmbH unless specifically noted otherwise.

    This source code is released under the htvcenter Enterprise Server and Client License, unless otherwise agreed with htvcenter Enterprise GmbH.
    The latest version of this license can be found here: http://htvcenter-enterprise.com/license

    By using this software, you acknowledge having read this license and agree to be bound thereby.

                http://htvcenter-enterprise.com

    Copyright 2014, htvcenter Enterprise GmbH <info@htvcenter-enterprise.com>
*/
-->



<script type="text/javascript">
		window.onload = function() {
			var th = $('#Tabelle').height();
			$('#htvcenter_enterprise_footer').css("top",th + 90);

		};
</script>


<div id="content_container">

<h1>{title}</h1>
<form action="{thisfile}">
{table}
</form>

</div>