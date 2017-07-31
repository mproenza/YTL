<?php $driverName = __('el chofer')?>
<?php $hasProfile = isset ($data['Driver']['DriverProfile']) && $data['Driver']['DriverProfile'] != null && !empty ($data['Driver']['DriverProfile'])?>
<?php if($hasProfile) :?>
    <?php
        $src = '';
        if(Configure::read('debug') > 0) $src .= '/yotellevo'; // HACK: para poder trabajar en mi PC y que pinche en el server tambien
        $src .= '/'.str_replace('\\', '/', $data['Driver']['DriverProfile']['avatar_filepath']);
        
        $driverName = $data['Driver']['DriverProfile']['driver_name'];
    ?>
<?php endif;?>
<?php $topPosition = 60?>
<div class="col-md-8 col-md-offset-2 well" id="fixed" style="position: fixed;top: <?php echo $topPosition?>px;z-index: 100;background-color: white;padding:10px">
    <div style="width: 100%">
        <?php if($hasProfile):?><div style="float: left"><img src="<?php echo $src?>" title="<?php echo $data['Driver']['DriverProfile']['driver_name']?>" class="info" style="max-height: 30px; max-width: 30px"/></div><?php endif;?>
        <div style="float: left;padding-left: 10px" class="h5"><?php echo __('Tus mensajes con %s', '<code><big>'.$driverName.'</big></code>')?></div>
        <div style="float: left;padding-left: 20px;padding-top: 9px"><span class="text-muted"><?php echo __('Solicitud')?> <small>#</small></span><?php echo $data['Travel']['id']?></div>
    </div>
</div>
<div style="height: 85px;"></div> <!-- Separator -->
    

<!-- VIAJES Y CONTROLES -->
<div class="row" style="top: 200px">
    <div class="col-md-6 col-md-offset-3">
        <?php echo $this->element('travel', array('travel'=>$data, 'showConversations'=>false, 'actions'=>false))?>
    </div>
</div>

<br/>
<br/>

<!-- MENSAJES -->
<?php if(empty ($conversations)):?><div class="row"><div class="col-md-6 col-md-offset-3">No hay conversaciones hasta el momento</div></div>
<?php else:?>
    <?php foreach ($conversations as $message):?>
    <div class="row container-fluid">
        <div class="col-md-9 col-md-offset-1">
            <?php echo $this->element('widget_conversation_message', array('message'=>$message['DriverTravelerConversation'], 'driver_name'=>$driverName))?>
        </div>
        <br/>
    </div>
    <?php endforeach;?>
<?php endif?>

<script type="text/javascript">
    $(window).scroll(function(){
        $("#fixed").css("top", Math.max(0, <?php echo $topPosition?> - $(this).scrollTop()));
    });
</script>