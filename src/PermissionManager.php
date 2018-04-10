<?php namespace Datlv\Authority;

use Illuminate\Support\Collection;
use Kit;
use Authority;

/**
 * Class PermissionManager
 *
 * @package Datlv\Authority
 */
class PermissionManager extends Collection
{
    /**
     * CRUD actions
     *
     * @var array
     */
    protected $crud = ['create', 'read', 'update', 'delete'];

    /**
     * Đăng ký CRUD actions của $model
     *
     * @param string|mixed $model
     *
     * @return static
     */
    public function registerCRUD($model)
    {
        foreach ($this->crud as $action) {
            $this->register($model, $action);
        }

        return $this;
    }

    /**
     * Đăng lý một $action đối với $model
     *
     * @param string|mixed $model
     * @param $action
     * @param string $title
     *
     * @return static
     */
    public function register($model, $action, $title = null)
    {
        $permission = [];
        $permission['title'] = $title ?: "trans::authority::permission.{$action}";
        $permission['class'] = Kit::getClass($model);
        $permission['alias'] = Kit::alias($model);
        $permission['action'] = $action;
        $permission['id'] = $permission['alias'] . '.' . $permission['action'];

        return $this->put($permission['id'], $permission);
    }

    /**
     * Lấy permission $id
     *
     * @param string $id
     *
     * @return array
     */
    public function getOrFail($id)
    {
        abort_unless($this->has($id), 500, "Unregistered Permission $id!");

        return $this->get($id);
    }

    /**
     * Tất cả permissions, nhóm theo model => sử dụng cho permissions selectize
     *
     * @return array
     */
    public function groupByModel()
    {
        return $this->groupBy(function ($item) {
            return Kit::title($item['class']);
        })->map(function (Collection $permissions) {
            return $permissions->mapWithKeys(function ($item) {
                return [$item['id'] => mb_fn_str($item['title'])];
            })->all();
        })->all();
    }

    /**
     * @param string|\Datlv\Authority\Role $role
     *
     * @return static
     */
    public function attachedTo($role)
    {
        return $this->only(Authority::role($role)->permissions(true)->pluck('permission_id')->all());
    }
}
