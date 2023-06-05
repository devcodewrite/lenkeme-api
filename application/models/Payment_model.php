<?php
defined('BASEPATH') OR exit('No direct script access allowed');

/**
 * Sms model class 
 *
 * @copyright  @2022 Codewrite Technology Ltd
 * @version    Release: version 1.0.0
 * @since      Class available since Release 1.0.0
 */ 

class Payment_model extends CI_Model {
    
    var $table = 'payments';

    /**
     * @return Response
     */
    
}
class Response{
    public $status;
    public $message;

    public function __construct(string $message = null, bool $status = false) {
        $this->message = $message;
        $this->status = $status;
    }
    public function charge():bool
    {
        return $this->status;
    }
    public function message()
    {
        return $this->message;
    }
}