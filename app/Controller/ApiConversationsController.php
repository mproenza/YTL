<?php
App::uses('ApiAppController', 'Controller');
App::uses('MessagesUtil', 'Util');

class ApiConversationsController extends ApiAppController {
    
    public $uses = array('DriverTravel', 'ApiSync.SyncObject');
    
    public function beforeFilter() {
        parent::beforeFilter();
        
        $this->Auth->allow('iniFetch', 'sync', 'newMessagesInConversation', 'newMessageToTraveler');
    }
    
    
    // ***** INI FETCH *****
    public function iniFetch() {
        //throw new NotFoundException();
        
        $relevantConversations = $this->getRelevantConversations();
        
        // Vamos a coger solo las conversaciones sincronizadas anteriormente cuya fecha de viaje no haya expirado (de hoy en adelante)
        $today = date('Y-m-d', strtotime('today'));
        $conversationsInSyncQueue = $this->getFullConversations($this->getConversationsIdsInSyncQueue($today));
        
        // Eliminar duplicadas
        $conversations = $this->eliminateDuplicateConversations($relevantConversations, $conversationsInSyncQueue);
        
        // Marcar como sincronizadas las conversaciones que vamos a enviar en el iniFetch
        $synced = $this->markConversationsAsSynced($conversations, -1);
        
        // También marcar las conversaciones que estan expiradas en la sync_queue como sincronizadas (batchId = -1) para que no se sincronicen más
        $expiredConversations = $this->getConversationsInSyncQueue($today, -1);
        $this->markConversationsAsSynced($expiredConversations, -1);
        
        // RESPUESTA
        $this->set(array(
            'success' => true,
            'data' => $conversations,
            'synced' =>$synced,
            'full' => $conversationsInSyncQueue,
            '_serialize' => array('success', 'data', 'synced', 'full')
        ));
    }
    private function getRelevantConversations() {
        
        $user = $this->getUser();
        
        // Buscar las conversaciones que se estan SIGUIENDO junto con sus mensajes
        $today = date('Y-m-d', strtotime('today'));
        $sql = $this->getSqlSelectFieldsForConversation()
            . " FROM drivers_travels
                
                INNER JOIN travels_conversations_meta
                ON travels_conversations_meta.conversation_id = drivers_travels.id
                AND travels_conversations_meta.following = true
                AND drivers_travels.travel_date > '".$today."'"

            . " LEFT JOIN driver_traveler_conversations ON driver_traveler_conversations.conversation_id = drivers_travels.id
                
                LEFT JOIN travels ON drivers_travels.travel_id = travels.id
                
                LEFT JOIN discount_rides ON drivers_travels.discount_id = discount_rides.id
                
                WHERE
                    drivers_travels.driver_id = ".$user['id']."
                        
                ORDER BY conversation_id";
        
        $conversationsToSync = $this->DriverTravel->query($sql);
        
        return $this->buildConversations($conversationsToSync);
    }
    
    /**
     * @param $date: fecha de referencia para obtener los resultados
     * @param: $searchDirection: 1 > desde $date hacia el futuro, -1 > fechas menores que $date
     *
     */
    private function getConversationsInSyncQueue($date, $searchDirection = 1) {
        $user = $this->getUser();
        
        $dateCondition = $searchDirection > 0?'>=':'<';
        $dateCondition .= "'".$date."'";
        
        $sql = $this->getSqlSelectFieldsForConversation()
                
            //. ", api_sync_queue_2driver_conversations.batch_id" // Este campo es para saber si la conversacion fue sincronizada
            . " FROM api_sync_queue_2driver_conversations
                INNER JOIN drivers_travels ON api_sync_queue_2driver_conversations.conversation_id = drivers_travels.id 
                    AND drivers_travels.travel_date ".$dateCondition." 
                LEFT JOIN driver_traveler_conversations ON api_sync_queue_2driver_conversations.msg_id = driver_traveler_conversations.id
                LEFT JOIN travels ON drivers_travels.travel_id = travels.id
                LEFT JOIN discount_rides ON drivers_travels.discount_id = discount_rides.id
                LEFT JOIN travels_conversations_meta ON travels_conversations_meta.conversation_id = drivers_travels.id
                
                WHERE drivers_travels.driver_id = ".$user['id']."
                    
                ORDER BY conversation_id";
        
        $conversationsToSync = $this->DriverTravel->query($sql);
        
        return $this->buildConversations($conversationsToSync);
    }
    
