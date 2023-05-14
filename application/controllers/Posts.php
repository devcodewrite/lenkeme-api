<?php
defined('BASEPATH') or exit('No direct script allowed');

class Posts extends MY_Controller
{
    /**
     * Show a list of resources
     * @return http json
     */
    public function index($id = null)
    {
        if ($id !== null) {
            $gate = auth()->can('view', 'post');
            if ($gate->allowed()) {
                $post  = $this->post->find($id);
                if ($post) {
                    $out = [
                        'data' => $post,
                        'status' => true,
                    ];
                } else {
                    $out = [
                        'status' => false,
                        'message' => "post not found!"
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
            $gate = auth()->can('viewAny', 'post');
            if ($gate->denied()) {
                $out = [
                    'status' => false,
                    'message' => $gate->message
                ];
                http_response_code(401);
                httpResponseJson($out);
                return;
            }

            $page = $this->input->get('page');
            $length = $this->input->get('length');
            $inputs = $this->input->get();
            $query = $this->post->all();

            $where = [];

            if ($this->input->get('approval'))
                $where = array_merge($where, ['user_posts.approval' => $inputs['approval']]);
            
            if ($this->input->get('visibility'))
                $where = array_merge($where, ['user_posts.visibility' => $inputs['visibility']]);

            $query->where($where);

            $out = json($query, $page, $length, $inputs, function ($item) {
                $user = $this->user->find($item->user_id);
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
    }

    /**
     * Show a list of resources
     * @return http json
     */
    public function approved($id = null)
    {
        $auser = auth()->user();
        if ($id !== null) {
            $gate = auth()->can('view', 'post');
            if ($gate->allowed()) {
                $post  = $this->post->find($id);
                if ($post) {
                    $out = [
                        'data' => $post,
                        'status' => true,
                    ];
                } else {
                    $out = [
                        'status' => false,
                        'message' => "post not found!"
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
            $gate = auth()->can('viewAny', 'post');
            if ($gate->denied()) {
                $out = [
                    'status' => false,
                    'message' => $gate->message
                ];
                http_response_code(401);
                httpResponseJson($out);
                return;
            }

            $page = $this->input->get('page');
            $length = $this->input->get('length');
            $inputs = $this->input->get();
            $query = $this->post->all();

            $where = [
            ];
           
            if($auser){
                $query->group_start();
                $query->where("CASE WHEN user_posts.user_id = {$auser->id} THEN user_posts.visibility IS NOT NULL ELSE user_posts.visibility='public' END",null,false);
                $query->group_end();
                $query->group_start();
                $query->where("CASE WHEN user_posts.user_id = {$auser->id} THEN user_posts.approval IS NOT NULL ELSE user_posts.approval='approved' END",null,false);
                $query->group_end();
            }else {
                $where = array_merge($where, [
                    'user_posts.visibility' => 'public',
                    'user_posts.approval' => 'approved'
                ]);
            }
            $query->where($where);

            $out = json($query, $page, $length, $inputs, function ($item) {
                $user = $this->user->find($item->user_id);
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
    }


    /**
     * Store a resource
     * print json Response
     */
    public function store()
    {
        $gate = auth()->can('create', 'post');
        if ($gate->allowed()) {
            $record = inputJson();
            $record = $record ? $record : $this->input->post();
            $post  = $this->post->create($record);
            $error = $this->session->flashdata('error_message');
            if ($post) {
                $out = [
                    'data' => $post,
                    'input' => $record,
                    'status' => true,
                    'message' =>  $error? $error:'posts created successfully!'
                ];
            } else {
                $out = [
                    'status' => false,
                    'message' => $error ? $error : "posts couldn't be created!"
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
    public function update(int $id = null)
    {
        $gate = auth()->can('update', 'post', $this->post->find($id));
        if ($gate->allowed()) {
            $record = inputJson();
            $record = $record ? $record : $this->input->post();
            $post = $this->post->update($id, $record);
            if ($post) {
                $out = [
                    'data' => $post,
                    'input' => $record,
                    'status' => true,
                    'message' => 'post updated successfully!'
                ];
            } else {
                $out = [
                    'status' => false,
                    'message' => "post couldn't be updated!"
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
        $gate = auth()->can('delete', 'post', $this->post->find($id));
        if ($gate->allowed()) {
            if ($this->post->delete($id)) {
                $out = [
                    'status' => true,
                    'message' => 'post data deleted successfully!'
                ];
            } else {
                $out = [
                    'status' => false,
                    'message' => "post data couldn't be deleted!"
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
