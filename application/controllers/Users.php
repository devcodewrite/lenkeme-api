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

            $query->where($where);

            $out = json($query, $start, $length, $inputs);
            $out = array_merge($out, [
                'input' => $this->input->get(),
            ]);
            httpResponseJson($out);
        }
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

    /**
     * Update a resource
     * print json Response
     */
    public function make_artisan(int $id = null)
    {
        $gate = auth()->can('update', 'user', $this->user->find($id));
        if ($gate->allowed()) {
            $record = inputJson();
            $userjobs = $this->userjob->create($record);
            $user = false;
            $jobsCount = sizeof($userjobs);

            if ($jobsCount > 0)
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

    /**
     * Delete a resource
     * print json Response
     */
    public function delete(int $id = null)
    {
        $gate = auth()->can('delete', 'user', $this->user->find($id));
        if ($gate->allowed()) {
            if ($this->user->delete($id)) {
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
        if (!auth()->authorized()) {
            httpReponseError('Unauthorized Access!', 401);
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
        if (!auth()->authorized()) {
            httpReponseError('Unauthorized Access!', 401);
        }
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
        if (!auth()->authorized()) {
            httpReponseError('Unauthorized Access!', 401);
        }
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
