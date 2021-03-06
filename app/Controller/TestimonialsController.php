<?php

App::uses('CakeEmail', 'Network/Email');
App::uses('EmailsUtil', 'Util');
App::uses('StringsUtil', 'Util');

class TestimonialsController extends AppController {

    public $components = array('Paginator');
    public $uses = array('Testimonial', 'DriverTravel', 'Driver', 'TravelConversationMeta', 'DriverProfile', 'User');
    
    public function beforeFilter() {
        parent::beforeFilter();

        $this->Auth->allow('enter_code', 'featured', 'view', 'reviews','reply');
        if (isset($this->request->params['pass']['0'])) {
            if ($this->request->params['action'] == 'add') {
                if (isset($this->request->params['pass']['1']))
                    $driver_travel_id = $this->request->params['pass']['1'];
                    $this->Auth->allow('add');                
                    
            }
            else if (in_array($this->request->params['action'], array('edit', 'preview'))) {
                $testimonial_id = $this->request->params['pass']['0'];
                $testimonial_data = $this->Testimonial->findById($testimonial_id);
                if (!isset($testimonial_data['DriverTravel']['id']))                //externo
                    $this->Auth->allow('preview');
                else
                    $driver_travel_id = $testimonial_data['DriverTravel']['id'];
            }

            if (isset($driver_travel_id)) {
                $driver_travel_data = $this->DriverTravel->findById($driver_travel_id);

                $user_real = isset($driver_travel_data['Travel']['user_id']) ? $driver_travel_data['Travel']['user_id'] : null;
                if ($this->Auth->loggedIn() || $this->Auth->login())
                    $user_requesting = AuthComponent::user('id');

                if ($user_requesting == $user_real)
                    $this->Auth->allow('edit', 'preview');
            }
        }
    }
    
    public function isAuthorized($user){
        if($this->request->action == 'edit') return false;

        if($user['role'] =='operator' && in_array($this->action, array('view_filtered', 'index', 'preview', 'state_change', 'lang_change', 'admin', 'request_testimonial'))) 
            return true;

        //nadie puede annadir testimonios internos [a menos que se le haya permitido en el beforeFilter] -> [solo si es el usuario del viaje]
        if($this->request->action == 'add' && isset($this->request->params['pass']['1']))
            return false;

        return parent::isAuthorized($user);
    }

    public function index() {
        $this->redirect(array('action' => 'view_filtered/pending'));
    }
    
    public function featured($redirect = true) {
        if($redirect) return $this->redirect(array('action'=>'reviews', '?'=>$this->request->query), 301);
        
        $this->Testimonial->recursive = 4;
        
        //$this->Driver->unbindModel(array('belongsTo' => array('Province')));
        $this->Driver->unbindModel(array('hasAndBelongsToMany' => array('Locality')));
        
        $langs = array(Configure::read('Config.language'));
        if(isset($this->request->query['also']) && Configure::read('Config.language') != $this->request->query['also']) {
            $langs[] = $this->request->query['also'];
        }
        
        $conditions = array('Testimonial.featured'=>true, 'Testimonial.lang'=>$langs);
        
        $this->paginate = array('order'=>array('Testimonial.created'=>'DESC'), 'limit'=>30);
        $this->set('testimonials', $this->paginate($conditions));        
        
        // STATS
        $stats = $this->Session->read('App.stats');
        if(!$stats) {
            $doneSQL = "SELECT COUNT( DISTINCT travels.id ) AS hires, SUM( travels.people_count ) AS people
                        FROM travels
                        INNER JOIN users ON travels.user_id = users.id
                        AND users.role !=  'admin'
                        AND users.role !=  'tester'
                        INNER JOIN drivers_travels ON travels.id = drivers_travels.travel_id
                        INNER JOIN travels_conversations_meta ON drivers_travels.id = travels_conversations_meta.conversation_id
                        AND (
                        travels_conversations_meta.state = 'D'
                        OR travels_conversations_meta.state = 'P'
                        )";

            $reviewsSQL = "SELECT COUNT( testimonials.id ) AS reviews
                        FROM testimonials
                        WHERE testimonials.state = 'A'";

            $done = $this->Testimonial->query($doneSQL);
            $reviews = $this->Testimonial->query($reviewsSQL);

            $stats = array('hires'=>$done[0][0]['hires'], 'people'=>$done[0][0]['people'], 'reviews'=>$reviews[0][0]['reviews']);
            
            $this->Session->write('App.stats', $stats);
        }
        
        $this->set(compact('stats'));
    }
    public function reviews() {
        $this->featured(false);
        $this->render('featured');
    }

