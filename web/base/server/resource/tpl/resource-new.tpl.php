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
{form_docu}
<br>
<br>
<b>{form_auto_resource}</b>
<br>
<br>

<form action="{thisfile}" method="GET">
<div>
	<h3>{form_add_resource}</h3>
	{form}
	{name}
	{ip}
	{mac}
</div>
<div>{submit}&#160;{cancel}</div>
</form>

