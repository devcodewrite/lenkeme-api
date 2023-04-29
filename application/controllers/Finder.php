<?php
defined('BASEPATH') or exit('No direct script allowed');

class Finder extends MY_Controller
{

    /**
     * Show list of resources
     * @return http json
     */
    public function find_artisans()
    {
        $start = $this->input->get('start');
        $length = $this->input->get('length');
        $inputs = $this->input->get();

        $query = $this->user->all()
            ->distinct()
            ->join('user_jobs', 'user_jobs.user_id=users.id')
            ->join('jobs', 'jobs.id=user_jobs.job_id');

        $where = [
            'users.status' => 'active',
            'users.user_type' => 'artisan'
        ];
        if ($this->input->get('jobs')) {
            foreach (explode(',', $inputs['jobs']) as $job) {
                $inputs = array_merge($inputs, [
                    'jobs.title' => $job,
                    'jobs.description' => $job
                ]);
            }
            unset($inputs['jobs']);
        }

        $fields = [
            'jobs.title',
            'jobs.description',
            'users.firstname',
            'users.lastname',
            'users.display_name',
            'user_jobs.location',
            'users.city',
            'users.country'
        ];
        $query->group_start();
        foreach ($fields as  $field) {
            $query->or_like($field, $inputs['keywords'], 'both');
        }
        $query->group_end();
        unset($inputs['keywords']);

        $query->group_start();
        $query->like(1);
        foreach ($inputs as $key => $val) {
            if (!empty(trim($val)))
                $query->like($key, $val, 'both');
        }
        $query->group_end();
        $query->where($where);

        $out = json($query, $start, $length, $inputs, function($item){
            return (object)array_merge((array)$item, [ 
                'jobs'=>$this->job->all()
                    ->join('user_jobs', 'user_jobs.job_id=jobs.id')
                    ->where('user_jobs.user_id', $item->id)
                    ->get()
                    ->result()
            ]);
        });
        $out = array_merge($out, [
            'input' => $this->input->get(),
        ]);
        httpResponseJson($out);
    }

    /**
     * Show list of resources
     * @return http json
     */
    public function suggest_artisans()
    {

        $start = $this->input->get('start');
        $length = inputJson('length', 20);
        $inputs = $this->input->get();

        $query = $this->job->all2()
            ->distinct()
            ->select('concat(jobs.title,"-",ifnull(user_jobs.location,""),",",users.city) as suggestion', true)
            ->select(['jobs.title', 'users.city', 'users.country', 'user_jobs.location'])
            ->join('user_jobs', 'user_jobs.job_id=jobs.id')
            ->join('users', 'users.id=user_jobs.user_id');

        $where = [
            'users.status' => 'active',
            'users.user_type' => 'artisan'
        ];
        if ($this->input->get('jobs')) {
            foreach (explode(',', $inputs['jobs']) as $job) {
                $inputs = array_merge($inputs, [
                    'jobs.title' => $job,
                    'jobs.description' => $job
                ]);
            }
            unset($inputs['jobs']);
        }
        $query->group_start();
        $query->or_like('jobs.title', $inputs['keywords'],  'both');
        $query->or_like('jobs.description', $inputs['keywords'],  'both');
        $query->or_like('users.firstname', $inputs['keywords'],  'both');
        $query->or_like('users.lastname', $inputs['keywords'],  'both');
        $query->or_like('users.display_name', $inputs['keywords'],  'both');
        $query->or_like('users.city', $inputs['keywords'],  'both');
        $query->group_end();
        unset($inputs['keywords']);

        $query->group_start();
        $query->like(1);
        foreach ($inputs as $key => $val) {
            if (!empty(trim($val)))
                $query->like($key, $val, 'both');
        }
        $query->group_end();

        $query->where($where);

        $out = json($query, $start, $length, $inputs);
        $out = array_merge($out, [
            'input' => $this->input->get(),
        ]);
        httpResponseJson($out);
    }
}
