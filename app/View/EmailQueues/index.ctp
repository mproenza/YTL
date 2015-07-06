<div style="float:left;padding-right:20px"><?php echo count($emails)?> emails</div>
<table class='table table-striped table-hover'>
    <thead><th>To</th><th>Subject</th><th>Template</th><th>Template Vars</th><th>Sent</th><th>Locked</th><th>Send Tries</th><th></th></thead>
    <tbody> 
    <?php foreach ($emails as $e): ?>
        <tr>
            <td><?php echo $e['EmailQueue']['to']?></td>
            <td>
            <?php 
            echo $e['EmailQueue']['subject'];
            
            if(isset ($e['EmailQueue']['template_vars']['conversation_id']) && substr($e['EmailQueue']['template'], 0, 8) === 'response') {
                echo '<hr/>';
                echo '<div>';
                echo $this->Html->link('Ver esta conversación', array('controller'=>'driver_traveler_conversations', 'action'=>'view/'.$e['EmailQueue']['template_vars']['conversation_id']));
                echo '</div>';
            }
            ?>
            </td>
            <td><?php echo $e['EmailQueue']['template']?></td>
            <td>
            <?php 
            if(isset ($e['EmailQueue']['template_vars']['travel'])) {
                echo json_encode($e['EmailQueue']['template_vars']['travel']);
            } else {
                echo json_encode($e['EmailQueue']['template_vars']);
            }
            ?>
            </td>
            <td><?php echo $e['EmailQueue']['sent']?></td>
            <td><?php echo $e['EmailQueue']['locked']?></td>
            <td><?php echo $e['EmailQueue']['send_tries']?></td>
            <td>
                <?php if($e['EmailQueue']['sent'] || $e['EmailQueue']['send_tries'] > 3):?>
                    <ul class="list-inline">
                        <li><?php echo $this->Html->link('<i class="glyphicon glyphicon-trash"></i> Eliminar', array('action'=>'remove/'.$e['EmailQueue']['id']), array('escape'=>false, 'confirm'=>'¿Está seguro que quiere eliminar este email?'))?></li>
                    </ul>
                <?php endif?>
                <?php if($e['EmailQueue']['locked']):?>
                    <ul class="list-inline">
                        <li><?php echo $this->Html->link('<i class="glyphicon glyphicon-ok"></i> Desbloquear', array('action'=>'unlock/'.$e['EmailQueue']['id']), array('escape'=>false, 'confirm'=>'¿Está seguro que quiere desbloquear este email?'))?></li>
                    </ul>
                <?php endif?>
                <?php if($e['EmailQueue']['send_tries'] > 3):?>
                    <ul class="list-inline">
                        <li><?php echo $this->Html->link('<i class="glyphicon glyphicon-refresh"></i> Resetear', array('action'=>'reset/'.$e['EmailQueue']['id']), array('escape'=>false, 'confirm'=>'¿Está seguro que quiere resetear este email?'))?></li>
                    </ul>
                <?php endif?>
            </td>
        </tr>
    <?php endforeach; ?>
    </tbody>
</table>

<?php ?>