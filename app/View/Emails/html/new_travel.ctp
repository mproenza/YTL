<?php echo Configure::read('email_message_separator')?>

<?php if(!isset ($driver_name)) $driver_name = 'chofer'?>
<p>Hola <?php echo $driver_name?>,</p>
<div>
    <p>
        Un nuevo anuncio de viaje (<b>#<?php echo $travel['Travel']['id']?></b>) ha sido registrado recientemente con los siguientes datos:
    </p>
    <div style="border-left: #efefef solid 2px;padding-left: 15px"> 
        <?php echo $this->element('travel', array('travel'=>$travel, 'embedEmail'=>true, 'actions'=>false))?>
    </div>
    <p> 
        <?php $respondEmail = (Configure::read('conversations_via_app') && !isset ($admin));?>
        <?php if($respondEmail):?>
            Para comunicarte con el viajero <b>responde este correo sin modificar el asunto</b>.
            <b>Nota:</b> Puedes responder desde otro correo, copiando el asunto de este correo en el que vayas a enviar.
        <?php endif?>
        <?php if(!isset ($admin)):?>
            <div>¡Ponte en contacto <?php if(!Configure::read('conversations_via_app')):?>con el viajero<?php endif?> y haz que tu oferta sea la mejor!</div>
        <?php endif?>
    </p>
</div>

<?php 
if(!isset ($creator_role)) $creator_role = 'regular';
?>

<?php if(isset ($admin)):?>
    <small>
    <p>
        Usted recibió este correo porque es Administrador de <em>YoTeLlevo</em>.
    </p>
    
    <p>
        Viaje creado por: <?php echo $travel['User']['username']?>
    </p>
    
    <?php if(isset ($admin['drivers']) && count($admin['drivers']) > 0):?>
        <p>
            Se encontraron <?php echo count($admin['drivers'])?> choferes para notificar:
            <ul>
                <?php
                foreach ($admin['drivers'] as $d) {
                    echo '<li>'.$d['Driver']['username'].'</li>';
                }
                ?>
            </ul>
        </p>
        <p>
            <?php if($creator_role === 'regular'):?>
                Se notificaron exitosamente <b><?php echo $admin['notified_count']?></b> choferes.
            <?php else:?>
                Este viaje fue creado por un <b><?php echo $creator_role?></b>, por lo cual <b>fue enviado a choferes de prueba solamente</b>.
            <?php endif;?>
        </p>
    <?php endif?>
    </small>
<?php else: ?>
    <p>
        <small>
        Usted recibió este correo porque está registrado en <em>YoTeLlevo</em> como chofer.
        </small>
    </p>
    
    <p><a class="social-link" href="http://yotellevocuba.com">yotellevocuba.com</a> | <a class="social-link" href="https://www.facebook.com/yotellevoTaxiCuba">Facebook</a></p>
<?php endif?>