    private function getConversationsIdsInSyncQueue($date, $searchDirection = 1) {
        $user = $this->getUser();
        
        $dateCondition = $searchDirection > 0?'>=':'<';
        $dateCondition .= "'".$date."'";
        
        $sql = "SELECT DISTINCT (api_sync_queue_2driver_conversations.conversation_id) as conversation_id
                FROM api_sync_queue_2driver_conversations
                INNER JOIN drivers_travels ON api_sync_queue_2driver_conversations.conversation_id = drivers_travels.id 
                    AND drivers_travels.travel_date ".$dateCondition."
                        
                WHERE drivers_travels.driver_id = ".$user['id']."
                    
                ORDER BY conversation_id";
        
        $conversationsIds = $this->DriverTravel->query($sql);
        
        return $conversationsIds;
    }
    private function getFullConversations(array $ids) {
        // Sanity check
        if($ids == null || empty($ids)) return array();
        
        $user = $this->getUser();
        
        // Convertir el arreglo de ids a la forma ('1', '2', '3', '4', '5')
        $idsStr = '(';
        $sep = '';
        foreach ($ids as $id) {
            $idsStr .= $sep."'".$id['api_sync_queue_2driver_conversations']['conversation_id']."'";
            $sep = ',';
        }
        $idsStr .= ')';
        
        $sql = $this->getSqlSelectFieldsForConversation()
                
            . " FROM drivers_travels"
            
            . " LEFT JOIN travels_conversations_meta ON travels_conversations_meta.conversation_id = drivers_travels.id"
                
            . " LEFT JOIN driver_traveler_conversations ON driver_traveler_conversations.conversation_id = drivers_travels.id
                
                LEFT JOIN travels ON drivers_travels.travel_id = travels.id
                
                LEFT JOIN discount_rides ON drivers_travels.discount_id = discount_rides.id
                
                WHERE
                    drivers_travels.driver_id = ".$user['id']." AND drivers_travels.id IN ".$idsStr
                        
            . " ORDER BY conversation_id";
        
        $conversationsToSync = $this->DriverTravel->query($sql);
        
        return $this->buildConversations($conversationsToSync);
    }
    // ***** INI FETCH (END) *****
    
    
    // ***** SYNC *****
    
