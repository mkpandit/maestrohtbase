<?php
/*
    htvcenter Enterprise developed by HTBase Corp.

    All source code and content (c) Copyright 2015, HTBase Corp unless specifically noted otherwise.

    This source code is released under the htvcenter Enterprise Server and Client License, unless otherwise agreed with HTBase Corp.

    By using this software, you acknowledge having read this license and agree to be bound thereby.

                http://www.htbase.com

    Copyright 2015, HTBase Corp <contact@htbase.com>
*/

// -----------------------------------------------------------------------------------------------------------------------
//
//
//
//
//
// -----------------------------------------------------------------------------------------------------------------------
class Folder
{
var $files = array();
var $folders = array();
var $arExcludedFiles = array('.', '..');

	//-------------------------------------------------------------------
	//
	//
	//-------------------------------------------------------------------
	function getFolderContent($path, $excludes = '') {

		if($excludes != '') {
			$arExcludedFiles = array_merge($this->arExcludedFiles, $excludes);
		} else {
			$arExcludedFiles = $this->arExcludedFiles;
		}

		$handle = opendir ("$path/.");
		while (false != ($file = readdir ($handle))) {
			if (in_array($file, $arExcludedFiles) == FALSE){
				if (is_file("$path/$file")== TRUE) {
				   $myFile = new File("$path/$file");
				   $this->files[] = $myFile;
				}
			}
		}
	}
	
	//-------------------------------------------------------------------
	//
	//
	//-------------------------------------------------------------------
	function getFolders($path, $excludes = '') {

		if($excludes != '') {
			$arExcludedFiles = array_merge($this->arExcludedFiles, $excludes);
		} else {
			$arExcludedFiles = $this->arExcludedFiles;
		}
		$handle = opendir ("$path/.");
		while (false != ($file = readdir ($handle))) {
			if (in_array($file, $arExcludedFiles) == FALSE){
				if (is_dir("$path/$file")== TRUE) {
					$this->folders[] = $file;
				}
			}
		}
		sort($this->folders);
	}
}
