<?php

App::uses('CakeEventListener', 'Event');
App::uses('Printaitor', 'Printers.Utility');
App::uses('FiscalPrint', 'Printers.Utility');


/**
 * Nodes Event Handler
 *
 * @category Event
 * @package  Croogo.Nodes.Event
 * @license  http://www.opensource.org/licenses/mit-license.php The MIT License
 * @link     http://www.croogo.org
 */
class MesasEventListener implements CakeEventListener {

/**
 * implementedEvents
 */
	public function implementedEvents() {
		return array(
			'Mesa.print' => array(
				'callable' => 'onMesaPrint',
				//'passParams' => true,
			),			
			'Mesa.cerrada' => array(
				'callable' => 'onMesaPrint',
				//'passParams' => true,
			),			
		);
	}


	public function onMesaPrint( $event ) {
		$mesa_id = $event->subject()->id;
		
		return FiscalPrint::imprimirTicketMesa($mesa_id);
	}

}