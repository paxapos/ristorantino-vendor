<?php

App::uses('ComandaAppController', 'Comanda.Controller');
App::uses('ReceiptPrint', 'Printers.Utility');

class ComandasController extends ComandaAppController {

	public $name = 'Comandas';


    public function imprimir( $comanda_id ){
        $this->Comanda->id = $comanda_id;
        ReceiptPrint::comanda( $this->Comanda );
        $this->redirect( $this->referer() );
    }
    
    
	public function add( $mesa_id = null ){
            
            if (isset($this->request->data)) {                
                $this->Comanda->create();
                if ($this->Comanda->save($this->request->data)) {
                    $this->Session->setFlash( __("Comanda Guardada"), 'Risto.flash_success' );
                } else {
                    $this->Session->setFlash(__('The Comanda could not be saved. Please, try again.'), 'Risto.flash_error');
                }
                $this->redirect($this->request->data['Comanda']['redirect']);
            } else {
                $this->request->data['Comanda']['redirect'] = $this->referer();
            }
            $this->set('mesa_id', $mesa_id);                 
	}

    public function terminadas($printer_id = null) {
        $conditions = array(
                'Comanda.comanda_estado_id' => COMANDA_ESTADO_LISTO,
                'Mesa.estado_id' => MESA_ABIERTA,
                'Mesa.deleted' => 0,
                );

        $contain = array(
                'Printer',
                'ComandaEstado',
                'Mesa' => 'Mozo',                
                );

        if (!empty($printer_id)) {
            $conditions['Comanda.printer_id'] = $printer_id;
        }

        $comandas = $this->Comanda->buscarSeparandoEntradasYPrincipales("all", $conditions);

        $this->elementMenu = null;
        $this->layout = "comandero";

        $printers = $this->Comanda->Printer->find('list', array('conditions'=>array('driver' => 'Receipt')));


        $this->set(compact('comandas', 'printers', 'printer_id'));

    }

    public function comandero_index($printer_id = null) {
        $this->elementMenu = null;
        $this->layout = "comandero";
        $conditions = array(
                'Comanda.comanda_estado_id !=' => COMANDA_ESTADO_LISTO,
                'Comanda.comanda_estado_id IS NOT NULL',
                'Mesa.estado_id' => MESA_ABIERTA,
                );

        if (!empty($printer_id)) {
            $conditions['Comanda.printer_id'] = $printer_id;
        }

        $cantComandas = $this->Comanda->find('count', array(
            'conditions' => $conditions,
            ));

        $comandas = $this->Comanda->buscarSeparandoEntradasYPrincipales('all', $conditions);
        $this->autoRender = true;
        
        
        $comandaEstadosInicial = $this->Comanda->ComandaEstado->find('list', array('conditions'=> array('ComandaEstado.id' => COMANDA_ESTADO_PENDIENTE)));

        $comandaEstadosFinal = $this->Comanda->ComandaEstado->find('list', array('conditions'=> array('ComandaEstado.id' => COMANDA_ESTADO_LISTO)));

        $comandaEstados = array();
        if ( $printer_id ) {
            $comandaEstados = $this->Comanda->ComandaEstado->find('list', array('conditions'=> array('ComandaEstado.printer_id' => $printer_id)));
        }
        array_unshift( $comandaEstados, $comandaEstadosInicial );
        array_push( $comandaEstados, $comandaEstadosFinal );

        $comandaGuardadaUltima = Cache::read("Comandero.ultima_comanda_id.$printer_id");
        $comandaLeidaUltima = CakeSession::read("Comandero.ultima_comanda_id.$printer_id");

        $this->set(compact('comandas', 'cantComandas', 'printer_id', 'comandaEstados'));

        if ( $this->request->is('ajax') ) {
            $this->layout = "ajax";
        }
    }


    /**
     * 
     * Listado de comandas activas para ser utilizado por el comandero
     * en el restaurante
     * 
     * @param integer $printer_id si no se selecciona ningna impresora trae a todas
     * 
     * 
     **/
    public function comandero( $printer_id = null){

        $this->menuElement = 'Risto.menu_comandero';
        $printers = $this->Comanda->Printer->find('list', array('conditions'=>array('driver' => 'Receipt')));

        $this->comandero_index();

        $this->set(compact('printers', 'printer_id'));
    }


    public function hayActualizacion( $printer_id = null ){
        if ( $printer_id ) {
            $pathText = "Comandero.ultima_comanda_id.$printer_id";
        } else {
            $pathText = "Comandero.ultima_comanda_id";
        }
        $comandaGuardadaUltima = Cache::read($pathText);

        $comandaLeidaUltima = CakeSession::read($pathText);
        CakeSession::write($pathText, $comandaGuardadaUltima);
        
        $coso = false;
        if ( $comandaLeidaUltima != $comandaGuardadaUltima ) {
            $coso = true;
        }

        $this->set('comandasActualizadas', $coso);
        $this->set('_serialize', array('comandasActualizadas'));

    }



    public function comandero_estado_change_next($comanda_id) {
        $this->__buscarEstadoAntoSig($comanda_id, 'next');

        $this->render('comandero_estado_change');
    }


    public function comandero_estado_change_previous($comanda_id) {
        $this->__buscarEstadoAntoSig($comanda_id, 'prev');

        $this->render('comandero_estado_change');
    }
	

