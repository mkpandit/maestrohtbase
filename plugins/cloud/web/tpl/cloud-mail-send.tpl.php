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

<h2>{title} <a href="{external_portal_name}" class="btn pull-right" target="_blank">Launch {external_portal_name}</a></h2>

<div class="row">
	<div class="span7">
		<form action="{thisfile}" id="themailform">
			{form}
			<h3>{cloud_mail_data}</h3>
			{cloud_mail_to}
			{cloud_mail_subject}
			{cloud_mail_body}
			
			{submit}
		</form>
	</div>
</div>