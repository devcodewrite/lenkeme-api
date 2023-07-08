<?php
defined('BASEPATH') or exit('No direct script allowed');

class Subscriptions extends MY_Controller
{
    /**
     * Show a list of resources
     * @return http json
     */
    public function index($id = null)
    {
        if ($id !== null) {
            $gate = auth()->can('view', 'subscription');
            if ($gate->allowed()) {
                $subscription  = $this->subscription->find($id);
                if ($subscription) {
                    $out = [
                        'data' => $subscription,
                        'status' => true,
                    ];
                } else {
                    $out = [
                        'status' => false,
                        'message' => "subscription not found!"
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
            $gate = auth()->can('viewAny', 'subscription');
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
            $query = $this->subscription->all();

            $where = [];

            if ($this->input->get('approval'))
                $where = array_merge($where, ['user_subscriptions.approval' => $inputs['approval']]);

            if ($this->input->get('visibility'))
                $where = array_merge($where, ['user_subscriptions.visibility' => $inputs['visibility']]);

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
            $gate = auth()->can('view', 'subscription');
            if ($gate->allowed()) {
                $subscription  = $this->subscription->find($id);
                if ($auser) {
                    $favUser = $this->favourite->find($auser->id, $subscription->user_id);
                    $subscription->user->is_favourite = $favUser ? true : false;
                }
                if ($subscription) {
                    $out = [
                        'data' => $subscription,
                        'status' => true,
                    ];
                } else {
                    $out = [
                        'status' => false,
                        'message' => "subscription not found!"
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
            $gate = auth()->can('viewAny', 'subscription');
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
            $query = $this->subscription->all();

            $where = [];
            if ($auser) {
                $query->group_start();
                $query->where("CASE WHEN user_subscriptions.user_id = {$auser->id} THEN user_subscriptions.visibility IS NOT NULL ELSE user_subscriptions.visibility='public' END", null, false);
                $query->group_end();
                $query->group_start();
                $query->where("CASE WHEN user_subscriptions.user_id = {$auser->id} THEN user_subscriptions.approval IS NOT NULL ELSE user_subscriptions.approval='approved' END", null, false);
                $query->group_end();
            } else {
                $where = array_merge($where, [
                    'user_subscriptions.visibility' => 'public',
                    'user_subscriptions.approval' => 'approved'
                ]);
            }
            $query->where($where);

            $out = json($query, $page, $length, $inputs, function ($item) use ($auser) {
                $user = $this->user->find($item->user_id);
                if ($auser) {
                    $favUser = $this->favourite->find($auser->id, $item->id);
                    $user->is_favourite = $favUser ? true : false;
                }
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
        $gate = auth()->can('create', 'subscription');
        if ($gate->allowed()) {
            $record = inputJson();
            $record = $record ? $record : $this->input->subscription();
            $subscription  = $this->subscription->create($record);
            $error = $this->session->flashdata('error_message');
            if ($subscription) {
                $out = [
                    'data' => $subscription,
                    'input' => $record,
                    'status' => true,
                    'message' =>  $error ? $error : 'subscriptions created successfully!'
                ];
            } else {
                $out = [
                    'status' => false,
                    'message' => $error ? $error : "subscriptions couldn't be created!"
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
        $gate = auth()->can('update', 'subscription', $this->subscription->find($id));
        if ($gate->allowed()) {
            $record = inputJson();
            $record = $record ? $record : $this->input->subscription();
            $subscription = $this->subscription->update($id, $record);
            if ($subscription) {
                $out = [
                    'data' => $subscription,
                    'input' => $record,
                    'status' => true,
                    'message' => 'subscription updated successfully!'
                ];
            } else {
                $out = [
                    'status' => false,
                    'message' => "subscription couldn't be updated!"
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
        $gate = auth()->can('delete', 'subscription', $this->subscription->find($id));
        if ($gate->allowed()) {
            if ($this->subscription->delete($id)) {
                $out = [
                    'status' => true,
                    'message' => 'subscription deleted successfully!'
                ];
            } else {
                $out = [
                    'status' => false,
                    'message' => "subscription couldn't be deleted!"
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
    public function restore(int $id = null)
    {
        $gate = auth()->can('delete', 'subscription', $this->subscription->find($id));
        if ($gate->allowed()) {
            $data = $this->subscription->restore($id);
            if ($data) {
                $out = [
                    'status' => true,
                    'data' => $data,
                    'message' => 'subscription restored successfully!'
                ];
            } else {
                $out = [
                    'status' => false,
                    'message' => "subscription couldn't be restored!"
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
