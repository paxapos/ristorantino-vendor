
<div class="sabores form">
<?php echo $this->Form->create('Sabor');?>
<fieldset>
 		<legend><?php echo __('Editar Adicional');?></legend>
	<?php
		echo $this->Form->input('id');
		echo $this->Form->input('name');
		echo $this->Form->input('categoria_id');
		echo $this->Form->input('precio');
	?>
     <?php echo $this->Form->submit('Buscar', array('class'=>'btn btn-success btn-lg')); ?>
     <?php echo $this->Form->end() ?>
 </fieldset>
</div>
