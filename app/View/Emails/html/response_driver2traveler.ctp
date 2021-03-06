<?php App::uses('TimeUtil', 'Util')?>
<?php App::uses('EmailsUtil', 'Util')?>
<?php App::uses('DriverTravel', 'Model')?>

<?php echo Configure::read('email_message_separator')?>

<?php
$driver_intro = __d('conversation', 'El chofer');
$driver_desc = '#'.$driver['id'];
$driver_avatar = null;
$show_profile = false;

if(isset ($driver['DriverProfile']) && !empty($driver['DriverProfile'])) {
    $driver_intro = $driver['DriverProfile']['driver_name'];
    $driver_desc = $driver['DriverProfile']['driver_name'];
    if(isset ($driver['DriverProfile']['show_profile']) && $driver['DriverProfile']['show_profile'])
        $show_profile = true;
    
    $fullBaseUrl = Configure::read('App.fullBaseUrl');
    if(Configure::read('debug') > 0) $fullBaseUrl .= '/yotellevo'; // HACK: para poder trabajar en mi PC y que pinche en el server tambien
    
    $driver_avatar = $fullBaseUrl.'/'.str_replace('\\', '/', $driver['DriverProfile']['avatar_filepath']);
}

$travel_hint = '#'.DriverTravel::getIdentifier($driver_travel);
( $driver_travel['notification_type'] == DriverTravel::$NOTIFICATION_TYPE_DIRECT_MESSAGE ) ? $travel_hint .= ' '.__('para el').' '.TimeUtil::prettyDate($driver_travel['travel_date']) : $travel_hint .= ' '.$travel['origin'].' - '.$travel['destination'];
?>

<div id="conversation-header">
    <div><?php echo __d('conversation', 'Hola, tienes un mensaje del chofer %s de YoTeLlevo.', '<b>'.$driver_desc.'</b>')?></div>
    <div><?php echo __d('conversation', 'Para enviar tu respuesta <b>responde este correo sin modificar el asunto</b>.')?></div>
</div>
<hr style="color:#efefef; background-color:#efefef; height:1px; max-height: 1px; border:none; margin-bottom: 10px;"/>

<p><?php echo __d('conversation', 'Solicitud')?> <b><?php echo $travel_hint?></b>.</p>

<div>
    <?php if($show_profile):?>    
        <p>
            <?php
            $profile_path = $fullBaseUrl.'/driver_traveler_conversations/show_profile/'.$conversation_id;
            //$messages_path = $fullBaseUrl.'/conversations/messages/'.$conversation_id.'?highlight=message-'.$last_msg_id;
            ?>
            
            <a  style="padding:10px;color: #333;background-color: #ebebeb;border-color: #adadad;text-decoration: none"
                href="<?php echo $profile_path/*$messages_path*/?>"
                target="_blank"><b><?php echo __d('conversation', 'Mira fotos de %s y su auto', $driver_intro)?> »</b>
            </a>
        </p>
    <?php endif;?>
        
    <?php if($driver_avatar != null):?>
    <p><img class="driver-avatar" src="<?php echo $driver_avatar?>"/></p>
    <?php endif;?>
    
    <p><b><?php echo __d('conversation', '%s dice', $driver_intro)?>:</b></p>
</div>


<div style="border-left: #efefef solid 2px;padding-left: 15px">
   <?php echo preg_replace("/(\r\n|\n|\r)/", "<br/>", EmailsUtil::getFirsPart($response));?>
</div>

<hr style="color:#efefef; background-color:#efefef; height:1px; max-height: 1px; border:none; margin-top: 10px;margin-bottom: 10px;"/>
<div class="email-salute">
    <p><?php echo __d('conversation', 'Atentamente, el equipo de')?> <a href="http://yotellevocuba.com">YoTeLlevo</a></p>
    <p><a href="http://yotellevocuba.com/blog/<?php echo Configure::read('Config.language')?>">Blog</a> | <a href="https://www.facebook.com/yotellevoTaxiCuba">Facebook</a></p>
</div>