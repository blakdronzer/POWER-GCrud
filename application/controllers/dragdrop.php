<?php if (! defined ( 'BASEPATH' )) exit ( 'No direct script access allowed' );

class Dragdrop extends CI_Controller {
	function __construct() {
		parent::__construct ();
		
		$this->load->database ();
		$this->load->helper ( 'url' );
		$this->load->library ( 'session' );
		$this->load->helper ( 'priority' );
	}
	
	function updatePosition($table, $sourceId, $distance, $direction) {
		$this->load->library ( 'Priority_manager' );
		$manager = new Priority_manager ();
		$manager->setTable ( $table );
		$manager->setPriorityField ( 'priority' );
		
		switch ($direction) {
			case 'up' :
				$manager->moveUpBy ( $sourceId, $distance );
				break;
			case 'down' :
				$manager->moveDownBy ( $sourceId, $distance );
				break;
			case 'top' :
				$manager->moveToTop ( $sourceId );
				break;
			case 'bottom' :
				$manager->moveToBottom ( $sourceId );
				break;
			case 'default' :
				$manager->moveTo ( $sourceId, $distance );
				break;
		}
	}
	function updateGroupPosition($table, $group, $sourceId, $distance, $direction) {
		$this->load->library ( 'Priority_manager' );
		$manager = new Priority_manager ();
		$manager->setTable ( $table );
		$manager->setGroupField ( $group );
		$manager->setPriorityField ( 'priority' );
		
		switch ($direction) {
			case 'up' :
				$manager->moveUpBy ( $sourceId, $distance );
				break;
			case 'down' :
				$manager->moveDownBy ( $sourceId, $distance );
				break;
			case 'top' :
				$manager->moveToTop ( $sourceId );
				break;
			case 'bottom' :
				$manager->moveToBottom ( $sourceId );
				break;
			case 'default' :
				$manager->moveTo ( $sourceId, $distance );
				break;
		}
	}
	
	function dragdrop_js() {
		$js = '
				var startPosition;
				var endPosition;
				var itemBeingDragged;
				var allIds = new Array();
		
		
				function makeTableDragable() {
					try {
						if (makeTableSortable && typeof(makeTableSortable) == "function")
							makeTableSortable();							
					} catch(err) {
						//NOTHING TO BE DONE HERE
					}				
				}
				
				function makeAjaxCall(_url) {
					/* Send the data using post and put the results in a div */
					$.ajax({
						url: _url,
						type: "get",
						success: function(){
							$(".pReload").click();
							makeTableSortable();
						},
						error:function(){
							alert("There was a failure while repositioning the element");
						}
					});
				}
		
				function moveUp(sourceId) {
				url="' . $this->session->userdata('callableAction') . '/" + sourceId +"/1/up";
						makeAjaxCall(url);
				}
	
				function moveDown(sourceId) {
					url="' . $this->session->userdata('callableAction') . '/" + sourceId +"/1/down";
					makeAjaxCall(url);
				}
										
				function moveToTop(sourceId) {
					url="' . $this->session->userdata('callableAction') . '/" + sourceId +"/1/top";
					makeAjaxCall(url);
				}
	
				function moveToBottom(sourceId) {
					url="' . $this->session->userdata('callableAction') . '/" + sourceId +"/1/bottom";
					makeAjaxCall(url);
				}
				
				// Return a helper with preserved width of cells
				var fixHelper = function(e, ui) {
						ui.children().each(function() {
						$(this).width($(this).width());
					});
					return ui;
				};
	
				function makeTableSortable() {
					$("#flex1 tbody").sortable(
					{
						helper: fixHelper,
						cursor : "move",
						create: function(event, ui) {
							allRows = $( "#flex1 tbody" ).sortable({ items: "> tr" }).children();
							for(var i=0; i< allRows.length; i++) {
								var _row = allRows[i];
								_id = _row.attributes["data_id"].value;
								//_id = _id.substr(4);
								allIds.push(_id);
								//console.log("Pushed - " + _id);
							}
						},
						start : function(event, ui) {
							startPosition = ui.item.prevAll().length + 1;
							itemBeingDragged = ui.item.attr("data_id");
						},
						update : function(event, ui) {
							endPosition = ui.item.prevAll().length + 1;
							if(startPosition != endPosition) {
								if(startPosition > endPosition) {
									distance = startPosition - endPosition;
									url="' . $this->session->userdata('callableAction') . '/" + itemBeingDragged +"/" + distance + "/up";
									makeAjaxCall(url);
								} else {
									distance = endPosition - startPosition;
									url="' . $this->session->userdata('callableAction') . '/" + itemBeingDragged +"/" + distance + "/down";
									makeAjaxCall(url);
								}
							}
						}
					})
				}
					
				window.onload = function() {
					makeTableSortable();
				};';
		header("Content-type: text/javascript");
		echo $js;
	}
	
	function resetPositions($table, $group_field=FALSE, $group_value=FALSE) {
		$this->load->library('Priority_manager');
		$manager = new Priority_manager();
		$manager->setTable($table);
		$manager->setGroupField($group_field);
		$manager->setPriorityField('priority');
		$manager->rearrangePriorities($group_value);
	}
	
	public function populate_up_down($value, $row) {
		$str = "<a href='javascript:moveToTop(" . $row->id . ")'><img src='" . base_url() . "assets/images/navigate-top-icon.png'></a>";
		$str .= "<a href='javascript:moveUp(" . $row->id . ")'><img src='" . base_url() . "assets/images/navigate-up-icon.png'></a>";
		$str .= "<a href='javascript:moveDown(" . $row->id . ")'><img src='" . base_url() . "assets/images/navigate-down-icon.png'></a>";
		$str .= "<a href='javascript:moveToBottom(" . $row->id . ")'><img src='" . base_url() . "assets/images/navigate-bottom-icon.png'></a>";
		return $str;
	}
	
	public function move_totop($table, $post_array, $primary_key) {
		$this->updateGroupPosition($table, 'category_id', $primary_key, 0, 'top');
	}
}
?>