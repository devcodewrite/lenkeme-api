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
    $w = $this->input->get();
    $setting = $this->setting->all()->where($w)->get()->result();
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
