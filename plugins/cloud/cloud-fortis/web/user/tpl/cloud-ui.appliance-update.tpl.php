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

{?}

-->
</div></div>
<div class="col-xs-12 col-sm-9 col-md-10 col-lg-10 windows_plane">
<div id="content_container">
	<h1>{label}</h1>
	<div style="float:left;">
	<form action="{thisfile}" method="POST">
		{form}
		{cpu}
		{memory}
		{disk}
		{comment}
		<div id="buttons">{submit}&#160;{cancel}</div>
	</form>
	</div>
	<div style="float:left;margin:0 0 0 50px;width: 230px;">
		<h3>{label_update_notice}</h3>
		<div>{update_cpu_notice}</div>
		<div>{update_disk_notice}</div>

	</div>

</div>
</div>
