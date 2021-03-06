<?php App::uses('User', 'Model')?>
<?php App::uses('Locality', 'Model')?>

<?php
if (!isset($do_ajax))
    $do_ajax = false;

if(!isset ($intent)) $intent = 'add';
if (!isset($form_action)) {
    $form_action = 'add';
    $intent = 'add';
}

if (!isset($style))
    $style = '';
if (!isset($is_modal))
    $is_modal = false;

$buttonStyle = '';
if ($is_modal)
    $buttonStyle = 'display:inline-block;float:left';

$asLink = false;

$origin = '';
$destination = '';
if(isset ($travel) && !empty ($travel)) {
    $saveButtonText = __('Salvar Datos');
    
    $origin = $travel['Travel']['origin'];
    $destination = $travel['Travel']['destination'];
    
} else {
    $asLink = true;
    $buttonStyle = 'font-size:18pt;white-space: normal;';
    $saveButtonText = __d('travel', 'Enviar solicitud ahora').' <div style="font-size:12pt;padding-left:50px;padding-right:50px">'.__d('travel', 'Contacta con %s choferes. Escoge uno para tu viaje.', '<big>3</big>').'</div>';
}

$form_disabled = !User::canCreateTravel();
?>

<?php if($intent === 'add' && $form_disabled):?>
    <div class="alert alert-warning">
        <?php echo __('<b>Verifica tu cuenta de correo electrónico</b> para crear más anuncios de viajes. El formulario de viajes permanecerá desactivado hasta que verifiques tu cuenta')?>. 
        <div style="padding-top: 10px">
            <big><big><b><?php echo $this->Html->link('<i class="glyphicon glyphicon-ok"></i> '.__('Enviar correo de verificación'), array('controller'=>'users', 'action'=>'send_confirm_email'), array('escape'=>false))?></b></big></big>
            <div><small>(<?php echo __('Enviaremos un correo a <b>%s</b> con las instrucciones', AuthComponent::user('username'))?>)</small></div>
        </div>        
    </div>
<?php else:?>
    <div>
        <div id='travel-ajax-message'></div>
        <div id="TravelFormDiv">
        <?php echo $this->Form->create('Travel', array('default' => !$do_ajax, 'url' => array('controller' => 'travels', 'action' => $form_action), 'style' => $style, 'id'=>'TravelForm'));?>
        <fieldset>
            <?php
            echo $this->Form->input('origin', array('type' => 'text', 'class'=>'locality-typeahead', 'label' => __d('travel', 'Origen del viaje'), 'required'=>true, 'value'=>$origin, 'autofocus'=>'autofocus'));
            echo $this->Form->input('destination', array('type' => 'text', 'class'=>'locality-typeahead', 'label' => __d('travel', 'Destino del viaje'), 'required'=>true, 'value'=>$destination));
            echo $this->Form->custom_date('date', array('label' => __d('travel', 'Fecha del viaje'), 'dateFormat' => 'dd/mm/yyyy'));
            echo $this->Form->input('people_count', array('label' => __('Personas que viajan <small class="text-info">(máximo número de personas)</small>'), 'default' => 1, 'min' => 1));
            echo $this->Form->input('details', array('label' => __d('travel', 'Detalles del viaje'), 
                'placeholder' => __('Cualquier detalle que quieras explicar')));
            echo $this->Form->checkbox_group(Travel::getPreferences(), array('header'=>__('Preferencias')));
            echo $this->Form->input('id', array('type' => 'hidden'));

            $submitOptions = array('style' => $buttonStyle, 'class'=>'btn btn-block btn-primary', 'id'=>'TravelSubmit', 'escape'=>false);
            echo $this->Form->submit(__($saveButtonText), $submitOptions, $asLink);
            if ($is_modal)
                echo $this->Form->button(__('Cancelar'), array('id' => 'btn-cancel-travel', 'style' => 'display:inline-block'));
            ?>
        </fieldset>
        <?php echo $this->Form->end(); ?>
        </div>
    </div>
<?php endif?>


<?php
// CSS
$this->Html->css('bootstrap', array('inline' => false));
$this->Html->css('vitalets-bootstrap-datepicker/datepicker.min', array('inline' => false));
$this->Html->css('typeaheadjs-bootstrapcss/typeahead.js-bootstrap', array('inline' => false));

//JS
$this->Html->script('jquery', array('inline' => false));
$this->Html->script('bootstrap', array('inline' => false));

$this->Html->script('vitalets-bootstrap-datepicker/bootstrap-datepicker.min', array('inline' => false));

$this->Html->script('jquery-validation-1.10.0/dist/jquery.validate.min', array('inline' => false));
$this->Html->script('jquery-validation-1.10.0/localization/messages_es', array('inline' => false));

$this->Html->script('typeaheadjs/typeahead-martin', array('inline' => false));


$this->Js->set('localities', Locality::getAsSuggestions());
echo $this->Js->writeBuffer(array('inline' => false));

?>

<script type="text/javascript">    
    $(document).ready(function() {        
        $('.datepicker').datepicker({
            format: "dd/mm/yyyy",
            language: '<?php echo Configure::read('Config.language')?>',
            startDate: 'today',
            todayBtn: "linked",
            autoclose: true,
            todayHighlight: true
        });
        
        $('#TravelForm').validate({
            wrapper: 'div',
            errorClass: 'text-danger',
            errorElement: 'div'
        });  
        
        <?php if(!$do_ajax):?>
            $('#TravelForm').submit(function() {
                if (!$(this).valid()) return false;
                
                //$('#TravelForm :input').prop('disabled', true);
                //$('#TravelFormDiv').prop('disabled', true);
                
                $('#TravelSubmit').attr('disabled', true);
                $('#TravelSubmit').val('<?php echo __('Espera')?> ...');
            })
        <?php endif?>
    })
</script>

<script type="text/javascript">
    $(document).ready(function() {
        $('input.locality-typeahead').typeahead({
            valueKey: 'name',
            local: window.app.localities,
            limit: 20
        })/*.on('typeahead:selected', function(event, datum) {
            
        })*/;
        
        $('input.tt-hint').addClass('form-control');
        $('.twitter-typeahead').css('display', 'block');
    });

</script>

<script type="text/javascript">
    //<![CDATA[
    function get_form( element )
    {
        while( element )
        {
            element = element.parentNode
            if( element.tagName.toLowerCase() == "form" ) {
                return element
            }
        }
        return 0; //error: no form found in ancestors
    }
    //]]>
</script>