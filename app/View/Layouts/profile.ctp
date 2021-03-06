<?php App::uses('User', 'Model')?>
<?php App::uses('Driver', 'Model')?>

<?php
$userLoggedIn = AuthComponent::user('id') ? true : false;

if($userLoggedIn) {
    $user = AuthComponent::user();
    $userRole = $user['role'];
    $pretty_user_name = User::prettyName($user, true);
}
?>

<?php     
    $other = array('en' => 'es', 'es' => 'en');
    $lang = $this->Session->read('Config.language');
 
    $lang_changed_url             = $this->request['pass'];
    $lang_changed_url             = array_merge($lang_changed_url, $this->request['named']);
    $lang_changed_url['?']        = $this->request->query;
    $lang_changed_url['language'] = $other[$lang];
?>

<!DOCTYPE html>
<html xmlns="http://www.w3.org/1999/xhtml" xmlns:fb="http://ogp.me/ns/fb#">
    <head>        
        <?php echo $this->Html->charset(); ?>
        <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
        <?php        
        $title = __d('driver_profile', '%s, chofer en %s, Cuba', $profile['DriverProfile']['driver_name'], $profile['Province']['name']).' - '.__d('driver_profile', 'Auto hasta %s pax', $profile['Driver']['max_people_count']);
        if($profile['Driver']['has_air_conditioner']) $title .= ' '.__d('driver_profile', 'con aire acondicionado');
        
        $description = __d('driver_profile', 'Contacta a %s para acordar tus recorridos en Cuba. Recibe una oferta de precio directamente de él y decide si te gustaría contratarlo.', Driver::shortenName($profile['DriverProfile']['driver_name']));
        ?>
        <title><?php echo $title.' | YoTeLlevo' ?></title>
        <meta name="description" content="<?php echo $description?>"/>
        
        <!-- FACEBOOK SHARE -->        
        <meta property="og:title" content="<?php echo substr($title, 0, 90)?>">
        <?php if($profile['DriverProfile']['featured_img_url'] != null):?>
        <meta property="og:image" content="<?php echo $profile['DriverProfile']['featured_img_url']?>">
        <?php endif?>
        <meta property="og:description" content="<?php echo $description?>">
        
        <style type="text/css">
            
            #navbar #nav a.nav-link{
                /*color:white;*/
                font-family:'Montserrat', sans-serif;
                font-size:13px;
                /*margin-top:4px;*/
                text-transform:uppercase
            }
            #navbar #nav a.nav-link:hover,#navbar #nav a.nav-link:focus{
                background-color:transparent;
                text-decoration:none
            }
            #navbar #nav .navbar-btn{
                margin-left:15px;
            }
            
            #profile-description img {
                margin-top: 20px;
                margin-bottom: 20px;
            }
            
        </style>
        
        <?php
        // META
        $this->Html->meta('icon');
        
        $this->Html->css('default-bundle', array('inline' => false));        
        $this->Html->script('default-bundle', array('inline' => false));

        echo $this->fetch('meta');
        echo $this->fetch('css');
        echo $this->fetch('script');
        ?>
        
        <script type="text/javascript">
            $(document).ready(function() {
                $('.info').tooltip({placement:'bottom', html:true});
            })
        </script>
    </head>
    <body>

        <?php echo $this->Session->flash('auth'); ?>

        <div class="container-fluid">
            <div id="navbar" class="navbar navbar-default" role="navigation">
                <nav id="nav">
                <!-- Brand and toggle get grouped for better mobile display -->
                <!--<div class="container-fluid">-->
                    <div class="navbar-header">
                        <button type="button" class="navbar-toggle" data-toggle="collapse" data-target="#app-navbar-collapse">
                            <span class="sr-only">Toggle navigation</span>
                            <span class="icon-bar"></span>
                            <span class="icon-bar"></span>
                            <span class="icon-bar"></span>
                        </button>
                        <a class="navbar-brand" href="#!">
                            <big>Yo</big>Te<big>Llevo</big>
                        </a>
                        <div class="pull-left navbar-brand">
                            <?php $lang = SessionComponent::read('app.lang');?>
                            <?php if($lang != null && $lang == 'en'):?>
                                <?php echo $this->Html->link($this->Html->image('Spain.png'), $lang_changed_url, array('class' => 'nav-link', 'title'=>'Traducir al Español', 'escape'=>false, 'style'=>'text-decoration:none')) ?>
                            <?php else:?>
                                <?php echo $this->Html->link($this->Html->image('UK.png'), $lang_changed_url, array('class' => 'nav-link', 'title'=>'Translate to English', 'escape'=>false, 'style'=>'text-decoration:none')) ?>
                            <?php endif;?>
                        </div>
                    </div>
                    <!-- Collect the nav links, forms, and other content for toggling -->
                    <div class="collapse navbar-collapse" id="app-navbar-collapse">
                        <ul class="nav navbar-nav">
                            <?php if ($userLoggedIn) :?>

                                <?php if($userRole === 'regular' || $userRole === 'admin' || $userRole === 'tester') :?>
                                    <li><?php echo $this->Html->link(__('Solicitar viaje'), array('controller' => 'travels', 'action' => 'add'), array('class' => 'nav-link', 'escape'=>false));?></li> 
                                    <li class="divider-vertical"></li>
                                    <li><?php echo $this->Html->link(__('Mis Anuncios'), array('controller' => 'travels', 'action' => 'index'), array('class' => 'nav-link', 'escape'=>false));?></li>
                                    <li class="divider-vertical"></li>
                                    <li title="<?php echo __('Mira los mensajes que tienes con cada uno de los choferes y mantente al tanto de tus acuerdos de viaje')?>" class="info">
                                        <?php echo $this->Html->link('<button type="button" class="btn btn-success navbar-btn">'.__('Mis Mensajes').'</button>', array('controller' => 'conversations'), array('escape'=>false, 'style'=>'padding:0px;padding-right:10px'))?>
                                    </li>

                                    <?php if($userRole === 'admin') :?>
                                    <li class="divider-vertical"></li>
                                    <li class="dropdown">
                                        <a href="#" data-toggle="dropdown" class="dropdown-toggle nav-link">
                                            Administrar
                                            <b class="caret"></b>
                                        </a>
                                        <ul class="dropdown-menu">
                                            <li class="dropdown-submenu">
                                                <a tabindex="-1" href="#">Administrar</a>
                                                <ul class="dropdown-menu">
                                                    <li><?php echo $this->Html->link('Usuarios', array('controller' => 'users', 'action' => 'index')) ?></li>
                                                    <li><?php echo $this->Html->link('Choferes', array('controller' => 'drivers', 'action' => 'index')) ?></li>                                            
                                                    <li class="divider"></li>
                                                    <li><?php echo $this->Html->link('Provincias', array('controller' => 'provinces', 'action' => 'index')) ?></li>
                                                    <li><?php echo $this->Html->link('Localidades', array('controller' => 'localities', 'action' => 'index')) ?></li>
                                                    <li><?php echo $this->Html->link('Tesauro', array('controller' => 'locality_thesaurus', 'action' => 'index')) ?></li>
                                                </ul>
                                            </li>
                                            <li class="dropdown-submenu">
                                                <a tabindex="-1" href="#">Ver</a>
                                                <ul class="dropdown-menu">
                                                    <li><?php echo $this->Html->link('Viajes (Todos)', array('controller' => 'travels', 'action' => 'all')) ?></li>
                                                    <li><?php echo $this->Html->link('Pendientes (Todos)', array('controller' => 'travels', 'action' => 'all_pending')) ?></li>
                                                    <li class="divider"></li>
                                                    <li><?php echo $this->Html->link('Email Queue', array('controller' => 'email_queues', 'action' => 'index')) ?></li>
                                                </ul>
                                            </li>
                                            <li class="dropdown-submenu">
                                                <a tabindex="-1" href="#">Logs</a>
                                                <ul class="dropdown-menu">
                                                    <li><?php echo $this->Html->link('Raw Emails', array('controller' => 'admins', 'action' => 'view_log/emails_raw')) ?></li>
                                                    <li><?php echo $this->Html->link('Info Requerida', array('controller' => 'admins', 'action' => 'view_log/info_requested')) ?></li>
                                                    <li><?php echo $this->Html->link('Viajes por Correo', array('controller' => 'admins', 'action' => 'view_log/travels_by_email')) ?></li>                                                    
                                                    <li><?php echo $this->Html->link('Conversaciones', array('controller' => 'admins', 'action' => 'view_log/conversations')) ?></li>
                                                </ul>
                                            </li>
                                            <li class="divider"></li>
                                            <li class="dropdown-submenu">
                                                <a tabindex="-1" href="#">Tests</a>
                                                <ul class="dropdown-menu">
                                                    <li><?php echo $this->Html->link('Ver Viajes Admins', array('controller' => 'travels', 'action' => 'all_admins')) ?></li>
                                                </ul>
                                            </li>
                                        </ul>
                                    </li>
                                    <?php endif;?>
                                <?php endif;?>

                            <?php else: ?>
                                <li>
                                    <?php echo $this->Html->link(__('Ir al Inicio'), '/'.SessionComponent::read('Config.language'), array('class' => 'nav-link', 'escape'=>false));?>
                                </li>
                            <?php endif;?> 
                        </ul>

                        <ul class="nav navbar-nav navbar-right">
                            <?php $talkingToDriver = $this->Session->read('visited-driver-'.$profile['Driver']['id']);?>
                            <?php if (!$talkingToDriver): ?>
                                <li title="<?php echo __d('driver_profile', 'Envía un mensaje a este chofer para acordar un viaje con él')?>" class="info">
                                    <a href="#!" class="goto" data-go-to="message-driver" style="padding:0px;padding-right:5px">
                                        <button type="button" class="btn btn-info navbar-btn">
                                            <?php echo __d('driver_profile', 'Mensaje a este chofer')?>
                                        </button>
                                    </a>
                                </li>
                            <?php else:?>
                                <?php if($userLoggedIn && $talkingToDriver):?>
                                    <li>
                                        <?php echo $this->Html->link('<button type="button" class="btn btn-info navbar-btn">'.__d('driver_profile', 'Ver mis mensajes con %s', Driver::shortenName($profile['DriverProfile']['driver_name'])).'</button>', array('controller'=>'conversations', 'action'=>'messages', $talkingToDriver), array('escape'=>false, 'style'=>'padding:0px;padding-right:5px'))?>
                                    </li>
                                <?php endif ?>
                            <?php endif ?>
                            <li title="<?php echo __d('driver_profile', 'Escribe una opinión sobre tu viaje con este chofer')?>" class="info">
                                <?php echo $this->Html->link('<button type="button" class="btn btn-warning navbar-btn">'.__d('driver_profile', 'Opinar sobre este chofer').'</button>', array('controller' => 'testimonials', 'action'=>'enter_code'), array('escape'=>false, 'style'=>'padding:0px;padding-right:5px'))?>
                            </li>

                            <?php if ($userLoggedIn): ?>
                                <li class="dropdown">
                                    <a href="#" data-toggle="dropdown" class="dropdown-toggle nav-link">
                                        <?php echo $pretty_user_name;?>
                                        <b class="caret"></b>
                                    </a>
                                    <ul class="dropdown-menu">
                                        <li><?php echo $this->Html->link(__('Perfil'), array('controller' => 'users', 'action' => 'profile')) ?></li>
                                        <li class="divider"></li>
                                        <li><?php echo $this->Html->link(__('Salir'), array('controller' => 'users', 'action' => 'logout')) ?></li>
                                    </ul>
                                </li>
                            <?php else: ?>
                                <li>
                                    <?php echo $this->Html->link(__('Entrar'), array('controller' => 'users', 'action' => 'login'), array('class' => 'nav-link')) ?>
                                </li>
                            <?php endif ?>

                        </ul>
                    </div><!-- /.navbar-collapse -->
                <!--</div>-->
                </nav>
            </div>
            <?php echo $this->Session->flash(); ?>
            <?php echo $this->fetch('content'); ?>
        </div>
        <div id="footer">
            <div class="container-fluid">
                <?php echo $this->element('footer')?>
            </div>
        </div> 
        
        <?php if( ROOT != 'C:\wamp\www\yotellevo' && (!$userLoggedIn || $userRole === 'regular') ):?>
            <!-- Start 1FreeCounter.com code -->

            <script language="JavaScript">
            var data = '&r=' + escape(document.referrer)
                + '&n=' + escape(navigator.userAgent)
                + '&p=' + escape(navigator.userAgent)
                + '&g=' + escape(document.location.href);

            if (navigator.userAgent.substring(0,1)>'3')
            data = data + '&sd=' + screen.colorDepth 
                + '&sw=' + escape(screen.width+'x'+screen.height);

            document.write('<a href="http://www.1freecounter.com/stats.php?i=109722" target=\"_blank\" >');
            document.write('<img alt="Free Counter" border=0 hspace=0 '+'vspace=0 src="http://www.1freecounter.com/counter.php?i=109722' + data + '">');
            document.write('</a>');
            </script>

            <!-- End 1FreeCounter.com code -->

            <!-- Google Analytics -->
            <script>
            (function(i,s,o,g,r,a,m){i['GoogleAnalyticsObject']=r;i[r]=i[r]||function(){
            (i[r].q=i[r].q||[]).push(arguments)},i[r].l=1*new Date();a=s.createElement(o),
            m=s.getElementsByTagName(o)[0];a.async=1;a.src=g;m.parentNode.insertBefore(a,m)
            })(window,document,'script','//www.google-analytics.com/analytics.js','ga');

            ga('create', 'UA-60694533-1', 'auto');
            ga('send', 'pageview');
            </script>
        <?php endif;?>
    </body>
</html>