    public function view_filtered($filtro = 'pending') {
        $this->Testimonial->recursive = 3;
        $this->DriverTravel->unbindModel(array('hasOne' => array('TravelConversationMeta')));
        $this->Driver->unbindModel(array('hasAndBelongsToMany' => array('Locality')));

        $conditions = array();
        if ($filtro != 'all')
            $conditions = array('Testimonial.state =' => Testimonial::$statesValues[$filtro]);

        $this->Paginator->settings = array('limit' => 50);
        $testimonials = $this->Paginator->paginate('Testimonial', $conditions);

        $this->set('testimonials', $testimonials);
        $this->set('filter_applied', $filtro);

        $this->render('all');
    }

    public function add($driver_code, $conversation_id = null) {
        $driver_code = strtolower($driver_code); // El codigo siempre va a estar en lowercase en la BD. Ver DriversController::edit_profile
        
        // Buscar el perfil por el codigo del chofer
        $dp_data = $this->DriverProfile->findByDriverCode($driver_code);
        
        if (!$dp_data) // Si no se encuentra el perfil entonces el codigo esta mal
            throw new NotFoundException(__d('testimonials', 'No se encontró un chofer con este código. Revíselo e intente de nuevo.'));

        // Verificaciones si se pasa la conversacion
        if ($conversation_id != NULL) {
            $dt_data = $this->DriverTravel->findById($conversation_id);
            if (!$dt_data)
                throw new NotFoundException(__('No existe la Conversación solicitada.'));

            if ($dt_data['DriverTravel']['driver_id'] != $dp_data['DriverProfile']['driver_id'])
                throw new BadRequestException(__('La conversación no corresponde al chofer solicitado'));
            
            $user = $this->User->findById($dt_data['Travel']['user_id']);
        }

        if ($this->request->is('post')) {
            $datasource = $this->Testimonial->getDataSource();
            $datasource->begin();
            
            $this->Testimonial->create();

            $this->request->data['Testimonial']['conversation_id'] = $conversation_id;// estaba data['Testimonial']['driver_travel_id'] que no es correcto
            $this->request->data['Testimonial']['driver_id'] = $dp_data['DriverProfile']['driver_id'];
            $this->request->data['Testimonial']['validation_token'] = StringsUtil::getWeirdString();
            $this->request->data['Testimonial']['driver_reply_token'] = StringsUtil::getWeirdString();
            if( isset($user['User']['username']) )
                $this->request->data['Testimonial']['email'] = $user['User']['username'];
            
            $OK = $this->Testimonial->save($this->request->data);
            if($OK) {
                $tid = $this->Testimonial->id;
                $testimonial = $this->_getTestimonial($tid);
                
                //guardando en conversations_meta el testimonio
                if($conversation_id != NULL) {
                    $this->TravelConversationMeta->id = $conversation_id;
                    $meta = array();

                    $meta['TravelConversationMeta']['testimonial_id'] = $tid;                
                    $this->TravelConversationMeta->save($meta);
                }
                
                // Enviar correos al admin y al chofer
                $OK = $this->_sendAdminMail($testimonial);
                if(!$OK) CakeLog::write('testimonial_errors', "Error al enviar mensaje de nuevo testimonio $tid al admin");
                $OK = $this->_sendEmailToDriver($testimonial);
                if(!$OK) CakeLog::write('testimonial_errors', "Error al enviar mensaje de nuevo testimonio $tid al chofer");
                
                //if( $this->_sendVerificationMail($tid) )
                    $datasource->commit();
                /*else{    
                    $datasource->rollback();
                    $this->setErrorMessage( __d('testimonials', 'Error al enviar mensaje de verificación') );
                }  */
                return $this->redirect(array('action' => 'preview', $tid));
            } else {
                $datasource->rollback();
                $this->setErrorMessage( __d('testimonials', 'Ocurrió un error al intentar guardar el testimonio.') );
            }
            
        } else {
            if ($conversation_id != NULL) {
                $data = $this->Testimonial->findByConversationId($conversation_id);  //debe devolver solo 1 registro aunque no este modelado asi en la bd
                if (isset($data['Testimonial']['id']))
                    return $this->redirect(array('action' => 'edit', $data['Testimonial']['id']));
            }

            $this->Driver->unbindModel(array('hasAndBelongsToMany' => array('Locality')));
            $data = $this->Driver->findById($dp_data['DriverProfile']['driver_id']);
            $this->set('driver', $data['Driver']);
            $this->set('driver_profile', $data['DriverProfile']);
            $this->set('external', ($conversation_id == NULL));
        }
    }

