<?php     
    $other = array('en' => 'es', 'es' => 'en');
    $lang = $this->Session->read('Config.language');

    $lang_changed_url             = $this->request['pass'];
    $lang_changed_url             = array_merge($lang_changed_url, $this->request['named']);
    $lang_changed_url['?']        = $this->request->query;
    $lang_changed_url['language'] = $other[$lang];
?>

<div id="front-page-bg">
    <div id="navbar">
        <nav id="nav" class="navbar">
            <div class="navbar-header">
                <button type="button" class="navbar-toggle collapsed" data-toggle="collapse" data-target=".navbar-ex1-collapse">
                    <span class="sr-only">Toggle navigation</span>
                    <span class="icon-bar"></span>
                    <span class="icon-bar"></span>
                    <span class="icon-bar"></span>
                </button>
                    <span class="white navbar-brand"><big>Yo</big>Te<big>Llevo</big></span>
                <div class="pull-left navbar-brand">
                    <?php $lang = SessionComponent::read('app.lang');?>
                    <?php if($lang != null && $lang == 'en'):?>
                        <?php echo $this->Html->link($this->Html->image('Spain.png').' Español', $lang_changed_url, array('class' => 'nav-link', 'title'=>'Traducir al Español', 'escape'=>false)) ?>
                    <?php else:?>
                        <?php echo $this->Html->link($this->Html->image('UK.png').' English', $lang_changed_url, array('class' => 'nav-link', 'title'=>'Translate to English', 'escape'=>false)) ?>
                    <?php endif;?>
                </div>
            </div>
            <div class="navbar-collapse navbar-ex1-collapse collapse" style="height: 1px;">
                <ul class="nav navbar-nav navbar-right">
                    <li><?php echo $this->Html->link(__d('homepage', 'Entrar'), array('controller' => 'users', 'action' => 'login'), array('class' => 'nav-link', 'rel'=>'nofollow')) ?></li>
                </ul>
            </div>
        </nav>
    </div>
    
    <div class="col-md-8 col-md-offset-2 value-proposition" style="margin-top: 20px">
        <h1 id="sell"><big><?php echo __d('homepage', '¿Necesitas un chofer con auto en %s?', '<code><b><big>Cuba</big></b></code>') ?></big></h1>
        <br/>
        <h2>
            <b><?php echo __d('homepage', 'Recibe ofertas directamente de %s choferes que viven en Cuba y consigue un taxi para tus excursiones y traslados', __('varios')) ?></b>
        </h2>
        <br/>
        <h3>
            <?php echo __d('homepage', 'Conoce los precios de antemano')?> • <?php echo __d('homepage', 'Mira fotos y opiniones sobre los choferes')?> • <?php echo __d('homepage', 'Acuerda todo antes de llegar a la isla')?>
        </h3>
        <div class="sell-button" style="padding-top:30px">
            <a href="#!" class="btn btn-success show-travel-form">
                <?php echo __d('homepage', 'Sí, solicitar ofertas a choferes en Cuba') ?>
                <div class="sub">
                    <?php echo __d('homepage', 'Contrata al que creas mejor') ?>
                </div>
            </a>
        </div>
    </div>
</div>

<div class="row sell" style="margin-top:80px">
    <div class="col-md-4 center">
        <?php
        $filesBaseUrl = '/files';
        if(Configure::read('debug') > 0) {
            $filesBaseUrl = '/yotellevo'.$filesBaseUrl;
        }
        ?>
        <span class="glyphicon"><?php echo $this->Html->image('avatar-yoslandy.jpg', array('class'=>'img-responsive'))?></span>
        <span class="glyphicon"><?php echo $this->Html->image('avatar-escurdia.jpg', array('class'=>'img-responsive'))?></span>
        <span class="glyphicon"><?php echo $this->Html->image('avatar-paco.jpg', array('class'=>'img-responsive'))?></span>
        <p class="lead">
            <?php echo __d('homepage', 'Te ponemos en contacto con hasta 3 de nuestros choferes para que acuerdes tu viaje directamente con ellos via correo electrónico antes de llegar a la isla') ?>.
        </p>
    </div>
    <div class="col-md-4 center sell-snippet">
        <span class="glyphicon glyphicon-comment"></span>
        <span class="glyphicon glyphicon-usd"></span>
        <p class="lead">
            <?php echo __d('homepage', 'Los choferes te darán sus precios y tú puedes preguntar cualquier cosa relativa al viaje. Conoce un poco a los choferes mientras intercambian correos') ?>.
        </p>
    </div>
    <div class="col-md-4 center sell-snippet">
        <span class="glyphicon glyphicon-briefcase"></span>
        <span class="glyphicon glyphicon-camera"></span>
        <span class="glyphicon glyphicon-music"></span>
        <p class="lead">
            <?php echo __d('homepage', 'Contrata al chofer que creas mejor de acuerdo a tu presupuesto o necesidades especiales. O simplemente haz un amigo que te lleve en su auto a donde quieras ir') ?>.
        </p>
    </div>
