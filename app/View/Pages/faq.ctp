<?php $isLoggedIn = AuthComponent::user('id') ? true : false;?>

<div class="container">
    <div class="row">
        <div class="col-md-8 col-md-offset-2">
            <legend><big><?php echo __('Preguntas Frecuentes')?></big></legend>
            <p><b><?php echo __('¿Qué hace <em>YoTeLlevo</em>?')?></b></p>
            <p>
                <?php echo __('<em>YoTeLlevo</em> conecta viajeros interesados en moverse en taxi por Cuba con choferes independientes 
                propietarios de un taxi y dispuestos a atender sus peticiones.')?>
            </p>
            
            <p><b><?php echo __('¿Cómo puedo crear un Anuncio de Viaje?')?></b></p>
            <p>
                <?php echo __('Es muy fácil! Para crear un Anuncio de Viaje sólo tienes que')?> 
                <?php
                if(!$isLoggedIn) echo $this->Html->link(__('ir al formulario de anuncios'), array('controller'=>'pages', 'action'=>'home#TravelRequest'));
                else echo $this->Html->link(__('ir al formulario de anuncios'), array('controller'=>'travels', 'action'=>'add'));
                ?>
                <?php echo __('y completarlo')?>.
            </p>
            
            <p><b><?php echo __('¿Qué pasa cuando creo un Anuncio de Viaje?')?></b></p>
            <p>
                <?php echo __('Enseguida hasta 3 de nuestros choferes reciben una notificación con los detalles del viaje
                y la manera para contactarte que escribiste al llenar el formulario. Con esta información, los choferes 
                pueden contactarte para acordar los detalles')?>.
            </p>
            
            <p><b><?php echo __('¿Por qué 3 choferes? ¿No basta con uno?')?></b></p>
            <p>
                <?php echo __('Nuestros choferes son pilotos retirados, pescadores, profesionales, todos propietarios de un taxi que nos ayudan a moverte por la isla; 
                ellos están a disposición de los viajeros, pero a la vez son personas independientes que tienen sus propias agendas. 
                Para incrementar las posibilidades de que tu viaje reciba atención, le enviamos la notificación a 3 de ellos')?>.
            </p>
            
            <p><b><?php echo __('¿Qué pasa si ninguno me contacta?')?></b></p>
            <p>
                <?php echo __('Esto es muy difícil que suceda. Sin embargo, en caso de que suceda, puedes escribirnos a ')?>
                <a href="mailto:soporte@<?php echo Configure::read('domain_name')?>">soporte@<?php echo Configure::read('domain_name')?></a>
                <?php echo __('y plantearnos el problema; con mucho gusto lo resolveremos')?>.
            </p>
            
            <p><b><?php echo __('¿Qué pasa si me contacta más de un chofer?')?></b></p>
            <p>
                <?php echo __('Elijes el chofer que creas que se ajuste mejor a tus requerimientos, o con el que más hayas congeniado. Tienes la posibilidad de poder negociar
                todos los detalles directamente con ellos y escoger el de tu elección')?>.
            </p>
            
            <p><b><?php echo __('¿Estoy obligado a viajar con uno de los choferes que me contacte?')?></b></p>
            <p>
                <?php echo __('Claro que NO. Crear un Anuncio de Viaje en <em>YoTeLlevo</em> no te compromete a viajar con nuestros choferes. Simplemente rechaza gentilmente
                las ofertas de los choferes que te contacten y sigue sin más distracciones')?>.
            </p>
            
            <p><b><?php echo __('¿Qué pasa cuando llega el momento del viaje?')?></b></p>
            <p>
                <?php echo __('El chofer debe irte a buscar en su taxi; esto es parte del acuerdo que hagan tú y tu chofer. Acuerda con el chofer todos los detalles: 
                lugar de recogida, fecha y hora, recorridos, paradas, precios, estancias. El chofer puede darte su número de teléfono para gestiones
                de último minuto, en caso de contratiempos')?>.
            </p>
            
            <p><b><?php echo __('¿Puedo hacer cambios al acuerdo que ya hice con el chofer?')?></b></p>
            <p>
                <?php echo __('Definitivamente sí. Puedes hacer todos los cambios que quieras. Nuestros choferes se ajustan a todas tus necesidades e improvisaciones.
                De hecho, para conocer mejor Cuba es mejor aprender en el camino y ajustarte')?>.
            </p>
            
            <p><b><?php echo __('¿Cómo pago los servicios de <em>YoTeLlevo</em>?')?></b></p>
            <p>
                <?php echo __('El pago lo haces directamente al chofer de tu taxi, en la manera acordada entre ustedes (normalmente es en efectivo)')?>.
            </p>
            
            <br/>
            <br/>
            <p><b><?php echo __('¿Tienes otras preguntas que quieras hacernos?')?> <?php echo $this->Html->link(__('Contáctanos'), array('action'=>'contact'))?></b></p>
            
        </div>
    </div>
</div>