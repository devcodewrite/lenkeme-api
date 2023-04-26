<?php
defined('BASEPATH') or exit('Direct acess is not allowed');

class Mail_model extends CI_Model
{
    public function sendResetPasswordLink($record)
    {
        if(!$record) return;

        $where = $this->user->extract($record);

        $user = $this->user->where($where)->row();
        if(!$user){
            $this->session->set_flashdata('error_message', "Your email doesn't exist in this system!");
        }

        $this->email->from($this->config->item('isave_email'), 'iSave');
        $this->email->to($user->email);
        $this->email->subject('Send Email Codeigniter');
        $this->email->message('The email send using codeigniter library');

        return $this->email->send();
        
    }
}