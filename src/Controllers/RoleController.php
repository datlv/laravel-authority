<?php

namespace Datlv\Authority\Controllers;

use Datlv\Authority\BackendManager;
use Datlv\Authority\Role;
use Datlv\Kit\Extensions\BackendController;
use Datlv\User\User;
use Authority;

/**
 * Class RoleController
 *
 * @package Datlv\Authority
 */
class RoleController extends BackendController
{
    /**
     * @var \Datlv\Authority\BackendManager;
     */
    protected $manager;

    public function __construct()
    {
        parent::__construct();
        $this->manager = new BackendManager();
    }

    /**
     * @return \Illuminate\Contracts\View\Factory|\Illuminate\View\View
     */
    public function index()
    {
        $this->buildHeading(
            trans('authority::common.manage'),
            'fa-male',
            ['#' => trans('authority::common.roles')]
        );

        return view('authority::role.index', [
            'roles'      => $this->manager->roles,
            'countUsers' => $this->manager->countUsers,
        ]);
    }

    /**
     * @param string $id
     *
     * @return \Illuminate\Contracts\View\Factory|\Illuminate\View\View
     */
    public function show($id)
    {
        $role = $this->getRole($id);
        $this->buildHeading(
            [trans('authority::common.roles') . ':', $role->full_title],
            'fa-male',
            [route('backend.role.index') => trans('authority::common.roles'), '#' => $role->full_title]
        );
        // Tất cả users đã được gán role này
        $users = $role->users();
        // 10 users khác chưa gán $role
        $selectize_users = User::forSelectize($users->pluck('id'), 10)->get()->all();

        // Permissions
        $attached_permissions = Authority::permission()->attachedTo($role);
        $permissions = $attached_permissions->groupByModel();
        $selectize_permissions = Authority::permission()->except($attached_permissions->keys()->all())->groupByModel();

        return view('authority::role.show',
            compact('role', 'users', 'selectize_users', 'permissions', 'selectize_permissions'));
    }

    // User Manage ----------------------------------------------------

    /**
     * @param string $id
     * @param \Datlv\User\User $user
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function attachUser($id, User $user)
    {
        $this->getRole($id)->attachUser($user->id);

        return response()->json(
            [
                'type'    => 'success',
                'content' => trans('authority::common.attach_user_success'),
            ]
        );
    }

    /**
     * @param string $id
     * @param \Datlv\User\User $user
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function detachUser($id, User $user)
    {
        $this->getRole($id)->detachUser($user->id);

        return response()->json(
            [
                'type'    => 'success',
                'content' => trans('authority::common.detach_user_success'),
            ]
        );
    }

    /**
     * @param string $id
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function detachAllUser($id)
    {
        $this->getRole($id)->detachUser();

        return response()->json(
            [
                'type'    => 'success',
                'content' => trans('authority::common.detach_all_user_success'),
            ]
        );
    }

    // Permission Manage ----------------------------------------------------

    /**
     * @param string $id
     * @param string $permission_id
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function attachPermission($id, $permission_id)
    {
        $this->getRole($id)->attachPermission($permission_id);

        return response()->json(
            [
                'type'    => 'success',
                'content' => trans('authority::common.attach_permission_success'),
            ]
        );
    }

    /**
     * @param string $id
     * @param string $permission_id
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function detachPermission($id, $permission_id)
    {
        $this->getRole($id)->detachPermission($permission_id);

        return response()->json(
            [
                'type'    => 'success',
                'content' => trans('authority::common.detach_permission_success'),
            ]
        );
    }

    /**
     * @param string $id
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function detachAllPermission($id)
    {
        $this->getRole($id)->detachPermission();

        return response()->json(
            [
                'type'    => 'success',
                'content' => trans('authority::common.detach_all_permission_success'),
            ]
        );
    }

    /**
     * @param string $id
     *
     * @return Role
     */
    protected function getRole($id)
    {
        abort_unless(authority()->definedRole($id), 404, trans('authority::role.invalid'));

        return authority()->role($id);
    }
}
