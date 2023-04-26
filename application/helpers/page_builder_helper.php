<?php
if (!function_exists('app_start')) {
    function app_start()
    {
        $ci = &get_instance();
        $ci->load->view('templates/app_start');
    }
}

if (!function_exists('app_end')) {
    function app_end()
    {
        $ci = &get_instance();
       $ci->load->view('templates/app_end');
    }
}

if (!function_exists('app_header')) {
    function app_header()
    {
        $ci = &get_instance();
        $ci->load->view('templates/app_header');
    }
}

if (!function_exists('app_sidebar')) {
    function app_sidebar()
    {
        $ci = &get_instance();
        $ci->load->view('templates/app_sidebar');
    }
}

if (!function_exists('app_footer')) {
    function app_footer()
    {
        $ci = &get_instance();
        $ci->load->view('templates/app_footer');
    }
}


if (!function_exists('page_start')) {
    function page_start()
    {
        $ci = &get_instance();
        $ci->load->view('templates/page_start');
    }
}

if (!function_exists('page_end')) {
    function page_end()
    {
        $ci = &get_instance();
       $ci->load->view('templates/page_end');
    }
}

if (!function_exists('load_page_scripts')) {
    function load_page_scripts()
    {
    }
}
if (!function_exists('load_page_styles')) {
    function load_page_styles()
    {
       
    }
}

if(!function_exists('get_nav_status')){
    function get_nav_status(string $uri){
        $ci = (object)get_instance();
        return $ci->uri->segment(1) === $uri? 'mm-active':'';
    }
}

if(!function_exists('get_nav_status1')){
    function get_nav_status1(string $uri){
        return uri_string() === $uri? 'mm-active':'';
    }
}