<?php

/* v. 1.1
 * Created by Victor Golovko for the fans of Grocerycrud library.
* SITE: http://svc.by
* SKYPE: ipgolovko
* E-MAIL: siptik@mail.ru
*/

class Grocery_crud_categories
{
	protected $ci;
	protected $grocery_crud_obj;

	protected $related_table;
	protected $table_name;
	protected $sort_field;
	protected $categories_primary_key;
	protected $related_title_field;
	protected $related_table_fields;
	protected $parent_field;
	protected $where;
	protected $order_by;

	protected $first_url;
	protected $segment_name = 'category';
	protected $text	 = array('all_rows' => '-----------------');
	protected $class;
	protected $style;
	protected $category;
	protected $tree;
	protected $level;
	protected $child_nodes;

	function __construct(& $crud_obj = null, $config = null)
	{
		if (!isset($crud_obj) or !isset($config))
			return false;

		$this->ci = & get_instance();
		$this->grocery_crud_obj = $crud_obj;

		foreach ($config as $k => $v)
		{
			$this->$k = $v;
		}

		$segs = $this->ci->uri->segment_array();
		if (in_array($this->segment_name, $segs))
			$this->category = $segs[(array_search($this->segment_name, $segs) + 1)];
	}

	function get_tree()
	{
		if (isset($this->parent_field))
			return $this->adjacency_list_r(0, 0);
		else
			return $this->get_categories();
	}

	function get_categories()
	{
		$fields = array();
		$fields[] = $this->categories_primary_key;
		if(is_array($this->related_table_fields)) {
			$fields = array_merge($fields, $this->related_table_fields);
		} else {
			$fields[] = $this->related_table_fields;
		}
		
		$this->ci->db->select(implode(',', $fields));		
		if($this->order_by)
			$this->ci->db->order_by($this->order_by);

		if($this->where)
			$this->ci->db->where($this->where);
		
		$result = $this->ci->db->get($this->related_table)->result_array();
		if(is_array($this->related_table_fields)) {
			$finalResult = array();
			for($i=0; $i < count($result); $i++) {
				$_row = array();
				$_row[$this->categories_primary_key] = $result[$i][$this->categories_primary_key];
				$_row[$this->related_title_field] = '';
				$n = 0;
				foreach($this->related_table_fields as $field_name) {
					if($n > 0) {
						$_row[$this->related_title_field] .= ' ';
					}
					$_row[$this->related_title_field] .= $result[$i][$field_name];
					$n++;
				}
				$finalResult[] = $_row;
			}
			$result = $finalResult;
		}
		return $result;		
	}

	function adjacency_list_r($parent = 0, $level = 0)
	{

		$this->level = $level;
		$this->level++;
		if($this->order_by)
			$this->ci->db->order_by($this->order_by);
		
		if($this->where)
			$this->ci->db->where($this->where);

		$query = $this->ci->db->get_where($this->related_table, array($this->parent_field => $parent));

		if ($query->num_rows() > 0)
		{
			foreach ($query->result_array() as $row)
			{

				$row['level'] = $this->level;
				$this->tree[] = $row;
				$this->adjacency_list_r($row[$this->categories_primary_key], $this->level);
				$this->level--;
			}
		}
		return $this->tree;
	}

	function get_child_nodes_r($parent = 0)
	{
		$this->ci->db->select($this->categories_primary_key);
		$query = $this->ci->db->get_where($this->related_table, array($this->parent_field => $parent));
		if ($query->num_rows() > 0)
		{
			foreach ($query->result_array() as $row)
			{
				$this->child_nodes[] = $row[$this->categories_primary_key];
				$this->get_child_nodes_r($row[$this->categories_primary_key]);
			}
		}
		return $this->child_nodes;
	}

	function create_select()
	{
		$this->set_where();
		$categories = $this->get_tree();
		$select= '<div id="category-' . $this->related_title_field . '">';
		$select.= '
				<script type="text/javascript">
				$(document).ready(function() {
				el = $("#cs_' . $this->related_title_field . '");
						$(".tDiv").prepend(el);
						$("#groceryCrudTable_length").prepend(el);

	});
						function go(targ,selObj,restore){
						eval(targ+".location=\'"+selObj.options[selObj.selectedIndex].value+"\'");
						if (restore) selObj.selectedIndex=0;
	}
						</script>
						';

		$select.='<span id="cs_' . $this->related_title_field . '"><div  class="tDiv2" style="width:auto;">';
		$select.= '<select id="c_' . $this->related_title_field . '" name="c_' . $this->related_title_field . '" style="' . $this->style . '"  class="' . $this->class . '" onchange="go(\'parent\',this,0)">' . "\n";
		$select.='<option value="' . $this->first_url . '">' . $this->text['all_rows'] . '</option>' . "\n";
		if (isset($categories))
		{
			foreach ($categories as $items)
			{
				$select.='<option value="' . $this->first_url . '/' . $this->segment_name . '/' . $items[$this->categories_primary_key] . '"';
				$select.=($items[$this->categories_primary_key] == $this->category) ? 'selected="selected"' : '';
				$select.= '>';

				if (isset($items['level']) && $items['level'] > 1)
				{
					$select.=str_repeat("&nbsp;&nbsp;&nbsp;&nbsp;", ($items['level'] - 1)) . '|----' . $items[$this->related_title_field] . '</option>' . "\n";
				}
				else
				{
					$select.=$items[$this->related_title_field] . '</option>' . "\n";
				}
			}
		}
		$select.='</select>';
		$select.='</div></span>';
		$select.='</div>';

		return $select;
	}


	// for a grocerycrud object
	function set_where()
	{
		if (isset($this->category))
		{
			if (isset($this->parent_field))
			{
				$categories   = $this->get_child_nodes_r($this->category);
				$categories[] = $this->category;
				$in	   = NUll;
				foreach ($categories as $item)
					$in.= $this->ci->db->escape($item) . ",";

				$in    = substr($in, 0, -1);
				$where = "`$this->table_name`.`" . $this->sort_field . "` IN(" . $in . ")";
				$this->grocery_crud_obj->where($where);
			}
			else
			{
				$this->grocery_crud_obj->where($this->table_name . '.' . $this->sort_field, $this->category);
			}
		}
	}

	function render()
	{
		$dd     = $this->get_dropdown();
		$output = $this->grocery_crud_obj->render();
		$output->output = $dd . $output->output;
		return $output;
	}

	function get_dropdown()
	{
		$select = '';
		$state = $this->grocery_crud_obj->getState();
		if ($state != 'edit' && $state != 'add')
		{
			$select = $this->create_select();
		}
		return $select;
	}
}