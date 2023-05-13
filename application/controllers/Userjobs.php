<?php
defined('BASEPATH') or exit('No direct script allowed');

class Userjobs extends MY_Controller
{

    /**
     * Store a resource
     * print json Response
     */
    public function store()
    {
        $gate = auth()->can('create', 'userjob');
        if ($gate->allowed()) {
            $record = inputJson();
            $record = $record ? $record : $this->input->post();


            $userjob  = $this->userjob->create($record);
            $error = $this->session->flashdata('error_message');

            if ($userjob) {
                $out = [
                    'data' => $userjob,
                    'input' => $record,
                    'status' => true,
                    'message' => 'userjobs created successfully!'
                ];
            } else {
                $out = [
                    'status' => false,
                    'message' => ($error ? $error : "userjobs couldn't be created!")
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
        $gate = auth()->can('delete', 'userjob', $this->userjob->find($id));
        if ($gate->allowed()) {
            if ($this->userjob->delete($id)) {
                $out = [
                    'status' => true,
                    'message' => 'userjob data deleted successfully!'
                ];
            } else {
                $out = [
                    'status' => false,
                    'message' => "userjob data couldn't be deleted!"
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
