<?php
/**
 * htvcenter Top
 *
    htvcenter Enterprise developed by HTBase Corp.

    All source code and content (c) Copyright 2015, HTBase Corp unless specifically noted otherwise.

    This source code is released under the htvcenter Enterprise Server and Client License, unless otherwise agreed with HTBase Corp.

    By using this software, you acknowledge having read this license and agree to be bound thereby.

                http://www.htbase.com

    Copyright 2015, HTBase Corp <contact@htbase.com>
 */

class htvcenter_upload
{
/**
* absolute path to template dir
* @access public
* @var string
*/
var $tpldir;
/**
* translation
* @access public
* @var array
*/
var $lang;

	//--------------------------------------------
	/**
	 * Constructor
	 *
	 * @access public
	 * @param htmlobject_response $response
	 * @param object $htvcenter
	 */
	//--------------------------------------------
	function __construct($response, $htvcenter) {
		$this->response = $response;
		$this->htvcenter  = $htvcenter;
		$this->htvcenter->init();
		require_once($this->htvcenter->get('classdir').'/file.upload.class.php');
	}

	//--------------------------------------------
	/**
	 * Action
	 *
	 * @access public
	 * @return htmlobject_template
	 */
	//--------------------------------------------
	function action() {
		$content[] = $this->upload();

		$tab = $this->response->html->tabmenu('upload_tab');
		$tab->message_param = 'upload_msg';
		$tab->css = 'htmlobject_tabs';
		$tab->add($content);
		return $tab;
	}

	//--------------------------------------------
	/**
	 * Upload
	 *
	 * @access public
	 * @return array
	 */
	//--------------------------------------------
	function upload() {

		$response = $this->response;
		$form = $response->get_form('upload', 'true');
		$form->box_css = 'htmlobject_box';
		$form->display_errors = false;

		$submit = $form->get_elements('submit');
		$submit->handler = 'onclick="wait();"';
		$form->add($submit, 'submit');

		$d['upload_1']['label']                    = $this->lang['public_key'];
		$d['upload_1']['object']['type']           = 'input';
		$d['upload_1']['object']['attrib']['type'] = 'file';
		$d['upload_1']['object']['attrib']['name'] = 'upload_1';
		$d['upload_1']['object']['attrib']['size'] = 30;

		$d['upload_2']['label']                    = $this->lang['server_license'];
		$d['upload_2']['object']['type']           = 'input';
		$d['upload_2']['object']['attrib']['type'] = 'file';
		$d['upload_2']['object']['attrib']['name'] = 'upload_2';
		$d['upload_2']['object']['attrib']['size'] = 30;

		$d['upload_3']['label']                    = $this->lang['client_license'];
		$d['upload_3']['object']['type']           = 'input';
		$d['upload_3']['object']['attrib']['type'] = 'file';
		$d['upload_3']['object']['attrib']['name'] = 'upload_3';
		$d['upload_3']['object']['attrib']['size'] = 30;

		$form->add($d);

		if(!$form->get_errors() && $response->submit()) {
			$upload = new file_upload($this->htvcenter->file());
			$upload->lang = $this->htvcenter->user()->translate($upload->lang, $this->htvcenter->get('basedir')."/web/base/lang", 'file.upload.ini');
			$error = '';
			for($i = 1; $i < 4; $i++) {
				if($_FILES['upload_'.$i]['name'] !== '') {
					$msg = $upload->upload('upload_'.$i, $this->htvcenter->get('webdir').'/tmp/', '', true);
					if($msg !== '') {
						$error .= $msg['msg'].'<br>';
					}
				}
			}
			if($error !== '') {
				$_REQUEST['upload_msg'] = $error;
			} else {
				$response_msg = array();
				for($i = 1; $i < 4; $i++) {
					if($_FILES['upload_'.$i]['name'] !== '') {
						$command = $this->htvcenter->get('basedir')."/bin/htvcenter license -l ".$this->htvcenter->get('webdir')."/tmp/".$_FILES['upload_'.$i]['name']." --htvcenter-cmd-mode background";
						$resource = new resource();
						$resource->get_instance_by_id(0);
						$resource->send_command($resource->ip, $command);
						$response_msg[] = sprintf($this->lang['msg'], $_FILES['upload_'.$i]['name']);
						sleep(4);
					}
				}
				$response_msg = implode('<br>', $response_msg);
				$response->redirect($this->htvcenter->get('baseurl').'/index.php?datacenter_msg='.$response_msg.'&upload_msg='.$response_msg);
			}
		}
		$t = $this->response->html->template($this->htvcenter->get('webdir').'/tpl/upload.tpl.php');
		$t->add($response->html->thisfile, 'thisfile');
		$t->add($this->lang['label'], 'label');
		$t->add($this->lang['welcome'], 'welcome');
		$t->add($this->lang['explanation'], 'explanation');
		$t->add($form->get_elements());
		$t->group_elements(array('param_' => 'form'));

		$content['label']   = $this->lang['tab'];
		$content['value']   = $t;
		$content['target']  = $this->response->html->thisfile;
		$content['request'] = $this->response->get_array('upload', 'true' );
		$content['onclick'] = false;
		$content['active']  = true;

		return $content;
	}

}
