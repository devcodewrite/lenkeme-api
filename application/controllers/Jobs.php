<?php
defined('BASEPATH') or exit('No direct script allowed');

class jobs extends MY_Controller
{
    /**
     * Show a list of resources
     * @return http json
     */
    public function index($id = null)
    {
        if ($id !== null) {
            $gate = auth()->can('view', 'job');
            if ($gate->allowed()) {
                $job  = $this->job->find($id);
                if ($job) {
                    $out = [
                        'data' => $job,
                        'status' => true,
                    ];
                } else {
                    $out = [
                        'status' => false,
                        'message' => "job not found!"
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
            $gate = auth()->can('viewAny', 'job');
            if ($gate->denied()) {
                $out = [
                    'status' => false,
                    'message' => $gate->message
                ];
                http_response_code(401);
                httpResponseJson($out);
                return;
            }

            $start = $this->input->get('start');
            $length = $this->input->get('length');
            $inputs = $this->input->get();
            $query = $this->job->all();

            $where = [];

            if ($this->input->get('status'))
                $where = array_merge($where, ['jobs.status' => $inputs['status']]);

            $query->where($where);

            $out = json($query, $start, $length, $inputs);
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
        $gate = auth()->can('create', 'job');
        if ($gate->allowed()) {
            $record = inputJson();
            $job  = $this->job->create($record);
            if ($job) {
                $out = [
                    'data' => $job,
                    'input' => $record,
                    'status' => true,
                    'message' => 'jobs created successfully!'
                ];
            } else {
                $out = [
                    'status' => false,
                    'message' => "jobs couldn't be created!"
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
        $gate = auth()->can('update', 'job', $this->job->find($id));
        if ($gate->allowed()) {
            $record = $this->input->job();
            $job = $this->job->update($id, $record);
            if ($job) {
                $out = [
                    'data' => $job,
                    'input' => $record,
                    'status' => true,
                    'message' => 'job updated successfully!'
                ];
            } else {
                $out = [
                    'status' => false,
                    'message' => "job couldn't be updated!"
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
        $gate = auth()->can('delete', 'job', $this->job->find($id));
        if ($gate->allowed()) {
            if ($this->job->delete($id)) {
                $out = [
                    'status' => true,
                    'message' => 'job data deleted successfully!'
                ];
            } else {
                $out = [
                    'status' => false,
                    'message' => "job data couldn't be deleted!"
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