    public function edit($id) {
        $testimonial = $this->Testimonial->findById($id);
        if (!$testimonial)
            throw new NotFoundException( __d('testimonials', 'El Testimonio solicitado no existe') );

        if ($this->request->is('post') || $this->request->is('put')) {
            $this->Testimonial->id = $id;
            $this->request->state = Testimonial::$statesValues['pending'];


            if ($this->Testimonial->save($this->request->data)) {
                return $this->redirect(array('action' => 'preview', $id));
            }
            $this->setErrorMessage( __d('testimonials', 'Ocurrió un error al intentar guardar el testimonio.') );
        }

        if (!$this->request->data)
            $this->request->data = $testimonial;

        $this->Driver->unbindModel(array('hasAndBelongsToMany' => array('Locality')));
        $data = $this->Driver->findById($testimonial['Testimonial']['driver_id']);
        $this->set('driver', $data['Driver']);
        $this->set('driver_profile', $data['DriverProfile']);
    }

    public function preview($id) {
        $this->Driver->attachProfile($this->Testimonial);
        $data = $this->Testimonial->findById($id);
        if (!$data)
            throw new NotFoundException(__d('testimonials', 'No existe el testimonio solicitado'));

        $this->set('data', $data);
    }
    
    public function view($id) {
        $this->Driver->attachProfile($this->Testimonial);
        $data = $this->Testimonial->findById($id);
        if (!$data)
            throw new NotFoundException(__d('testimonials', 'No existe el testimonio solicitado'));

        $this->layout = 'testimonial_view';
        $this->set('data', $data);
    }
    
    public function set_featured($id) {
        $OK = $this->change_field($id, 'featured', true);
        
        //if(!$OK)
        
        $this->redirect($this->referer().'#testimonial'.$id);
    }
    public function unset_featured($id) {
        $OK = $this->change_field($id, 'featured', false);
        
        //if(!$OK)
        
        $this->redirect($this->referer().'#testimonial'.$id);
    }
    
    public function set_sample($id) {
        $OK = $this->change_field($id, 'use_as_sample', true);
        
        //if(!$OK)
        
        $this->redirect($this->referer().'#testimonial'.$id);
    }
    public function unset_sample($id) {
        $OK = $this->change_field($id, 'use_as_sample', false);
        
        //if(!$OK)
        
        $this->redirect($this->referer().'#testimonial'.$id);
    }
    
    private function change_field($testimonialId, $field, $value) {
        $this->Testimonial->id = $testimonialId;
        if(!$this->Testimonial->exists()) throw new NotFoundException ();
        
        return $this->Testimonial->save(array('Testimonial'=>array($field=>$value, 'modified'=>false)));
    }

    public function state_change($id, $state, $action = 'admin') {
        $this->Driver->attachProfile($this->Testimonial);//Tuve que ponerlo porque no funcionaba el profile, no se por que (antes funcionaba)
        $data = $this->Testimonial->findById($id);
        if (!$data)
            throw new NotFoundException('No existe el testimonio solicitado');

        if (!in_array($state, Testimonial::$statesValues))
            throw new NotFoundException('El estado no es válido');

        $save_data = array('id' => $id, 'state' => $state, 'modified' => false);            
        
        if ($this->Testimonial->save($save_data)) {
            
            // Enviar correo de aprobacion al chofer
            $state_str = Testimonial::$states[$state];
            if($state_str == 'approved') $this->_sendApprovedToDriver($data);
            
            if ($action == 'admin')
                return $this->redirect(array('action' => "admin/$id"));
            else
                return $this->redirect(array('action' => "view_filtered/$state_str#testimonial$id"));
        }
        $this->setErrorMessage('No se pudo cambiar el estado');
    }

