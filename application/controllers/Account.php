<?php
defined('BASEPATH') or exit('No direct script allowed');

class Account extends MY_Controller
{
    public function __construct() {
        parent::__construct();
        if (!auth()->authorized()) {
            httpReponseError('Unauthorized Access!', 401);
        }
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
            $user = $this->user->update($user->id, $record);
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
            
            if ($this->userjob->create($record))
                $user = $this->user->update($user->id, ['user_type' => 'artisan']);
                $user->jobs = $this->userjob->find($user->id)->result();
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
                    'code' => 13,
                    'message' => "User couldn't be made an artisan. Possible reason: jobs not selected or job are inactive."
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

        $start = inputJson('start', 0);
        $length = inputJson('length', 100);
        $inputs = inputJson();
        $query = $this->job->all()
            ->select(['location'])
            ->join('user_jobs', 'user_jobs.job_id=jobs.id');

        $where = ['user_jobs.user_id' => $user->id];
        $query->where($where);

        $out = json($query, $start, $length, $inputs);
        $out = array_merge($out, [
            'input' => $this->input->get(),
        ]);
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

        $start = inputJson('start', 0);
        $length = inputJson('length', 100);
        $inputs = inputJson();
        $query = $this->favourite->all();
        $where = ['user_id' => $user->id];

        $query->where($where);

        $out = json($query, $start, $length, $inputs);
        $out = array_merge($out, [
            'input' => $this->input->get(),
        ]);
        httpResponseJson($out);
    }
}
