<?php
defined('BASEPATH') or exit('No direct script access allowed');

class Setting_model extends CI_Model
{
    protected $table = 'settings';

    public $hidden = [
        'type',
    ];

    public function get(string $key = null, string $default = '')
    {
        $where = [
            'keyword' => $key,
            'type' => 'system'
        ];
        $setting = $this->all()->where($where)->get()->row();
        if ($setting) return $setting->value;

        return $default;
    }

    public function all()
    {
        $fields = [];
        
        foreach ($this->db->field_data($this->table) as $field_data) {
            if (in_array($field_data->name, $this->hidden)) continue; // skip hidden fields
            array_push($fields, "{$this->table}.$field_data->name");
        }

        return
            $this->db->select($fields, true)
            ->from($this->table);
    }

    public function set(string $key, string $value = '', string $type = 'system')
    {
        $data = [
            'keyword' => $key,
            'value' => $value,
            'type' => $type
        ];
        return $this->db->replace($this->table, $data);
    }



    public function toAvatar($url = null, $model = null)
    {
        $avatars = [
            'male' => base_url('assets/images/man.png'),
            'female' => base_url('assets/images/woman.png'),
            'other' => base_url('assets/images/user.png'),
        ];
        return $url ? $url : $avatars[($model ? ($model->sex ? $model->sex : 'other') : 'other')];
    }

    public function canViewAny($user)
    {
        if (!auth()->authorized()) {
            httpReponseError('Unauthorized Access!', 401);
        }
        return auth()->allow();
    }

    public function canView($user, $model)
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
}