    public function lang_change($id, $lang, $action = 'admin') {
        $data = $this->Testimonial->findById($id);
        if (!$data)
            throw new NotFoundException('No existe el testimonio solicitado');

        if (!in_array($lang, Testimonial::$langs))
            throw new NotFoundException('El idioma no es válido');

        $save_data = array('id' => $id, 'lang' => $lang, 'modified' => false);
        if ($this->Testimonial->save($save_data)) {
            $state_str = Testimonial::$states[$data['Testimonial']['state']];
            if ($action == 'admin')
                return $this->redirect(array('action' => "admin/$id"));
            else
                return $this->redirect(array('action' => "view_filtered/$state_str#testimonial$id"));
        }
        $this->setErrorMessage('No se pudo cambiar el idioma');
    }

    public function delete($id) {
        if (!$this->request->is('get'))
            throw new MethodNotAllowedException();

        if ($this->Testimonial->delete($id))
            return $this->redirect(array('action' => 'index'));
    }
    
    private function _getTestimonial($id) {
        $this->Testimonial->recursive = 3;
        $this->DriverTravel->unbindModel(array('hasOne' => array('TravelConversationMeta')));
        $this->Driver->unbindModel(array('hasAndBelongsToMany' => array('Locality')));
        $this->Driver->unbindModel(array('belongsTo' => array('Province')));
        return $this->Testimonial->findById($id);
    }

    private function _sendAdminMail($testimonial) {
        $vars = array('testimonial' => $testimonial['Testimonial'], 'driver' => $testimonial['Driver']);
        /*if (isset($data['Driver']['DriverProfile']['avatar_filepath'])) {
            $vars = array_merge($vars, array('driver_profile' => $data['Driver']['DriverProfile']));
        }*/ 

        if (isset($testimonial['DriverTravel']['Travel']))
            $vars = array_merge($vars, array('travel' => $testimonial['DriverTravel']['Travel'], 'user' => $testimonial['DriverTravel']['Travel']['User']));
        
        $to = 'yuniel@yotellevocuba.com';
        $subject = 'Nuevo testimonio';
        if(isset ($testimonial['Driver']['DriverProfile'])) $subject .= ' sobre '.$testimonial['Driver']['DriverProfile']['driver_name'];

        return EmailsUtil::email($to, $subject, $vars, 'no_responder', 'testimonial_new');
    }
    
    private function _sendVerificationMail($id) {
        $data = $this->Testimonial->findById($id);
        $vars['Testimonial'] = $data['Testimonial'];
        
        return EmailsUtil::email($data['Testimonial']['email'], __d('testimonial', 'Sobre YoTeLlevo'), $vars, 'no_responder', 'testimonial_verify');
    }
    
    private function _sendEmailToDriver($testimonial) {
        $vars = array(
            'driver_name'=>$testimonial['Driver']['DriverProfile']['driver_name'],
            'testimonial'=>$testimonial['Testimonial'], 
            'driver_nick'=>$testimonial['Driver']['DriverProfile']['driver_nick'],
        );
        return EmailsUtil::email(
                $testimonial['Driver']['username'], 
                'Tienes un nuevo testimonio', 
                $vars, 
                'super', 
                'new_testimonial2driver', 
                array('from_name'=>'Nuevo Testimonio, YoTeLlevo', 'from_email'=>'martin@yotellevocuba.com'));
    }
    
    private function _sendApprovedToDriver($testimonial) {        
        $vars = array(
            'driver_name'=>$testimonial['Driver']['DriverProfile']['driver_name'],
            'testimonial'=>$testimonial['Testimonial'], 
            'driver_nick'=>$testimonial['Driver']['DriverProfile']['driver_nick'],
        );
        return EmailsUtil::email(
                $testimonial['Driver']['username'], 
                'Comparte esta opinión sobre tí en tu Facebook!', 
                $vars, 
                'super', 
                'approved_testimonial2driver', 
                array('from_name'=>'Testimonio, YoTeLlevo', 'from_email'=>'martin@yotellevocuba.com'));
    }
    
