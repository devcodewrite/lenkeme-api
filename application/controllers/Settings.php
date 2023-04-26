<?php
defined('BASEPATH') or exit('No direct script allowed');

class Settings extends MY_Controller
{
    /**
     * Show a list of resources
     * @return string html view
     */
    public function index()
    {

        $data = [
          'pageTitle' => "System Settings"
        ];
        $this->load->view('pages/setup/settings', $data);
    }

    public function update()
    {
        if($this->input->post()){
			$record = $this->input->post();

			if(empty($record['value'])){
				return httpResponseJson($record);
			}
			if($this->setting->set($record['name'], $record['value'])){
                return httpResponseJson($record);
			}
		}
    }
}