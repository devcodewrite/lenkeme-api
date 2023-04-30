<?php
defined('BASEPATH') or exit('Direct acess is not allowed');

class App_model extends CI_Model
{
    public $table = 'apps';

    public $hidden = [
        'deleted_at'
    ];

    public function create(array $record)
    {
        if (!$record) return;
        $record['system_user_id'] = auth()->user()->id;

        $data = $this->extract($record);

        if ($this->db->insert($this->table, $data)) {
            $id = $this->db->insert_id();
            return $this->find($id);
        }
    }

    /**
     * Update a record
     * @param $id
     * @return Boolean
     */
    public function update(int $id, array $data)
    {
        $data = $this->extract($data);
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
     * Get app by id
     */
    public function find(string $id)
    {
        if (!$id) return;

        $where = [
            'id' => $id,
        ];
        $app = $this->all()->where($where)->get()->row();
        if(!$app) return false;
        return $app;
    }

    /**
     * Get apps by column where cluase
     */
    public function where(array $where)
    {
        return $this->db->get_where($this->table, $where);
    }

    /**
     * Get all apps
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

    public function canViewAny($user){
        return auth()->allow();
    }

    public function canView($user, $model){
        return auth()->allow();
    }

    public function canCreate($user){
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

    public function canDelete($user, $model){
        if (!auth()->authorized()) {
            httpReponseError('Unauthorized Access!', 401);
        }
        return auth()->allow();
    }
}
