<?php
defined('BASEPATH') or exit('No direct script access allowed');

class Auth_model extends CI_Model
{
     protected $excluded_uris = [];
     public function hasPermission(string $uri = null)
     {
          if (!$this->checkPermissions($uri)) {
               httpReponseError('Unauthorized Access!', 401);
          }
          return true;
     }

     private function checkPermissions(string $uri = null)
     {
          // verify api key for app
          $api_key = $this->getHeaderApiKey();
          $where = ['api_key' => $api_key, 'request' => 'approved'];
          if ($this->app->where($where)->num_rows() === 0) {
               httpReponseError('Unauthorized Access! Invalid Api Key', 401);
               return false;
          }
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
          $token = $this->getHeaderToken();
          $where = [
               'status' => 'active',
               'token' => $token,
          ];
          $user = $this->user->all()->select('token')->where($where)->get()->row();
          $sysuser = $this->sysuser->all()->select('token')->where($where)->get()->row();

          if (!$user && !$sysuser) {
               $this->session->set_flashdata('auth_error', "This account doesn't exist! or its disabled!");
               $this->session->set_flashdata('auth_error_code', 5);
               return false;
          }

          if (empty($token) || $token === null) {
               $this->session->set_flashdata('auth_error', "Invalid access token!");
               $this->session->set_flashdata('auth_error_code', 12);
               return false;
          }
          return $user ? $user : $sysuser;
     }

     public function getHeaderToken()
     {
          $auth = explode(' ', $this->input->get_request_header('Authorization'));
          if (sizeof($auth) > 1) return $auth[1];
          return null;
     }

     public function getHeaderApiKey()
     {
          return $this->input->get_request_header('Api-Key');
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
          $user = $this->user->all()->select(['password', 'token'])->where($where)->get()->row();
          if (!$user) {
               $this->session->set_flashdata('auth_error', "This account doesn't exist!");
               $this->session->set_flashdata('auth_error_code', 4);
               return false;
          }
          if ($user->email_verified_at === null) {
               //      $this->session->set_flashdata('auth_error', "Email is not verified. Check your email for your verification link and click on it to verify.");
               //      $this->session->set_flashdata('auth_error_code', 6);
               //      return false;
          }

          if ($user->phone_verified_at === null) {
               $this->session->set_flashdata('auth_error', "Phone number is not verified!");
               $this->session->set_flashdata('auth_error_code', 7);
               return false;
          }

          if ($user->status === 'inactive') {
               $this->session->set_flashdata('auth_error', "This account is de-activated or suspended!");
               $this->session->set_flashdata('auth_error_code', 8);
               return false;
          }

          if (password_verify($pass, $user->password)) {
               $this->user->update($user->id, ['last_login_at' => date('Y-m-d H:i:s', strtotime('now Africa/Accra'))]);

               return $this->user->all()->select(['token'])->where($where)->get()->row();
          }
          $this->session->set_flashdata('auth_error', "Invalid credentials");
          $this->session->set_flashdata('auth_error_code', 9);

          return false;
     }

     public function loginSysUser(string $username = null, string $pass = null)
     {
          $where = [
               'username' => $username,
          ];
          $user = $this->sysuser->all()->select(['password', 'token'])->where($where)->get()->row();
          if (!$user) {
               $this->session->set_flashdata('auth_error', "This account doesn't exist!");
               $this->session->set_flashdata('auth_error_code', 4);
               return false;
          }
          if ($user->email_verified_at === null) {
               $this->session->set_flashdata('auth_error', "Email is not verified. Check your email for your verification link and click on it to verify.");
               $this->session->set_flashdata('auth_error_code', 6);
               return false;
          }

          if ($user->phone_verified_at === null) {
               // $this->session->set_flashdata('auth_error', "Phone number is not verified!");
               // $this->session->set_flashdata('auth_error_code', 7);
               // return false;
          }

          if ($user->status === 'inactive') {
               $this->session->set_flashdata('auth_error', "This account is de-activated or suspended!");
               $this->session->set_flashdata('auth_error_code', 8);
               return false;
          }

          if (password_verify($pass, $user->password)) {
               $this->sysuser->update($user->id, ['last_login_at' => date('Y-m-d H:i:s', strtotime('now Africa/Accra'))]);

               return $this->sysuser->all()->select(['token'])->where($where)->get()->row();
          }
          $this->session->set_flashdata('auth_error', "Invalid credentials");
          $this->session->set_flashdata('auth_error_code', 9);

          return false;
     }

     public function error()
     {
          return $this->session->flashdata('auth_error');
     }
     public function error_code()
     {
          return $this->session->flashdata('auth_error_code');
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
