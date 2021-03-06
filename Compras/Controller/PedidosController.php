<?php
App::uses('ComprasAppController', 'Compras.Controller');

/**
 * Pedidos Controller
 *
 */
class PedidosController extends ComprasAppController {


	public function index() {
		$this->Prg->commonProcess();
        $conds = $this->Pedido->parseCriteria( $this->Prg->parsedParams() );
		$this->Paginator->settings['conditions'] = $conds;

		$pedidos = $this->Paginator->paginate();
		$this->set(compact('pedidos'));     

	}
	


	public function pendientes() {
		$pedidos = $this->Pedido->PedidoMercaderia->find('all', array(
			'conditions' => array(
					'PedidoMercaderia.pedido_id IS NULL'
				),
			'contain' => array(
				'Mercaderia'=> array('Proveedor', 'Rubro'=>'Proveedor'),
				'Pedido'=>array('User'),
				'UnidadDeMedida',
				),
			'order' => array('PedidoMercaderia.created'=>'DESC'),
		));

		$pedPorRubro = array();
		foreach ($pedidos as $p) {			
			$rubroId = !empty($p['Mercaderia']['Rubro']['id']) ? $p['Mercaderia']['Rubro']['id'] : 0;
			$pedPorRubro[$rubroId]['Rubro'] = $p['Mercaderia']['Rubro'];
			$pedPorRubro[$rubroId]['PedidoMercaderia'][] = $p;
		}
		$pedidos = $pedPorRubro;

		$this->set(compact('pedidos', 'pedidoEstados'));
	}

	public function mover_oc($id) {
		if ( !$this->Pedido->exists($id) ) {
			throw new NotFoundException(__("El ID de OC no fue encontrado"));
		}
		if ( $this->request->is(array("put","post"))) {
			if ( $this->Pedido->exists( $this->request->data['Pedido']['nuevo_id'] ) ) {
				$npid = $this->request->data['Pedido']['nuevo_id'];

				if ( $this->Pedido->PedidoMercaderia->updateAll(array(
						'PedidoMercaderia.pedido_id' => $this->request->data['Pedido']['nuevo_id']
						), array(
						'PedidoMercaderia.pedido_id' => $id
						)) ) {
					 if ($this->Pedido->delete($id)) {
			            $this->Session->setFlash(__('Órden de Compra movido correctamente'));
			            $this->redirect(array('action'=>'index'));
			        }
				}
			} else {
				$this->Session->setFlash(_("No existe el pedido con ese ID"), 'Risto./Flash/flash_error');
			}

		}
		$this->Pedido->id = $id;
		$this->Pedido->contain(array(
			'PedidoMercaderia' => array('Mercaderia', 'UnidadDeMedida'),
			));
		$this->request->data = $this->Pedido->read();
	}



	public function form ( $id = null ) {
		if ( $this->request->is(array('post', 'put'))) {
			try{

				if ( $this->Pedido->PedidoMercaderia->saveLimpios( $this->request->data ) ) {
					$this->Session->setFlash("Se guardó el pedido correctamente");
				} else {
					$this->Session->setFlash("Error al guardar el pedido", 'Risto./Flash/flash_error');
				}
			} catch(Exception $e){
				$this->Session->setFlash( $e->getMessage(), 'Risto.Flash/flash_warning');	
			}


            $this->redirect(array('action'=>'index'));

		} else if (!empty($id)){
			$this->Pedido->id = $id;
			$this->Pedido->contain(array(
					'Proveedor',
					'PedidoMercaderia'=>'Mercaderia',

				));
			$this->request->data = $this->Pedido->read();
			$pedidoMercaderias = array();
			foreach($this->request->data['PedidoMercaderia'] as $pm ) {
				$pedidoMercaderias[] = array(
					'PedidoMercaderia'=>$pm,
					'Mercaderia'	  =>$pm['Mercaderia']
					);
			}
		}


        $unidadDeMedidas = $this->Pedido->PedidoMercaderia->UnidadDeMedida->find('list');
        $mercaderias = $this->Pedido->PedidoMercaderia->Mercaderia->find('list');
        $proveedores = $this->Pedido->Proveedor->find('list');
        $mercaUnidades = $this->Pedido->PedidoMercaderia->Mercaderia->find('list', array('fields'=> array('id', 'unidad_de_medida_id')));
        $this->set(compact('mercaderias', 'unidadDeMedidas', 'mercaUnidades', 'proveedores', 'pedidoMercaderias'));
	}


