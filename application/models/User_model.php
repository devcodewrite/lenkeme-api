<?php
defined('BASEPATH') or exit('Direct acess is not allowed');

class User_model extends CI_Model
{
    public $table = 'users';

    public $hidden = [
        'token',
        'otp_code',
        'password',
        'deleted_at'
    ];

    public function create(array $record)
    {
        if (!$record) return;
        $record['token'] = sha1($record['password'] . uniqid());
        if (!empty($record['password'])) $record['password'] = password_hash($record['password'], PASSWORD_DEFAULT);

        $data = $this->extract($record);

        if ($this->user->where(['phone' => $record['phone']])->row()) {
            $pat1 = substr($record['phone'], 0, 2);
            $pat2 = substr($record['phone'], 8, 2);
            $this->session->set_flashdata('error_message', "You already have account with this phone number $pat1***$pat2");
            $this->session->set_flashdata('error_code', 2);
            return false;
        }

        if (isset($record['email'])) {
            if ($this->user->where(['email' => $record['email']])->row()) {
                $this->session->set_flashdata('error_message', "You already have account with this email " . $record['email']);
                $this->session->set_flashdata('error_code', 3);
                return false;
            }
        }

        if (!isset($record['username'])) {
            $lastid = $this->db->select()->from($this->table)->order_by('id', 'asc')->limit(1)->get()->row('id');
            $username = "user_" . substr(($lastid + 123 + random_int(1000000000, PHP_INT_MAX)), 0, 10);

            if ($this->user->where(['username' => $username])->num_rows() === 0) {
                $data['username'] = $username;
            } else {
                $username = "user_" . substr(($lastid + 123 + random_int(1000000000, PHP_INT_MAX)), 0, 10);
                $data['username'] = $username;
            }
        }
        $jobs = [];
        if (isset($record['user_type'])) {
            $jobs = isset($record['jobs']) ? explode(',', $record['jobs']) : [];
            if (
                $record['user_type'] === 'artisan' &&
                (sizeof($jobs) === 0 || $this->job->all()->where_in('jobs.id', $jobs)->count_all_results() === 0)
            ) {
                $this->session->set_flashdata('error_message', "Provide at least one valid job in order to register an artisan account.");
                $this->session->set_flashdata('error_code', 15);
                return false;
            }
            $sjob = $this->job->find($jobs[0]);
            if ($sjob) {
                $record['photo_url'] = $sjob['avatar'];
            }
        }

        if ($this->db->insert($this->table, $data)) {
            $id = $this->db->insert_id();
            if (sizeof($jobs) > 0) {
                $record['user_id'] = $id;
                $this->userjob->create($record);
            }
            if (isset($_FILES['photo'])){
                $path = $this->uploadPhoto($id);
                $record2['photo_url'] = $path;
                return $this->update($id, $record2);
            }
            return $this->find($id);
        }
    }

    /**
     * Update a record
     * @param $id
     * @return Boolean
     */
    public function update(int $id, array $record)
    {
        if (!$record && !isset($_FILES['photo'])) return;
        if (!empty($record['password'])) {
            $record['password'] = password_hash($record['password'], PASSWORD_DEFAULT);
        } else {
            unset($record['password']);
        }
        if (isset($_FILES['photo'])){
            $path = $this->uploadPhoto($id);
            $record['photo_url'] = $path;
        }
        $data = $this->extract($record);
        $this->db->set($data);
        $this->db->where('id', $id);
        $this->db->update($this->table);
        return $this->find($id);
    }

    /**
     * Delete a record
     * @param $id
     * @return Boolean
     */
    public function delete(int $id)
    {
        $this->db->set(['deleted_at' => date('Y-m-d H:i:s')]);
        $this->db->where('id', $id);
        $this->db->update($this->table);
        return $this->db->affected_rows() > 0;
    }

    /**
     * Extract only values of only fields in the table
     * @param $data
     * @return Array
     */
    protected function extract(array $data)
    {

        // filter array for only specified table data
        $filtered = array_filter($data, function ($key, $val) {
            return $this->db->field_exists($val, $this->table);
        }, ARRAY_FILTER_USE_BOTH);

        return $filtered;
    }

    /**
     * Upload photo
     * @param string $field_name
     * @return Boolean
     */
    public function uploadPhoto($id, string $field_name = 'photo', $scale = '90%', $dim = ['w' => '', 'h' => ''], $disp_error = true)
    {
        $path = "uploads/photos/users";
        if (!is_dir($path)) mkdir("./$path", 0777, TRUE);

        $config['upload_path'] = "./$path";
        $config['allowed_types'] = 'gif|jpg|png|jpeg';
        $config['file_name'] = $id;
        $this->load->library('upload', $config);

        if ($this->upload->do_upload($field_name)) {
            $file_data = $this->upload->data();

            $resize['image_library'] = 'gd2';
            $resize['create_thumb'] = FALSE;
            $resize['maintain_ratio'] = TRUE;
            $resize['quality'] = $scale;
            $resize['width'] = $dim['w'];
            $resize['height'] = $dim['h'];
            $resize['source_image'] = $file_data['full_path'];
            $this->load->library('image_lib', $resize);

            if (!$this->image_lib->resize()) {
                if ($disp_error) $this->session->set_flashdata('error_message', $this->image_lib->display_errors('', ''));
                return false;
            }
        } else {
            if ($disp_error) $this->session->set_flashdata('error_message', $this->upload->display_errors('', ''));
            return false;
        }
        return base_url("$path/" . $file_data['file_name']);
    }

    /**
     * Get user by id
     */
    public function find(int $id = null)
    {
        if (!$id) return;

        $where = [
            'id' => $id,
            "deleted_at" => null
        ];
        $user = $this->all()->where($where)->get()->row();

        if (!$user) return false;
        $user->jobs = $this->userjob->find($user->id);
        return $user;
    }

    /**
     * Get users by column where cluase
     */
    public function where(array $where)
    {
        $where = array_merge($where, ["{$this->table}.deleted_at =" => null]);
        return $this->db->get_where($this->table, $where);
    }

    /**
     * Get all users
     */
    public function all()
    {
        $where = ["{$this->table}.deleted_at =" => null];
        $fields = [];

        foreach ($this->db->field_data($this->table) as $field_data) {
            if (in_array($field_data->name, $this->hidden)) continue; // skip hidden fields
            array_push($fields, "{$this->table}.$field_data->name");
        }

        return
            $this->db->select($fields, true)
            ->from($this->table)
            ->where($where);
    }

    public function all2()
    {
        $where = ["{$this->table}.deleted_at =" => null];
        $fields = [];

        return
            $this->db->select($fields, true)
            ->from($this->table)
            ->where($where);
    }


    public function canViewAny($user)
    {
        return auth()->allow();
    }

    public function canView($user, $model)
    {
        return auth()->allow();
    }

    public function canCreate($user)
    {
        return auth()->allow();
    }

    public function canUpdate($user, $model)
    {
        if (!auth()->authorized()) {
            httpReponseError('Unauthorized Access!', 401);
        }
        return auth()->allow();
    }

    public function canDelete($user, $model)
    {
        if (!auth()->authorized()) {
            httpReponseError('Unauthorized Access!', 401);
        }
        return auth()->allow();
    }
}
