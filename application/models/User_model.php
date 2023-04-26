<?php
defined('BASEPATH') or exit('Direct acess is not allowed');

class User_model extends CI_Model
{
    public $table = 'users';

    public $hidden = [
        'token',
        'password',
        'deleted_at'
    ];

    public function create(array $record)
    {
        if (!$record) return;
        if (!empty($record['password'])) $record['password'] = password_hash($record['password'], PASSWORD_DEFAULT);
        $data = $this->extract($record);

        if($this->user->or_where(['username' => $record['username']])->row()){
            $this->session->set_flashdata('error_message', "@".$record['username']." has been taken!");
            return false;
        }

        if($this->user->or_where(['phone' => $record['phone']])->row()){
            $pat1 = substr($record['phone'], 0,2);
            $pat2 = substr($record['phone'], 8,2);
            $this->session->set_flashdata('error_message', "You already have account with this phone number $pat1***$pat2");
            return false;
        }

        if ($this->db->insert($this->table, $data)) {
            $this->uploadPhoto($this->db->insert_id());
            return $this->find($this->db->insert_id());
        }
    }

    /**
     * Update a record
     * @param $id
     * @return Boolean
     */
    public function update(int $id, array $record)
    {

        if (!$record) return;

        if (!empty($record['password'])) {
            $record['password'] = password_hash($record['password'], PASSWORD_DEFAULT);
        }else{
            unset($record['password']);
        }
            $data = $this->extract($record);
            $this->db->set($data);
            $this->db->where('id', $id);
            $this->db->update($this->table);
            $this->uploadPhoto($id);
        
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
    public function uploadPhoto($id, string $field_name = 'photo', string $col_name = 'photo_url', $disp_error = true, $scale = '60%', $dim = ['w' => '100', 'h' => '100'])
    {
        $config['upload_path'] = './uploads/photos/' . $this->table;
        $config['allowed_types'] = 'gif|jpg|png|jpeg';
        $config['file_name'] = uniqid($id);
        $this->load->library('upload', $config);

        if ($this->upload->do_upload($field_name)) {
            $file_data = $this->upload->data();

            $resize['image_library'] = 'gd2';
            $resize['create_thumb'] = TRUE;
            $resize['maintain_ratio'] = TRUE;
            $resize['quality'] = $scale;
            $resize['width'] = $dim['w'];
            $resize['height'] = $dim['h'];
            $resize['source_image'] = $file_data['full_path'];

            $this->load->library('image_lib', $resize);

            if (!$this->image_lib->resize()) {
                if ($disp_error) {
                    $this->session->set_flashdata('error_message', $this->image_lib->display_errors('', ''));
                }
                return false;
            }
        } else {
            if ($disp_error) {
                $this->session->set_flashdata('warning_message', $this->upload->display_errors('', ''));
                return false;
            }
            return true;
        }
        $data = [
            $col_name => base_url('uploads/photos/' . $this->table . "/" . $file_data['file_name']),
        ];
        return $this->update($id, $data);
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
        return $this->all()->where($where)->get()->row();
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
        $fields = [
        ];
       
        foreach($this->db->field_data($this->table) as $field_data){
            if(in_array($field_data->name,$this->hidden)) continue; // skip hidden fields
            array_push($fields, "{$this->table}.$field_data->name");
        }

        return
            $this->db->select($fields,true)
            ->from($this->table)
            ->where($where);
    }

    public function canViewAny($user)
    {
        return auth()->allow();
        $role = $this->user->find($user->id)->role;
        if ($role)
            return
                $role->permission->is_admin === '1'
                ? auth()->allow() : (in_array('view', explode(',', $role->permission->users)) ? auth()->allow()
                    : auth()->deny("You don't have permission to view this recored."));
        return auth()->deny("You don't have permission to view this recored.");
    }

    public function canView($user, $model)
    {
        return auth()->allow();
        $role = $this->user->find($user->id)->role;
        if ($role)
            return
                $role->permission->is_admin === '1'
                ? auth()->allow() : (in_array('view', explode(',', $role->permission->users)) ? auth()->allow()
                    : auth()->deny("You don't have permission to view this recored."));
        return auth()->deny("You don't have permission to view this recored.");
    }

    public function canCreate($user)
    {
        $role = $this->user->find($user->id)->role;
        if ($role)
            return
                $role->permission->is_admin === '1'
                ? auth()->allow() : (in_array('create', explode(',', $role->permission->users)) ? auth()->allow()
                    : auth()->deny("You don't have permission to create this record."));
        return auth()->deny("You don't have permission to create this record.");
    }

    public function canUpdate($user, $model)
    {
        $role = $this->user->find($user->id)->role;
        if ($role)
            return
                $role->permission->is_admin === '1'
                ? auth()->allow() : (in_array('update', explode(',', $role->permission->users)) ? auth()->allow()
                    : auth()->deny("You don't have permission to update this record."));
        return auth()->deny("You don't have permission to update this record.");
    }

    public function canDelete($user, $model)
    {
        $role = $this->user->find($user->id)->role;
        if ($role)
            return
                $role->permission->is_admin === '1'
                ? auth()->allow() : (in_array('delete', explode(',', $role->permission->users)) ? auth()->allow()
                    : auth()->deny("You don't have permission to delete this record."));
        return auth()->deny("You don't have permission to delete this record.");
    }
}
