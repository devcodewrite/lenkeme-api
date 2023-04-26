<?php
defined('BASEPATH') or exit('No direct script access allowed');

class Auth_model extends CI_Model
{
     protected $excluded_uris = [
     ];

     public function hasPermission(string $uri = null)
     {
          if (!$this->checkPermissions($uri)) {
               httpReponseError('Unauthorized Access!', 401);
          }
     }

     public function checkPermissions(string $uri = null)
     {
          if (!$uri) return false;

          if (in_array($uri, $this->excluded_uris)) return true;

          return true;
     }

     /**
      * check authenticated user can perform $action on $moduleName
      */
     public function can(string $action = '', string $moduleName = '', $module = null)
     {
          if ($action === '' || $moduleName === '') return false;

          return $this->{$moduleName}->{'can' . ucfirst($action)}(auth()->user(), $module);
     }

     public function canAny(array $actions = [], string $moduleName = '', $module = null)
     {
          if (sizeof($actions) === 0 || $moduleName === '') return false;
          foreach ($actions as $key => $action) {
               $gate = $this->{$moduleName}->{'can' . ucfirst($action)}(auth()->user(), $module);
               if ($gate->allowed()) return $gate;
          }

          return $this->deny();
     }

     public function allow(string $message = null)
     {
          return new AuthResponse(true, $message);
     }

     public function deny(string $message = null)
     {
          return new AuthResponse(false, $message);
     }

     public function authorized()
     {
          $where = [
               'status' => 'active',
               'token' => $this->getHeaderToken(),
          ];
          $user = $this->user->where($where)->row();
          if (!$user) {
               $this->session->set_flashdata('auth_error', "This account doesn't exist! or its closed!");
               return false;
          }
          return $user;
     }

     public function getHeaderToken()
     {
          return explode(' ',$this->input->get_request_header('Authorization'))[1];
     }

     public function user()
     {
          return $this->authorized();
     }

     public function loginUser(string $username = null, string $pass = null)
     {
          $where = [
               'username' => $username,
          ];
          $user = $this->user->all()->select(['password','token'])->where($where)->get()->row();
          if (!$user) {
               $this->session->set_flashdata('auth_error', "This account doesn't exist!");
               return false;
          }
          if($user->email_verified_at === null){
         //      $this->session->set_flashdata('auth_error', "Email is not verified. Check your email for your verification link and click on it to verify.");
         //      return false;
          }

          if($user->phone_verified_at === null){
               $this->session->set_flashdata('auth_error', "Phone number is not verified!");
               return false;
          }

          if($user->status === 'inactive'){
               $this->session->set_flashdata('auth_error', "This account is de-activated or suspended!");
               return false;
          }
     
          if (password_verify($pass, $user->password)) {
               return $this->user->all()->select(['token'])->where($where)->get()->row();
          }
          $this->session->set_flashdata('auth_error', "Invalid credentials");

          return false;
     }

     public function error()
     {
          return $this->session->flashdata('auth_error');
     }
}

class AuthResponse
{
     public $message;
     protected $status;

     public function __construct(bool $status, $message = null)
     {
          $this->status = $status;
          $this->message = $message;
     }
     public function allowed()
     {
          return $this->status;
     }
     public function denied()
     {
          return !$this->status;
     }
}
