<?php
defined('BASEPATH') or exit('No direct script allowed');

class Auth extends MY_Controller
{
    /**
     * Show login form
     * @return string html view
     */
    public function index()
    {
        $data = [
            'pageTitle' => 'Login - ' . config_item('app_name'),
        ];
        $this->load->view('welcome_message', $data);
    }

    /**
     * Authenticate user and login
     * print json Response
     */
    public function login()
    {
        $username = inputJson('username');
        $username = $username ? $username : $this->input->post('username');

        if ($this->isPhoneNumber($username)) {
            $user = $this->user->where(['phone' => $username])->row();
            if ($user) $username = $user->username;
        } else if ($this->isEmail($username)) {
            $user = $this->user->where(['email' => $username])->row();
            if ($user) $username = $user->username;
        }
        $password = inputJson('password');
        $password = $password ? $password : $this->input->post('password');
        $user = auth()->loginUser(
            $username,
            $password
        );
        if ($user) {
            $out = [
                'status' => true,
                'data' => $user,
                'message' => 'You have logged in successfully!'
            ];
        } else {
            $error_code = auth()->error_code();
            $out = [
                'status' => false,
                'code' => $error_code,
                'message' => auth()->error()
            ];

            if ($error_code == 7) {
                $user = $this->user->where(['username' => $username])->row();
                $otp = random_int(1000, 9999);
                $temp = 'Hi {$firstname}, your OTP code is: {$code}. Do not share this with anyone.';
                $sms = $this->sms->sendPersonalised($temp, [
                    [
                        'phone' => $user->phone,
                        'firstname' => $user->firstname,
                        'code' => $otp
                    ]
                ]);
                if ($sms->sent()) $this->user->update($user->id, ['otp_code' => $otp]);
                $out = array_merge($out, [
                    'data' => (object)['phone' => $user->phone]
                ]);
            }
        }
        httpResponseJson($out);
    }

    /**
     * Authenticate user and login
     * print json Response
     */
    public function system_login()
    {
        $username = inputJson('username');
        $username = $username ? $username : $this->input->post('username');

        if ($this->isPhoneNumber($username)) {
            $user = $this->sysuser->where(['phone' => $username])->row();
            if ($user) $username = $user->username;
        }

        $password = inputJson('password');
        $password = $password ? $password : $this->input->post('password');
        $user = auth()->loginSysUser(
            $username,
            $password
        );
        if ($user) {
            $out = [
                'status' => true,
                'data' => $user,
                'message' => 'You have logged in successfully!'
            ];
        } else {
            $error_code = auth()->error_code();
            $out = [
                'status' => false,
                'code' => $error_code,
                'message' => auth()->error()
            ];

            if ($error_code == 7) {
                $user = $this->sysuser->where(['username' => $username])->row();
                $otp = random_int(1000, 9999);
                $temp = 'Hi {$firstname}, your OTP code is: {$code}. Do not share this with anyone.';
                $sms = $this->sms->sendPersonalised($temp, [
                    [
                        'phone' => $user->phone,
                        'firstname' => $user->firstname,
                        'code' => $otp
                    ]
                ]);
                if ($sms->sent()) $this->sysuser->update($user->id, ['otp_code' => $otp]);
                $out = array_merge($out, [
                    'data' => (object)['phone' => $user->phone]
                ]);
            }
        }
        httpResponseJson($out);
    }

    private function isPhoneNumber($username = null): bool
    {
        return preg_match('/^[0-9]{10}+$/', $username);
    }

    private function isEmail($username = null): bool
    {
        return filter_var($username, FILTER_VALIDATE_EMAIL);
    }
    /**
     * Store a resource
     * print json Response
     */
    public function register()
    {
        $record = inputJson();
        $record = $record ? $record : $this->input->post();

        $user  = $this->user->create($record);
        $error = $this->session->flashdata('error_message');
        $error_code = $this->session->flashdata('error_code');

        if ($user) {
            $otp = random_int(1000, 9999);
            $temp = 'Hi {$firstname}, your OTP code is: {$code}. Do not share this with anyone.';
            $sms = $this->sms->sendPersonalised($temp, [
                [
                    'phone' => $user->phone,
                    'firstname' => $user->display_name,
                    'code' => $otp
                ]
            ]);
            if ($sms->sent()) $this->user->update($user->id, ['otp_code' => $otp]);
            $out = [
                'data' => $user,
                'input' => $record,
                'status' => true,
                'message' => 'Users created successfully!' . ($sms->sent() ? '' : ' Otp code could not be sent!')
            ];
        } else {
            $out = [
                'status' => false,
                'input' => $record,
                'code' => $error_code ? $error_code : 0,
                'message' => $error ? $error : "Users couldn't be created!",
            ];
        }

        httpResponseJson($out);
    }

    /**
     * Store a resource
     * print json Response
     */
    public function verify_otp()
    {
        $record = inputJson();
        $record = $record ? $record : $this->input->post();

        $where = [
            'phone' => $record['phone'],
        ];
        $user  = $this->user->all()
            ->select(['otp_code'])
            ->where($where)
            ->get()
            ->row();

        if (!$user) {
            $out = [
                'status' => false,
                'code' => 10,
                'message' => "Phone number doesn't exist in our system!"
            ];
            return httpResponseJson($out);
        }
        if ($user->otp_code !== $record['otp_code']) {
            $out = [
                'status' => false,
                'code' => 11,
                'message' => "Invaild otp code!"
            ];
            return httpResponseJson($out);
        }
        $data = [
            'phone_verified_at' => date('Y-m-d H:i:s', strtotime('now Africa/Accra')),
            'phone' => $user->new_phone ? $user->new_phone : $user->phone,
            'new_phone' => null,
            'otp_code' => null,
        ];

        if ($user->last_login_at === null) {
            $data = array_merge($data, ['status' => 'active', 'token' => sha1($user->id . uniqid())]);
        }

        $user = $this->user->update($user->id, $data);
        $error = $this->session->flashdata('error_message');
        if ($user) {
            $out = [
                'data' => $user,
                'input' => $record,
                'status' => true,
                'message' => 'Otp verified successfully!'
            ];
        } else {
            $out = [
                'status' => false,
                'message' => $error ? $error : "We couldn't verify otp code. Please try again!"
            ];
        }

        httpResponseJson($out);
    }

    /**
     * Store a resource
     * print json Response
     */
    public function send_otp()
    {
        $record = inputJson();
        $record = $record ? $record : $this->input->post();

        $where = [
            'phone' => $record['phone'],
        ];
        $user  = $this->user->all()
            ->where($where)
            ->get()
            ->row();

        if (!$user) {
            $out = [
                'status' => false,
                'code' => 10,
                'message' => "Phone number doesn't exist in our system!"
            ];
        } else {
            $otp = random_int(1000, 9999);
            $temp = 'Hi {$firstname}, your OTP code is: {$code}. Do not share this with anyone.';
            $sms = $this->sms->sendPersonalised($temp, [
                [
                    'phone' => $record['new_phone'],
                    'firstname' => $user->display_name,
                    'code' => $otp
                ]
            ]);
            if ($sms->sent()) $this->user->update($user->id, ['otp_code' => $otp]);

            $out = [
                'input' => $record,
                'status' => true,
                'message' => 'Otp send successfully!'
            ];
        }
        httpResponseJson($out);
    }
}
