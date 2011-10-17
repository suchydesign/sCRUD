<?php $this->load->view('layout_parts/header'); ?>
<ul id="menu">
<?php //$this->load->view('layout_parts/menu'); ?>
</ul>
<h2>Tables</h2>
<table>
	<?=form_open(current_url()); ?>
	<?php foreach($tables as $table): ?>
	<?php foreach($table as $t): ?>
	<tr>
		<td><?=$t; ?></td>
		<td><?=form_checkbox('tables[]', $t, TRUE); ?></td>
	</tr>
	<?php endforeach; ?>
	<?php endforeach; ?>
	<tr>
		<td colspan="2"><?=form_submit('generate', 'Generate App'); ?></td>
	</tr>
	<?=form_close(); ?>
</table>
<?php $this->load->view('layout_parts/footer'); ?>