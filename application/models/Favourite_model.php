<?php
defined('BASEPATH') or exit('Direct acess is not allowed');

class Favourite_model extends CI_Model
{
    public $table = 'user_favourites';

    public function create(array $record)
    {
        if (!$record) return;
        $record['user_id'] = auth()->user()->id;

        if(intval($record['user_id1']) ===intval($record['user_id'])){
            $this->session->set_flashdata('error_message', "You cannot favourite yourself!");
            return false;
        }
        if(!$this->user->find(intval($record['user_id1']))){
            $this->session->set_flashdata('error_message', "Favourite user not found!");
            return false;
        }

        $data = $this->extract($record);

        if ($this->db->insert($this->table, $data)) {
            return $this->find($record['user_id'],$record['user_id1']);
        }
    }

    /**
     * Delete a record
     * @param $id
     * @return Boolean
     */
    public function delete(int $id)
    {
        $userId = auth()->user()->id;
        return $this->db->delete($this->table, ['user_id1' => $id, 'user_id'=>$userId]);
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
    public function find(int $userId, int $id)
    {
        if (!$id) return;

        $where = [
            'user_id1' => $id,
            'user_id' => $userId,
        ];
        $user_favourite = $this->all()->where($where)->get()->row();
        if(!$user_favourite) return false;
        return $user_favourite;
    }

    /**
     * Get user_favourites by column where cluase
     */
    public function where(array $where)
    {
        return $this->db->get_where($this->table, $where);
    }

    /**
     * Get all user_favourites
     */
    public function all()
    {
        $fields = [$this->table.'.*',];
        foreach($this->db->field_data($this->user->table) as $field_data){
            if(in_array($field_data->name,$this->user->hidden)) continue; // skip hidden fields
            array_push($fields, "{$this->user->table}.$field_data->name");
        }

        return
            $this->db->select($fields, true)
            ->join('users', "users.id={$this->table}.user_id1")
            ->from($this->table);
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

    public function canCreate($user){
         if (!auth()->authorized()) {
            httpReponseError('Unauthorized Access!', 401);
        }
        return auth()->allow();
    }

    public function canUpdate($user, $model){ 
        return auth()->allow();
    }

    public function canDelete($user, $model){
        if (!auth()->authorized()) {
            httpReponseError('Unauthorized Access!', 401);
        }
        return auth()->allow();
    }
}
