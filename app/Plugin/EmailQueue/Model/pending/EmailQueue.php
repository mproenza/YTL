<?php

App::uses('AppModel', 'Model');

require_once 'EmailAttachment.php';

/**
 * EmailQueue model
 *
 */
class EmailQueue extends AppModel {

    public $order = 'id DESC';
    /**
     * Name
     *
     * @var string $name
     * @access public
     */
    public $name = 'EmailQueue';
    /**
     * Database table used
     *
     * @var string
     * @access public
     */
    public $useTable = 'email_queue';
    
    public $hasMany = 'EmailAttachment';

    /**
     * Stores a new email message in the queue
     *
     * @param mixed $to email or array of emails as recipients
     * @param array $data associative array of variables to be passed to the email template
     * @param array $options list of options for email sending. Possible keys:
     *
     * - subject : Email's subject
     * - send_at : date time sting representing the time this email should be sent at (in UTC)
     * - template :  the name of the element to use as template for the email message
     * - layout : the name of the layout to be used to wrap email message
     * - format: Type of template to use (html, text or both)
     * - config : the name of the email config to be used for sending
     *
     * @param array $returnData a reference to get extra values from what happens. So far, the array can be filled with the following data:
     * 
     * - attachments_ids: an array with the ids of the attachments just saved
     * 
     * @return void
     */
    public function enqueue($to, array $data, $options = array(), array &$returnData = null) {
        $defaults = array(
            'subject' => '',
            'send_at' => gmdate('Y-m-d H:i:s'),
            'template' => 'default',
            'layout' => 'default',
            'format' => 'both',
            'template_vars' => $data,
            'config' => 'default',
            'attachments' => array(),
            'savepath' => './tmp/files/',
            'lang' => Configure::read('default_language')
        );

        $email = array('EmailQueue'=>$options + $defaults);        
        
        if (!is_array($to)) {
            $to = array($to);
        }
        
        $attachments = array();        
        
        if(isset ($options['attachments']) && $options['attachments'] != null && is_array($options['attachments']) && !empty ($options['attachments'])) {
            foreach ($options['attachments'] as $key => $value) {
                $attachments[] = array('filename'=>$key, 'contents'=>$value['contents'], 'mimetype'=>$value['mimetype']);
            }
        }
        
        $datasource = $this->getDataSource();
        $datasource->begin();

        $OK = true;
        foreach ($to as $t) {
            $email['EmailQueue']['to'] = $t;
            $this->create();
            
            /*$email['EmailAttachment'] = $attachments;
            $this->saveAssociated($email);*/
            
            $OK = $this->save($email);
            
            if($OK) {
                $emailId = $this->getLastInsertID();
            
                $attachmentModel = new EmailAttachment();
                
                if($returnData != null) $returnData['attachments_ids'] = array();
                foreach ($attachments as $a) {
                    $a = array('EmailAttachment'=>$a);
                    $a['EmailAttachment']['email_queue_id'] = $emailId;

                    $attachmentModel->create();
                    $OK = $attachmentModel->save($a);
                    
                    if($returnData != null) $returnData['attachments_ids'][] = $attachmentModel->getLastInsertID();
                    
                    if(!$OK) break;
                }
            }
            
            if(!$OK) break;
            
        }
        
        if($OK) $datasource->commit();
        else $datasource->rollback();
        
        return $OK;
    }

    /**
     * Returns a list of queued emails that needs to be sent
     *
     * @param integer $size, number of unset emails to return
     * @return array list of unsent emails
     * @access public
     */
    public function getBatch($size = 10) {
        $this->getDataSource()->begin();

        $emails = $this->find('all', array(
                'limit' => $size,
                'conditions' => array(
                    'EmailQueue.sent' => false,
                    'EmailQueue.send_tries <=' => 3,
                    'EmailQueue.send_at <=' => gmdate('Y-m-d H:i:s'),
                    'EmailQueue.locked' => false
                ),
                'order' => array('EmailQueue.created' => 'ASC')
            ));

        if (!empty($emails)) {
            $ids = Set::extract('{n}.EmailQueue.id', $emails);
            $this->updateAll(array('locked' => true), array('EmailQueue.id' => $ids));
        }

        $this->getDataSource()->commit();
        return $emails;
    }

