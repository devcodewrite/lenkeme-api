<?php
defined('BASEPATH') or exit('No direct script allowed');

class Auth extends CI_Controller
{
    /**
     * Show login form
     * @return string html view
     */
    public function index()
    {
        $data = [
            'pageTitle' => 'Login - '.config_item('app_name'),
        ];
        $this->load->view('welcome_message', $data);
    }

    /**
     * Authenticate user and login
     * print json Response
     */
    public function login ()
    {
        $user = auth()->loginUser(
            inputJson('username'),
            inputJson('password'));
       if($user){
            $out = [
                'status' => true,
                'data' => $user,
                'message' => 'You have logged in successfully!'
            ];
        }
        else {
            $out = [
                'status' => false,
                'message' => auth()->error()
            ];
        }
        httpResponseJson($out);
    }

     /**
     * Store a resource
     * print json Response
     */
    public function register()
    {
        $record = inputJson();
        $user  = $this->user->create($record);
        if ($user) {
            $out = [
                'data' => $user,
                'input' => $record,
                'status' => true,
                'message' => 'Users created successfully!'
            ];
        } else {
            $out = [
                'status' => false,
                'message' => "Users couldn't be created!"
            ];
        }

        httpResponseJson($out);
    }
}