    /*
     * EJEMPLO DE RESPUESTA
     * 
     * $sync = array(
            
            // NEW REQUEST
            array(
                'id'=>'5ce80e4b-6780-4916-b6b2-1a94c0a80165', 
                'code'=>"7545",
                'travel_date'=>6545645645435,
                'created'=>9756757,
                
                'travel_request' => array(
                    'id'=>19345,
                    'origin'=>'La Habana',
                    'destination'=>'Santiago de Cuba',
                    'pax'=>3,
                    'details'=>'Holaaaaaa!!!!',
                    'date'=>654564564,
                    'created'=>9756757,                    
                ),
                
                'messages'=> array(
                    
                )
            ),
            
            // OLD REQUEST, 2 NEW MESSAGES
            array(
                'id'=>'5cecb99f-1234-4db6-8a01-142410d2655b',
                'code'=>"19345",
                'travel_date'=>6545645646574,
                'created'=>867586797898,
                
                'travel_request' => array(
                    'id'=>19345,
                    'origin'=>'La Habana',
                    'destination'=>'Managua',
                    'pax'=>3,
                    'details'=>'Otra!!!!',
                    'date'=>65465465,
                    'created'=>9756757,
                ),
                
                'messages'=> array(
                    array('id' => 1, 'order' => 2, 'message' => 'Msg1', 'created' => 34656656, 'media'=>array()),
                    array('id' => 2, 'order' => 3, 'message' => 'Msg2', 'created' => 656765765, 'media'=>array('url'=>'http://192.168.1.102/yotellevo/files/20190204_150453_jpg')),
                    array('id' => 3, 'order' => 4, 'message' => 'Msg3', 'created' => 656765767, 'media'=>array('url'=>'http://192.168.1.102/yotellevo/files/cookbook_pdf')),
                    array('id' => 4, 'order' => 5, 'message' => 'Msg2', 'created' => 656765765, 'media'=>array('url'=>'http://192.168.1.102/yotellevo/files/lua_jpg')),
                )
            ),
            
            // NEW DIRECT MESSAGE + 1 MORE MESSAGE
            array(
                'id'=>'5cecb99f-2ce0-4db6-8a01-142410d2655b',
                'code'=>"D172",
                'travel_date'=>654564564722256,
                'created'=>867586797898,
                
                'travel_request' => null,
                
                'messages'=> array(
                    array('id' => 3, 'order' => 1, 'message' => 'Msg1', 'created' => 34656656),
                    array('id' => 4, 'order' => 2, 'message' => 'Msg2', 'created' => 656765),
                    array('id' => 5, 'order' => 3, 'message' => 'Msg2', 'created' => 656765765, 'media'=>array('url'=>'http://192.168.1.102/yotellevo/files/lua_jpg')),
                )
            ),
        );
     */
    public function sync($batchId) {
        $conversations = $this->getConversationsToSync($batchId);
        
        // Hack: Quitar campo estado (aqui en el sync no hace falta)
        foreach ($conversations as &$value) {
            unset($value['state']);
        }
        
        $synced = $this->markConversationsAsSynced($conversations, $batchId);
        
        // RESPUESTA
        $this->set(array(
            'success' => true,
            'synced_count' =>count($synced),
            'data' => $conversations,
            'synced' =>$synced,
            '_serialize' => array('success','synced_count', 'data', 'synced')
        ));
    }
    private function getConversationsToSync($batchId, $conversationId = null) {
        $user = $this->getUser();
        
        // Buscar las conversaciones asociadas a los mensajes que vamos a sincronizar
        $idCondition = "";
        if($conversationId != null) $idCondition = "AND drivers_travels.id = '".$conversationId."'";
        $sql = $this->getSqlSelectFieldsForConversation()
                
            . " FROM api_sync_queue_2driver_conversations
                INNER JOIN drivers_travels ON api_sync_queue_2driver_conversations.conversation_id = drivers_travels.id
                LEFT JOIN driver_traveler_conversations ON api_sync_queue_2driver_conversations.msg_id = driver_traveler_conversations.id
                LEFT JOIN travels ON drivers_travels.travel_id = travels.id
                LEFT JOIN discount_rides ON drivers_travels.discount_id = discount_rides.id
                LEFT JOIN travels_conversations_meta ON travels_conversations_meta.conversation_id = drivers_travels.id
                WHERE 
                    drivers_travels.driver_id = ".$user['id']."
                    
                    AND (
                        api_sync_queue_2driver_conversations.batch_id = ".$batchId."
                        OR
                        (
                            api_sync_queue_2driver_conversations.batch_id IS NULL 
                            AND 
                            api_sync_queue_2driver_conversations.sync_date IS NULL 
                        )                        
                    )
                    
                    ".$idCondition."
                ORDER BY conversation_id";
        
        $conversationsToSync = $this->DriverTravel->query($sql);
        
        return $this->buildConversations($conversationsToSync);
    }
    // ***** SYNC (END) *****
    
    /*
     * EJEMPLO DE RESPUESTA
     * 
     * $new = array(
            array(
                'id'=>$conversationId,
                'code'=>"D172",
                'travel_date'=>654564564722256,
                'created'=>867586797898,
                
                'travel_request' => null,
                
                'messages'=> array(
                    array('id' => 5, 'order' => 3, 'message' => 'XXX', 'created' => 134656656, 'media'=>array('url'=>'https://www.portal.nauta.cu/data/files/BOLETIN_H-B_Final.pdf')),
                    array('id' => 6, 'order' => 4, 'message' => 'YYY', 'created' => 134656656, 'media'=>array('url'=>'http://192.168.1.102/yotellevo/files/20190204_150453_jpg')),
                )
            ),
        );
     */    
    public function newMessagesInConversation($conversationId, $batchId) {
        
        $conversations = $this->getConversationsToSync($batchId, $conversationId);
        
        $synced = $this->markConversationsAsSynced($conversations, $batchId);
        
        $conv = null;
        if(!empty($conversations)) $conv = $conversations[0]; // Aqui siempre va a venir una sola conversacion
        
        $this->set(array(
            'success' => true,
            'data' => $conv,
            'synced'=>$synced,
            '_serialize' => array('success', 'data', 'synced')
        ));
    }
    
