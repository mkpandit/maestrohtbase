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

<h2>{label}</h2>

<div id="form">
	<form action="{thisfile}" method="GET">
	{form}

	<fieldset>
		<div style="float:left;">
			{snapshot}
			{availability_zone}
			{size}
			{type}
			{iops}
		</div>
		<div style="float:right; width: 500px;">
			<br>
			<br>
			{snapshot_list}
		</div>
		<div class="floatbreaker" style="line-height:0px;height:0px;clear:both;">&nbsp;</div>
	</fieldset>

	<div id="buttons">{submit}&#160;{cancel}</div>

	</form>
</div>
