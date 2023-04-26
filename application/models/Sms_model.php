<?php
defined('BASEPATH') OR exit('No direct script access allowed');

include_once (APPPATH.'Zenoph/Notify/AutoLoader.php');

use Zenoph\Notify\Enums\AuthModel;
use Zenoph\Notify\Request\NotifyRequest;
use Zenoph\Notify\Request\SMSRequest;
use Zenoph\Notify\Enums\TextMessageType;
use Zenoph\Notify\Store\MessageReport;

/**
 * Sms model class 
 *
 * @copyright  @2022 Codewrite Technology Ltd
 * @version    Release: version 1.0.0
 * @since      Class available since Release 1.0.0
 */ 

class Sms_model extends CI_Model {
    
    var $table = 'sms';
    var $hasOne = ['users' => 'added_by'];

    /**
     * @return Response
     */
    public function sendPersonalised(string $msg,array $data):Response
    {
        $response = new Response();
        $smsUnits = $this->setting->get('sms_units', 0);

        if($smsUnits <= 0) return new Response('Insufficient sms units!',false);
        
        try {
            NotifyRequest::setHost($this->config->item('sms_host'));
            if(ENVIRONMENT !== 'development'){
                NotifyRequest::useSecureConnection(true);
            }
            
            // initialise request
            $smsReq = new SMSRequest();
            $smsReq->setAuthModel(AuthModel::API_KEY);
            $smsReq->setAuthApiKey($this->config->item('sms_api_key'));
            
            $smsReq->setSender($this->config->item('sms_sender_id')); 
            $smsReq->setMessage($msg);
            $smsReq->setMessageType(TextMessageType::TEXT);
            
            $smsCount = 0;
            // add personalised data to destinations
            foreach ($data as $item){
                $phone = $item['phone'];
                unset($item['phone']);
                $values = array_values($item);
                $smsCount++;
                $smsReq->addPersonalisedDestination($phone, false, $values);
            }
            
            // submit must be after the loop
            $msgResp = $smsReq->submit();
            if($msgResp->getHttpStatusCode()){
                $this->setting->set('sms_units', $smsUnits - $smsCount);
                $response->status = true;
                $response->message = "Message sent successfully!";
            }
            else {
                $response->status = false;
                $response->message = "Message couldn't be sent!";
            }
        } 
        
        catch (\Exception $ex) {
           $response->status = false;
           $response->message = $ex->getMessage();
        }

        return $response;
    }
}
class Response{
    public $status;
    public $message;

    public function __construct(string $message = null, bool $status = false) {
        $this->message = $message;
        $this->status = $status;
    }
    public function sent():bool
    {
        return $this->status;
    }
    public function message()
    {
        return $this->message;
    }
}