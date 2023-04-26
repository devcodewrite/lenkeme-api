<?php
defined('BASEPATH') or exit('Direct acess is not allowed');

class Permission_model extends CI_Model
{
    protected $table = 'permissions';


    public function create(array $record)
    {
        if (!$record) return;
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

        foreach($this->modules() as $row){
            if($row->name === 'is_super_admin'
            || $row->name === 'is_admin') continue;
            $data[$row->name] = isset($data[$row->name])?implode(',',$data[$row->name]):'';
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
        return $this->db->delete($this->table, ['id' => $id]);
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
     * Get permission by id
     */
    public function find(int $id)
    {
        $where = [
            'id' => $id,
        ];
        return $this->all()->where($where)->get()->row();
    }

    /**
     * Get permissions by column where cluase
     */
    public function where(array $where)
    {
        return $this->db->get_where($this->table, $where);
    }

    /**
     * Get all permissions
     */
    public function all()
    {
        $fields = [];
        return
            $this->db->select($fields, true)
            ->from($this->table);
    }

    /**
     * Get all users that belongs to this permission id
     */
    public function users(int $id)
    {
        $rtable = 'users';

        return $this->db->select("$rtable.*")
            ->from($rtable)
            ->where(['permission_id' => $id])
            ->where("$rtable.deleted_at =", null)
            ->get()
            ->result();
    }

    public function modules()
    {
        return array_filter($this->db->field_data($this->table), function ($val) {
            return !in_array($val->name, ['updated_at', 'created_at','id']);
        });
    }

    public function canViewAny($user){
        $role = $this->user->find($user->id)->role;
        if ($role)
            return
                $role->permission->is_admin === '1'
                ? auth()->allow() : (in_array('view', explode(',', $role->permission->permissions))?auth()->allow()
                :auth()->deny("You don't have permission to view this recored."));
        return auth()->deny("You don't have permission to view this recored.");
    }

    public function canView($user, $model){
        $role = $this->user->find($user->id)->role;
        if ($role)
            return
                $role->permission->is_admin === '1'
                ? auth()->allow() : (in_array('view', explode(',', $role->permission->permissions))?auth()->allow()
                :auth()->deny("You don't have permission to view this recored."));
        return auth()->deny("You don't have permission to view this recored.");
    }

    public function canCreate($user){
        $role = $this->user->find($user->id)->role;
        if ($role)
            return
                $role->permission->is_admin === '1'
                ? auth()->allow() : (in_array('create', explode(',', $role->permission->permissions))?auth()->allow()
                :auth()->deny("You don't have permission to create this record."));
        return auth()->deny("You don't have permission to create this record.");
    }

    public function canUpdate($user, $model){
        $role = $this->user->find($user->id)->role;
        if ($role)
            return
                $role->permission->is_admin === '1'
                ? auth()->allow() : (in_array('update', explode(',', $role->permission->permissions))?auth()->allow()
                :auth()->deny("You don't have permission to update this record."));
        return auth()->deny("You don't have permission to update this record.");
    }

    public function canDelete($user, $model){
        $role = $this->user->find($user->id)->role;
        if ($role)
            return
                $role->permission->is_admin === '1'
                ? auth()->allow() : (in_array('delete', explode(',', $role->permission->permissions))?auth()->allow()
                :auth()->deny("You don't have permission to delete this record."));
        return auth()->deny("You don't have permission to delete this record.");
    }
}