</div>

<!-- START THE FEATURETTES -->
<div class="featurette-container arrow_box arrow_box_bottom">

    <hr class="featurette-divider">

    <div class="row featurette">
        <div class="col-md-5">
            <?php echo $this->Html->image('driver.jpg', array('class' => 'featurette-image img-responsive img-rounded', 'alt'=>__d('homepage', 'Chofer de taxi sonriendo'))) ?>
        </div>
        <div class="col-md-7">
            <h2 class="featurette-heading"><?php echo __d('homepage', 'Conoce a tu chofer') ?></h2>
            <br/>
            <br/>
            <p class="lead"><?php echo __d('homepage', 'El chofer con quien viajas es importante y hace la diferencia. Nuestros choferes son pilotos, pescadores, profesionales, todos propietarios de un auto que nos ayudan a moverte por la isla. Conecta con tu chofer de la manera que prefieras.') ?></p>
            <p><?php echo $this->Html->link('<big><big>'.__d('homepage', 'Mira testimonios sobre nuestros choferes').' »</big></big>', array('controller'=>'testimonials', 'action'=>'featured', '?'=>array('also'=>Configure::read('Config.language') == 'es'?'en':'es')), array('class'=>'btn btn-warning', 'escape'=>false))?></p>
        </div>
    </div>

    <hr class="featurette-divider">
    
    <div class="row featurette">
        <div class="col-md-push-7 col-md-5">
            <?php echo $this->Html->image('budget-plan.jpg', array('class' => 'featurette-image img-responsive img-rounded', 'alt'=>__d('homepage', 'Pareja planeando presupuesto de viaje'))) ?>
        </div>
        <div class="col-md-pull-5 col-md-7">
            <h2 class="featurette-heading"><?php echo __d('homepage', 'Planifica tu presupuesto de antemano') ?></h2>
            <br/>
            <br/>
            <p class="lead"><?php echo __d('homepage', 'Planifica los costos de transportación con tu chofer antes de llegar a la isla. Asegúrate de cuánto puedes disponer para otras actividades y ahórrate las sorpresas de tener que negociar tarifas con choferes en la calle en el momento de llegar.') ?></p>
        </div>
    </div>

    <hr class="featurette-divider">
    
    <div class="row featurette">
        <div class="col-md-5">
            <?php echo $this->Html->image('taxi-pick.jpg', array('class' => 'featurette-image img-responsive img-rounded', 'alt'=>__d('homepage', 'Familia subiendo al auto'))) ?>
        </div>
        <div class="col-md-7">
            <h2 class="featurette-heading"><?php echo __d('homepage', 'No pierdas tiempo esperando transporte') ?></h2>
            <br/>
            <br/>
            <p class="lead"><?php echo __d('homepage', '¿Por qué esperar por un bus para llegar a dónde quieres? ¿Por qué ajustarte a su horario de salida? Tu tiempo en Cuba es limitado y es mejor tener un taxi a tu disposición y decidir cuándo moverte y hacia qué lugar. Es tan simple como recostarte en tu asiento y dejarte llevar.') ?></p>
        </div>
    </div>
    
    <hr class="featurette-divider">

    <div class="row featurette">
        <div class="col-md-push-7 col-md-5">
            <?php echo $this->Html->image('collage.jpg', array('class' => 'featurette-image img-responsive img-rounded', 'alt'=>__d('homepage', 'Varios destinos turísticos en Cuba'))) ?>
        </div>
        <div class="col-md-pull-5 col-md-7">
            <h2 class="featurette-heading"><?php echo __d('homepage', 'Desata tu creatividad') ?></h2>
            <br/>
            <br/>
            <p class="lead"><?php echo __d('homepage', 'Cuba es un país hermoso para visitar y recorrer, y ninguno de sus lugares es igual. Arma tu viaje de la manera que quieras. Decide dónde quieres ir sin ajustarte a itinerarios rígidos y aburridos. Haz cambios de última hora cuando desees, aprende y decide en el camino y crea los mejores recuerdos.') ?></p>
        </div>
    </div>
</div>
<!-- /END THE FEATURETTES -->

<div id="travel-create" class="form-cover bg-warning">
    <div class="row">
        <div id="TravelRequest" style="margin-top: 25px;padding: 15px;padding-top: 25px;">
            <div style="text-align:center;margin-bottom: 30px">
                <div class="handwritten-2"><big><big><b><?php echo __d('homepage', 'Haz un viaje sorprendente con tu chofer en Cuba') ?></b></big></big></div>
                <div><big><?php echo __d('homepage', '<b>Consigue un taxi para tus traslados</b> creando una solicitud de viaje') ?></big></div>
            </div>
            <?php echo $this->Session->flash(); ?>            
            <?php echo $this->element('pending_travel_form', array('bigButton' => true, 'horizontal' => true)); ?>
        </div>
    </div>
