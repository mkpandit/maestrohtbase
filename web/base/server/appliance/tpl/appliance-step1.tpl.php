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
//-->

<!-- div#storageformaddn div#comment_box div.left  -->

<h2>{label}</h2>

<div id="step1">
<form action="{thisfile}" method="GET" id="server-add-step-1">

	<div id="info" class="span5">
		{info}
	</div>
	<div class="floatbreaker">&#160;</div>
	<div id="form" class="span5">
		{form}
		{name}
		<label id="desklbl" >Description</label>
		{comment}
	</div>
	<div class="floatbreaker">&#160;</div>

	<div id="buttons">{submit}&#160;{cancel}</div>
</form>
<script>
	function validateServerName(){
		var serverName = $("div#step1 #name").val();
		if (serverName == ""){
			alert("Server name can not be empty");
			return(false);
		} else if (serverName.indexOf(' ') >= 0) {
			alert("Server name can not have space");
			return(false);
		}
		else {
			document.getElementById('#server-add-step-1').submit();
			return(true);
		}
	}
</script>
</div>
