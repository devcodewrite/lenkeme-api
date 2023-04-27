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
        $start = inputJson('start', 0);
        $length = inputJson('length', 100);
        $inputs = $this->input->get();

        $query = $this->user->all()
            ->distinct()
            ->join('user_jobs', 'user_jobs.user_id=users.id')
            ->join('jobs', 'jobs.id=user_jobs.job_id');

        $where = [
            'users.status' => 'active',
        ];
        
        $query->group_start();
        $query->or_like('concat(jobs.title,"-",user_jobs.location,",",users.city)', $inputs['keywords'], 'both');
        $query->group_end();
        unset($inputs['keywords']);

        $query->group_start();
        $query->like('1');
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

    /**
     * Show list of resources
     * @return http json
     */
    public function suggest_artisans()
    {

        $start = inputJson('start', 0);
        $length = inputJson('length', 20);
        $inputs = $this->input->get();

        $query = $this->job->all()
            ->select('concat(jobs.title,"-",user_jobs.location,",",users.city) as suggestion',true)
            ->join('user_jobs', 'user_jobs.job_id=jobs.id')
            ->join('users', 'users.id=user_jobs.user_id');

        $where = [
            'users.status' => 'active',
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
        $query->or_like('jobs.title', $inputs['keywords']);
        $query->or_like('jobs.description', $inputs['keywords']);
        $query->or_like('users.firstname', $inputs['keywords']);
        $query->or_like('users.lastname', $inputs['keywords']);
        $query->or_like('users.display_name', $inputs['keywords']);
        $query->or_like('users.city', $inputs['keywords']);
        $query->group_end();
        unset($inputs['keywords']);

        $query->group_start();
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
