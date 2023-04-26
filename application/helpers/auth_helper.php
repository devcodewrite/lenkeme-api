<?php
if (!function_exists('auth')) {
    function auth():object
    {
        $ci = (object)get_instance();
        return $ci->auth;
    }
}
