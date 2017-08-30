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
<style>
	#project_tab_ui { display: none; }  /* hack for tabmenu issue */
	.left { min-width: 10rem; }
</style>

<div class="cat__content">
	<cat-page>
	<section class="card">	
	<div class="card-header">
        <span class="cat__core__title">
            <strong>{label}</strong>
        </span>
    </div>
    <div class="card-block">
    	<form action="{thisfile}" method="POST">
		{form}
		{cu_email}
		{cu_forename}
		{cu_lastname}
		{cu_street}
		{cu_city}
		{cu_country}
		{cu_phone}
		{cu_password}
		{cu_password_repeat}
		<div id="buttons" class="leftsidebtn">{submit}</div>
		</form>
	</div>
	<div class="card-block">
		<h3>{details}:</h3>
		<p><b>{user_name}:</b> {user_name_value}</p>
		<p><b>{user_group}:</b> {user_group_value}</p>
		<p><b>{cloud_user_ccus}:</b> {cloud_user_ccus_value}</p>
		<p><b>{cloud_user_lang}:</b> {cloud_user_lang_value}</p>
		<br/>
		<!--
		{transactions}
		-->
	</div>
	</section>
	</cat-page>
</div>
