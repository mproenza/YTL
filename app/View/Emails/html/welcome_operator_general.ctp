<?php $assistant = Configure::read('customer_assistant');?>

<p>
    <?php echo __d('user_email', 'Hola, le doy la bienvenida a YoTeLlevo... y a Cuba también! Gracias por usar nuestro servicio.')?>
</p>

<p>
    <?php echo __d('user_email', 'Mi nombre es %s y voy a ser su asistente mientras planea su viaje con nuestros choferes.', $assistant['name'])?>
</p>

<p>
    <?php echo __d('user_email', 'Como asistente voy a estar a cargo de resolver cualquier problema que pueda surgir durante su interacción con los choferes, problemas con el sitio y cualquier duda que pueda surgirle. Aunque todos los términos del servicio los debe arreglar con su chofer (precios, horarios, recorridos, etc), yo estoy al tanto de lo que va ocurriendo y puedo ayudar en algunas cosas. Siéntase libre de responder este correo para hacerme alguna pregunta, o simplemente para decir <em>hola</em>.', $assistant['name'])?>
</p>

<p>
    <?php echo __d('user_email', 'Quería además aprovechar y asegurarme de que nuestro servicio le sea de ayuda, y que usted sepa qué esperar y le saque el mayor provecho posible. Para eso le dejo aquí algunos datos y tips que pueden ser muy útiles mientras usa YoTeLlevo y habla con los choferes')?>:
</p>

<ul>
    <li>
        <?php echo __d('user_email', 'Primeramente, nuestros choferes son personas propietarias de un auto que se dedican a transportar viajeros como usted aquí en Cuba. Lo interesante es que usted puede estar hablando con personas tan diversas como un economista, un pescador o un piloto retirado.')?>
    </li>
    <li>
        <?php echo __d('user_email', 'Las ofertas de precios que usted recibirá pueden ser diferentes (a veces muy diferentes). Nuestros choferes trabajan de manera independiente y pueden tener distintas tarifas según sus costos personales u otros factores. Lo bueno es que usted puede escoger.')?>
    </li>
    <li>
        <?php echo __d('user_email', 'Asegúrese de dejar claro cada detalle antes de la fecha de recogida. Acuerde la hora, lugar y cualquier otra información necesaria para el primer encuentro. Guarde el número telefónico del chofer para contactos de último minuto.')?>
    </li>        
    <li>
        <?php echo __d('user_email', 'No espere que los choferes se puedan comunicar por mensajes de texto (sms) o llamadas telefónicas si usted no está dentro de Cuba. Estas opciones son muy caras para ellos. De todas formas, pregúnteles por su número móvil si quiere escribirles más cómodamente; ellos seguramente responderán por correo.')?>
    </li>
    <li>
        <?php echo __d('user_email', 'No dude en dejarles saber amablemente a los choferes si usted encuentra el precio un poco caro. Incluso cuando los precios no son negociables todo el tiempo, puede ser que el chofer pueda hacer una mejor oferta si usted se lo pide. Por favor, haga esto sólo si no puede costear el precio inicial.')?>
    </li>
    <li>
        <?php echo __d('user_email', 'Por último, usted va a mantener una conversación con una persona en Cuba. Aproveche y congenie con esa persona que va a ser su chofer :)')?>
    </li>
</ul>

<p>
    <?php echo __d('user_email', 'Bueno, si necesita algo más siéntase libre de responder este correo y hacerme una pregunta. Estaré muy feliz de podérsela contestar y ponerme en contacto con usted. O puede preguntarle a los choferes, ellos probablemente tienen más experiencia que yo.')?>
</p>

<p>
    <?php echo __d('user_email', 'Espero que tenga un maravilloso viaje a Cuba')?>!
</p>

<p>
    <?php echo __d('user_email', 'Saludos')?>,
</p>

<p>
    <?php echo __d('user_email', '%s y el equipo de <a href="http://yotellevocuba.com">YoTeLlevo</a>.', $assistant['name'])?>
</p>