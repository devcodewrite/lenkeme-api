<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Setting_model extends CI_Model
{
    protected $table = 'settings';

    public function get(string $key = null, string $default = '')
    {
        $setting = $this->db->get_where($this->table, ['keyword' => $key])->row();
        if($setting) return $setting->value;

        return $default;
    }

    public function all()
    {
        return $this->db->get_where($this->table)->result();
    }

    public function set(string $key, string $value = '')
    {
        $data = [
            'keyword' => $key,
            'value' => $value,
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
        return $url? $url: $avatars[($model?($model->sex?$model->sex:'other'):'other')];
    }

    public function canViewAny($user){
        if (!auth()->authorized()) {
            httpReponseError('Unauthorized Access!', 401);
        }
        return auth()->allow();
    }

    public function canView($user, $model){
        if (!auth()->authorized()) {
            httpReponseError('Unauthorized Access!', 401);
        }
        return auth()->allow();
    }

    public function canUpdate($user, $model){
        if (!auth()->authorized()) {
            httpReponseError('Unauthorized Access!', 401);
        }
        return auth()->allow();
    }
}
