<?php if(!isset($driver_name)) $driver_name = 'chofer'?>

<p>Hola <?php echo $driver_name?>,</p>

<p>Acabamos de aprobar una opinión de clientes suyos! Ahora se encuentra pública en su perfil como una recomendación que verán otros clientes potenciales.</p>

<p>Tener opiniones positivas siempre ayuda a convencer a los viajeros, además de que son una prueba de la calidad de sus servicios y de su experiencia.</p>

<p><b>Le animamos a compartir esta opinión en su Facebook para que amigos y clientes interesados la puedan ver!</b></p>

<p style="margin-top: 10px;margin-bottom: 10px;">
    <span>
        <a  style="padding:10px;background-color: #3b5998;color: #FFFFFF !important;text-decoration: none"
            href="https://www.facebook.com/sharer/sharer.php?u=<?php echo $this->Html->url(array('language'=>$testimonial['lang'], 'controller' => 'drivers', 'action' => 'profile',$driver_nick,'?'=>array('see-review'=>$testimonial['id']), 'base'=>false), true) ?>" 
            target="_blank">
            <b>Comparte esta opinión sobre tí en tu Facebook »</b>
        </a>
    </span>
<p/>

<p>A continuación los detalles de la opinión:</p>

<div>
    <?php
    $client = '<b>'.$testimonial['author'].'</b>';
    if($testimonial['country'] != null && !empty($testimonial['country'])) 
        $client .= ' de <b>'.$testimonial['country'].'</b>';
    ?>
    <p><?php echo $client?> dice:</p>
    <p><?php echo preg_replace("/(\r\n|\n|\r)/", "<br/>", $testimonial['text']);?></p>
    <?php if ($testimonial['image_filepath']): ?>
        <p><b>El testimonio tiene una foto!</b></p>
    <?php endif?>

    <p style="margin-top: 10px;margin-bottom: 10px;">
        <a  style="padding:10px;background-color: #3b5998;color: #FFFFFF !important;text-decoration: none"
            href="https://www.facebook.com/sharer/sharer.php?u=<?php echo $this->Html->url(array('language'=>$testimonial['lang'], 'controller' => 'drivers', 'action' => 'profile',$driver_nick,'?'=>array('see-review'=>$testimonial['id']), 'base'=>false), true) ?>" >
            <b>Comparte esta opinión sobre tí en tu Facebook »</b>
        </a>
    <p/>
    
    <p>También puedes responder al cliente esta opinión:</p>
    <p>
        <?php $urlDef = array('controller' => 'testimonials', 'action' => 'reply/' . $testimonial['id'].'/'.$testimonial['driver_reply_token'], 'base'=>false) ?>
        <a href='<?php echo $this->Html->url($urlDef, true) ?>'>Click aquí para responderle al cliente</a>
    </p>
    
    <p>Esta opinión será PUBLICADA junto al testimonio en tu perfil.</p>
    
</div>

<p>Saludos,</p>
<p>El equipo de <a href="http://yotellevocuba.com">YoTeLlevo</a></p>