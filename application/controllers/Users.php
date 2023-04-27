<?php
defined('BASEPATH') or exit('No direct script allowed');

class Users extends MY_Controller
{
    /**
     * Show a list of resources
     * @return http json
     */
    public function index($id = null)
    {
        if ($id !== null) {
            $gate = auth()->can('view', 'user');
            if ($gate->allowed()) {
                $user  = $this->user->find($id);
                if ($user) {
                    $out = [
                        'data' => $user,
                        'status' => true,
                    ];
                } else {
                    $out = [
                        'status' => false,
                        'message' => "User not found!"
                    ];
                }
            } else {
                $out = [
                    'status' => false,
                    'message' => $gate->message
                ];
            }
            httpResponseJson($out);
        } else {
            $gate = auth()->can('viewAny', 'user');
            if ($gate->denied()) {
                $out = [
                    'status' => false,
                    'message' => $gate->message
                ];
                httpReponseError($out, 401);
                return;
            }

            $start = inputJson('start', 0);
            $length = inputJson('length', 100);
            $inputs = inputJson();
            $query = $this->user->all();

            $where = [];

            if ($this->input->get('status'))
                $where = array_merge($where, ['users.status' => $inputs['status']]);

            if ($this->input->get('user_type'))
                $where = array_merge($where, ['users.user_type' => $inputs['user_type']]);

            $query->where($where);

            $out = json($query, $start, $length, $inputs);
            $out = array_merge($out, [
                'input' => $this->input->get(),
            ]);
            httpResponseJson($out);
        }
    }

    /**
     * Show a list of resources
     * @return http json
     */
    public function artisans($id = null)
    {
            $gate = auth()->can('viewAny', 'user');
            if ($gate->denied()) {
                $out = [
                    'status' => false,
                    'message' => $gate->message
                ];
                httpReponseError($out, 401);
                return;
            }

            $start = inputJson('start', 0);
            $length = inputJson('length', 100);
            $inputs = inputJson();
            $query = $this->user->all();

            $where = [
                'users.status' => 'active',
                'users.user_type' => 'artisan',
            ];
            $query->where($where)
            ->where('users.phone_verified_at !=', null);

            $out = json($query, $start, $length, $inputs);
            $out = array_merge($out, [
                'input' => $this->input->get(),
            ]);
            httpResponseJson($out);
    }
    /**
     * Show a list of post resources
     * @return http json
     */
    public function posts($id = null)
    {
        $user  = $this->user->find($id);
        if (!$user) {
            $out = [
                'status' => false,
                'message' => "User not found!"
            ];
            httpReponseError($out, 401);
        }

        $gate = auth()->can('viewAny', 'post');
        if ($gate->denied()) {
            $out = [
                'status' => false,
                'message' => $gate->message
            ];
            httpReponseError($out, 401);
            return;
        }

        $start = inputJson('start', 0);
        $length = inputJson('length', 100);
        $inputs = inputJson();
        $query = $this->post->all();

        $where = ['user_id' => $user->id];

        if ($this->input->get('status'))
            $where = array_merge($where, ['posts.status' => $inputs['status']]);

        $query->where($where);

        $out = json($query, $start, $length, $inputs);
        $out = array_merge($out, [
            'input' => $this->input->get(),
        ]);
        httpResponseJson($out);
    }

    /**
     * Update a resource
     * print json Response
     */
    public function update(int $id = null)
    {
        $gate = auth()->can('update', 'user', $this->user->find($id));
        if ($gate->allowed()) {
            $record = inputJson();
            $user = $this->user->update($id, $record);
            if ($user) {
                $out = [
                    'data' => $user,
                    'input' => $record,
                    'status' => true,
                    'message' => 'User updated successfully!'
                ];
            } else {
                $out = [
                    'status' => false,
                    'message' => "User couldn't be updated!"
                ];
            }
        } else {
            $out = [
                'status' => false,
                'message' => $gate->message
            ];
        }

        httpResponseJson($out);
    }
}
