<?php
require_once 'grocery_crud.php';
class Grocery_crud_with_tab extends grocery_CRUD {
	
	protected $is_tabed			= false;
	protected $tabs				= array('default'=>'default');
	protected $tab_fields			= array('default'=>array());
	
	
	function add_tab($tab, $title) {
		if(array_key_exists($tab, $this->tabs) !== FALSE) {
			$this->tabs[$tab] = $title;
		} else {
			array_push($this->tabs, $tab);
			$this->tabs[$tab] = $title;
		}
	}
	
	function add_tab_field($tab, $field) {
		if(array_key_exists($tab, $this->tabs) !== FALSE) {
			if(array_search($field, $this->tab_fields[$tab]) === FALSE) {
				array_push($this->tab_fields[$tab], $field);
			}
		} else {
			//$this->tabs[$tab] = $tab;
			array_push($this->tabs, $tab);
			array_push($this->tabs[$tab], $field);
		}
	}
	
	public function set_tab($tab, $tab_title, $tab_fields)
	{
		$this->is_tabed = true;
		foreach ($tab_fields as $field) {
			if(array_key_exists($tab, $this->tabs) !== FALSE) {
				$this->tabs[$tab] = $tab_title;
				if(array_search($field, $this->tab_fields[$tab]) === FALSE) {
					array_push($this->tab_fields[$tab], $field);
				}
			} else {
				$this->tabs[$tab] = $tab_title;
				$this->tab_fields[$tab] = array();
				array_push($this->tab_fields[$tab], $field);
			}
		}
		return $this;
	}		
	
	protected function showAddForm()
	{
		$this->set_js_lib($this->default_javascript_path.'/'.grocery_CRUD::JQUERY);
	
		$data 				= $this->get_common_data();
		$data->types 		= $this->get_field_types();
		$data->masks			= $this->field_mask;
	
		$data->list_url 		= $this->getListUrl();
		$data->insert_url		= $this->getInsertUrl();
		$data->validation_url	= $this->getValidationInsertUrl();
		$data->input_fields 	= $this->get_add_input_fields();
	
		$data->fields 			= $this->get_add_fields();
		$data->hidden_fields	= $this->get_add_hidden_fields();
		$data->unset_back_to_list	= $this->unset_back_to_list;
		$data->unique_hash			= $this->get_method_hash();
		$data->is_ajax 			= $this->_is_ajax();
		
		$data->has_tabs		= $this->is_tabed;
		$data->tabs			= $this->tabs;
		$data->tab_fields		= $this->tab_fields;
		if($this->is_tabed)
			$this->_theme_view('add_with_tabs.php',$data);
		else 
			$this->_theme_view('add.php',$data);
		$this->_inline_js("var js_date_format = '".$this->js_date_format."';");
	
		$this->_get_ajax_results();
	}
	
	protected function showEditForm($state_info)
	{
		$this->set_js_lib($this->default_javascript_path.'/'.grocery_CRUD::JQUERY);
	
		$data 				= $this->get_common_data();
		$data->types 		= $this->get_field_types();
		$data->masks			= $this->field_mask;
	
		$data->field_values = $this->get_edit_values($state_info->primary_key);
	
		$data->add_url		= $this->getAddUrl();
	
		$data->list_url 	= $this->getListUrl();
		$data->update_url	= $this->getUpdateUrl($state_info);
		$data->delete_url	= $this->getDeleteUrl($state_info);
		$data->read_url		= $this->getReadUrl($state_info->primary_key);
		$data->input_fields = $this->get_edit_input_fields($data->field_values);
		$data->unique_hash			= $this->get_method_hash();
	
		$data->fields 		= $this->get_edit_fields();
		$data->hidden_fields	= $this->get_edit_hidden_fields();
		$data->unset_back_to_list	= $this->unset_back_to_list;
	
		$data->validation_url	= $this->getValidationUpdateUrl($state_info->primary_key);
		$data->is_ajax 			= $this->_is_ajax();

		$data->has_tabs		= $this->is_tabed;
		$data->tabs			= $this->tabs;
		$data->tab_fields		= $this->tab_fields;
		
		if($this->is_tabed)
			$this->_theme_view('edit_with_tabs.php',$data);
		else
			$this->_theme_view('edit.php',$data);
		
		$this->_inline_js("var js_date_format = '".$this->js_date_format."';");
	
		$this->_get_ajax_results();
	}
	
	protected function showReadForm($state_info)
	{
		$this->set_js_lib($this->default_javascript_path.'/'.grocery_CRUD::JQUERY);
	
		$data 				= $this->get_common_data();
		$data->types 		= $this->get_field_types();
	
		$data->field_values = $this->get_edit_values($state_info->primary_key);
	
		$data->add_url		= $this->getAddUrl();
	
		$data->list_url 	= $this->getListUrl();
		$data->update_url	= $this->getUpdateUrl($state_info);
		$data->delete_url	= $this->getDeleteUrl($state_info);
		$data->read_url		= $this->getReadUrl($state_info->primary_key);
		$data->input_fields = $this->get_read_input_fields($data->field_values);
		$data->unique_hash			= $this->get_method_hash();
	
		$data->fields 		= $this->get_view_fields();	// Exclusively get view fields rather then edit fields
		$data->hidden_fields	= $this->get_edit_hidden_fields();
		$data->unset_back_to_list	= $this->unset_back_to_list;
	
		$data->validation_url	= $this->getValidationUpdateUrl($state_info->primary_key);
		$data->is_ajax 			= $this->_is_ajax();
	

		$data->has_tabs		= $this->is_tabed;
		$data->tabs			= $this->tabs;
		$data->tab_fields		= $this->tab_fields;
		
		if($this->is_tabed)
			$this->_theme_view('read_with_tabs.php',$data);
		else
			$this->_theme_view('read.php',$data);
				
		
		$this->_inline_js("var js_date_format = '".$this->js_date_format."';");
	
		$this->_get_ajax_results();
	}	
	
		
	/**
	 *
	 * Or else ... make it work! The web application takes decision of what to do and show it to the final user.
	 * Without this function nothing works. Here is the core of grocery CRUD project.
	 *
	 * @access	public
	 */
	public function pre_render()
	{
		parent::pre_render();
		$this->finalize_tabs();
	}		
	
	protected function finalize_tabs() {
		//By default create a default tab
		if(array_key_exists('default', $this->tabs) == FALSE) {
			$this->tabs[]='default';
			$this->tab_fields['default'] = array();
		} 
		//Check with all fields - the ones that are not in any tab will be in default tab
		$add_fields = $this->get_add_fields();
		foreach ($add_fields as $field_num => $field) {
			$_field = $field->field_name;
			$exists = false;
			foreach ($this->tab_fields as $gfk=>$_tabfield) {
				//if($gfk == 'default')
				//	continue;
				for($i=0; $i < count($_tabfield); $i++){ 
					if($_field == $_tabfield[$i]) {
						$exists = true;
						break;
					}
					if($exists)
						break;
				}
			} 
			if(!$exists) {
				array_push($this->tab_fields['default'], $_field);
			}
		}
	}
}