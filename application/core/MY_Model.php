<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class MY_Model extends CI_Model
{
	protected $tableName = '';
	protected $pk = 'id';
	protected $dataTypes = array();

	public function __construct()
    {
        parent::__construct();
    }
	
	public function all()
	{
		$query = $this->db->get($this->tableName);
		return $this->_return_array_of_obj($query->result());
	}
	
	public function find($data) 
	{			
		$query = $this->db->get_where($this->tableName, $data);
		return $this->_return_array_of_obj($query->result());
	}
	
	public function create($data)
	{
		return $this->db->insert($this->tableName, $data);
	}
	
	public function update($data)
	{
		return $this->db->where($this->pk, $data['id'])
						->update($this->tableName, $data);
	}
	
	public function delete($data)
	{
		return $this->db->delete($this->tableName, $data);
	}
	
	protected function _return_array_of_obj($result)
	{
		$return = array();
		foreach($result as $row)
			$return[] = $row;
			
		return $return;
	}
	
	protected function _set_data_types()
	{
		$table = 'COLUMNS';
		$oldDB = $this->db->database;
		
		$dbi = $this->load->database('info_schema', true);
		
		$query = $dbi->get_where($table, array('TABLE_NAME' => $this->tableName));
		$data = $this->_return_array_of_obj($query->result());

		foreach($data as $d)
			$this->dataTypes[$d->COLUMN_NAME] = strtolower($d->DATA_TYPE);
		
		$this->load->database('default', true);
	}
	
	public function get_data_types()
	{
		return $this->dataTypes;
	}

}

/* End of file MY_Model.php */
/* Location: ./application/core/MY_Model.php */