    private function _sendApprovedReplyToTraveler($reply) {

        $vars = array(
            'driver_name'=>$reply['Testimonial']['Driver']['DriverProfile']['driver_name'],
            'testimonial'=>array('id'=>$reply['Testimonial']['id'], 'lang'=>$reply['Testimonial']['lang']),
            'driver_nick'=>$reply['Testimonial']['Driver']['DriverProfile']['driver_nick'],
            'traveler_name'=>$reply['Testimonial']['author'],
            'reply_text'=>$reply['TestimonialsReply']['reply_text'],
            
        );
        
        $lang = (isset($reply['Testimonial']['lang']))? 
                $reply['Testimonial']['lang']
                :'es';
        $subject = $reply['Testimonial']['Driver']['DriverProfile']['driver_name'].' respondió su opinión';
        if($lang == 'en') $subject = $reply['Testimonial']['Driver']['DriverProfile']['driver_name'].' replied your review';;
        
        return EmailsUtil::email(
                $reply['Testimonial']['email'], 
                $subject, 
                $vars, 
                'no_responder', 
                'approved_testimonial_reply2traveler',
                array('lang'=>$lang));
    }

    public function admin($id) {
        $data = $this->Testimonial->findById($id);
        if (!$data)
            throw new NotFoundException('No existe el testimonio solicitado');

        $this->set('testimonial', $data['Testimonial']);
    }

    public function enter_code() {
        if ($this->request->is('post')) {
            $driver_code = $this->request->data['Testimonial']['driver_code'];
            if (!$driver_code)
                return;

            $driver_code = strtolower($driver_code); // El codigo siempre va a estar en lowercase en la BD. Ver DriversController::edit_profile
            $driver = $this->DriverProfile->findByDriverCode($driver_code);
            if (!$driver) {
                // TODO: Mostrar una respuesta elegante donde se ayude al usuario
                $this->setErrorMessage(__d('testimonials', 'No se encontró un chofer con este código. Revíselo e intente de nuevo.'));
            } else{
                $this->DriverProfile->id = $driver['DriverProfile']['id'];
                $attempts = $driver['DriverProfile']['testimonial_attempts'] + 1;
                if( !$this->DriverProfile->saveField('testimonial_attempts', $attempts) )
                    CakeLog::write('testimonial_errors', "Error al actualizar 'testimonial_attempts', driver_code = $driver_code, attempt = $attempts"); 

                $this->redirect( array('action' => 'add', $driver_code) );
            }
        }
    }
    
    public function verify($token) {
        $data = $this->Testimonial->findByValidationToken($token);
        if(!$data)
           throw new NotFoundException( __d('testimonials', 'El token para la validación es incorrecto') );
        
        $this->Testimonial->id = $data['Testimonial']['id'];
        if( $this->Testimonial->saveField('validated', true) ){
            $this->setSuccessMessage( __d('testimonials', 'Su comentario ha sido validado') );
        }
        else $this->setErrorMessage( __d('testimonials', 'Ha ocurrido un error al salvar los datos') );
    }
    
    
    
    public function request_testimonial($conversationId) {
        // TODO: Optimizar el cargado de datos, que sobran muchos en la consulta y en los parametros que se le pasan al correo
        
        if($this->request->is('post')) {
            
            $this->DriverTravel->recursive = 2;
            $this->Driver->unbindModel(array('hasAndBelongsToMany' => array('Locality')));
            $data = $this->DriverTravel->findById($conversationId);
            if (!$data)
                throw new NotFoundException('Conversación inválida.');
            
            $vars = array();
            $vars['profile_data'] =array(
                'driver_name'=>$data['Driver']['DriverProfile']['driver_name'],
                'driver_code'=>$data['Driver']['DriverProfile']['driver_code'],
                'conversation'=>$conversationId,
                );
            if($this->request->data['Data']['name'] != null) $vars['traveler_name'] = $this->request->data['Data']['name'];

            $datasource = $this->TravelConversationMeta->getDataSource();
            $datasource->begin();
            
            // Coger el idioma del formulario que viene
            $lang = (isset($this->request->data['Data']['lang']))?$this->request->data['Data']['lang']:$data['User']['lang'];

            $subject = 'Puedes agradecer a tu chofer, '.Driver::shortenName($data['Driver']['DriverProfile']['driver_name']).', por su servicio aquí en Cuba';
            if($lang == 'en') $subject = 'You can thank your driver, '.Driver::shortenName($data['Driver']['DriverProfile']['driver_name']).', for his service here in Cuba';

            $to = $data['User']['username'];
            $OK = EmailsUtil::email($to, $subject, $vars, 'coo', 'request_testimonial', array('lang'=>$lang));
            if ($OK) {
                $this->TravelConversationMeta->id = $conversationId;
                $OK = $this->TravelConversationMeta->saveField('testimonial_requested', true);
            }

            if ($OK) {
                $this->setSuccessMessage('Pedido de testimonio enviado');
                $datasource->commit();
            } else {
                $this->setErrorMessage('Falló el pedido de testimonio');
                $datasource->rollback();
            }

            return $this->redirect($this->referer());
            
        }
        
        
    }
    
