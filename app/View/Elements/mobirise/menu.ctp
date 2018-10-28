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
    
    
    $mainCTA = __d('mobirise/default', 'Contactar choferes en Cuba');
    if(isset($cta)) $mainCTA = $cta;
?>


<section class="menu cid-qTkzRZLJNu" once="menu" id="menu1-0">

    <nav class="navbar navbar-expand beta-menu navbar-dropdown align-items-center navbar-fixed-top navbar-toggleable-sm bg-color transparent">
        <button class="navbar-toggler navbar-toggler-right" type="button" data-toggle="collapse" data-target="#navbarSupportedContent" aria-controls="navbarSupportedContent" aria-expanded="false" aria-label="Toggle navigation">
            <div class="hamburger">
                <span></span>
                <span></span>
                <span></span>
                <span></span>
            </div>
        </button>
        <div class="menu-logo">
            <div class="navbar-brand">
                <span class="navbar-logo">
                    <?php echo $this->Html->link($this->Html->image('logo37.png', array('alt'=>'Yo Te Llevo Cuba logo', 'style'=>'height:3.8rem')), '/'.SessionComponent::read('Config.language'), array('escape'=>false));?>
                </span>
                <span class="navbar-caption-wrap">
                    <?php echo $this->Html->link('YO TE LLEVO - CUBA', '/'.SessionComponent::read('Config.language'), array('class'=>'navbar-caption text-white display-4'));?>
                </span>
            </div>
        </div>
        <div class="collapse navbar-collapse" id="navbarSupportedContent">
            <ul class="navbar-nav nav-dropdown" data-app-modern-menu="true">
                <li class="nav-item pull-left">
                    <?php $lang = SessionComponent::read('app.lang');?>
                    <?php if($lang != null && $lang == 'en'):?>
                        <?php echo $this->Html->link($this->Html->image('Spain.png', array('style'=>'max-width:22px')).'&nbsp;'.'Español', $lang_changed_url, array('class' => 'nav-link link text-white display-4', 'title'=>'Traducir al Español', 'escape'=>false)) ?>
                    <?php else:?>
                        <?php echo $this->Html->link($this->Html->image('UK.png').'&nbsp;'.'English', $lang_changed_url, array('class' => 'nav-link link text-white display-4', 'title'=>'Translate to English', 'escape'=>false)) ?>
                    <?php endif;?>
                </li>
                
                <li class="nav-item">
                    <?php echo $this->Html->link('<span class="mbri-search mbr-iconfont mbr-iconfont-btn"></span> '.__d('mobirise/default', 'Sobre Nosotros'), array('controller'=>'testimonials', 'action'=>'featured', '?'=>array('also'=>Configure::read('Config.language') == 'es'?'en':'es')), array('class'=>'nav-link link text-white display-4', 'escape'=>false)); ?>
                </li>
            </ul>
            <div class="navbar-buttons mbr-section-btn">
                <a class="btn btn-sm btn-primary display-4" href="#<?php echo __d('mobirise/default', 'solicitar')?>">
                    <span class="mbri-cust-feedback mbr-iconfont mbr-iconfont-btn"></span>
                    <?php echo __d('mobirise/default', $mainCTA)?>
                </a>
            </div>
            
            <?php if(!$userLoggedIn):?>
            <ul class="navbar-nav nav-dropdown" data-app-modern-menu="true">
                <li class="nav-item">
                    <?php echo $this->Html->link('<span class="mbri-user mbr-iconfont mbr-iconfont-btn"></span> '.__('Entrar'), array('controller' => 'users', 'action' => 'login'), array('class' => 'nav-link link text-white display-4', 'rel'=>'nofollow', 'escape'=>false)) ?>
                </li>
            </ul>
            <?php endif?>
            
        </div>
    </nav>
</section>