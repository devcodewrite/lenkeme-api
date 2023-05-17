<?php
defined('BASEPATH') or exit('Direct acess is not allowed');

class Postapproval_model extends CI_Model
{
    public $table = 'post_approvals';

    public function create(array $record)
    {
        if (!$record) return;
        $data = $this->extract($record);
        return $this->db->insert($this->table, $data);
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
            "{$this->table}.user_post_id" => $id,
        ];
        $post_approval = $this->all()
            ->where($where)
            ->get()
            ->result();
        return $post_approval;
    }

    /**
     * Get post_approvals by column where cluase
     */
    public function where(array $where)
    {
        return $this->db->get_where($this->table, $where);
    }

    /**
     * Get all post_approvals
     */
    public function all()
    {
        $fields = [];

        foreach ($this->db->field_data($this->job->table) as $field_data) {
            if (in_array($field_data->name, $this->job->hidden)) continue; // skip hidden fields
            array_push($fields, "{$this->job->table}.$field_data->name");
        }
        return $this->db->select($fields)
            ->from($this->table)
            ->join('user_posts', 'user_posts.id=post_approvals.user_post_id');
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

    public function canCreate($user)
    {
        if (!auth()->authorized()) {
            httpReponseError('Unauthorized Access!', 401);
        }
        return auth()->allow();
    }

    public function canUpdate($user, $model)
    {
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
