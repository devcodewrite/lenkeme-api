<?php

class MY_Controller extends CI_Controller
{
    public function _remap($method, $params = array())
    {
        if (auth()->hasPermission(uri_string())) {
            if (method_exists($this, $method)) {
                return call_user_func_array(array($this, $method), $params);
            }
            httpReponseError('Page Not Found!', 404);
        }
    }
}