	public function recepcion ( $id = null ) {
		if ( $this->request->is(array('post', 'put'))) {
			$pdata = array();
			$pmlimpio = $this->Pedido->PedidoMercaderia->limpiarPedidosSinCant($this->request->data['PedidoMercaderia']);

			// paso las mercaderias a "pedidos pendientes cuando la cantidad recepcionada es menor a la pedida en la OC"
			foreach ($pmlimpio as $pm ) {
				$cant = $pm['PedidoMercaderia']['cantidad'] - $pm['PedidoMercaderia']['cantidad_anterior'];
				if ( $cant < 0 ) {
					$this->Pedido->PedidoMercaderia->create();
					$newPedidoMerca = $pm;
					$newPedidoMerca['PedidoMercaderia']['cantidad'] = abs($cant);
					unset($newPedidoMerca['PedidoMercaderia']['pedido_id']);
					unset($newPedidoMerca['PedidoMercaderia']['id']);
					unset($newPedidoMerca['Pedido']);
					$this->Pedido->PedidoMercaderia->save($newPedidoMerca);
				}
			}
			
			$savePedido =  $this->Pedido->save($this->request->data['Pedido'], array('fields'=> array('recepcionado')));
		 	$saveMerca	= $this->Pedido->PedidoMercaderia->saveAll( $pmlimpio );
			if ( $savePedido && $saveMerca ) {
				$this->Session->setFlash("Se guardó la recepción correctamente");
			} else {
				debug( $this->Pedido->PedidoMercaderia->validationErrors);
				debug( $this->Pedido->validationErrors);
				$this->Session->setFlash("Error al guardar la recepción de mercaderia", 'Risto./Flash/flash_error');
				$this->redirect(array('action'=>'index'));
			}

			if ( !empty($this->request->data['Pedido']['gen_gasto'])) {
				$this->generar_gasto($id);
			} else {
            	$this->redirect(array('action'=>'index'));
			}

		} else if (!empty($id)){
			$this->Pedido->id = $id;
			$this->Pedido->contain(array(
					'Proveedor',
					'PedidoMercaderia'=>'Mercaderia',
				));
			$this->request->data = $this->Pedido->read();
			$pedidoMercaderias = array();
			foreach($this->request->data['PedidoMercaderia'] as $pm ) {
				$pedidoMercaderias[] = array(
					'PedidoMercaderia'=> $pm,
					'Mercaderia'	  => $pm['Mercaderia']
					);
			}
		}

        $unidadDeMedidas = $this->Pedido->PedidoMercaderia->UnidadDeMedida->find('list');
        $mercaderias = $this->Pedido->PedidoMercaderia->Mercaderia->find('list');
        $proveedores = $this->Pedido->Proveedor->find('list');
        $mercaUnidades = $this->Pedido->PedidoMercaderia->Mercaderia->find('list', array('fields'=> array('id', 'unidad_de_medida_id')));
        $this->set(compact('mercaderias', 'unidadDeMedidas', 'mercaUnidades', 'proveedores', 'pedidoMercaderias'));
	}


	public function proveedor_info ( $id ) {
		$this->Pedido->Proveedor->contain(array(
			'Rubro'
			));
		$proveedor = $this->Pedido->Proveedor->read(null, $id);

		$this->set(compact("proveedor"));
	}


	public function create () {
		$provs = $pedidoMercaderias = array();
		if ( $this->request->is(array('put', 'post')) ) {

			// filtrar los checkbox que vinieron vacios. 
			// seleccionar solo las mercaderias_id que tengan ID 
			if (!empty($this->request->data['Pedido']['mercaderia_id'])) {

			$mercasId = array_filter( $this->request->data['Pedido']['mercaderia_id'], function( $item){
				return (boolean) $item;
			} );
            
			// buscar las mercaderias que vinieron seleccionadas
			$pedidoMercaderias = $this->Pedido->PedidoMercaderia->find('all', array(
				'conditions' => array(
						'PedidoMercaderia.id' => $mercasId
					),
				'contain' => array(
						'Mercaderia' => array(
							'Rubro.Proveedor',
							'Proveedor',
							'UnidadDeMedida',
							),
					),
				));

			// filtrar los proveedores involucrados
			$provs = $this->Pedido->PedidoMercaderia->getProveedoresInvolucrados($pedidoMercaderias);

			$this->request->data = null;
			if (!empty($provs)) {
				// agarra el primero y lo pone como sugerido
				$this->request->data['Pedido']['proveedor_id'] = $provs[0];
			}
		  }
		}

		$provConds = array();
		if ( $provs ) {
			$provConds = array( 'Proveedor.id NOT IN' => array_values($provs) );
		}

		$proveedoresList = $this->Pedido->PedidoMercaderia->Mercaderia->Proveedor->find('list', array(
			'conditions' => $provConds,
			));
		if (empty($provs)) {
			$proveedores = $proveedoresList;
		} else {
			$recomendados = $this->Pedido->PedidoMercaderia->Mercaderia->Proveedor->find('list', array(
					'conditions' => array(
							"Proveedor.id" => $provs,
						)
				));
			$proveedores = array(
				'Recomendados' => $recomendados,
				'Otros' => $proveedoresList
				);
		}
		$unidadDeMedidas = $this->Pedido->PedidoMercaderia->UnidadDeMedida->find('list');
        $mercaderias = $this->Pedido->PedidoMercaderia->Mercaderia->find('list');
        $mercaUnidades = $this->Pedido->PedidoMercaderia->Mercaderia->find('list', array('fields'=> array('id', 'unidad_de_medida_id')));
        $this->set(compact('mercaderias', 'unidadDeMedidas', 'mercaUnidades', 'proveedores', 'pedidoMercaderias'));


        $this->render('form');
	}

