<?php
require_once 'grocery_crud.php';
class Grocery_crud_with_group extends grocery_CRUD {
	
	protected $is_grouped			= false;
	protected $groups				= array();
	protected $group_fields			= array();
	
	
	function add_group($group, $title) {
		if(array_key_exists($group, $this->groups) !== FALSE) {
			$this->groups[$group] = $title;
		}
	}
	
	function add_group_field($group, $field) {
		if(array_key_exists($group, $this->groups) !== FALSE) {
			if(array_search($field, $this->group_fields[$group]) === FALSE) {
				array_push($this->group_fields[$group], $field);
			}
		} else {
			$this->groups[$group] = $group;
			array_push($groups[$group], $field);
		}
	}
	
	public function set_group($group, $group_title, $group_fields)
	{
		$this->is_grouped = true;
		foreach ($group_fields as $field) {
			if(array_key_exists($group, $this->groups) !== FALSE) {
				if(array_search($field, $this->group_fields[$group]) === FALSE) {
					array_push($this->group_fields[$group], $field);
				}
			} else {
				$this->groups[$group] = $group_title;
				$this->group_fields[$group] = array();
				array_push($this->group_fields[$group], $field);
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
		
		$data->has_groups		= $this->is_grouped;
		$data->groups			= $this->groups;
		$data->group_fields		= $this->group_fields;
	
		if($this->is_grouped)
			$this->_theme_view('add_with_groups.php',$data);
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

		$data->has_groups		= $this->is_grouped;
		$data->groups			= $this->groups;
		$data->group_fields		= $this->group_fields;
		
		if($this->is_grouped)
			$this->_theme_view('edit_with_groups.php',$data);
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
	

		$data->has_groups		= $this->is_grouped;
		$data->groups			= $this->groups;
		$data->group_fields		= $this->group_fields;
		
		if($this->is_grouped)
			$this->_theme_view('read_with_groups.php',$data);
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
		$this->finalize_groups();
	}		
	
	protected function finalize_groups() {
		//By default create a default group
		if(array_key_exists('default', $this->groups) == FALSE) {
			$this->groups['default']='default';
			$this->group_fields['default'] = array();
		}
		//Check with all fields - the ones that are not in any group will be in default group
		$add_fields = $this->get_add_fields();
		foreach ($add_fields as $field_num => $field) {
			$_field = $field->field_name;
			$exists = false;
			foreach ($this->group_fields as $gfk=>$_groupfield) {
				if($gfk == 'default')
					continue;
				for($i=0; $i < count($_groupfield); $i++){ 
					if($_field == $_groupfield[$i]) {
						$exists = true;
						break;
					}
					if($exists)
						break;
				}
			} 
			if(!$exists) {
				array_push($this->group_fields['default'], $_field);
			}
		}
	}
}