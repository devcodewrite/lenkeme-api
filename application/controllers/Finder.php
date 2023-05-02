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
        $page = $this->input->get('page');
        $length = $this->input->get('length');
        $inputs = $this->input->get();

        if (stripos(trim($this->input->get('keywords')), '@') === 0) {
            $query = $this->user->all()
                ->distinct()
                ->join('user_jobs', 'user_jobs.user_id=users.id', 'left')
                ->join('jobs', 'jobs.id=user_jobs.job_id', 'left');

            $query->like('users.username', ltrim($inputs['keywords'], '@'), 'both');
        } else {
            $query = $this->user->all()
                ->distinct()
                ->join('user_jobs', 'user_jobs.user_id=users.id')
                ->join('jobs', 'jobs.id=user_jobs.job_id');

            $fields = [
                'jobs.title',
                'user_jobs.location',
                'users.city',
                'users.country'
            ];
            $query->group_start();
            foreach ($fields as $key => $field) {
                foreach (str_split($inputs['keywords']) as $s) {
                    $query->or_like($field, $s, 'both');
                }
            }
            $query->group_end();
        }

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

        unset($inputs['keywords']);
        unset($inputs['length']);
        unset($inputs['page']);

        $query->where($where);

        $out = json($query, $page, $length, $inputs, function ($item) {
            return (object)array_merge((array)$item, [
                'jobs' => $this->job->all()
                    ->join('user_jobs', 'user_jobs.job_id=jobs.id')
                    ->where('user_jobs.user_id', $item->id)
                    ->get()
                    ->result()
            ]);
        });
        if ($out)
            $out = array_merge($out, [
                'input' => $this->input->get(),
            ]);
        else  $out = [
            'status' => false,
            'input' => $this->input->get(),
        ];
        httpResponseJson($out);
    }

    /**
     * Show list of resources
     * @return http json
     */
    public function suggest_artisans()
    {
        $page = $this->input->get('page');
        $length = inputJson('length', 20);
        $inputs = $this->input->get();

        $where = [
            'users.status' => 'active',
            'users.user_type' => 'artisan'
        ];

        if (stripos(trim($this->input->get('keywords')), '@') === 0) {
            $query = $this->user->all()
                ->distinct()
                ->select('concat("@",users.username) as suggestion')
                ->join('user_jobs', 'user_jobs.user_id=users.id', 'left');
            $query->group_start();
            $query->like('users.username', ltrim($inputs['keywords'], '@'), 'both');
            $query->group_end();
        } else {
            $query = $this->job->all2()
                ->distinct()
                ->select('concat(jobs.title,"-",ifnull(user_jobs.location,""),",",users.city) as suggestion', true)
                ->select(['jobs.title as job_title', 'users.city', 'users.country', 'user_jobs.location'])
                ->join('user_jobs', 'user_jobs.job_id=jobs.id')
                ->join('users', 'users.id=user_jobs.user_id');

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
            $query->or_like('users.city', $inputs['keywords'],  'both');
            $query->group_end();
        }

        unset($inputs['keywords']);
        unset($inputs['length']);
        unset($inputs['page']);

        $query->group_start();
        $query->like(1);

        foreach ($inputs as $key => $val) {
            if (!empty(trim($val)))
                $query->like($key, $val, 'both');
        }
        $query->group_end();
        $query->where($where);

        $out = json($query, $page, $length, $inputs);
        if ($out)
            $out = array_merge($out, [
                'input' => $this->input->get(),
            ]);
        else  $out = [
            'status' => false,
            'input' => $this->input->get(),
        ];
        httpResponseJson($out);
    }
}