	public function desvincular_gasto($id) {
		if ( !$this->Pedido->exists($id) ) {
			throw new NotFoundException( __("No existe un pedido con ese ID") );
		}

		$this->Pedido->id = $id;
		if ( $this->Pedido->saveField('gasto_id', null)) {
			$this->Session->setFlash(_("Se desvinculó la OC con el Gasto"));
		} else {
			$this->Session->setFlash(_("no se pudo desvincular la OC con el Gasto", "Risto.flash_error"));
		}

		$this->redirect($this->referer());
	}


	public function generar_gasto($id) {
		if (!$this->Pedido->exists($id) ){
			throw new NotFoundException(__("No existe el Pedido"));
		}
		$this->Pedido->id = $id;
		$this->Pedido->contain = array('PedidoMercaderia');
		$pedido = $this->Pedido->read();

		$total = 0;
		foreach ( $pedido['PedidoMercaderia'] as $pm ) {
			$total += $pm['precio'];
		}
		$proveedorId = $pedido['Pedido']['proveedor_id'];

		$data = array('Gasto' => array(
				'proveedor_id' => $proveedorId,
				'fecha' => date('Y-m-d'),
				'importe_neto' => $total,
				'importe_total' => $total,
				));
		$this->Pedido->Gasto->create();
		
		$ultimoGasto = $this->Pedido->Gasto->find('first', array(
			'conditions' => array(
				'Gasto.proveedor_id' => $proveedorId
				),
			'order' => array('Gasto.fecha'=> 'DESC'),
			'contain' => array(
				'Proveedor',
				'TipoFactura'
				),
			));
		if ( $ultimoGasto ) {
			$data ['Gasto']['tipo_factura_id'] = $ultimoGasto['Gasto']['tipo_factura_id'];
		}
		if ( $this->Pedido->Gasto->save($data) ) {

			$this->Pedido->saveField('gasto_id', $this->Pedido->Gasto->id);

			$url = array(
				'plugin' => 'account',
				'controller' => 'gastos',
				'action' => 'edit',
				$this->Pedido->Gasto->id,
				);
			$this->Session->setFlash(__("Se generó un nuevo gasto"));
		} else {
			$url = $this->referer();
			debug($this->Pedido->Gasto->validationErrors);die;
			$this->Session->setFlash(__("Error al generar nuevo gasto", 'flash_error'));
		}

		$this->redirect($url);
	}


	public function imprimir ( $id ) {
		$this->Pedido->id = $id;
		try{
			ReceiptPrint::imprimirPedidoCompra($this->Pedido);
			$this->Session->setFlash( __( "Se envió a imprimir la Órden de Compra #%s", $id ));
		} catch(Exception $e){
			$this->Session->setFlash( $e->getMessage(), 'Risto.Flash/flash_warning');	
		}
		$this->redirect($this->referer() );
	}

	public function view ( $id ) {
		$pedido = $this->Pedido->find('first', array(
			'conditions'=>array('Pedido.id'=>$id),
			'contain' => array(
				'Proveedor',
				'PedidoMercaderia'=> array(
					'Mercaderia'=>array('Proveedor'),
					'UnidadDeMedida',
					'PedidoEstado',
					'Proveedor',
					)
				)
			));

		$this->set('pedido', $pedido);
	}


	public function delete($id = null)
    {
        if (!$id) {
            $this->Session->setFlash(__('Invalid id for Órden de Compra'));
            $this->redirect( $this->referer() );
        }
        if ($this->Pedido->delete($id)) {
            $this->Session->setFlash(__('Órden de Compra eliminada correctamente'));
            if ( !$this->request->is('ajax') ) {
                $this->redirect(array('action'=>'index') );
            }
        }
        $this->Session->setFlash(__('La Órden de Compra no se puede eliminar. Reintente.'));
        $this->redirect($this->referer() );
    }

}
