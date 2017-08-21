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
<h2>{label}<span id="add-new-role" class="pull-right">{add}</span></h2>


{table}

<script>
	$('span#add-new-role').click(function(){
		//e.preventDefault();
		$('.lead').hide();
		var storagelink = $(this).find('a.add').attr('href');
		$('#storageformaddn').load(storagelink + ' #role_administration_tab1', function() {
			$('.lead').hide();
			$('#storageformaddn select').selectpicker();
			$('#storageformaddn select').hide();
			$('#volumepopupaddn').find('ul li a').text("Add a new role");
			$('#storageformaddn').find('h2').remove();
			$('#volumepopupaddn').show();
		});
		return false;
	});
	$('#volumepopupcloseaddn').click(function(){
		$('#volumepopupaddn').hide();
	});
</script>