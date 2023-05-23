<?php
defined('BASEPATH') or exit('No direct script allowed');

class Favourites extends MY_Controller
{

    /**
     * Store a resource
     * print json Response
     */
    public function store()
    {
        $gate = auth()->can('create', 'favourite');
        if ($gate->allowed()) {
            $record = inputJson();
            $record = $record ? $record : $this->input->post();


            $favourite  = $this->favourite->create($record);
            $error = $this->session->flashdata('error_message');

            if ($favourite) {
                $out = [
                    'data' => $favourite,
                    'input' => $record,
                    'status' => true,
                    'message' => 'favourites created successfully!'
                ];
            } else {
                $out = [
                    'status' => false,
                    'message' => ($error ? $error : "favourites couldn't be created!")
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
        $auser = auth()->authorized();
        if(!$auser){
            $out = [
                'status' => false, 
                'code' => $auser->error_code(),
                'message' => $auser->error()
            ];
            return  httpResponseJson($out);
        }
        $gate = auth()->can('delete', 'favourite', $this->favourite->find($auser->id, $id));
        if ($gate->allowed()) {
            if ($this->favourite->delete($id)) {
                $out = [
                    'status' => true,
                    'message' => 'favourite data deleted successfully!'
                ];
            } else {
                $out = [
                    'status' => false,
                    'message' => "favourite data couldn't be deleted!"
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
