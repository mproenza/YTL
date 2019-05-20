
<?php 
$comments = '';
//Misma idea que para comentarios de viaje, usando el campo para enviar mensaje directo al usuario
$colorClass = 'text-muted';
$title = 'Mensaje al chofer';
if(!empty($comments)) {
    $colorClass = 'text-info';
    $title = '';
}
else $comments = $title;
    
?>

<span id="messaging-cancel-<?php echo $thread['id']?>" style="display:none;float:right;">
    <a href="#!" class="cancel-edit-messaging-<?php echo $thread['id']?>">&ndash; cancelar</a>
</span>
<div id='messaging-form-<?php echo $thread['id']?>' style="display:none">
    <br/>    
    <?php echo $this->element('driver_messaging_form', array('data' => $conversation)); ?>
    <br>
    
</div>
<div id="successMSG"></div>


<script type="text/javascript">
    $("#messaging-icon").html("<span id='messaging-set-<?php echo $thread['id']?>' style='display: inline-block;' class='btn btn-default'><a href='#!' title='<?php echo $title?>'  class='edit-messaging-<?php echo $thread['id']?>' style='text-decoration: none' data-where='messaging-form-<?php echo $thread['id']?>'><span class='info' title='<?php echo $comments?>' data-placement='bottom'><i class='glyphicon glyphicon-send <?php echo $colorClass?>'></i></span></a></span>");
    
    $('.cancel-edit-messaging-<?php echo $thread['id']?>').click(function() {        
        $('#messaging-form-<?php echo $thread['id']?>, #messaging-cancel-<?php echo $thread['id']?>').toggle();
        $(".theme-config-box").toggleClass("show");
        
        if ($("#box-menu").hasClass('glyphicon glyphicon-chevron-left'))
            $("#box-menu").attr('class', 'glyphicon glyphicon-chevron-right');
        else
            $("#box-menu").attr('class', 'glyphicon glyphicon-chevron-left');
    });
    
    
    $('.edit-messaging-<?php echo $thread['id']?>').click(function() {
        /*goTo messaging form*/        
        goTo('messaging-controls',150,-70);        
        $('#messaging-form-<?php echo $thread['id']?>, #messaging-cancel-<?php echo $thread['id']?>').toggle();
        $(".theme-config-box").toggleClass("show");
         if ($("#box-menu").hasClass('glyphicon glyphicon-chevron-left'))
            $("#box-menu").attr('class', 'glyphicon glyphicon-chevron-right');
        else
            $("#box-menu").attr('class', 'glyphicon glyphicon-chevron-left');
    });
</script>