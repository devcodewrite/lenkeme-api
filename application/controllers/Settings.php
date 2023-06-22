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
    $gate = auth()->can('view', 'setting');

    if ($gate->allowed()) {
      $setting = $this->setting->all();
      if ($setting) {
        $out = [
          'data' => $setting,
          'status' => true,
        ];
      } else {
        $out = [
          'status' => false,
          'message' => "No setting found!"
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

  public function update()
  {
    if ($this->input->post()) {
      $record = $this->input->post();

      if (empty($record['value'])) {
        return httpResponseJson($record);
      }
      if ($this->setting->set($record['name'], $record['value'])) {
        return httpResponseJson($record);
      }
    }
  }
}
