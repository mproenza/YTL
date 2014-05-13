<div class="container">
    <div class="row">
        <div class="col-md-8 col-md-offset-2">
            
            <h2>Bienvenido a <em>YoTeLlevo</em> <div><small class="text-muted">Ahora te será mucho más fácil viajar</small></div></h2>
            <h3>
                Ya puedes crear tu primer anuncio de viaje: <?php echo $this->Html->link('<i class="glyphicon glyphicon-flag"></i> Crear Viaje', array('controller'=>'travels', 'action'=>'add'), array('escape'=>false))?>.                
            </h3>
            
            <br/>
            <br/>
            <p>                
                Enviamos un correo a tu cuenta (<b><?php echo AuthComponent::user('username')?></b>) para ser verificada. <b>Revisa tu correo y sigue las instrucciones</b>.
                Ten en cuenta que sólo podrás crear <b>1 anuncio de viaje</b> hasta que verifiques tu cuenta.
            </p>
            
            <?php echo $this->element('email_sent_tips', array('link'=>$this->Html->link('<i class="glyphicon glyphicon-ok"></i> Enviar correo de verificación', array('controller'=>'users', 'action'=>'send_confirm_email'), array('escape'=>false))))?>
                        
        </div>
    </div>
    
</div>