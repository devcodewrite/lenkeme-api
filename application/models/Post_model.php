<?php
defined('BASEPATH') or exit('Direct acess is not allowed');

class Post_model extends CI_Model
{
    public $table = 'user_posts';

    public $hidden = [
        'deleted_at'
    ];

    public function create(array $record)
    {
        if (!$record) return;

        $record['user_id'] = auth()->user()->id;
        $where = [
            'status' => 'active',
            'user_type !=' => 'super_admin'
        ];
        $sysuser = $this->sysuser->all()
            ->where($where, false)
            ->order_by('system_users.id', 'random')
            ->order_by('system_users.last_login_at', 'asc')
            ->limit(1)
            ->get()
            ->row();
        $record['system_user_id'] = $sysuser ? $sysuser->id : null;
        $data = $this->extract($record);

        if ($this->db->insert($this->table, $data)) {
            $id = $this->db->insert_id();
            $this->update($id, []); // uploads and update images
            return $this->find($id);
        }
    }

    /**
     * Update a record
     * @param $id
     * @return Boolean
     */
    public function update(int $id, array $data =null)
    {
        $data = $this->extract($data);
        $post = $this->find($id);

        if (isset($_FILES['images'])) {
            $files = $_FILES; // save for later
            $cpt = sizeof($_FILES['images']['name']);
            $images = [];
            for ($i = 0; $i < $cpt; $i++) {
                $_FILES['image']['name'] = $files['images']['name'][$i];
                $_FILES['image']['type'] = $files['images']['type'][$i];
                $_FILES['image']['tmp_name'] = $files['images']['tmp_name'][$i];
                $_FILES['image']['error'] = $files['images']['error'][$i];
                $_FILES['image']['size'] = $files['images']['size'][$i];
                $path = $this->uploadImages($id, 'image');
                if ($path) array_push($images, $path);
            }
            $data['images'] = implode(",", $images);
        }
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
        $role = $this->find($id);
        if ($this->perm->delete($role->permission_id))
            return $this->db->delete($this->table, ['id' => $id]);

        return false;
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
     * Get role by id
     */
    public function find(int $id)
    {
        if (!$id) return;

        $where = [
            'id' => $id,
        ];
        $post = $this->all()->where($where)->get()->row();
        if (!$post) return false;
        $post->user = $this->user->find($post->user_id);
        return $post;
    }

    /**
     * Get posts by column where cluase
     */
    public function where(array $where)
    {
        return $this->db->get_where($this->table, $where);
    }

    /**
     * Get all posts
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
            ->from($this->table);
    }

    /**
     * Upload photo
     * @param string $field_name
     * @return Boolean
     */
    public function uploadImages($id, string $field_name = 'image', $scale = '90%', $dim = ['w' => '', 'h' => ''], $disp_error = true)
    {
        $path = "uploads/images/posts/$id";
        if (!is_dir($path)) mkdir("./$path", 0777, TRUE);

        $config['upload_path'] = "./$path";
        $config['allowed_types'] = 'gif|jpg|png|jpeg';
        $config['file_name'] = uniqid($id);
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
        if (!auth()->authorized()) {
            httpReponseError('Unauthorized Access!', 401);
        }
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