    /**
     *
     *
     *  @param string $afterObefore puede ser 'next' or 'prev'
     * 
     **/
    private function __buscarEstadoAntoSig($comanda_id, $afterObefore) {

        if ( $afterObefore == 'next' ) {
            $btn1 = 'before_comanda_estado_id';
            $btn2 = 'after_comanda_estado_id';
            $comanda_estado_id = COMANDA_ESTADO_LISTO;
        } else {
            $btn1 = 'after_comanda_estado_id';
            $btn2 = 'before_comanda_estado_id';
            $comanda_estado_id = COMANDA_ESTADO_PENDIENTE;
        }
        
        $this->Comanda->id = $comanda_id;
        $this->Comanda->recursive = -1;        
        $comanda = $this->Comanda->read();
        $printer_id = $comanda['Comanda']['printer_id'];
        $comanda_estado_actual_id = $comanda['Comanda']['comanda_estado_id'];

        if (    $comanda_estado_actual_id == COMANDA_ESTADO_PENDIENTE 
             || $comanda_estado_actual_id == COMANDA_ESTADO_LISTO ) {
            // si es estado inicial o final, como son generidocos debo buscarlos puntualmente 
            // dentro de ls estados posibles para esa printer_id
            $comandaEstado = $this->Comanda->ComandaEstado->find('first', array(
                'conditions' => array(
                    'ComandaEstado.printer_id' => $printer_id,
                    'ComandaEstado.'.$btn1 => $comanda_estado_actual_id
                    ),
                'recursive' => -1,
                ));
            // colocar el siguiente estado
            if ( $comandaEstado ) {
                $comanda_estado_id = $comandaEstado['ComandaEstado']['id'];
            }
        } else {
            $comandaEstado = $this->Comanda->ComandaEstado->find('first', array(
                'conditions' => array(
                    'ComandaEstado.id' => $comanda_estado_actual_id
                    ),
                'recursive' => -1,
                ));
            // colocar el siguiente estado
            $comanda_estado_id = $comandaEstado['ComandaEstado'][$btn2];
        }

        $this->__comandero_estado_change( $comanda_id, $comanda_estado_id );


        $conds = array(
                    'Comanda.id' => $comanda_id,
                    );
        $contain = array(
                    'Printer',
                    'ComandaEstado',
                    'Mesa' => 'Mozo',
                    'DetalleComanda' => array(
                        'Producto',
                        'DetalleSabor' => array('Sabor'),
                        ),
                    );
        $comanda = $this->Comanda->buscarSeparandoEntradasYPrincipales('first', $conds, $contain );

        $this->set('comanda', $comanda);

    }

    /**
     *
     *  Pasa una comanda a otro estado
     * 
     *  @param integer $comanda_id ID de la comanda
     *  @param integer $comanda_estado_id ID del estado que quiero modificar
     * 
     **/
    private function __comandero_estado_change( $comanda_id, $comanda_estado_id ){
        $this->Comanda->id = $comanda_id;
        $estadoAnteriorId = $this->Comanda->field('comanda_estado_id');
        $this->Comanda->saveField('comanda_estado_id', $comanda_estado_id);
        $link = '<a href="' . Router::url(array( 'action' => 'comandero_estado_change', $comanda_id, $estadoAnteriorId)) .'" class="btn btn-default">Deshacer Cambios</a>';
    }

	

    public function edit ( $id ) {
        if (!empty($this->request->data)) {
            if ( $this->Comanda->save($this->request->data) ) {
                $this->Session->setFlash('Se guardó correctamente la comanda', 'Risto.flash_success');
            } else {
                $this->Session->setFlash('Error al guardar la comanda', 'Risto.flash_error');
            }
            $this->redirect($this->request->data['Comanda']['redirect']);
        } else {
            $this->request->data = $this->Comanda->read(null, $id);    
            $this->request->data['Comanda']['redirect'] = $this->referer();
        }
        
        $mesas = $this->Comanda->Mesa->find('list', array(
                    'conditions'=>array(
                        'Mesa.estado_id'=>MESA_ABIERTA,
                        'Mesa.deleted'=>0
                        )));
        $mesa = $this->request->data['Mesa'];
        $mesas[$mesa['id']] = $mesa['numero'];

        $comandaEstados = $this->Comanda->ComandaEstado->find('list', array(
                'conditions'=>array(
                     'OR' => array( 
                        'ComandaEstado.printer_id' => $this->request->data['Comanda']['printer_id'],
                        'ComandaEstado.printer_id IS NULL',
                    ))));

        $this->set('mesas', $mesas);
        $this->set('comandaEstados', $comandaEstados);
        
    }


    public function delete( $id = null ) {
        if (!$id) {
            $this->Session->setFlash(__('Invalid id for Comanda'), 'Risto.flash_error');            
        }
        if ($this->Comanda->delete($id)) {
            $this->Session->setFlash(__('Comanda deleted'), 'Risto.flash_success');
        } else {
            $this->Session->setFlash(__('No se pudo eliminar la Comanda'), 'Risto.flash_error');
        }
        if ($this->request->is('ajax')) {
            return 1;
        } 
        $this->redirect($this->referer());
    }

}
