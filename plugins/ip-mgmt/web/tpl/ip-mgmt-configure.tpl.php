<!--
/*
    htvcenter Enterprise developed by htvcenter Enterprise GmbH.

    All source code and content (c) Copyright 2014, htvcenter Enterprise GmbH unless specifically noted otherwise.

    This source code is released under the htvcenter Enterprise Server and Client License, unless otherwise agreed with htvcenter Enterprise GmbH.
    The latest version of this license can be found here: http://htvcenter-enterprise.com/license

    By using this software, you acknowledge having read this license and agree to be bound thereby.

                http://htvcenter-enterprise.com

    Copyright 2014, htvcenter Enterprise GmbH <info@htvcenter-enterprise.com>

{?}

*/
-->

<script type="text/javascript">

	window.onload = function() {

		$("#select_0").change(function() {
			var sid = $("#select_0 option:selected").val();
			if (sid != 'none') {
				$("#select_1 option:[value=" + sid + "]").remove();
				$("#select_2 option:[value=" + sid + "]").remove();
				$("#select_3 option:[value=" + sid + "]").remove();
			}
		})
		$("#select_1").change(function() {
			var sid = $("#select_1 option:selected").val();
			if (sid != 'none') {
				$("#select_0 option:[value=" + sid + "]").remove();
				$("#select_2 option:[value=" + sid + "]").remove();
				$("#select_3 option:[value=" + sid + "]").remove();
			}
		})
		$("#select_2").change(function() {
			var sid = $("#select_2 option:selected").val();
			if (sid != 'none') {
				$("#select_0 option:[value=" + sid + "]").remove();
				$("#select_1 option:[value=" + sid + "]").remove();
				$("#select_3 option:[value=" + sid + "]").remove();
			}
		})
		$("#select_3").change(function() {
			var sid = $("#select_3 option:selected").val();
			if (sid != 'none') {
				$("#select_0 option:[value=" + sid + "]").remove();
				$("#select_1 option:[value=" + sid + "]").remove();
				$("#select_2 option:[value=" + sid + "]").remove();
			}
		})
};



	

</script>
<h2>{appliances_configuration}</h2>

<form action="{thisfile}">
{form}
{appliance_id}
{select_0}
{select_1}
{select_2}
{select_3}
<br>
<div id="buttons">{submit}&#160;{cancel}</div>
</form>