    public function newMessageToTraveler($conversationId) {
        
        if(!$this->request->is('post')) throw new MethodNotAllowedException();
        
        $user = $this->getUser();
        
        $attachments = array();
        if(isset($_FILES['file']['name'])) {
            $adjunto = $_FILES['file'];
        
            if($adjunto['name'] != '')
                $attachments = array($adjunto['name'] => array('contents' => file_get_contents($adjunto['tmp_name']), 'mimetype' => $adjunto['type']));
        }
        
        $mu = new MessagesUtil();
        $mu->sendMessage('driver', $conversationId, null, $this->request->data['message'], $attachments, 'APP');
        
        //CakeLog::write('api', print_r($this->request->data, true));
        
        $this->set(array(
            'success' => true,
            'data' => true,
            '_serialize' => array('success', 'data')
        ));
    } 
    
    
    // ***** AUX AND COMMON FUNCTIONS *****
    
    /*
     * Retorna la sentencia SQL que selecciona los campos indispensables para construir las conversaciones con buildConversations()
     */
    private function getSqlSelectFieldsForConversation() {
        return "SELECT
                    drivers_travels.id as conversation_id,
                    drivers_travels.identifier,
                    drivers_travels.notification_type,
                    drivers_travels.travel_date,
                    drivers_travels.created, 
                    drivers_travels.travel_id,
                    drivers_travels.discount_id,
                    
                    travels.origin, 
                    travels.destination, 
                    travels.people_count as pax,
                    travels.details,
                    travels.created as travel_created,
                    
                    discount_rides.origin as promo_origin, 
                    discount_rides.destination as promo_destination, 
                    discount_rides.price as promo_price,
                    
                    driver_traveler_conversations.id as msg_id,
                    driver_traveler_conversations.response_text,
                    driver_traveler_conversations.created as msg_created,
                    driver_traveler_conversations.response_by,
                    driver_traveler_conversations.attachments_ids,
                    
                    travels_conversations_meta.state,
                    travels_conversations_meta.following";
    }
    
    /*
     * @param $conversationsToBuild: Un arreglo con datos que se ajustan a lo que devuelve getSqlSelectFieldsForConversation()
     */
    private function buildConversations($conversationsToBuild) {
        // Armar las conversaciones
        $conversations = array();
        for ($index = 0; $index < count($conversationsToBuild);) {
            
            $c = $conversationsToBuild[$index];
            
            // Crear la travel_request si existe
            $travelRequest = null;
            if($c['drivers_travels']['travel_id'] != null) {
                $travelRequest = array(
                    'id'=>$c['drivers_travels']['travel_id'],
                    'origin'=>$c['travels']['origin'],
                    'destination'=>$c['travels']['destination'],
                    'pax'=>$c['travels']['pax'],
                    'details'=>$c['travels']['details'],
                    'date'=>1000*strtotime($c['drivers_travels']['travel_date']),
                    'created'=>1000*strtotime($c['travels']['travel_created']),
                );
            }
            
            // Crear la promotion si existe
            $promoRequest = null;
            if($c['drivers_travels']['discount_id'] != null) {
                $promoRequest = array(
                    'id'=>$c['drivers_travels']['discount_id'],
                    'origin'=>$c['discount_rides']['promo_origin'],
                    'destination'=>$c['discount_rides']['promo_destination'],
                    'price'=>$c['discount_rides']['promo_price'],
                );
            }
            
            /*// Poner datos a la conversacion relacionados con la sync_queue
            $syncData = null;
            if($c['api_sync_queue_2driver_conversations']) {
                if(isset($c['api_sync_queue_2driver_conversations']['batch_id']))
                    $syncData['batch_id'] = $c['api_sync_queue_2driver_conversations']['batch_id'];
            }*/
            
            // Crear la conversacion con los mensajes listos para adicionar
            $conversations[] = array(
                'id'=>$c['drivers_travels']['conversation_id'],
                'code'=> DriverTravel::getIdentifier($c['drivers_travels']),
                'travel_date'=>1000*strtotime($c['drivers_travels']['travel_date']),
                'created'=>1000*strtotime($c['drivers_travels']['created']),
                'state'=>self::calculateState($c['travels_conversations_meta']),
                
                'travel_request' => $travelRequest,
                'promo_request' => $promoRequest,
                
                //'sync_data' => $syncData,
                
                'messages'=>array()
            );
            
            // Adicionar los mensajes a la conversacion
            $current_convId = $c['drivers_travels']['conversation_id'];
            $i = $index;
            while($i < count($conversationsToBuild) && $conversationsToBuild[$i]['drivers_travels']['conversation_id'] == $current_convId) {
                
                $isMessagePresent = $conversationsToBuild[$i]['driver_traveler_conversations']['msg_id'] != null;                
                if($isMessagePresent) {
                    // Coger media
                    $media = array();
                    $hasMedia = $conversationsToBuild[$i]['driver_traveler_conversations']['attachments_ids'] != null && $conversationsToBuild[$i]['driver_traveler_conversations']['attachments_ids'] != '';
                    if($hasMedia) {
                        $attachModel = ClassRegistry::init('EmailQueue.EmailAttachment');
                        $atts = $attachModel->getAttachments($conversationsToBuild[$i]['driver_traveler_conversations']['attachments_ids']);
                          
                        $media = array('url'=>$atts[0]['url']);// TODO: Aqui solo se está enviando el primer adjunto!!
                    }

                    // Adicionar mensaje
                    $conversations[count($conversations) - 1]['messages'][] = 
                            array(
                                'id'=>$conversationsToBuild[$i]['driver_traveler_conversations']['msg_id'], 
                                'message'=>$conversationsToBuild[$i]['driver_traveler_conversations']['response_text'],
                                'created'=>1000*strtotime($conversationsToBuild[$i]['driver_traveler_conversations']['msg_created']),
                                'sent_by_driver'=>$conversationsToBuild[$i]['driver_traveler_conversations']['response_by'] == 'driver'?true:false,
                                'media'=>$media
                                );
                }
                
                $i++;
            }
            
            $index = $i;
        }
        
        return $conversations;
    }
    
