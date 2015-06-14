<?php

App::uses('ComandaAppModel', 'Comanda.Model');

define('DETALLE_COMANDA_TRAER_TODO', 0);
define('DETALLE_COMANDA_TRAER_PLATOS_PRINCIPALES', 1);
define('DETALLE_COMANDA_TRAER_ENTRADAS', 2);


class Comanda extends ComandaAppModel {

	var $name = 'Comanda';
	
	

	public $actsAs = array(
		'Containable',
		'Utils.SoftDelete', 
		);

	

	//The Associations below have been created with all possible keys, those that are not needed can be removed
	var $hasMany = array(
			'DetalleComanda' => array('className' => 'Comanda.DetalleComanda',
								'foreignKey' => 'comanda_id',
								'dependent' => true,
								'conditions' => '',
								'fields' => '',
								'order' => '',
								'limit' => '',
								'offset' => '',
								'exclusive' => '',
								'finderQuery' => '',
								'counterQuery' => ''
			)
	);
	
	
	var $belongsTo = array(
			'Mesa' => array('className' => 'Mesa.Mesa',
								'foreignKey' => 'mesa_id',
								'conditions' => '',
								'fields' => '',
								'order' => ''
			)
	);
        
	
	
	
	/**
	 * @param comanda_id
	 * @param con_entrada 	0 si quiero todos los productos
	 * 						1 si quiero solo platos principales
	 * 						2 si quiero solo las entradas
	 *
	 */
	public function listado_de_productos_con_sabores($id, $con_entrada = DETALLE_COMANDA_TRAER_TODOS){
		//inicialiozo variable return
		$items = array();

		if($id != 0){
			$this->id = $id;
		}

		
		$this->DetalleComanda->order = 'Producto.categoria_id';
		/*
		$this->DetalleComanda->recursive = 2;
		
		// le saco todos los modelos que no necesito paraqe haga mas rapido la consulta
		$this->DetalleComanda->Producto->unBindModel(array('hasMany' => array('DetalleComanda'), 
																 'belongsTo'=> array('Categoria')));
												 
		$this->DetalleComanda->DetalleSabor->unBindModel(array('belongsTo' => array('DetalleComanda')));
		*/
		unset($condiciones);
		$condiciones[]['DetalleComanda.comanda_id'] = $this->id;
		
		switch($con_entrada){
			case DETALLE_COMANDA_TRAER_PLATOS_PRINCIPALES: // si quiero solo platos principales
				$condiciones[]['DetalleComanda.es_entrada'] = 0;
				break;
			case DETALLE_COMANDA_TRAER_ENTRADAS: // si quiero solo entradas
				$condiciones[]['DetalleComanda.es_entrada'] = 1;
				break;
			default: // si quiero todo = DETALLE_COMANDA_TRAER_TODoS
				break;
		}
		
		$items = $this->DetalleComanda->find('all',array('conditions'=>$condiciones,
														'contain'=>array(
															'Producto'=>array('Printer'),
															'Comanda'=> array('Mesa'=>array('Mozo')),
															'DetalleSabor'=>array('Sabor')
			)
											));
		return $items;
	}
	
	
	/**
	 * @param comanda_id
	 * @return array() de printer_id
	 */
	public function comanderas_involucradas($id){
		$this->recursive = 2;
		$group = array('Producto.printer_id');
		$result =  $this->DetalleComanda->find('all',array(	
                    'conditions' => array('DetalleComanda.comanda_id'=> $id),
                            'group'=>$group,
                            'fields'=>$group));
		$v_retorno = array();
		foreach($result as $r){
			$v_retorno[] = $r['Producto']['printer_id'];
		}
		return $v_retorno;
	}


	public function afterSave(  $created, $options = array() ) {
		$comanda = $this->find('first', array(
				'contain'=> false,
				'conditions' => array('Comanda.id' => $this->id),
		));
		if ( $comanda ) {
			$this->Mesa->id = $comanda['Comanda']['mesa_id'];
			return $this->Mesa->saveField('modified', date('Y-m-d H:i:s'));
		}
	}



	/**
	*
	*	Metodo usado en el PrintaitorViewObj para
	*	generar los datos que seran enviados, en este caso al
	*	comanda.ctp
	* 	@param integer $id ID de la Comanda
	*	@return array de datos que seran expuestos en la vista como variables "$this->set()"
	**/
	public function getViewDataForComandas ( $id = null ) {
		
		$observacion = $this->field('observacion');
		$this->contain(array(
			'Mesa.Mozo'
			));
		$comanda = $this->read();
		$entradas = $this->listado_de_productos_con_sabores($this->id, DETALLE_COMANDA_TRAER_ENTRADAS);
		$platos_principales = $this->listado_de_productos_con_sabores($this->id, DETALLE_COMANDA_TRAER_PLATOS_PRINCIPALES);

		$productos = array_merge($entradas, $platos_principales);

		return array(
			'productos'=> $productos,
			'entradas' => $entradas,
			'observacion' => $observacion,
			'comanda' => $comanda,
			);
	}

	
}
