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

        //$auser = auth()->user();
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
            $query->or_like('concat("%\'",jobs.title," __ ",users.address,"\'%")', $inputs['keywords'],  'both', false);
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
            //  $auser = auth()->user();

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

            $conj = "";
            foreach (explode(' ', trim($inputs['keywords'])) as $key) {
                if (in_array(strtolower(trim($key)), ['in', 'at', 'from'])) {
                    $conj = " $key ";
                    break;
                }
            }
            $q =
                '(CASE WHEN  jobs.title LIKE "%' . trim($inputs['keywords'])
                . '%" THEN concat(jobs.title,"' . $conj . '") WHEN users.address LIKE "%'
                . trim($inputs['keywords']) . '%" THEN concat(jobs.title," ",users.address,",",users.city) WHEN users.city LIKE "%'
                . trim($inputs['keywords']) . '%" THEN concat(jobs.title,",",users.city) WHEN concat(jobs.title,"' . $conj . '",ifnull(users.city,"")) LIKE "%'
                . trim($inputs['keywords']) . '%" THEN concat(jobs.title,"' . $conj . '",ifnull(users.city,""))  WHEN concat(jobs.title,"' . $conj . '",ifnull(users.address,"")) LIKE "%'
                . trim($inputs['keywords']) . '%" THEN concat(jobs.title,"' . ($conj==''?' ':$conj) . '",ifnull(users.address,""),",",users.city) WHEN jobs.description LIKE "%'
                . trim($inputs['keywords']) . '%" THEN jobs.description ELSE concat(jobs.title,"' . $conj . '",users.city) END)';

            $query = $this->job->all2()
                ->select([
                    "$q as suggestion",
                    'jobs.title',
                    'jobs.description',
                    'users.address',
                    'users.city',
                    'user_jobs.user_id'
                ], false)
                ->join('user_jobs', 'user_jobs.job_id=jobs.id','left')
                ->join('users', 'users.id=user_jobs.user_id', 'left');

            $query->group_start();
            $query->or_like('jobs.title', trim($inputs['keywords']),  'both');
            $query->or_like('jobs.description', trim($inputs['keywords']),  'both');
            $query->or_like('concat(jobs.title,"' . $conj . '",ifnull(users.city,""))', trim($inputs['keywords']),  'both');
            $query->or_like('concat(jobs.title,"' . ($conj==''?' ':$conj) . '",ifnull(users.address,""),",",users.city)', trim($inputs['keywords']),  'both');
            $query->or_like('users.city', trim($inputs['keywords']),  'both');
            $query->or_like('users.address', trim($inputs['keywords']),  'both');
            $query->group_end();

            if ($this->input->get('jobs')) {
                $query->group_start();
                foreach (explode(',', $inputs['jobs']) as $job) {
                    $query->where('jobs.id', $job);
                }
                $query->group_end();
            }
            $query->where($where);
            if (empty(trim($conj))) $query->group_by('user_jobs.job_id');

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
