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



<style type="text/css">



#resize_content_slider {
	position: relative;
	left: 105px;
	top: 0px;
	width: 170px;
	height: 20px;
	background: #BBBBBB;
}

.resize_content_slider_handle {
	background: #478AFF;
	border: solid 3px black;
}


</style>

<script type="text/javascript">
	$(document).ready(function() {

	  $("#resize_content_slider").slider({
		animate: true,
		step: 100,
		min: {cloud_image_disk_size},
		max: {cloud_uses_max_disk_size},
		start: {cloud_image_disk_size},
		handle: ".resize_content_slider_handle",
		change: resize_handleSliderChange,
		slide: resize_handleSliderSlide

	  });

	});


function resize_handleSliderChange(e, ui)
{
	$('#cloud_appliance_resize').val(ui.value);
}


function resize_handleSliderSlide(e, ui)
{
	$('#cloud_appliance_resize').val(ui.value);
}


</script>


</div></div>
<div class="col-xs-12 col-sm-9 col-md-10 col-lg-10 windows_plane">
<div id="content_container">

<form action="{thisfile}">

<h3>{cloud_ui_appliance_resize}</h3>

{form}

{cloud_ui_appliance_resize} (MB) {cloud_appliance_resize}

<div id="resize_content_slider">
	<div class="resize_content_slider_handle">
	</div>
</div>


<br><br>
{submit}{cancel}

</form>

</div></div>