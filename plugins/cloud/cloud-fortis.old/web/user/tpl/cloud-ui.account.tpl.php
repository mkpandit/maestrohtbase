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

</div></div>



<div class="col-xs-12 col-sm-8 col-md-9 col-lg-9 windows_plane">
<div id="content_container">
<h1>{label}</h1>
<form action="{thisfile}" method="POST">
{form}

		<div id="cloud_account_left_column">
			{cu_email}
			{cu_forename}
			{cu_lastname}
			{cu_street}
			{cu_city}
			{cu_country}
			{cu_phone}
			<br/>
			<h3>Password:</h3>
			{cu_password}
			{cu_password_repeat}
			<div id="buttons" class="leftsidebtn">{submit}</div>
		</div>
		<div id="cloud_account_right_column">
			<h3>{details}:</h3>
			<p><b>{user_name}:</b> {user_name_value}</p>
			<p><b>{user_group}:</b> {user_group_value}</p>
			<p><b>{cloud_user_ccus}:</b> {cloud_user_ccus_value}</p>
			<p><b>{cloud_user_lang}:</b> {cloud_user_lang_value}</p>
			<br/>
			{transactions}
		</div>
		<div class="floatbreaker" style="line-height:0px;clear:both;">&#160;</div>

</form>
</div>
</div>