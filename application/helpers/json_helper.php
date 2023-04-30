<?php

use SebastianBergmann\Type\TypeName;

use function PHPSTORM_META\type;

defined('BASEPATH') or exit('No direct script access allowed');

require_once BASEPATH . '/database/DB.php';


if (!function_exists('datatable')) {
    /**
     * @param mixed $query
     * @param int $page page number
     * @param int $per_page number of results per page
     * @return array structure for json
     */
    function json($query, $page = 1, $per_page = 100, $inputs = null, $callback = null)
    {
        $ci = (object)get_instance();
        $take = intval($per_page?$per_page:100);
        $start = abs(intval($page)-1)*$per_page;
        $total = 0;

        if ($query instanceof CI_DB_driver) {
            $query = (object) $query;
            if (isset($inputs['date_from']) || isset($inputs['date_to'])) {
                if (!empty($inputs['date_from']) || !empty($inputs['date_to'])) {
                    $ci->db->group_start();
                    $ci->db->where("DATE(" . $inputs['date_range_column'] . ")" . ' >=', $inputs['date_from'], true);
                    $ci->db->where("DATE(" . $inputs['date_range_column'] . ")" . ' <=', $inputs['date_to'], true);
                    $ci->db->group_end();
                }
            }

            if ($inputs) {
                if (isset($inputs['columns']) && isset($inputs['search'])) {
                    $ci->db->group_start();
                    foreach ($inputs['columns'] as $col) {
                        if (isset($col['name']) && isset($inputs['search']))
                            $ci->db->or_like($col['name'], $inputs['search']['value'], 'both');
                    }
                    $ci->db->group_end();
                }
                if (isset($inputs['order'])) {
                    foreach ($inputs['order'] as $order) {
                        $ci->db->order_by($inputs['columns'][intval($order['column'])]['name'], $order['dir']);
                    }
                }
            }


            $total = $query->get()->num_rows();
            $query = $ci->db->last_query();
            $result = $ci->db->query($query . " limit $start, $take");
            return json_array($result, $total, $callback);
        } else if (gettype($query) === 'string') {
            $result = $ci->db->query($query);
            $total = $result->num_rows();
            $result = $ci->db->query($query . " limit $start, $take");
            return json_array($result, $total, $callback);
        }
        throw new InvalidArgumentException('$query must be of type string or an instance of CI_DB_driver', 0);
    }

    function json_array(CI_DB_mysqli_result $result, $total, $callback = null)
    {
        if (!$result) return []; // for invalid result
        $result2 = [];
        if ($callback)
            foreach ($result->result() as $key => $item) {
                array_push($result2, $callback($item));
            }
        $data = [
            'recordsTotal' => $total,
            'recordsFiltered' => $result->num_rows(),
            'data' => $callback ? $result2 : $result->result(),
            'status' => true,
        ];
        return $data;
    }
    /**
     * @param array|object $data
     * Output result to http response as application/json content type
     */
    function httpResponseJson($data)
    {
        $ci = (object)get_instance();
        $ci->output
            ->set_content_type('application/json')
            ->set_output(json_encode($data));
    }

    function inputJson($key = null, $default = null)
    {
        $ci = (object)get_instance();
        $data = json_decode($ci->input->raw_input_stream, true);
        if ($key === null) return $data;

        if ($data === null) return $default;
        $res = null;
        if (isset($data[$key]))
            $res = $data[$key];
        return $res ? $res : $default;
    }
    /**
     * @param string|array $message 
     * @param int $code
     */
    function httpReponseError($message = 'Resource Not Found!', int $code = 404)
    {
        if (gettype($message) === 'string') {
            http_response_code($code);
            die($message);
        } else {
            $ci = (object)get_instance();
            $ci->output->set_header('Content-Type: application/json');
            http_response_code(401);
            echo json_encode($message);
            exit($code);
        }
    }
}
