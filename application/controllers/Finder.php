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
        $length = inputJson('length', 20);
        $inputs = $this->input->get();
        $authUser = auth()->user();
        $where = ['users.status' => 'active', 'users.user_type' => 'artisan'];

        if (stripos(trim($this->input->get('keywords')), '@') === 0) {
            $query = $this->user->all()
                ->join('user_jobs', 'user_jobs.user_id=users.id', 'left')
                ->group_by('users.id');
            $query->group_start();
            $query->like('users.username', ltrim($inputs['keywords'], '@'), 'both');
            $query->group_end();

            $query->where($where);
            $out = json($query, $page, $length, $inputs, function ($item) use ($authUser) {
                if ($authUser) {
                    $favUser = $this->favourite->find($authUser->id, $item->id);
                    $item->is_favourite = $favUser ? true : false;
                }
                $item->jobs = $this->job->all()
                    ->join('user_jobs', 'user_jobs.job_id=jobs.id')
                    ->where('user_jobs.user_id', $item->id)
                    ->get()
                    ->result();

                return $item;
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
        } else {
            $query = $this->user->all()
                ->join('user_jobs', 'user_jobs.user_id=users.id')
                ->join('jobs', 'jobs.id=user_jobs.job_id')
                ->group_by('users.id');

            $query->group_start();
            $query->or_like('jobs.title', $inputs['keywords'],  'both');
            $query->or_like('jobs.description', $inputs['keywords'],  'both');
            $query->or_like('concat("%\'",jobs.title," in ",user_jobs.location,"\'%")', $inputs['keywords'],  'both', false);
            $query->or_like('users.city', $inputs['keywords'],  'both');
            $query->group_end();

            if ($this->input->get('jobs')) {
                $query->group_start();
                foreach (explode(',', $inputs['jobs']) as $job) {
                    $query->where('jobs.id', $job);
                }
                $query->group_end();
            }
            $query->where($where);

            $out = json($query, $page, $length, $inputs, function ($item) use($authUser) {
                if ($authUser) {
                    $favUser = $this->favourite->find($authUser->id, $item->id);
                    $item->is_favourite = $favUser ? true : false;
                }
                $item->jobs = $this->job->all()
                    ->join('user_jobs', 'user_jobs.job_id=jobs.id')
                    ->where('user_jobs.user_id', $item->id)
                    ->get()
                    ->result();

                return $item;
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

        $where = ['users.status' => 'active', 'users.user_type' => 'artisan'];

        if (stripos(trim($this->input->get('keywords')), '@') === 0) {
            $query = $this->user->all2()
                ->distinct()
                ->select('concat("@",users.username) as suggestion, users.id as user_id')
                ->join('user_jobs', 'user_jobs.user_id=users.id', 'left');
            $query->group_start();
            $query->like('users.username', ltrim($inputs['keywords'], '@'), 'both');
            $query->group_end();

            $query->where($where);
            $out = json($query, $page, $length, $inputs, function ($item) {
                return (object)array_merge((array)$item, [
                    'user' => isset($item->user_id) ? $this->user->all()
                        ->where('users.id', $item->user_id)
                        ->get()
                        ->result() : null
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
        } else {
            $q =
                '(CASE WHEN  jobs.title LIKE "%' . $inputs['keywords']
                . '%" THEN  concat(jobs.title," in ",user_jobs.location) WHEN concat("%\'",jobs.title," in ",user_jobs.location,"\'%") LIKE "%'
                . $inputs['keywords'] . '%" THEN concat(jobs.title," in ",user_jobs.location) WHEN jobs.description LIKE "%'
                . $inputs['keywords'] . '%" THEN jobs.description WHEN user_jobs.location LIKE "%'
                . $inputs['keywords'] . '%" THEN user_jobs.location WHEN users.city LIKE "%'
                . $inputs['keywords'] . '%" THEN concat(user_jobs.location,",",users.city) ELSE concat(jobs.title," in ",users.city) END)';

            $query = $this->job->all2()
                ->select([
                    "$q as suggestion",
                    'jobs.title',
                    'jobs.description',
                    'user_jobs.location',
                    'users.city',
                    'user_jobs.user_id'
                ], false)
                ->join('user_jobs', 'user_jobs.job_id=jobs.id')
                ->join('users', 'users.id=user_jobs.user_id');

            $query->group_start();
            $query->or_like('jobs.title', $inputs['keywords'],  'both');
            $query->or_like('jobs.description', $inputs['keywords'],  'both');
            $query->or_like('concat("%\'",jobs.title," in ",user_jobs.location,"\'%")', $inputs['keywords'],  'both', false);
            $query->or_like('users.city', $inputs['keywords'],  'both');
            $query->group_end();

            if ($this->input->get('jobs')) {
                $query->group_start();
                foreach (explode(',', $inputs['jobs']) as $job) {
                    $query->where('jobs.id', $job);
                }
                $query->group_end();
            }
            $query->where($where);

            $out = json($query, $page, $length, $inputs, function ($item) {
                return (object)array_merge((array)$item, [
                    'user' => isset($item->user_id) ? $this->user->all()
                        ->where('users.id', $item->user_id)
                        ->get()
                        ->result() : null
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
    }
}
