<?php

namespace Datlv\Authority;

/**
 * Class Manager
 *
 * @package Datlv\Authority
 */
class Manager
{
    /**
     * @var PermissionManager
     */
    protected $permissionManager;
    /**
     * Cache danh sách Role object, ['name' => Role]
     *
     * @var \Datlv\Authority\Role[]
     */
    protected $roles = [];

    /**
     * @var array
     */
    protected $role_groups = [];

    /**
     * @var \Datlv\Authority\User[]
     */
    protected $users = [];

    /**
     * Đã đếm số user cho từ role
     *
     * @var bool
     */
    protected $userCounted = false;

    public function __construct()
    {
        $this->role_groups = config('authority.role_groups');
    }


    /**
     * Authority của user hiện tại
     * Ví dụ sử dụng: Authority::user()->is('sys.admin')
     *
     * @param int|\Datlv\User\User $id
     *
     * @return \Datlv\Authority\User
     */
    public function user($id = null)
    {
        $user = user_model($id);
        abort_if(is_null($user), 500, 'User model not found!');
        if (empty($this->users[$user->id])) {
            $this->users[$user->id] = new User($user);
        }

        return $this->users[$user->id];
    }

    /**
     * Quản lý role $id
     * Sử dụng: Authority::role(sys.sadmin)->users()
     *
     * @param string|\Datlv\Authority\Role $id
     *
     * @return Role
     */
    public function role($id)
    {
        if (!is_string($id)) {
            return $id;
        }
        if (empty($this->roles[$id])) {
            $level = config("authority.roles.$id");
            abort_unless($level && $this->validate($id), 500, "Error: Undefined Role $id !");

            $this->roles[$id] = new Role($id, $level);
        }

        return $this->roles[$id];
    }

    /**
     * @param string $name
     *
     * @return \Datlv\Authority\PermissionManager|array
     */
    public function permission($name = null)
    {
        if (is_null($this->permissionManager)) {
            $this->permissionManager = new PermissionManager();
        }

        return is_null($name) ? $this->permissionManager : $this->permissionManager->getOrFail($name);
    }

    /**
     * @param $id
     *
     * @return boolean
     */
    public function definedRole($id)
    {
        return !is_null(config("authority.roles.$id"));
    }

    /**
     * Kiểm tra role ID, chắc chắn có dạng: group.name
     *
     * @param string $role
     *
     * @return bool
     */
    public function validate($role)
    {
        return preg_match('/^[a-z0-9_]+\.[a-z0-9_]+$/', $role);
    }

    /**
     * Lấy danh sách roles từ $argument, phân các role bằng ',' hoặc '|'
     * Sử dụng:
     * - parserRoles(['sys.admin', 'sys.sadmin'])
     * - parserRoles('sys.admin|sys.sadmin')
     * - parserRoles('administrator,sys.tester') ==> administrator: role group ==> chuyển đổi thành các role của nó
     *
     * @param string|array $argument
     * @param string $delimiter
     *
     * @return array
     */
    public function parserRoles($argument, $delimiter = ',|')
    {
        $roles = is_array($argument) ? $argument : preg_split("/ ?[$delimiter] ?/", $argument);
        $result = [];
        foreach ($roles as $role) {
            if (isset($this->role_groups[$role])) {
                // role group
                $result = array_merge($result, $this->role_groups[$role]);
            } else {
                // role bình thường
                $result[] = $role;
            }
        }

        return $result;
    }
}
