<?php

App::uses('AppController', 'Controller');
App::uses('CakeEmail', 'Network/Email');
App::uses('DriverTravel', 'Model');
App::uses('User', 'Model');
App::uses('Locality', 'Model');
App::uses('Province', 'Model');
App::uses('TimeUtil', 'Util');

class DriversController extends AppController {
    
    public $uses = array('DriverTravelerConversation',/*-*/ 'Driver', 'Locality', 'DriverLocality', 'DriverTravel', 'DriverProfile', 'DriverTravelByEmail', 'Travel', 'TravelByEmail', 'User', 'Testimonial', 'TravelConversationMeta', 'UserInteraction');
    
    public $components = array('TravelLogic', 'Paginator', 'RequestHandler');
    
    public $helpers = array('Js');
    
    public function beforeFilter() {
        parent::beforeFilter();
        $this->Auth->allow('profile','messages', 'drivers_by_province','add_offer');
    }
    
    public function index() {
        $this->Driver->recursive = 1;
        $this->Driver->bindModel(array('belongsTo'=>array('User'=>array('foreignKey'=>'operator_id', 'fields'=>array('id', 'username', 'display_name', 'role')))));
        
        /*if(AuthComponent::user('role') == 'operator')
            $this->Driver->Behaviors->load('Operations.OperatorScope', array('match'=>'Driver.operator_id'));*/
        
        $this->set('drivers', $this->Driver->find('all'));
        
    }
    
    public function view_travels($driverId) {
        $this->set('driver', $this->Driver->findById($driverId));
        
        $driverTavels = $this->DriverTravel->find('all', array('conditions'=>array('Driver.id'=>$driverId)));
        $ids = array();
        foreach ($driverTavels as $dt) {
            $ids[] = $dt['Travel']['id'];
        }
        
        Travel::prepareFullConversations($this);
        $this->set('travels', $this->Travel->find('all', array('conditions'=>array('Travel.id'=>$ids))));
        $this->set('drivers', $this->Driver->getAsSuggestions());
    }

    public function add() {
        if ($this->request->is('post') || $this->request->is('put')) {
            
            $this->Driver->create();
            
            //$this->request->data['Driver']['role'] = 'driver';
            if ($this->Driver->saveAssociated($this->request->data)) {
                $this->setInfoMessage('El chofer se guardó exitosamente.');
                return $this->redirect(array('action' => 'index'));                
            }
            $this->setErrorMessage('Ocurrió un error guardando el chofer.');
        }
        $this->set('localities', $this->Locality->getAsList());
        $this->set('provinces', $this->Driver->Province->find('list'));
        $this->set('operators', $this->User->getOperatorsList(true));
    }

    public function edit($id = null) {
        $this->Driver->id = $id;
        if (!$this->Driver->exists()) {
            throw new NotFoundException('Chofer inválido.');
        }
        if ($this->request->is('post') || $this->request->is('put')) {
            
            if(strlen($this->request->data['Driver']['password']) == 0) unset ($this->request->data['Driver']['password']);
            
            if ($this->Driver->saveAll($this->request->data)) {
                $this->setInfoMessage('El chofer se guaró exitosamente.');
                return $this->redirect(array('action' => 'index'));
            }
            $this->setErrorMessage('Ocurrió un error salvando el chofer');
        } else {
            $this->request->data = $this->Driver->read(null, $id);
            unset($this->request->data['Driver']['password']);
            
            $this->set('localities', $this->Locality->getAsList());
            $this->set('provinces', $this->Driver->Province->find('list'));
            $this->set('operators', $this->User->getOperatorsList(true));
        }
    }

    public function remove($id = null) {
        $this->Driver->id = $id;
        if (!$this->Driver->exists()) {
            throw new NotFoundException('Invalid driver');
        }
        if ($this->Driver->delete()) {
            $this->setInfoMessage('El chofer se eliminó exitosamente.');
        } else {
            $this->setErrorMessage('Ocurrió un error eliminando el chofer');
        }
        
        return $this->redirect(array('action' => 'index'));
    }
    
    public function edit_profile($id = null) {
        $this->Driver->id = $id;
        if (!$this->Driver->exists()) {
            throw new NotFoundException('Chofer inválido.');
        }
        if ($this->request->is('post') || $this->request->is('put')) {
            $this->request->data['DriverProfile']['driver_id'] = $id;
            $this->request->data['DriverProfile']['driver_code'] = strtolower($this->request->data['DriverProfile']['driver_code']); // Salvar el codigo siempre lowercase
            
            if($this->DriverProfile->save($this->request->data)) {
                $this->setInfoMessage('El perfil  se guardó exitosamente.');
                return $this->redirect(array('action'=>'profile/'.$this->request->data['DriverProfile']['driver_nick']));
            }
        }
        
        $this->Driver->recursive = 0;
        $driver = $this->Driver->findById($id);
        
        $this->request->data = $this->DriverProfile->findByDriverId($id);
        
        $this->set('driver', $driver);
    }
    
