<?php App::uses('TimeUtil', 'Util')?>

<?php $driver_name = 'chofer'?>
<?php if($data['Driver']['driver_name'] != null) $driver_name = $data['Driver']['driver_name']?>
<p>Hola <?php echo $driver_name?>,</p>

<p>
    Este es un correo automático que contiene información de los viajes que usted tiene confirmada su realización con nosotros, pero que aún no ha pagado la comisión.
</p>
<p>Los viajes que tenemos pendientes de pago (o realizándose) son los siguientes:</p>
<ul>
    <?php foreach ($data['Travel'] as $travel):?>
    <li>
        #<?php echo $travel['travel_id']?> (<?php echo $travel['travel_origin']?> - <?php echo $travel['travel_destination']?>) con fecha de inicio <?php echo TimeUtil::prettyDate($travel['travel_date'])?>
    </li>
    <?php endforeach?>
</ul>

<p>
    Si usted ya ha pagado alguno de estos viajes, debe enviarnos la fecha del pago o transferencia y el monto total de la misma. Además debe enviarnos un desglose por viajes y monto de la comisión de cada uno.
</p>

<p>
    Puede ser que usted haya realizado otros viajes también pero que aún no hemos verificado o que usted no nos ha confirmado aún. Si tiene viajes sin verificar por favor confírmenos su realización o no.
</p>

<p> 
    Si encuentra algún problema en esta información, por favor póngase en contacto con nosotros a través de este mismo correo. Puede ser que aún no hayamos actualizado toda la información en el sitio o que haya alguna confusión. Siéntase libre de comunicarnos cualquier inconveniente.
</p>

<p>
    Muchísimas gracias y saludos,
</p>
<p>El equipo de <a href="http://yotellevocuba.com">YoTeLlevo</a></p>
<p>
    <small>
    Este correo le fue enviado automáticamente porque tiene viajes realizados y sin pagar. Este recordatorio se le enviará cada 5 días mientras tenga viajes sin pagar.
    </small>
</p>