</div>

<div class="arrow_box arrow_box_top">
    <div class="row sell">
        <div class="col-md-12 center">
            <p class="lead">
                <?php echo __d('homepage', '¿Necesitas ideas? Estos son destinos populares con origen en') ?> <span class="handwritten" style="font-size: 24pt;display:inline-block">La Habana</span>
            </p>
        </div>
        <div class="col-sm-6 col-md-3 center">
            <a href="#!" style="text-decoration: none">
                <div class="destination-thumb varadero">
                    <p class="handwritten white dest" style="font-size: 24pt">Varadero</p>
                    <h1 class="action white"><?php echo __d('homepage', 'Conseguir taxi') ?> <b>La Habana-Varadero</b></h1>
                </div>
            </a>
        </div>
        <div class="col-sm-6  col-md-3 center">
            <a href="#!" style="text-decoration: none">
                <div class="destination-thumb trinidad">
                    <p class="handwritten white dest" style="font-size: 24pt">Trinidad</p>
                    <h1 class="action white"><?php echo __d('homepage', 'Conseguir taxi') ?> <b>La Habana-Trinidad</b></h1>
                </div>
            </a>
        </div>
        <div class="col-sm-6  col-md-3 center">
            <a href="#!" style="text-decoration: none">
                <div class="destination-thumb vinales">
                    <p class="handwritten white dest" style="font-size: 24pt">Viñales</p>
                    <h1 class="action white"><?php echo __d('homepage', 'Conseguir taxi') ?> <b>La Habana-Viñales</b></h1>
                </div>
            </a>
        </div>
        <div class="col-sm-6  col-md-3 center">
            <a href="#!" style="text-decoration: none">
                <div class="destination-thumb all-around">
                    <p class="handwritten white dest" style="font-size: 24pt"><?php echo __d('homepage', 'Recorrido por la isla') ?></p>
                    <h1 class="action white"><?php echo __d('homepage', 'Conseguir taxi') ?> <b>La Habana-<?php echo __d('homepage', 'Recorrido por la isla') ?></b></h1>
                </div>
            </a>
        </div>
    </div>
</div>

<div id="social-media-sharer" style="margin-top: 140px">
    <div class="center" style="padding-bottom: 20px">
        <span class="lead"><?php echo __d('homepage', 'Comparte esta página con tus familiares y amigos')?>!</span>
    </div>
    <div class="center">
        <span class="social-button"><a rel="nofollow" class="twitter" target="_blank" href="https://twitter.com/home?status=<?php echo __('Aseg%C3%BArate%20de%20tener%20el%20mejor%20%23chofer%20con%20%23auto%20en%20%23Cuba%20en%20http://yotellevocuba.com%0A%0A%23Taxi%20para%20%23excursiones%20y%20%23transfers%20via%20%40yotellevocuba')?>">Twitter</a></span>
        <span class="social-button"><a rel="nofollow" class="google" target="_blank" href="https://plus.google.com/share?url=http://yotellevocuba.com">Google+</a></span>
        <span class="social-button"><a rel="nofollow" class="facebook" target="_blank" href="https://www.facebook.com/sharer/sharer.php?u=http://yotellevocuba.com">Facebook</a></span>
    </div>
</div>

<div id="review-driver" style="margin-top: 100px">
    <div class="center" style="padding-bottom: 20px">
        <span class="lead"><big><big><?php echo __d('homepage', '¿Viajaste con alguno de nuestros choferes?')?></big></big></span>
    </div>
    <div class="col-md-6 col-md-offset-3">
        <?php echo $this->Form->button('<big><big><big>'.__d('homepage', 'Deja una opinión aquí sobre él o ella').' »</big></big></big>', array('controller'=>'testimonials', 'action'=>'enter_code', 'class'=>'btn-warning btn-block'), true); ?></big>
        <p class="text-warning center" style="margin-top: 20px;">
            <?php echo __d('homepage', 'Si GENUINAMENTE disfrutaste tu viaje, por favor deja una opinión.')?>
            <?php echo __d('homepage', 'Puede parecer insignificante pero es de gran ayuda para el chofer.')?>
        </p>
    </div>
</div>

<script type="text/javascript">
    
    function goTo(id) {
        $('html, body').animate({
            scrollTop: $('#' + id).offset().top
        }, 300);
    };
    
    $(document).ready(function() {
        
        
        $('.show-travel-form').click(function() {
            goTo('TravelRequest');            
            $('#PendingTravelOrigin').focus();
        });
        
        $('.destination-thumb').click(function() {
            goTo('TravelRequest');
            $('#PendingTravelOrigin').val('La Habana');
            $('#PendingTravelDestination').val($(this).find('.dest').text());
            $('#PendingTravelDate').focus();
        });
    });
</script>