    public function profile($nick) {
        //if(Configure::read('show_testimonials_in_profile')) $this->Driver->loadTestimonials($this->DriverProfile);
        $this->Driver->unbindModel(array('hasAndBelongsToMany'=>array('Locality')));
        $this->loadModel('TestimonialsReply');
        
        $profile = $this->Driver->find('first', array('conditions'=>array('DriverProfile.driver_nick'=>$nick)));
        
        if($profile != null && !empty ($profile)) {
            $this->layout = 'profile';
            $this->set('profile', $profile);
            
            if(Configure::read('show_testimonials_in_profile')){
                
                $result = $this->next_to_notify($profile['Province'], 6, $profile['Driver']['id']);
                $this->set('other_drivers', $result);
                
                /* Chequeamos para cargar el viaje con descuento si hay */
                if($this->request->query('discount')) {
                    $this->loadModel('DiscountRide');
                    $this->set('discount',
                            $this->DiscountRide->findFullById(
                                    $this->request->query('discount'),
                                    array('DiscountRide.driver_id'=>$profile['Driver']['id'], 'DiscountRide.active'=>1))
                            );
                }
                
                /*Primero chequeamos si es una vista directa de testimonio*/
                if($this->request->query('see-review')){
                    //getting given testimonial
                    $askedreview = $this->Testimonial->findById($this->request->query('see-review'));
                    $highlighted = $askedreview;
                    $this->set('highlighted',$highlighted);//Sending given review data for filling metadata
                    //Getting specific values for virtual field adding                
                    $haystack = array ('0'=>$askedreview['Testimonial']['id']);
                    //Transforming in a semmicolon separated string                
                    $askedreview = implode(',', $haystack);
                } else $askedreview = '';//if direct profile view
               
                //Creating a virtual field for returning given testimonial (if given) into pagination
                $this->Testimonial->virtualFields['in_review']=  "IF (Testimonial.id IN ('$askedreview'),0,1)";
                
                //Logica para ver /profile/drivername/reviews:testimonial_count
                if(!isset($this->passedArgs['reviews'])) $this->passedArgs['reviews'] = 5;
                
                $this->paginate = array( 'Testimonial' => array('limit' => $this->passedArgs['reviews'], 'recursive' => 2, 'order' => array('in_review'=>'asc'/*our given testimonial comes first*/,'Testimonial.created'=> 'desc')) );
                
                $this->set( 'testimonials', $this->paginate('Testimonial', array(
                    'Testimonial.driver_id' => $profile['Driver']['id'], 
                    'Testimonial.state'=>Testimonial::$statesValues['approved'] ))
                );
                                            
                if($this->request->is('ajax')) {                    
                    $render = '/Elements';
                    if(Configure::read('App.theme') != null) $render .= '/'.Configure::read('App.theme');
                    $render .= '/ajax_testimonials_list';
                    return $this->render($render, false);
                }   
            }
            
        } else {
            throw new NotFoundException(__('Este perfil no existe'));
        }
    }
    
    public function admin($id) {
        $this->set('driver', $this->Driver->findById($id));
        
        $this->set('timeline', $this->getDriverMessagesTimeline($id));
    }
    
    
    private function getDriverMessagesTimeline($driverId) {
        $today = date('Y-m-d', strtotime('today'));
        $endDate = $today;
        $iniDate = date('Y-m-d', strtotime("$endDate - 6 months"));
        if(!empty ($this->request->query)) {
            
            //WARNING: A continuacion estoy asumiendo que las fechas vienen en formato dd-mm-yyyy
            
            $strIniDate = $this->request->query['date_ini'];
            $iniDate = substr($strIniDate,6,4).'-'.substr($strIniDate,3,2).'-'.substr($strIniDate,0,2);
            
            if(isset ($this->request->query['date_end'])) {
                $strEndDate = $this->request->query['date_end'];
                $endDate = substr($strEndDate,6,4).'-'.substr($strEndDate,3,2).'-'.substr($strEndDate,0,2);
            }            
        }  
        
        $query = "SELECT driver_traveler_conversations.created, driver_traveler_conversations.conversation_id, drivers.id as driver_id, driver_traveler_conversations.response_by, driver_traveler_conversations.response_text

                FROM driver_traveler_conversations

                INNER JOIN drivers_travels ON drivers_travels.id = driver_traveler_conversations.conversation_id AND driver_traveler_conversations.created BETWEEN '$iniDate' AND '$endDate'

                INNER JOIN drivers ON drivers.id = drivers_travels.driver_id AND drivers.id = $driverId

                ORDER BY  driver_traveler_conversations.created";
        
        $messages = $this->Driver->query($query);
        $fixedMessages = array();
        foreach ($messages as $index=> $value) {
            $fixedMessages[$index] = array();
            $fixedMessages[$index]['date'] = $value['driver_traveler_conversations']['created'];
            $fixedMessages[$index]['response_by'] = $value['driver_traveler_conversations']['response_by'];
            $fixedMessages[$index]['response_text'] = $value['driver_traveler_conversations']['response_text'];
            if(strlen($fixedMessages[$index]['response_text']) > 500) $fixedMessages[$index]['response_text'] = substr ($fixedMessages[$index]['response_text'], 0, 500).'...';
            
            $d = strtotime($value['driver_traveler_conversations']['created']);
            $hour = date('H', $d);
            $min = date('i', $d);
            
            $fixedMessages[$index]['driver'] = -100;
            $fixedMessages[$index]['traveler'] = -100;
            if($value['driver_traveler_conversations']['response_by'] == 'driver')  $fixedMessages[$index]['driver'] = $hour*60 + $min;
            else if($value['driver_traveler_conversations']['response_by'] == 'traveler') $fixedMessages[$index]['traveler'] = $hour*60 + $min;
            
            $fixedMessages[$index]['timestr'] = "$hour:$min";
        }
        
        return $fixedMessages;
    }
    