    public function reply($testimonial_id, $driver_reply_token) {
        $this->layout = 'drivers_panel';
        
        if($this->request->is('post')) {
            $testimonial_id = $this->request->data['TestimonialsReply']['testimonial_id'];
            $driver_reply_token = $this->request->data['TestimonialsReply']['driver_reply_token'];
        }
        
        $data = $this->Testimonial->findById($testimonial_id);
        
        // Sanity check
        if (!$data) throw new NotFoundException('No existe el testimonio solicitado');
        if ($data['Testimonial']['driver_reply_token'] != $driver_reply_token) //verificamos si es el token correspondiente 
            throw new NotFoundException('Token inválido');
        
        if ($this->request->is('post')) {
            
            $this->loadModel('TestimonialsReply');
            
            //Adicionando manualmente el reply_by
            $this->request->data['TestimonialsReply']['reply_by'] = 'driver';

            $OK = $this->TestimonialsReply->save($this->request->data);
            if ($OK) $this->setSuccessMessage('Su respuesta ha sido enviada');
            else $this->setErrorMessage('Su respuesta no ha podido ser enviada');

            return $this->redirect(array('action' => "reply/".$this->request->data['TestimonialsReply']['testimonial_id']."/".$this->request->data['TestimonialsReply']['driver_reply_token']));
            
        } else {                
            $driver = $this->Driver->find('first', array(
                'conditions'=>array(
                    'Driver.id'=>$data['Testimonial']['driver_id']), 'contain'=>array('DriverProfile')
                )
            );
            $data = array_merge($data, $driver);
            $this->set('data', $data);
        }
                
    }
    
    public function replies($filtro = 'pending') {
        $this->loadModel('TestimonialsReply');  
        $this->TestimonialsReply->recursive = 3;
        $this->Driver->attachProfile($this->Testimonial);
        $this->TestimonialsReply->bindModel(array('belongsTo'=>array('Testimonial'))); 
        $this->Testimonial->bindModel(array('belongsTo'=>array('Driver'))); 
        
        $conditions = array();
        if ($filtro != 'all')
            $conditions = array('TestimonialsReply.state =' => TestimonialsReply::$statesValues[$filtro]);

        $this->Paginator->settings = array('limit' => 10);
        $replies = $this->Paginator->paginate('TestimonialsReply', $conditions);

        $this->set('replies', $replies);
        $this->set('filter_applied', $filtro);       

        $this->render('all_replies');
    }
    
    public function reply_state_change($id, $state, $action = 'admin') {        
        $this->loadModel('TestimonialsReply');
        $this->TestimonialsReply->recursive = 3;        
        $data = $this->TestimonialsReply->findById($id);
        if (!$data)
            throw new NotFoundException('No existe la respuesta solicitada');

        if (!in_array($state, TestimonialsReply::$statesValues))
            throw new NotFoundException('El estado no es válido');

        $save_data = array('id' => $id, 'state' => $state);
        if ($this->TestimonialsReply->save($save_data)) {
            
           // Enviar correo al viajero
           $state_str = TestimonialsReply::$states[$state];
           if($state_str == 'approved') $this->_sendApprovedReplyToTraveler($data);
            
            if ($action == 'admin')
                return $this->redirect(array('action' => "admin/$id"));
            else
                return $this->redirect(array('action' => "replies/$state_str#reply$id"));
        }
        $this->setErrorMessage('No se pudo cambiar el estado');
    }
    
}

?>
