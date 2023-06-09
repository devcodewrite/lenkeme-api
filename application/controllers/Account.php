<?php
defined('BASEPATH') or exit('No direct script allowed');

class Account extends MY_Controller
{
    public function __construct()
    {
        parent::__construct();
        if (!auth()->authorized()) {
            httpReponseError('Unauthorized Access!', 401);
        }
    }
    /**
     * Update a resource
     * print json Response
     */
    public function index()
    {
        $user = auth()->user();
        $gate = auth()->can('view', 'user', $user);
        if ($gate->allowed()) {
            if ($user) {
                $user = $this->user->find($user->id);
                $out = [
                    'data' => $user,
                    'status' => true,
                    'message' => ''
                ];
            } else {
                $out = [
                    'status' => false,
                    'message' => "No user found!"
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

    /**
     * Update a resource
     * print json Response
     */
    public function update()
    {
        $user = auth()->user();
        $gate = auth()->can('update', 'user', $user);
        if ($gate->allowed()) {
            $record = inputJson();
            $record = $record ? $record : $this->input->post();

            $user = $this->user->update($user->id, $record);
            if ($user) {
                $out = [
                    'data' => $user,
                    'input' => $record,
                    'status' => true,
                    'message' => 'User updated successfully!'
                ];
            } else {
                $error = $this->session->flashdata('error_message');
                $error_code = $this->session->flashdata('error_code');

                $out = [
                    'status' => false,
                    'code' => $error_code,
                    'message' => $error ? $error : "User couldn't be updated!"
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

    /**
     * Update a resource
     * print json Response
     */
    public function make_artisan()
    {
        $user = auth()->user();
        $gate = auth()->can('update', 'user', $user);
        if ($gate->allowed()) {
            $record = inputJson();
            $record = $record ? $record : $this->input->post();
            $record['user_id'] = $user->id;
            $userjobs = $this->userjob->create($record);

            if ($userjobs) {
                unset($record['user_id']);
                $ud = array_merge($record, ['user_type' => 'artisan']);
                if(!$user->photo_url){
                    $ud = array_merge($ud, ['photo_url' => $userjobs[0]->avatar]);
                }
                $user = $this->user->update($user->id,$ud);
            }
            if ($userjobs) {
                $out = [
                    'data' => $user,
                    'input' => $record,
                    'status' => true,
                    'message' => 'User updated successfully!'
                ];
            } else {
                $error = $this->session->flashdata('error_message');
                $error_code = $this->session->flashdata('error_code');
                $out = [
                    'status' => false,
                    'code' => $error_code ? $error_code : 13,
                    'message' => $error ? $error : "User couldn't be made an artisan. Possible reason: jobs not selected or job are inactive."
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

    /**
     * Delete a resource
     * print json Response
     */
    public function delete()
    {
        $user = auth()->user();
        $gate = auth()->can('delete', 'user', $user);
        if ($gate->allowed()) {
            if ($this->user->delete($user->id)) {
                $out = [
                    'status' => true,
                    'message' => 'User data deleted successfully!'
                ];
            } else {
                $out = [
                    'status' => false,
                    'message' => "User data couldn't be deleted!"
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

    /**
     * Show a list of post resources
     * @return http json
     */
    public function my_posts()
    {
        $gate = auth()->can('viewAny', 'post');
        if ($gate->denied()) {
            $out = [
                'status' => false,
                'message' => $gate->message
            ];
            httpReponseError($out, 401);
            return;
        }
        $user = auth()->user();

        $page = $this->input->get('page');
        $length = $this->input->get('length');
        $inputs = $this->input->get();
        $query = $this->post->all();

        $where = ['user_id' => $user->id];

        if ($this->input->get('approval'))
            $where = array_merge($where, ['user_posts.approval' => $inputs['approval']]);

        if ($this->input->get('visibility'))
            $where = array_merge($where, ['user_posts.visibility' => $inputs['visibility']]);

        $query->where($where);

        $out = json($query, $page, $length, $inputs, function ($item)use($user) {
            $item->user = $user;
            return $item;
        });
        if ($out)
            $out = array_merge($out, [
                'input' => $this->input->get(),
            ]);
        else  $out = [
            'status' => false,
            'input' => $this->input->get(),
        ];
        httpResponseJson($out);
    }

    /**
     * Show a list of post resources
     * @return http json
     */
    public function my_jobs()
    {
        $gate = auth()->can('viewAny', 'job');
        if ($gate->denied()) {
            $out = [
                'status' => false,
                'message' => $gate->message
            ];
            httpReponseError($out, 401);
            return;
        }
        $user = auth()->user();

        $page = $this->input->get('page');
        $length = $this->input->get('length');
        $inputs = $this->input->get();
        $query = $this->job->all()
            ->select(['location'])
            ->join('user_jobs', 'user_jobs.job_id=jobs.id');

        $where = ['user_jobs.user_id' => $user->id];
        $query->where($where);

        $out = json($query, $page, $length, $inputs);
        if ($out)
            $out = array_merge($out, [
                'input' => $this->input->get(),
            ]);
        else  $out = [
            'status' => false,
            'input' => $this->input->get(),
        ];
        httpResponseJson($out);
    }

    /**
     * Show a list of fav resources
     * @return http json
     */
    public function my_favourites()
    {
        $gate = auth()->can('viewAny', 'favourite');
        if ($gate->denied()) {
            $out = [
                'status' => false,
                'message' => $gate->message
            ];
            httpReponseError($out, 401);
            return;
        }
        $user = auth()->user();

        $page = $this->input->get('page');
        $length = $this->input->get('length');
        $inputs = $this->input->get();
        $query = $this->favourite->all();
        $where = ['user_id' => $user->id];

        $query->where($where);

        $out = json($query, $page, $length, $inputs, function ($item) {
            $item->jobs = $this->job->all()
                ->join('user_jobs', 'user_jobs.job_id=jobs.id')
                ->where('user_id', $item->id)
                ->get()
                ->result();
            return $item;
        });
        if ($out)
            $out = array_merge($out, [
                'input' => $this->input->get(),
            ]);
        else  $out = [
            'status' => false,
            'input' => $this->input->get(),
        ];
        httpResponseJson($out);
    }

    /**
     * Show a list of subscription resources
     * @return http json
     */
    public function my_subscriptions()
    {
        $gate = auth()->can('viewAny', 'usersubs');
        if ($gate->denied()) {
            $out = [
                'status' => false,
                'message' => $gate->message
            ];
            httpReponseError($out, 401);
            return;
        }
        $user = auth()->user();

        $page = $this->input->get('page');
        $length = $this->input->get('length');
        $inputs = $this->input->get();
        $query = $this->usersubs->all();
        $where = ['user_id' => $user->id];

        $query->where($where);

        $out = json($query, $page, $length, $inputs);
        if ($out)
            $out = array_merge($out, [
                'input' => $this->input->get(),
            ]);
        else  $out = [
            'status' => false,
            'input' => $this->input->get(),
        ];
        httpResponseJson($out);
    }

    
}
