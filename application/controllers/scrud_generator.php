<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

/**
 * Scrud_generator class
 * generate models, controller, views and validations for database tables
 */
class Scrud_generator extends CI_Controller 
{
	public function __construct()
    {
        parent::__construct();
		//$this->output->enable_profiler(TRUE);
    }

	public function index()
	{
		$this->_generate();		
		$q = $this->db->query('show tables');
		$data['tables'] = $q->result();		
		$this->load->view('scrud_generator/index', $data);
	}
	
	private function _generate()
	{
		if($this->input->post('generate'))
			foreach($_POST['tables'] as $table)
			{
				$names = $this->_generate_names($table);
				$this->_generateModel($names);
				$this->_generateController($names);
				$this->_generateViews($names);
				$this->_generateValidation($names);
			}
	}
	
	private function _generate_names($table)
	{
		$lowercase = strtolower($table);
		return $names = array(
			'controllerName' => ucfirst($lowercase),
			'modelName' => ucfirst($lowercase) . '_model',
			'viewsName' => $lowercase . '',
			'varName' => $lowercase . '',
			'tableName' => $table
		);
	}
	
	private function _generateController($names)
	{
		$fileName = strtolower($names['controllerName']);
		
		$this->load->model($names['modelName'], $names['varName']);
		$belongsTo = "";
		$belongsToGetData = "";
		$belongsArray = $this->$names['varName']->get_belongs_to();
		if(!empty($belongsArray)) foreach($belongsArray as $bt)
		{
			$btNames = $this->_generate_names($bt);
			$belongsTo .= "			\$this->load->model('{$btNames['modelName']}', '{$btNames['varName']}');\n";
			$belongsToGetData .= "		\$data['{$btNames['varName']}'] = \$this->{$btNames['varName']}->all();\n";
		}
		
		$data = 
"<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class {$names['controllerName']} extends CI_Controller 
{
	protected \$_editStatus = 'Operation complete.';
	
	public function __construct()
	{
		parent::__construct();
		\$this->load->library('form_validation');
		\$this->load->model('{$names['modelName']}', '{$names['varName']}');
		foreach(\$this->{$names['varName']}->get_belongs_to() as \$bt)
		{\n" 
		. $belongsTo . 			
		"		}
	}

	public function index()
	{
		if(\$this->_destroy())
			\$data['success'] = \$this->_editStatus;
			
		\$data['{$names['varName']}'] = \$this->{$names['varName']}->all();
		\$this->load->view('{$names['viewsName']}/index', \$data);
	}
	
	public function new_one()
	{
		if(\$this->_create())
			\$data['success'] = \$this->_editStatus;
			
		\$data['{$names['varName']}DataTypes'] = \$this->{$names['varName']}->get_data_types();\n"
		. $belongsToGetData .
		"		\$this->load->view('{$names['viewsName']}/new_one', \$data);
	}
	
	protected function _create()
	{
		if(\$this->input->post('create'))
			if(\$this->form_validation->run('{$names['varName']}/_create'))
			{	
				unset(\$_POST['create']);
				if(\$this->{$names['varName']}->create(\$_POST))
				{	
					unset(\$_POST);
					return TRUE;
				}
			}
		return FALSE;
	}
	
	public function show(\$id)
	{
		if(\$data['{$names['varName']}'] = \$this->{$names['varName']}->find(array('id' => \$id)))
			\$this->load->view('{$names['viewsName']}/show', \$data);
		else
			show_404(current_url());
	}
	
	public function edit(\$id)
	{
		if(\$this->_update())
			\$data['success'] = \$this->_editStatus;
			
		\$data['{$names['varName']}DataTypes'] = \$this->{$names['varName']}->get_data_types();\n"
		. $belongsToGetData .
		"		if(\$data['{$names['varName']}'] = \$this->{$names['varName']}->find(array('id' => \$id)))
			\$this->load->view('{$names['viewsName']}/edit', \$data);
		else
			show_404(current_url());
	}
	
	protected function _update()
	{
		if(\$this->input->post('update'))
			if(\$this->form_validation->run('{$names['varName']}/_update'))
			{	
				unset(\$_POST['update']);
				if(\$this->{$names['varName']}->update(\$_POST))	
				{	
					unset(\$_POST);
					return TRUE;
				}
			}
		return FALSE;
	}
	
	protected function _destroy()
	{
		if(\$this->input->post('delete'))
			if(\$this->form_validation->run('{$fileName}/_destroy'))
			{
				unset(\$_POST['delete']);
				if(\$this->{$names['varName']}->delete(\$_POST))	
				{	
					unset(\$_POST);
					return TRUE;
				}
			}
		return FALSE;
	}
}

/* End of file {$fileName}.php */
/* Location: ./application/controllers/{$fileName}.php */";
		
		file_put_contents(APPPATH . 'controllers/' . $fileName. '.php', $data);
	}
	
	private function _generateModel($names)
	{
		$fileName = strtolower($names['modelName']);
		$data = 
"<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class {$names['modelName']} extends MY_Model 
{
	public function __construct()
    {
        parent::__construct();
		\$this->tableName = '{$names['tableName']}';
		\$this->_set_data_types();
		\$this->_set_belongs_to();
    }
}

/* End of file {$fileName}.php */
/* Location: ./application/models/{$fileName}.php */";
	
		file_put_contents(APPPATH . 'models/' . $fileName . '.php', $data);
	}
	
	private function _generateViews($names)
	{
		$this->load->helper('formd');
		$fileName = strtolower($names['controllerName']);
		if(!is_dir(APPPATH . 'views/' . $fileName))
			mkdir(APPPATH . 'views/' . $fileName);
		
		$this->load->model($names['modelName'], $names['varName']);
		$belongsArray = $this->$names['varName']->get_belongs_to();
		
		$data = "";
		$data .= "	<li><?=anchor('{$fileName}', '{$names['controllerName']}'); ?></li>\n";
		file_put_contents(APPPATH . 'views/layout_parts/menu.php', $data, FILE_APPEND | LOCK_EX);
		
		$data = 
"<?php \$this->load->view('layout_parts/header'); ?>
<ul id=\"menu\">
<?php \$this->load->view('layout_parts/menu'); ?>
</ul>
<h2>Edit</h2>
<div id=\"error_message\">	
	<?php if(isset(\$success)): ?>
		<p class=\"success\"><?=\$success; ?></p>
	<?php endif; ?>
	<?=validation_errors(); ?>
</div>
<table>\n";
	$this->load->model($names['modelName']);
	$result = $this->$names['modelName']->get_data_types();
	$data .= "	<?=form_open(current_url()); ?>\n";
	if(!empty($result)) foreach($result as $key => $value)
	{
		if($key == 'id')
		{
			$data .= "		<?=" . form_input_type($value, $key) . "('$key', \${$names['varName']}[0]->$key); ?>\n";
		}
		else 
		{
			$bt = substr($key, 0, -3);
			if(in_array($bt, $belongsArray))
			{
				$data .= "
	<tr>
		<th>" . form_label($key, $key) . "</th>
		<td>
			<select name=\"$key\">
			<?php if(!empty(\${$bt})): ?>
			<?php foreach(\${$bt} as \$_{$bt}): ?>
				<option value=\"<?=\$_{$bt}->id; ?>\" <?php if(\$_{$bt}->id == (\$this->input->post('$key') ? \$this->input->post('$key') : \${$names['varName']}[0]->$key)) echo \"selected=\\\"selected\\\"\"; ?>><?=\$_{$bt}->id; ?></option>
			<?php endforeach; ?>
			<?php else: ?>
				<?=" . form_input_type($value, $key) . "('$key', (\$this->input->post('$key') ? \$this->input->post('$key') : \${$names['varName']}[0]->$key)); ?>
			<?php endif; ?>
			</select>
		</td>
	</tr>";
			}
			else
			{
				$data .="	<tr>\n";
				$data .= "		<th>" . form_label($key, $key) . "</th>\n";
				$data .= "		<td><?=" . form_input_type($value, $key) . "('$key', (\$this->input->post('$key') ? \$this->input->post('$key') : \${$names['varName']}[0]->$key)); ?></td>
	</tr>\n";
			}
		}
	}
	$data .= "	<tr>
		<td colspan=\"2\"><?=form_submit('update', 'Update'); ?></td>
	</tr>
	<?=form_close(); ?>
</table>
<ul>
	<li><?=anchor('{$names['varName']}', 'View all'); ?></li>
	<li><?=anchor('{$names['varName']}/' . \${$names['varName']}[0]->id, 'View'); ?></li>
	<li><?=anchor('{$names['varName']}/new', 'New'); ?></li>
	<li><?php 
		echo form_open(site_url('{$names['varName']}'));
		echo form_hidden('id', \${$names['varName']}[0]->id);
		echo form_submit('delete', 'Delete');
		echo form_close();
	?></li>
</ul>
<?php \$this->load->view('layout_parts/footer'); ?>";
		file_put_contents(APPPATH . 'views/' . $fileName . '/edit.php', $data);
		
		$data = 
"<?php \$this->load->view('layout_parts/header'); ?>
<ul id=\"menu\">
<?php \$this->load->view('layout_parts/menu'); ?>
</ul>
<h2>List</h2>
<div id=\"error_message\">	
	<?php if(isset(\$success)): ?>
		<p class=\"success\"><?=\$success; ?></p>
	<?php endif; ?>
	<?=validation_errors(); ?>
</div>
<table>\n";
	$data .= "	<tr>\n";
	if(!empty($result)) foreach($result as $key => $value)
	{
		$data .= "		<th>$key</th>\n";
	}
	$data .= "		<th colspan=\"3\">actions</th>
	</tr>
	<?php foreach(\${$names['varName']} as \$_{$names['varName']}): ?>
	<tr>\n";
		if(!empty($result)) foreach($result as $key => $value)
		{
			$data .= "		<td><?=\$_{$names['varName']}->$key; ?></td>\n";
		}
		$data .= "		<td><?=anchor('{$names['varName']}/' . \$_{$names['varName']}->id, 'view'); ?></td>
		<td><?=anchor('{$names['varName']}/edit/' . \$_{$names['varName']}->id, 'edit'); ?></td>
		<td><?php 
			echo form_open(current_url());
			echo form_hidden('id', \$_{$names['varName']}->id);
			echo form_submit('delete', 'Delete');
			echo form_close();
		?></td>
	</tr>
	<?php endforeach; ?>
</table>
<ul>
	<li><?=anchor('{$names['varName']}', 'View all'); ?></li>
	<li><?=anchor('{$names['varName']}/new', 'New'); ?></li>
</ul>
<?php \$this->load->view('layout_parts/footer'); ?>";
		file_put_contents(APPPATH . 'views/' . $fileName . '/index.php', $data);
		
		$data = 
"<?php \$this->load->view('layout_parts/header'); ?>
<ul id=\"menu\">
<?php \$this->load->view('layout_parts/menu'); ?>
</ul>
<h2>New</h2>
<div id=\"error_message\">	
	<?php if(isset(\$success)): ?>
		<p class=\"success\"><?=\$success; ?></p>
	<?php endif; ?>
	<?=validation_errors(); ?>
</div>
<table>\n";
	$this->load->model($names['modelName']);
	$result = $this->$names['modelName']->get_data_types();
	$data .= "	<?=form_open(current_url()); ?>\n";
	if(!empty($result)) foreach($result as $key => $value)
	{
		if($key != 'id')
		{
			$data .="	<tr>\n";
			$data .= "		<th>" . form_label($key, $key) . "</th>\n";
			$data .= "		<td><?=" . form_input_type($value, $key) . "('$key'); ?></td>
		</tr>\n";
		}
	}
	$data .= "	<tr>
		<td colspan=\"2\"><?=form_submit('create', 'Create'); ?></td>
	</tr>
	<?=form_close(); ?>
</table>
<ul>
	<li><?=anchor('{$names['varName']}', 'View all'); ?></li>
</ul>
<?php \$this->load->view('layout_parts/footer'); ?>";
		file_put_contents(APPPATH . 'views/' . $fileName . '/new_one.php', $data);
		
		$data = 
"<?php \$this->load->view('layout_parts/header'); ?>
<ul id=\"menu\">
<?php \$this->load->view('layout_parts/menu'); ?>
</ul>
<h2>Show</h2>
<table>\n";
	$this->load->model($names['modelName']);
	$result = $this->$names['modelName']->get_data_types();
	$data .= "	<?=form_open(current_url()); ?>\n";
	if(!empty($result)) foreach($result as $key => $value)
	{
		$data .="	<tr>\n";
		$data .= "		<th>$key</th>\n";
		$data .= "		<td><?=\${$names['varName']}[0]->$key; ?></td>
	</tr>\n";
	}
	$data .= "
</table>
<ul>
	<li><?=anchor('{$names['varName']}', 'View all'); ?></li>
	<li><?=anchor('{$names['varName']}/edit/' . \${$names['varName']}[0]->id, 'Edit'); ?></li>
	<li><?=anchor('{$names['varName']}/new', 'New'); ?></li>
	<li><?php 
		echo form_open(site_url('{$names['varName']}'));
		echo form_hidden('id', \${$names['varName']}[0]->id);
		echo form_submit('delete', 'Delete');
		echo form_close();
	?></li>
</ul>
<?php \$this->load->view('layout_parts/footer'); ?>";
		file_put_contents(APPPATH . 'views/' . $fileName . '/show.php', $data);
	}
	
	private function _generateValidation($names)
	{
		$this->load->model($names['modelName']);
		$dataTypes = $this->$names['modelName']->get_data_types();
		
		$data = "\n\$config['{$names['varName']}/_update'] = array(\n";
		$count = sizeof($dataTypes);
		$i = 1;
		foreach($dataTypes as $k => $v)
		{
			$data .= "	array(\n";
			$data .= "		'field' => '{$k}',\n";
			$data .= "		'label' => '{$k}',\n";
			if($k == 'id')
				$data .= "		'rules' => 'trim|required|integer|xss_clean'\n";
			elseif($v == 'tinyint' or $v == 'smallint' or $v == 'mediumint' or $v == 'int' or $v == 'bigint')
				$data .= "		'rules' => 'trim|required|integer|xss_clean'\n";
			elseif($v == 'decimal' or $v == 'float' or $v == 'double' or $v == 'real')
				$data .= "		'rules' => 'trim|required|numeric|xss_clean'\n";
			else
				$data .= "		'rules' => 'trim|required|xss_clean'\n";
			$data .= "	)";	
			
			if(array_key_exists('id', $dataTypes))
			{
				if($count - $i > 0)
					$data .= ",\n";
			}
			else
			{
				if($count - $i > 0)
					$data .= ",\n";
				else
					$data .= "\n";
			}
			$i++;		
		}
		$data .= ");\n";
		//print_r($data);
		file_put_contents(APPPATH . 'config/form_validation.php', $data, FILE_APPEND | LOCK_EX);
		
		$data = "\n\$config['{$names['varName']}/_create'] = array(\n";
		$count = sizeof($dataTypes);
		$i = 1;
		foreach($dataTypes as $k => $v)
		{
			if($k != 'id')
			{
				$data .= "	array(\n";
				$data .= "		'field' => '{$k}',\n";
				$data .= "		'label' => '{$k}',\n";
				if($v == 'tinyint' or $v == 'smallint' or $v == 'mediumint' or $v == 'int' or $v == 'bigint')
					$data .= "		'rules' => 'trim|required|integer|xss_clean'\n";
				elseif($v == 'decimal' or $v == 'float' or $v == 'double' or $v == 'real')
					$data .= "		'rules' => 'trim|required|numeric|xss_clean'\n";
				else
					$data .= "		'rules' => 'trim|required|xss_clean'\n";
				$data .= "	)";
				
				if(array_key_exists('id', $dataTypes))
				{
					if($count - $i > 1)
						$data .= ",\n";
				}
				else
				{
					if($count - $i > 0)
						$data .= ",\n";
					else
						$data .= "\n";
				}
				$i++;
			}
		}
		$data .= ");\n";
		//print_r($data);
		file_put_contents(APPPATH . 'config/form_validation.php', $data, FILE_APPEND | LOCK_EX);
		
		$data = "\n\$config['{$names['varName']}/_destroy'] = array(\n";
		$data .= "	array(\n";
		$data .= "		'field' => 'id',\n";
		$data .= "		'label' => 'id',\n";
		$data .= "		'rules' => 'trim|required|integer|xss_clean'\n";
		$data .= "	)\n";			
		$data .= ");\n";
		//print_r($data);
		file_put_contents(APPPATH . 'config/form_validation.php', $data, FILE_APPEND | LOCK_EX);
	}
	
}

/* End of file scrud_generator.php */
/* Location: ./application/controllers/scrud_generator.php */