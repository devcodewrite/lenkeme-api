<?php
defined('BASEPATH') or exit('No direct script allowed');

class Permissions extends MY_Controller
{

    /**
     * Update a resource
     * print json Response
     */
    public function update(int $id = null)
    {
        $gate = auth()->can('update', 'perm', $this->perm->find($id));
        if ($gate->allowed()) {
            $record = $this->input->post();
            $permission = $this->perm->update($id, $record);
            if ($permission) {
                $out = [
                    'data' => $permission,
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
}
