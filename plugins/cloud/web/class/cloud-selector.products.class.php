<?php
/**
 * Cloud Selector Products
 *
    htvcenter Enterprise developed by htvcenter Enterprise GmbH.

    All source code and content (c) Copyright 2014, htvcenter Enterprise GmbH unless specifically noted otherwise.

    This source code is released under the htvcenter Enterprise Server and Client License, unless otherwise agreed with htvcenter Enterprise GmbH.
    The latest version of this license can be found here: http://htvcenter-enterprise.com/license

    By using this software, you acknowledge having read this license and agree to be bound thereby.

                http://htvcenter-enterprise.com

    Copyright 2014, htvcenter Enterprise GmbH <info@htvcenter-enterprise.com>
*/


class cloud_selector_products
{

var $identifier_name;
var $lang;
var $actions_name = 'cloud_selector';



	//--------------------------------------------
	/**
	 * Constructor
	 *
	 * @access public
	 * @param htvcenter $htvcenter
	 * @param htmlobject_response $response
	 */
	//--------------------------------------------
	function __construct($htvcenter, $response, $controller) {
		$this->response = $response;
		$this->htvcenter = $htvcenter;
		$this->file = $this->htvcenter->file();
		$this->rootdir  = $this->htvcenter->get('rootdir');
		$this->webdir  = $this->htvcenter->get('webdir');
		$this->controller = $controller;



		$this->product = 'cpu';
		$product = $this->response->html->request()->get('product');
		if($product !== '' && in_array($product, $this->controller->products)) {
			$this->product = $product;
		}
		$this->response->add('product', $product);

		require_once($this->htvcenter->get('basedir').'/plugins/cloud/web/class/cloudconfig.class.php');
		$this->cloud_config = new cloudconfig();
		require_once($this->htvcenter->get('basedir').'/plugins/cloud/web/class/cloudselector.class.php');
		$this->cloudselector = new cloudselector();
	}

	//--------------------------------------------
	/**
	 * Action Cpu
	 *
	 * @access public
	 * @param enum $type [file|folder]
	 * @return htmlobject_response
	 */
	//--------------------------------------------
	function action() {
		$response = $this->products();
		$t = $this->response->html->template($this->tpldir."/cloud-selector-products.tpl.php");
		$t->add($this->response->html->thisfile, "thisfile");
		$t->add($response->table, 'table');
		$t->add($response->products, 'products');
		$t->add($this->lang['label_product_group'], 'label_product_group');
		$t->add($this->lang['label_add_product'], 'label_add_product');
		$t->add($response->form);
		$t->group_elements(array('param_' => 'form'));

		require_once($this->htvcenter->get('basedir').'/plugins/cloud/web/class/cloud-selector.add.class.php');

		$controller = new cloud_selector_add($this->htvcenter, $this->response, $this->controller);
		$controller->actions_name  = $this->actions_name;
		$controller->tpldir        = $this->tpldir;
		$controller->identifier_name = $this->identifier_name;
		$controller->message_param = $this->message_param;
		$controller->lang          = $this->lang;
		$data = $controller->action();

		$t->add($data, 'form_add');

		return $t;
	}

