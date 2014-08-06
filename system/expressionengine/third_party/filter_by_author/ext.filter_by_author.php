<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class Filter_by_author_ext
{
	public $settings = array();
	public $name = 'Filter By Author';
	public $version = '1.0.1';
	public $description = 'Adds an author filter to the edit entries screen.';
	public $settings_exist = 'n';
	public $docs_url = 'https://github.com/rsanchez/filter_by_author';
	
	/**
	 * constructor
	 * 
	 * @access	public
	 * @param	mixed $settings = ''
	 * @return	void
	 */
	public function __construct($settings = '')
	{
		$this->EE =& get_instance();
		
		$this->settings = $settings;
	}
	
	/**
	 * activate_extension
	 * 
	 * @access	public
	 * @return	void
	 */
	public function activate_extension()
	{
		$hook_defaults = array(
			'class' => __CLASS__,
			'settings' => '',
			'version' => $this->version,
			'enabled' => 'y',
			'priority' => 10
		);
		
		$hooks[] = array(
			'method' => 'cp_menu_array',
			'hook' => 'cp_menu_array'
		);
		
		$hooks[] = array(
			'method' => 'edit_entries_additional_where',
			'hook' => 'edit_entries_additional_where',
		);
		
		foreach ($hooks as $hook)
		{
			$this->EE->db->insert('extensions', array_merge($hook_defaults, $hook));
		}
	}
	
	/**
	 * update_extension
	 * 
	 * @access	public
	 * @param	mixed $current = ''
	 * @return	void
	 */
	public function update_extension($current = '')
	{
		if ($current == '' OR $current == $this->version)
		{
			return FALSE;
		}
		
		$this->EE->db->update('extensions', array('version' => $this->version), array('class' => __CLASS__));
	}
	
	/**
	 * disable_extension
	 * 
	 * @access	public
	 * @return	void
	 */
	public function disable_extension()
	{
		$this->EE->db->delete('extensions', array('class' => __CLASS__));
	}
	
	/**
	 * settings
	 * 
	 * @access	public
	 * @return	void
	 */
	public function settings()
	{
		$settings = array();
		
		return $settings;
	}
	
	/**
	 * Adds an author id to the search query
	 * 
	 * @param array $filter_data the original filter data from the search query
	 * 
	 * @return array    addtional wheres for the query
	 */
	public function edit_entries_additional_where($filter_data)
	{
		$_hook_wheres = $this->EE->extensions->last_call;
		
		if ($this->EE->input->post('author_id'))
		{
			$_hook_wheres['author_id'] = $this->EE->input->post('author_id');
		}
		
		return $_hook_wheres;
	}
	
	/**
	 * Adds the Filter by author dropdown to the edit entries screen via JS
	 * 
	 * @param array $menu the menu array
	 * 
	 * @return array    the menu array
	 */
	public function cp_menu_array($menu)
	{
		if ($this->EE->extensions->last_call !== FALSE)
		{
			$menu = $this->EE->extensions->last_call;
		}
		
		//confirm we're on the edit entries screen
		if ($this->EE->input->get('C') === 'content_edit' && ! $this->EE->input->get('M'))
		{
			$this->EE->load->library('javascript');
			
			$authors = array('' => lang('filter_by_author'));
			
			$this->EE->load->model('member_model');
			
			//get the eligible authors
			$query = $this->EE->member_model->get_authors();
			
			foreach ($query->result() as $row)
			{
				$authors[$row->member_id] = $row->screen_name ? $row->screen_name : $row->username;
			}
			
			//add the dropdown filter
			$this->EE->javascript->output('
				$("form#filterform div.group").append('.json_encode(NBS.NBS.form_dropdown('author_id', $authors, NULL, 'id="author_id"')).');
				$("#author_id").on("change", function() {
					$("#search_button").trigger("click");
				});
			');
		}
		
		return $menu;
	}
}

/* End of file ext.filter_by_author.php */
/* Location: ./system/expressionengine/third_party/filter_by_author/ext.filter_by_author.php */
