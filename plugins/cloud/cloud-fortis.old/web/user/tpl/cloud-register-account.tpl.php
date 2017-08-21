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

 to debug add {?}


-->
</div></div>
<div class="col-xs-12 col-sm-8 col-md-9 col-lg-9 windows_plane">
<h1>{label}</h1>
<div id="register_container">
<form action="{thisfile}" method="POST">
{form}

	{cu_name}
	{cu_forename}
	{cu_lastname}
	{cu_street}
	{cu_city}
	{cu_country}
	{cu_email}
	{cu_phone}
	<br>
	{cu_password}
	{cu_password_repeat}
	<div id="buttons">{submit}&#160;{cancel}</div>

</form>
</div>
</div>