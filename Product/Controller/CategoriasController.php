<?php

App::uses('ProductAppController', 'Product.Controller');


class CategoriasController extends ProductAppController
{

    public $name = 'Categorias';




    //var $layout;
    function beforeFilter()
    {
        parent::beforeFilter();
    }

    function index()
    {
        $this->Prg->commonProcess();
        $conds = $this->Categoria->parseCriteria( $this->Prg->parsedParams() );

        $this->Categoria->recursive = 0;
        $this->set('imagenes', $this->Categoria->find('list', array('fields' => array('Categoria.id', 'Categoria.media_id'))));
        $this->set('categorias', $this->Categoria->generateTreeList($conds, null ,null, '-&nbsp;-&nbsp;-&nbsp;'));
    }

    /**
     * 
     * Reordena el arbol alfabeticamente y devuelve a la pagtalla index
     * 
     */
    function reordenar()
    {
        $this->Categoria->reorder(array('field' => 'Categoria.name', 'order' => 'ASC'));
        $this->redirect(array('action' => 'index'));
    }

    function view($id = null)
    {
        if (!$id) {
            $this->Session->setFlash(__('Invalid Categoria.'));
            $this->redirect(array('action' => 'index'));
        }
        $this->set('categoria', $this->Categoria->read(null, $id));
    }

    function recover()
    {
        if ( $this->Categoria->recover() ) {
            $this->Session->setFlash('Recuperación correcta', 'Risto.flash_success');
        } else {
            $this->Session->setFlash('Error al querer arreglar la estructura', 'Risto.flash_error');
        }
        $this->redirect(array('action' => 'index'));
    }

    function verify()
    {
        $verificados = $this->Categoria->verify();
        if ( empty($verificados) ) {
            $this->Session->setFlash('Recuperación correcta', 'Risto.flash_success');
        } else {
            $cant = count($verificados);
            $this->Session->setFlash("Existen $cant de registros que no estan correctos. Pruebe con el link de \"recuperar\"", 'Risto.flash_error');
        }
        $this->redirect(array('action' => 'index'));
    }

    function edit($id = null) {
        
        if ( $this->request->is('post') || $this->request->is('put')) {

            if ($this->Categoria->save($this->request->data)) {
                $this->Session->setFlash(__('The Categoria has been saved'), 'Risto.flash_success');
                $this->redirect(array('action'=>'index'));
            } else {
                $this->Session->setFlash(__('The Categoria could not be saved. Please, try again.'), 'Risto.flash_error');
            }

        }
        if (empty($this->request->data) && $id ) {
            $this->request->data = $this->Categoria->read(null, $id);
        }
        $this->set('categorias', $this->Categoria->generateTreeList(null, null, null, '-- '));
    }

    function delete($id = null)
    {

        if (!$id) {
            $this->Session->setFlash(__('Invalid id for Categoria'), 'Risto.flash_error');
        }
        else if ($id == CATEGORIA_ROOT_ID) {
            $this->Session->setFlash(__('This Categoria is the main category and cant be deleted'), 'Risto.flash_error');
        }
        else if ($this->Categoria->delete( $id )) {
            $this->Session->setFlash(__('Categoria deleted'), 'Risto.flash_success');
        }
        $this->redirect(array('action' => 'index'));
    }

    function listar()
    {
        $categorias = $this->Categoria->array_listado();
        $this->set('categorias', $categorias);
        $this->set('_serialize', array('categorias')); //json output
    }

}

?>