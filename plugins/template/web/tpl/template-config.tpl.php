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
<h2>{label}</h2>

<div class="row">
	<div class="span6">
		<form action="{thisfile}" method="post">
			{form}
			{fields}
			<br>
			{submit}
		</form>
	</div>
	
	<div class="span3 offset1">
		<h3 class="first">Additional info <small>with examples</small></h3>
		<p>Use this column to add additional info to this view. You may use all markup as described in the UI development guide.</p>
		<p><span class="pill orange">Hint</span> You may want to show some <code>inline code</code>.</p>
	</div>
</div>