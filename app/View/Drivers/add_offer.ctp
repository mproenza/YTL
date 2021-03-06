<?php
$driverName = 'el chofer'.' <small class="text-muted">('.$driver['Driver']['username'].')</small>'?>
<?php $hasProfile = isset ($driver['DriverProfile']) && $driver['DriverProfile'] != null && !empty ($driver['DriverProfile'])?>
<?php if($hasProfile) :?>
    <?php
        $src = '';
        if(Configure::read('debug') > 0) $src .= '/yotellevo'; // HACK: para poder trabajar en mi PC y que pinche en el server tambien
        $src .= '/'.str_replace('\\', '/', $driver['DriverProfile']['avatar_filepath']);
        
        $driverName = $driver['DriverProfile']['driver_name'];
    ?>
<?php endif;?>

<header id="header">
    <div id="navbar" class="navbar navbar-default navbar-fixed-top" role="navigation">
        <nav id="nav">
            <div class="nav-link center" style="padding: 20px">
                <?php if($hasProfile):?><img src="<?php echo $src?>" style="max-height:30px;max-width:30px"/><?php endif;?>
                <?php echo 'Hola '.$driverName.'!' ?>
                <?php echo $this->html->link('Vea su perfil', array('controller'=>'drivers', 'action'=>'profile', $driver['DriverProfile']['driver_nick'])); ?>
            </div>    

        </nav>
    </div>
</header>

<div class="row" style="top: 200px"> 
    <div>
        <?php echo $this->element('form_create_discount_offer', array('driver'=>$driver));?>       
    </div>
</div>

<br/>
<br/>
<script type="text/javascript">
 $(document).ready(function() {
    
          if($('.clockpicker').length >0){
                function DisplayCurrentTime() { 
                   var date = new Date(); 
                   var hours = date.getHours() > 12 ? date.getHours() - 12 : date.getHours();
                   var am_pm = date.getHours() >= 12 ? "PM" : "AM"; hours = hours < 10 ? "0" + hours : hours; 
                   var minutes = date.getMinutes() < 10 ? "0" + date.getMinutes() : date.getMinutes();
                   var seconds = date.getSeconds() < 10 ? "0" + date.getSeconds() : date.getSeconds(); 
                   var time = hours + ":" + minutes + ":" + am_pm; 
                   //time = hours + ":" + minutes + am_pm;
                    return time; 
                }
       $('.clockpicker').clockpicker({
           'default': DisplayCurrentTime()
        , }).find('input').val(DisplayCurrentTime()) ;
       }
    }
    );
    

</script>