<h1>Pedidos Pendientes</h1>
<?php

foreach ($pedidos as $prov) {

	?>
	<table class="table table-condensed">
	    <caption class="center"><h4><?php echo !empty($prov['Proveedor']['name']) ? $prov['Proveedor']['name']: 'Sin Proveedor Definido'?></h4></caption>

		<thead>
			<tr>
				<th>Estado</th>
				<th>Cantidad</th>
				<th>Mercaderia</th>
				<th>Acciones</th>
			</tr>	
		</thead>
		
	<?php foreach ($prov['PedidoMercaderia'] as $merca ) { ?>
		<tr>
			<?php 
			$cant = (float)$merca['PedidoMercaderia']['cantidad'];
			$uMedida = $merca['UnidadDeMedida']['name'];
			$mercaderia = $merca['Mercaderia']['name'];
			$estado = $merca['PedidoEstado']['name'];
			$proveedor = !empty($merca['Mercaderia']['Proveedor']['name'])? $merca['Mercaderia']['Proveedor']['name'] : '';

			$detalle =  Inflector::pluralize($uMedida)." de " .$mercaderia;

			?>

			<td><?php echo $estado;?></td>
			<td><?php echo $cant;?></td>
			<td><?php echo $detalle;?></td>
			
			<td>
				<?php echo $this->Html->link("editar", array('controller'=>'PedidoMercaderias', 'action'=>'form', $merca['PedidoMercaderia']['id'] ) );?>
				 | 
				<?php echo $this->Html->link("completado", array('controller'=>'PedidoMercaderias', 'action'=>'completar', $merca['PedidoMercaderia']['id'] ) );?>
			</td>
		</tr>
	<?php }?>
	</table>
	<?php
}