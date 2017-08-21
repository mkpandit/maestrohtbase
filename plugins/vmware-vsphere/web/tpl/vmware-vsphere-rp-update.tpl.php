<!--
/*
    HyperTask Enterprise developed by HyperTask Enterprise GmbH.

    All source code and content (c) Copyright 2014, HyperTask Enterprise GmbH unless specifically noted otherwise.

    This source code is released under the HyperTask Enterprise Server and Client License, unless otherwise agreed with HyperTask Enterprise GmbH.
    The latest version of this license can be found here: http://htvcenter-enterprise.com/license

    By using this software, you acknowledge having read this license and agree to be bound thereby.

                http://htvcenter-enterprise.com

    Copyright 2014, HyperTask Enterprise GmbH <info@htvcenter-enterprise.com>
*/
//-->
<h2>{label}</h2>
<form action="{thisfile}" method="GET">
{form}
<div id="form">
{name}


	<fieldset>
		<legend>{lang_cpu}</legend>
		<div style="float:left;">
		{cpuexpandableReservation}
		{cpureservation}
		{cpulimit}
		<br>
		{cpulevel}
		{cpushares}
		</div>
	</fieldset>

	<fieldset>
		<legend>{lang_memory}</legend>
		<div style="float:left;">
		{memoryexpandableReservation}
		{memoryreservation}
		{memorylimit}
		<br>
		{memorylevel}
		{memoryshares}
		</div>
	</fieldset>



</div>
<div id="buttons">{submit}&#160;{cancel}</div>
</form>