    /*
     * Convierte el estado de la conversacion a un número compatible con la app móvil, según el siguiente enum:
     * UNCLASSIFIED: 0
     * FOLLOWING: 1
     * SCHEDULED: 2
     * CANCELLED: 3
     * COMPLETED: 4
     * 
     * NOTA: Esto debe cambiar si se hacen cambios en el enum en la app
     * 
     */
    private static function calculateState($meta) {
        if($meta['following']) return 2; // Enviar como SCHEDULED
        
        // TODO: Otros estados
        
        return 0;
    }
    
    private function markConversationsAsSynced($conversations, $batchId = null) {
        $SyncTable = ClassRegistry::init('ApiSync.SyncObject');
        $SyncTable->useTable = 'api_sync_queue_2driver_conversations';
        
        // Si el batchId = -1, entonces marcar como synced todas las entradas de cada conversacion, sin tener en cuenta el batchId
        $dismissBatchId = $batchId == -1?'true':'false';
        
        // Obtener las entradas que vamos a marcar como leidas
        $synced = array();
        foreach ($conversations as $c) {
            
            // Buscar todas las entradas en la cola de sincronizacion con el id de esta conversacion
            $syncedEntries = $SyncTable->find('all', 
                    array('conditions'=>array(
                            'conversation_id'=>$c['id'],
                            '('.$dismissBatchId.' OR batch_id = '.$batchId.'
                                OR
                                (
                                    batch_id IS NULL 
                                    AND 
                                    sync_date IS NULL
                                )
                            )',
                    )));
            
            // Poner datos de la sincronizacion (batch_id, sync_date, etc)
            foreach($syncedEntries as $entry) {
                
                $entry['SyncObject']['batch_id'] = $batchId;
                $entry['SyncObject']['batch_id_retry_count'] = $entry['SyncObject']['batch_id_retry_count'] + 1;
                $entry['SyncObject']['sync_date'] = gmdate('Y-m-d H:i:s');
                
                $synced[] = $entry;
            }
        }
        
        if(!empty($synced)) $SyncTable->saveAll($synced);
        
        return $synced;
    }
    
    private function eliminateDuplicateConversations(array $conversations1, array $conversations2) {
        $conversations = $conversations1;
        foreach ($conversations2 as $sqc) {
            $isDuplicate = false;
            foreach ($conversations1 as $rc) {
                if($sqc['id'] == $rc['id']) {
                    $isDuplicate = true;
                    break;
                }
            }
            
            if(!$isDuplicate) {
                unset($sqc['state']); // Quitarle el state a las conversaciones que no son relevantes
                $conversations[] = $sqc;
            }
        }
        
        return $conversations;
    }
    
}

?>