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
<h2>Edit Node</h2>

<div id="form-edit" class="personalize-node-edit">
	<form action="{thisfile}" method="POST">
		{form}
		{name}
		{node_class}
		{comment}
		<div id="buttons">{submit}&#160;{cancel}</div>
		<div class="status-message">{action_status}</div>
	</form>
</div>

<script>
	$("#puppet_node_class_select").on('change', function() {
		var class_selected = $(this).val();
		var node_content = $("#node_content").val().split("\n");
		var node_content_first_element = node_content[0];
		node_content.shift();
		if( (node_content.indexOf("include " + class_selected) > -1) ||  class_selected == " -- ") {
			alert("Already inluded this class or not a valid class");
		} else {
			$("#node_content").val(node_content_first_element + "\ninclude " + class_selected + "\n" + node_content.join("\n") );
		}
	});
	
</script>