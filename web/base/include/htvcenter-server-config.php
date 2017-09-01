<?php
/*
    htvcenter Enterprise developed by HTBase Corp.

    All source code and content (c) Copyright 2015, HTBase Corp unless specifically noted otherwise.

    This source code is released under the htvcenter Enterprise Server and Client License, unless otherwise agreed with HTBase Corp.

    By using this software, you acknowledge having read this license and agree to be bound thereby.

                http://www.htbase.com

    Copyright 2015, HTBase Corp <contact@htbase.com>
*/

if ((file_exists("/etc/init.d/htvcenter")) && (is_link("/etc/init.d/htvcenter"))) {
	$htvcenter_BASE_DIR=dirname(dirname(dirname(dirname(readlink("/etc/init.d/htvcenter")))));
} else {
	$htvcenter_BASE_DIR="/usr/share";
}
$htvcenter_SERVER_CONFIG_FILE="$htvcenter_BASE_DIR/htvcenter/etc/htvcenter-server.conf";


// function to get infos from the htvcenter-server.conf
function htvcenter_parse_conf ( $filepath ) {
	$ini = file( $filepath );
	if ( count( $ini ) == 0 ) { return array(); }
	$sections = array();
	$values = array();
	$globals = array();
	$i = 0;
	foreach( $ini as $line ){
		$line = trim( $line );
		// Comments
		if ( $line == '' || $line{0} != 'h' ) { continue; }
		// Key-value pair
		list( $key, $value ) = explode( '=', $line, 2 );
		$key = trim( $key );
		$value = trim( $value );
		$value = str_replace("\"", "", $value );
		$globals[ $key ] = $value;
	}
	return $globals;
}


$store = htvcenter_parse_conf($htvcenter_SERVER_CONFIG_FILE);
extract($store);
global $htvcenter_SERVER_CONFIG_FILE;

?>