	//--------------------------------------------
	/**
	 * Cloud Selector Products
	 *
	 * @access protected
	 * @return htmlobject_response
	 */
	//--------------------------------------------
	function products() {
		$response = $this->get_response();

		$head['rank']['title'] = $this->lang['table_rank'];
		$head['rank']['hidden'] = true;
		$head['rank']['sortable'] = true;
		$head['name']['title'] = $this->lang['table_name'];

		$head['quantity']['title'] = $this->lang['table_quantity'];
		if($this->product === 'kernel') {
			$head['quantity']['title'] = $this->lang['form_kernel'];
		}
		elseif($this->product === 'application') {
			$head['quantity']['title'] = $this->lang['form_application'];
		}
		elseif($this->product === 'resource') {
			$head['quantity']['title'] = $this->lang['form_resource'];
		}

		$head['price']['title'] = $this->lang['table_price'];
		$head['description']['title'] = $this->lang['table_description'];
		$head['action_up']['title'] = '&#160;';
		$head['action_up']['sortable'] = false;
		$head['state']['title'] = '&#160;';
		$head['state']['sortable'] = false;
		$head['state_s']['title'] = $this->lang['cloud_selector_product_state'];
		$head['state_s']['hidden'] = true;
		$head['action_remove']['title'] = '&#160;';
		$head['action_remove']['sortable'] = false;
		#$head['action_down']['title'] = '&#160;';
		#$head['action_down']['sortable'] = false;

		$cloud_selector_array = $this->cloudselector->display_overview_per_type($this->product);
		$b = array();
		foreach ($cloud_selector_array as $index => $cz) {

			// sorting
			$up_action = '';
			if($cz["sort_id"] !== '0') {
				$a = $this->response->html->a();
				$a->title   = sprintf($this->lang['action_sort_up'], $cz["sort_id"]);
				$a->label   = '<img src="/htvcenter/base/plugins/cloud/img/up.png">';
				$a->handler = 'onclick="wait();"';
				$a->css     = 'badge moveup';
				$a->href    = $this->response->get_url($this->actions_name, "up").'&cloud_selector_id='.$cz["id"];
				$up_action = $a->get_string();
			}

			// state
			$product_state = '';
			if ($cz["state"] == 1) {
				// disable action
				$a = $this->response->html->a();
				$a->title   = $this->lang['action_disable'];
				$a->label   = '<img src="/htvcenter/base/plugins/cloud/img/minus.png">';
				$a->handler = 'onclick="wait();"';
				$a->css     = 'badge minus';
				$a->href    = $this->response->get_url($this->actions_name, "state").'&cloud_selector_id='.$cz["id"];
				$product_state = 'd';
			} else {
				// disable action
				$a = $this->response->html->a();
				$a->title   = $this->lang['action_enable'];
				$a->label   = '<img src="/htvcenter/base/plugins/cloud/img/plus.png">';
				$a->handler = 'onclick="wait();"';
				$a->css     = 'badge plus';
				$a->href    = $this->response->get_url($this->actions_name, "state").'&cloud_selector_id='.$cz["id"];
				$product_state = 'e';
			}
			$state_action = $a->get_string();

			$del = $this->response->html->a();
			$del->title   = $this->lang['action_remove'];
			$del->label   = '<img src="/htvcenter/base/plugins/cloud/img/remove.png">';
			$del->handler = 'onclick="wait();"';
			$del->css     = 'badge delete';
			$del->href    = $this->response->get_url($this->actions_name, "remove").'&cloud_selector_id='.$cz["id"];

			$cz["name"] = str_replace('(localboot)', '', $cz["name"]);
			$cz["name"] = str_replace('(networkboot)', '', $cz["name"]);
			$cz["name"] = str_replace('KVM VM', 'OCH VM', $cz["name"]);
			
			$b[] = array(
				'id' => $cz["id"],
				'rank' => $cz["sort_id"],
				'quantity' => $cz["quantity"],
				'price' => $cz["price"],
				'name' => $cz["name"],
				'description' => $cz["description"],
				'state' => $state_action,
				'state_s' => $product_state,
				'action_up' => $up_action,
				'action_remove' => $del->get_string(),
			);
		}

		$table = $response->html->tablebuilder( 'cloud_selector_products', $this->response->get_array($this->actions_name, 'products'));
		$table->css             = 'htmlobject_table';
		$table->border          = 0;
		$table->id              = 'cloud_selector_table';
		$table->head            = $head;
		$table->sort            = 'rank';
		$table->autosort        = true;
		$table->max             = count($b);
		$table->sort_link       = false;
		$table->body = $b;

		$response->table = $table;

		foreach($this->controller->products as $prod) {
			if(isset($this->lang['product_'.$prod])) {
				$products[] = array($prod, $this->lang['product_'.$prod]);
			} else {
				$products[] = array($prod, ucfirst($prod));
			}
		}
		$prods = $this->response->html->select();
		$prods->id = 'product';
		$prods->name = 'product';
		$prods->add($products, array(0,1));
		$prods->selected = array($this->product);
		$prods->handler = 'onchange="wait();this.form.submit();"';
		$response->products = $prods;

		return $response;
	}


	function get_response() {
		$response = $this->response;
		$form = $response->get_form($this->actions_name, 'products');
		$response->form = $form;
		return $response;
	}

}
?>
