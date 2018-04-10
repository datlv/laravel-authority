<?php
namespace Datlv\Authority;

use DB;

/**
 * Class BackendManager
 *
 * @property-read \Datlv\Authority\Role[] $roles
 * @property-read integer[] $countUsers
 * @package Datlv\Authority
 */
class BackendManager
{
    /**
     * @var array
     */
    private $data;

    /**
     * BackendManager constructor.
     */
    public function __construct()
    {
        $this->data = ['roles' => [], 'countUsers' => []];

        foreach (config('authority.roles') as $group => $roles) {
            $this->data['roles'][$group] = [];
            $this->data['countUsers'][$group] = [];
            foreach ($roles as $name => $level) {
                $this->data['roles'][$group][$name] = new Role("{$group}.{$name}", $level);
                $this->data['countUsers'][$group][$name] = null;
            }
        }

        $counted = DB::table('role_user')
            ->select(DB::raw('role_group, role_name, count(*) as user_count'))
            ->groupBy('role_group', 'role_name')
            ->get();
        foreach ($counted as $count) {
            if (isset($this->data['roles'][$count->role_group][$count->role_name])) {
                $this->data['countUsers'][$count->role_group][$count->role_name] = $count->user_count;
            }
        }
    }

    /**
     * @param string $name
     *
     * @return mixed
     */
    public function __get($name)
    {
        return array_key_exists($name, $this->data) ? $this->data[$name] : null;
    }
}
