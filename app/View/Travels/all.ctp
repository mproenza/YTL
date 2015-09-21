<?php 
if(!isset($actions)) $actions = false;
if(!isset($details)) $details = true;
?>
<div class="container">
    <div class="row">
    <?php if(!empty ($travels) /*|| !empty ($travels_by_email)*/): ?>
        <div class="col-md-6 col-md-offset-3">
            <h3>Anuncios de Viajes</h3>
            <div>Filtros: 
                <ul>
                <?php 
                    foreach (Travel::$filtersForSearch as $filter) {
                        echo '<li style="display:inline-block;padding-right:20px">';
                        
                        if(!isset ($filter_applied)) echo $this->Html->link($filter, array('action'=>'view_filtered/'.$filter));
                        else if($filter != $filter_applied) echo $this->Html->link($filter, array('action'=>'view_filtered/'.$filter));
                        else echo '<span class="badge"><big>'.$filter.'</big></span>';
                        
                        echo '</li>';
                    }
                ?>
                </ul>
            </div>
            
            <div>Páginas: <?php echo $this->Paginator->numbers();?></div>
            
            <br/>
            <?php
            
            // Contar la cantidad total de mensajes nuevos
            $newMsgCount = 0;
            $newTravelerMsgCount = 0;
            $followingCount = 0;
            if(!empty ($travels)) {
                foreach ($travels as $travel) {
                    foreach ($travel['DriverTravel'] as $conv) {
                        if(isset ($conv['TravelConversationMeta']) && $conv['TravelConversationMeta'] != null && !empty ($conv['TravelConversationMeta'])) {
                            $newMsgCount += $conv['driver_traveler_conversation_count'] - $conv['TravelConversationMeta']['read_entry_count'];
                            if($conv['TravelConversationMeta']['following']) $followingCount++;
                        }
                            
                        else $newMsgCount += $conv['driver_traveler_conversation_count'];
                        
                        if(isset ($conv['DriverTravelerConversation']) && $conv['DriverTravelerConversation'] != null && !empty ($conv['DriverTravelerConversation'])) {
                            if($conv['DriverTravelerConversation']['response_by'] === 'traveler') $newTravelerMsgCount ++;
                        }
                    }
                }
            }
            ?>
            <div>En esta página:                 
                <span class="label label-info" style="font-size: 12pt"><?php echo $followingCount.' siguiendo'?></span>
                <span class="label label-success" style="font-size: 12pt">+<?php echo $newMsgCount.' nuevos mensajes'?></span>
                <span class="text-muted">(+<?php echo $newTravelerMsgCount.' de viajeros'?>)</span>
            </div>
            <br/>

            <?php if(!empty ($travels)): ?>                
                <br/>

                <ul style="list-style-type: none;padding: 0px">
                <?php foreach ($travels as $travel) :?>                
                    <li style="margin-bottom: 60px">
                        <?php echo $this->element('travel', array('travel'=>$travel, 'actions'=>$actions, 'details'=>$details))?>
                    </li> 
                <?php endforeach; ?>
                </ul>
                
                <br/>
            <?php endif; ?>
                
            <div>Páginas: <?php echo $this->Paginator->numbers();?></div>
        </div>

    <?php else :?>
        No hay anuncios de viajes
    <?php endif; ?>

    </div>
</div>