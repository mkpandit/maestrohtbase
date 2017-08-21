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

<div style="float:left;">
	{filter}
	<br>
	<br>
</div>
<div class="floatbreaker" style="line-height:0px;height:0px;clear:both;">&nbsp;</div>

<div id="form">
	<div style="width:250px;float:left;">
	</div>
	<div style="width:250px;float:right;">
		<div>{add_group}</div>
	</div>
	<div style="clear:both; margin: 0 0 25px 0;" class="floatbreaker">&#160;</div>
	<form action="{thisfile}" method="POST">
		{form}
		{table}
	</form>
</div>