    /**
     * Releases locks for all emails in $ids
     *
     * @return void
     * */
    public function releaseLocks($ids) {
        $this->updateAll(array('locked' => false), array('EmailQueue.id' => $ids));
    }

    /**
     * Releases locks for all emails in queue, useful for recovering from crashes
     *
     * @return void
     * */
    public function clearLocks() {
        $this->updateAll(array('locked' => false));
    }

    /**
     * Marks an email from the queue as sent
     *
     * @param string $id, queued email id
     * @return boolean
     * @access public
     */
    public function success($id) {
        $this->id = $id;
        return $this->saveField('sent', true);
    }

    /**
     * Marks an email from the queue as failed, and increments the number of tries
     *
     * @param string $id, queued email id
     * @return boolean
     * @access public
     */
    public function fail($id) {
        $this->id = $id;
        $tries = $this->field('send_tries');
        return $this->saveField('send_tries', $tries + 1);
    }

    /**
     * Converts array data for template vars into a json serialized string
     *
     * @param array $options
     * @return boolean
     * */
    public function beforeSave($options = array()) {        
        if (isset($this->data[$this->alias]['template_vars'])) {
            $this->data[$this->alias]['template_vars'] = json_encode($this->encode($this->data[$this->alias]['template_vars']));
        }
        if (isset($this->data[$this->alias]['subject'])) {
            $this->data[$this->alias]['subject'] = $this->encode($this->data[$this->alias]['subject']);
        }

        return parent::beforeSave($options);
    }

    /**
     * Converts template_vars back into a php array
     *
     * @param array $results
     * @param boolean $primary
     * @return array
     * */
    public function afterFind($results, $primary = false) {
        if (!$primary) {
            return parent::afterFind($results, $primary);
        }

        foreach ($results as &$r) {
            if (!isset($r[$this->alias]['template_vars'])) {
                return $results;
            }
            $r[$this->alias]['template_vars'] = json_decode($this->decode($r[$this->alias]['template_vars']), true);
            $r[$this->alias]['subject'] = $this->decode($r[$this->alias]['subject']);
        }

        return $results;
    }
    
    private function encode(array &$what) {
        if(is_string($what)) $what = utf8_encode(mb_convert_encoding($what, "HTML-ENTITIES", "UTF-8,ISO-8859-1"));
        else if(is_array($what))
            foreach ($what as &$w) {
                if(!is_array($w) && is_string($w)) $w = utf8_encode(mb_convert_encoding($w, "HTML-ENTITIES", "UTF-8,ISO-8859-1"));
                else if(is_array($w))$this->encode($w);
            }
            
        return $what;
    }
    
    private function decode($what) {
        return utf8_decode($what);
    }
    
    /*private function encode(array &$what) {
        foreach ($what as &$w) {
            if(!is_array($w) && is_string($w)) $w = mb_convert_encoding(utf8_encode($w), "HTML-ENTITIES", "UTF-8");
            else if(is_array($w))$this->encode($w);
        }
        return $what;
    }
    
    private function decode($what) {
        return utf8_decode(mb_convert_encoding($what, "UTF-8", "HTML-ENTITIES"));
    }*/
    
    /*private function encode(array &$what) {
        foreach ($what as &$w) {
            if(!is_array($w) && is_string($w)) $w = mb_convert_encoding(utf8_encode($w), "HTML-ENTITIES", "UTF-8");
            else if(is_array($w))$this->encode($w);
        }
        return $what;
    }
    
    private function decode($what) {
        return $what;
    }*/
    
    
    /*private function encode(array &$what) {
        foreach ($what as &$w) {
            if(!is_array($w) && is_string($w)) $w = utf8_encode($w);
            else if(is_array($w))$this->encode($w);
        }
        return $what;
    }
    
    private function decode($what) {
        return mb_convert_encoding($what, "HTML-ENTITIES", "UTF-8");
    }*/
    
}