    public function messages($conversationId) {
        $this->layout = 'driver_panel';        
       
        $this->DriverTravel->bindModel(array('belongsTo'=>array('Travel')));        
        $this->Driver->attachProfile($this->DriverTravel);
        
        $data = $this->DriverTravel->findById($conversationId);
        $this->set('data', $data);
        /* Chequeamos para cargar el viaje con descuento */
        if($data['DriverTravel']['notification_type'] == DriverTravel::$NOTIFICATION_TYPE_DISCOUNT_OFFER_REQUEST){
            $this->loadModel('DiscountRide');
            $this->set('discount', $this->DiscountRide->findFullById($data['DriverTravel']['discount_id']));
        }
               
        $conversations = $this->DriverTravelerConversation->findAllByConversationId($conversationId);        
        $this->set('conversations', $conversations);
        
                
    }
    
    public function add_offer($driver_token) {
        $this->layout = 'driver_discount';        
       
        $this->DriverTravel->bindModel(array('belongsTo'=>array('Travel')));        
        $this->Driver->attachProfile($this->DriverTravel);
        
        $driver = $this->Driver->find('all', array('conditions'=>array('Driver.web_auth_token'=>$driver_token)) );
        
        if(empty($driver_token)) throw new NotFoundException();
        
        $this->set('driver', $driver[0]);           
    }
    
    public function drivers_by_province($slug) {
        $province = Province::_provinceFromSlug($slug);
        
        if($province === null) throw new NotFoundException(__d('error', 'La provincia no existe'));
        
        $driversData = $this->Driver->getDriversCardsByProvince($province['id']);
        
        // Si no hay suficientes choferes en la provincia seleccionada, buscar choferes en provincias alternativas
        $shouldLookForAlternativeDrivers = count($driversData) < 10 && isset($province['alternative_province']) && $province['alternative_province'] != null;
        $altertativeDriversData = array();
        if($shouldLookForAlternativeDrivers) {
            $altertativeDriversData = $this->Driver->getDriversCardsByProvince($province['alternative_province']);
        }
        
        $this->set('drivers_data', $driversData);
        $this->set('alternative_drivers_data', $altertativeDriversData);
        $this->set('province', $province);
        
        $this->layout = 'drivers_by_province';
    }
    
    /*public function view_drivers_data($slug) {
        $province = Province::_provinceFromSlug($slug);
        
        if($province === null) throw new NotFoundException(__d('error', 'La provincia no existe'));
        
        $driversData = $this->driversInProvince($province['id']);
        
        $this->set('drivers_data', $driversData);
        $this->set('province', $province['name']);
    }*/
    
    private function next_to_notify($province, $count, $notThisDriverId = null) {
        
        //if($province === null) throw new NotFoundException(__d('error', 'La provincia no existe'));
        
        $driversData = $this->Driver->getNextToNotify($province['id'], $count, $notThisDriverId);
        
        // Si no hay suficientes choferes en la provincia seleccionada, buscar choferes en provincias alternativas
        $shouldLookForAlternativeDrivers = count($driversData) < 3 && isset($province['alternative_province']) && $province['alternative_province'] != null;
        $altertativeDriversData = array();
        if($shouldLookForAlternativeDrivers) {
            $altertativeDriversData = $this->Driver->getNextToNotiffy($province['alternative_province']);
        }
        /*Creamos el arreglo para retornar el resultado (supongo que solo necesitemos el email)
         pero igualmente dejo la consulta vieja para que me digas tu, por si necesitamos algo*/
        $drivers = array('drivers_data'=>$driversData,'alternative_drivers_data'=>$altertativeDriversData);
       
        return $drivers;       
    }
}

?>