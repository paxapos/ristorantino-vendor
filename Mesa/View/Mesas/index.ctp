<?php $this->element("Risto.layout_modal_edit", array('title'=>'Mesa', 'size'=>'modal-lg'));?>

<div class="content-white">
<div id="mesas-index">
    <?php echo $this->Html->link(__('Abrir %s', Configure::read('Mesa.tituloMesa')), array('action' => 'add'), array('class'=>'btn btn-lg btn-success pull-right btn-add')); ?>
    <h1><?php echo Inflector::pluralize(   Configure::read('Mesa.tituloMesa') ) ;?></h1>

    <!-- fomr search -->
    <div class="row">
        <?php echo $this->Form->create("Mesa"); ?>        

        <div class=" col-md-1">
            <?php echo $this->Form->input('numero', array('label' => Configure::read('Mesa.tituloMesa'), 'required'=>false)); ?>
            <?php echo $this->Form->input('mozo_id', array('label' => Configure::read('Mesa.tituloMozo'), 'empty' => 'Todos', 'required'=>false )); ?>
        </div>
        <div class="col-md-1">
            <?php echo $this->Form->input('total', array('label' => 'Importe'));
            echo $this->Form->input('estado_id', array(
                'label' => __('Estado'),
                'type' => 'select',
                'empty' => 'Seleccione',                
            ));
            ?>



        </div>


        <div class="col-md-2">
            <?php
            echo $this->Form->input('created_from', array(
                'label' => 'Creada desde',
                'type' => 'datetime',
            ));
            ?>
            <?php
            echo $this->Form->input('created_to', array(
                'label' => 'Creada hasta',
                'type' => 'datetime',
            ));
            ?>
        </div>

        <div class="col-md-2">
            <?php
            echo $this->Form->input('time_cerro_from', array(
                'label' => 'Facturada desde',
                'type' => 'datetime',
            ));
            ?>
            <?php
            echo $this->Form->input('time_cerro_to', array(
                'label' => 'Facturada hasta',
                'type' => 'datetime',
            ));
            ?>
        </div>

        <div class="col-md-2">
            <?php
            echo $this->Form->input('time_cobro_from', array(
                'label' => 'Cobrada desde',
                'type' => 'datetime',
            ));
            ?>
            <?php
            echo $this->Form->input('time_cobro_to', array(
                'label' => 'Cobrada hasta',
                'type' => 'datetime',
            ));
            ?>
        </div>

        <div class="col-md-2">
            <?php
            echo $this->Form->input('checkin_from', array(
                'label' => 'Checkin desde',
                'type' => 'date',
            ));
            ?>
            <?php
            echo $this->Form->input('checkin_to', array(
                'label' => 'Checkin hasta',
                'type' => 'date',
            ));
            ?>
        </div>

        <div class="col-md-2">
            <?php
            echo $this->Form->input('checkout_from', array(
                'label' => 'Checkout desde',
                'type' => 'date',
            ));
            ?>
            <?php
            echo $this->Form->input('checkout_to', array(
                'label' => 'Checkout hasta',
                'type' => 'date',
            ));
            ?>
        </div>

    </div>
    <!-- END form search -->


    <div class="clear"></div>

    <?php
    $url = array('action'=> $this->action, 'ext'=> 'xls', '?'=> $this->request->query);
    echo $this->Html->link(' <span class="glyphicon glyphicon-download"></span> '.__('Descargar Excel')
        , $url
        , array(
            'escape' => false,
            'data-ajax' => 'false',
            'class' => 'btn btn-primary pull-right',
            'id' => 'descargarExcel',
            'div'=> array(
                'class' => 'pull-right'
                )
        ));

    echo $this->Form->input('deleted', array('label' => 'Incluir Mesas Borradas', 'type'=>'checkbox', 'default'=>0));
          
            

      echo $this->Form->submit('Buscar', array('class' => 'btn btn-primary', 'title' => __('Buscar')));
      echo $this->Form->end();

      ?>
    <br />
    <?php
    if ($this->Paginator->params['paging']['Mesa']['count'] != 0) {
        echo $this->element('listado_tabla');
    } else {
        ?>
        </br>
        <strong>            
            <?php  echo __( 'No se encontraron %s', Inflector::pluralize(Configure::read('Mesa.tituloMesa') ) ); ?>
        </strong>
        <?php
    }
    ?>


</div>

<div>
    <p>
    <?php
    echo $this->Paginator->counter(array(
        'format' => __('Página {:page} de {:pages}, mostrando {:current} elementos de {:count}')
    ));
    ?></p>
<?php echo $this->element('Risto.pagination'); ?>
</div>
</div>