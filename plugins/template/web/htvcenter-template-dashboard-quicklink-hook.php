<?php
/*
    htvcenter Enterprise developed by htvcenter Enterprise GmbH.

    All source code and content (c) Copyright 2014, htvcenter Enterprise GmbH unless specifically noted otherwise.

    This source code is released under the htvcenter Enterprise Server and Client License, unless otherwise agreed with htvcenter Enterprise GmbH.
    The latest version of this license can be found here: http://htvcenter-enterprise.com/license

    By using this software, you acknowledge having read this license and agree to be bound thereby.

                http://htvcenter-enterprise.com

    Copyright 2014, htvcenter Enterprise GmbH <info@htvcenter-enterprise.com>
*/

function get_template_dashboard_quicklink($html) {
 	global $htvcenter_SERVER_BASE_DIR;

	// creade <i> tag for button icon	
	$quicklink_icon = $html->i();
	$quicklink_icon->css = 'glyphicons-icon beer';

/*	
	// create <span> tag for the colored corner
	$quicklink_corner = $html->span();
	$quicklink_corner->css = 'corner corner-orange';
	
	// create <label> tag for label in the corner
	$quicklink_corner_label = $html->label();
	$quicklink_corner_label->add('23');
*/	
	// create <span class="label"> for the button label
	$quicklink_label = $html->span();
	$quicklink_label->add('Demolink');
	$quicklink_label->css = 'label';
	
	// create <a> tag and add the above created elements
	$quicklink = $html->a();
//	$quicklink->label = $quicklink_icon->get_string() . $quicklink_label->get_string() . $quicklink_corner->get_string() . $quicklink_corner_label->get_string();
	$quicklink->label = $quicklink_icon->get_string() . $quicklink_label->get_string();
	$quicklink->css = 'btn quicklink template-quicklink';
	$quicklink->href = 'index.php?plugin=template&template_action=select';

	return $quicklink;
}

?>

