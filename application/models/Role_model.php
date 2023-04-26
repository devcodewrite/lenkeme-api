<?php
defined('BASEPATH') or exit('Direct acess is not allowed');

class Role_model extends CI_Model
{
    protected $table = 'roles';

    public function create(array $record)
    {
        if (!$record) return;
        $record['user_id'] = auth()->user()->id;

        $data = $this->extract($record);

        $perm = $this->perm->create(['users'=>'','members'=>'']);

        $data['permission_id'] = $perm->id;

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
     * Get role by id
     */
    public function find(int $id)
    {
        if (!$id) return;

        $where = [
            'id' => $id,
        ];
        $role = $this->all()->where($where)->get()->row();
        if(!$role) return false;

        $role->permission = $this->perm->find($role->permission_id);
       
        return $role;
    }

    /**
     * Get roles by column where cluase
     */
    public function where(array $where)
    {
        return $this->db->get_where($this->table, $where);
    }

    /**
     * Get all roles
     */
    public function all()
    {
        $fields = [];
        return
            $this->db->select($fields, true)
            ->from($this->table)
            ->where('id !=', 2);
    }

    /**
     * Get all users that belongs to this role id
     */
    public function users(int $id)
    {
        $rtable = 'users';

        return $this->db->select("$rtable.*")
            ->from($rtable)
            ->where(['role_id' => $id])
            ->where("$rtable.deleted_at =", null)
            ->get()
            ->result();
    }


    public function canViewAny($user){
        $role = $this->user->find($user->id)->role;
        if ($role)
            return
                $role->permission->is_admin === '1'
                ? auth()->allow() : (in_array('view', explode(',', $role->permission->roles))?auth()->allow()
                :auth()->deny("You don't have permission to view this recored."));
        return auth()->deny("You don't have permission to view this recored.");
    }

    public function canView($user, $model){
        $role = $this->user->find($user->id)->role;
        if ($role)
            return
                $role->permission->is_admin === '1'
                ? auth()->allow() : (in_array('view', explode(',', $role->permission->roles))?auth()->allow()
                :auth()->deny("You don't have permission to view this recored."));
        return auth()->deny("You don't have permission to view this recored.");
    }

    public function canCreate($user){
        $role = $this->user->find($user->id)->role;
        if ($role)
            return
                $role->permission->is_admin === '1'
                ? auth()->allow() : (in_array('create', explode(',', $role->permission->roles))?auth()->allow()
                :auth()->deny("You don't have permission to create this record."));
        return auth()->deny("You don't have permission to create this record.");
    }

    public function canUpdate($user, $model){
        $role = $this->user->find($user->id)->role;
        if ($role)
            return
                $role->permission->is_admin === '1'
                ? auth()->allow() : (in_array('update', explode(',', $role->permission->roles))?auth()->allow()
                :auth()->deny("You don't have permission to update this record."));
        return auth()->deny("You don't have permission to update this record.");
    }

    public function canDelete($user, $model){
        $role = $this->user->find($user->id)->role;
        if ($role)
            return
                $role->permission->is_admin === '1'
                ? auth()->allow() : (in_array('delete', explode(',', $role->permission->roles))?auth()->allow()
                :auth()->deny("You don't have permission to delete this record."));
        return auth()->deny("You don't have permission to delete this record.");
    }
}
