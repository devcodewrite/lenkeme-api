<?php
defined('BASEPATH') or exit('No direct script allowed');

class Roles extends MY_Controller
{
    /**
     * Show a list of resources
     * @return string html view
     */
    public function index()
    {
        $data = [
            'roles' => $this->role->all()->get()->result(),
        ];
        $this->load->view('pages/roles/list', $data);
    }

    /**
     * Show a resource
     * html view
     */
    public function view(int $id = null)
    {
        $role = $this->role->find($id);
        if (!$role) show_404();

        $gate = auth()->can('view', 'role');
        if ($gate->denied()) {
            show_error($gate->message, 401, 'An Unathorized Access!');
        }

        $data = [
            'role' => $role,
        ];
        $this->load->view('pages/roles/detail', $data);
    }

    /**
     * Show a form page for creating resource
     * html view
     */
    public function create()
    {
        $gate = auth()->can('create', 'role');
        if ($gate->denied()) {
            show_error($gate->message, 401, 'An Unathorized Access!');
        }
        $this->load->view('pages/roles/edit');
    }

    /**
     * Show a form page for updating resource
     * html view
     */
    public function edit(int $id = null)
    {
        $role = $this->role->find($id);
        if (!$role) show_404();

        $gate = auth()->can('update', 'role');
        if ($gate->denied()) {
            show_error($gate->message, 401, 'An Unathorized Access!');
        }

        $data = [
            'role' => $role,
        ];
        $this->load->view('pages/roles/edit', $data);
    }

    /**
     * Store a resource
     * print json Response
     */
    public function store()
    {
        $gate = auth()->can('create', 'role');
        if ($gate->allowed()) {
            $record = inputJson();
            $record = $record ? $record : $this->input->post();

            $role  = $this->role->create($record);
            if ($role) {
                $out = [
                    'data' => $role,
                    'input' => $record,
                    'status' => true,
                    'message' => 'Roles created successfully!'
                ];
            } else {
                $out = [
                    'status' => false,
                    'message' => "Roles couldn't be created!"
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
        $gate = auth()->can('update', 'role', $this->role->find($id));
        if ($gate->allowed()) {
            $record = inputJson();
            $record = $record ? $record : $this->input->post();

            $role = $this->role->update($id, $record);
            if ($role) {
                $out = [
                    'data' => $role,
                    'input' => $record,
                    'status' => true,
                    'message' => 'Role updated successfully!'
                ];
            } else {
                $out = [
                    'status' => false,
                    'message' => "Role couldn't be updated!"
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
        $gate = auth()->can('delete', 'role', $this->role->find($id));
        if ($gate->allowed()) {
            if ($this->role->delete($id)) {
                $out = [
                    'status' => true,
                    'message' => 'Role data deleted successfully!'
                ];
            } else {
                $out = [
                    'status' => false,
                    'message' => "Role data couldn't be deleted!"
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
