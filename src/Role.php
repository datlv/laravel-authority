<?php

namespace Datlv\Authority;

use DB;
use Datlv\User\User;
use Authority;

/**
 * Class Role
 * Quản lý 1 chức vụ
 *
 * @package Datlv\Authority
 */
class Role {
    /**
     * @var string
     */
    protected $table = 'role_user';
    /**
     * @var string
     */
    protected $table_permission = 'permission_role';
    /**
     * Cache danh sách users đã được gán role này
     *
     * @var \Datlv\User\User[]|\Illuminate\Database\Eloquent\Collection
     */
    protected $users;

    /**
     * @var int
     */
    protected $count_users = - 1;

    /**
     * @var string
     */
    protected $group;
    /**
     * @var string
     */
    protected $group_title;
    /**
     * @var string
     */
    protected $name;

    /**
     * = group.name
     *
     * @var string
     */
    protected $id;
    /**
     * @var string
     */
    protected $title;
    /**
     * @var string
     */
    protected $full_title;
    /**
     * @var int
     */
    protected $level;

    /**
     * URL xem chi tiết role trong backend
     *
     * @var string
     */
    protected $url;

    // Danh sách các thuộc tính được phép getter
    protected $attributes = [ 'id', 'group', 'group_title', 'name', 'title', 'full_title', 'level', 'url' ];

    /**
     * Cache
     *
     * @var \Illuminate\Support\Collection
     */
    protected $allPermissions;
    /**
     * Cache
     *
     * @var \Illuminate\Support\Collection
     */
    protected $immediatePermissions;

    /**
     * Role constructor.
     *
     * @param string $id
     * @param int $level
     */
    public function __construct( $id, $level ) {
        $this->id = $id;
        list( $this->group, $this->name ) = explode( '.', $id );
        $this->level = $level;
        $this->title = trans( "authority::role.{$this->id}" );
        $this->group_title = trans( "authority::role.{$this->group}.title" );
        $this->full_title = "{$this->title} ({$this->group_title})";
        $this->url = route( 'backend.role.show', [ 'role' => $this->id ] );
    }

    /**
     * Là cấp trên của role $id, cùng nhóm và có level cao hơn
     *
     * @param string $id
     *
     * @return boolean
     */
    public function isSuperiorOf( $id ) {
        $that = authority()->role( $id );

        return ( $this->group === $that->group ) && ( $this->level > $that->level );
    }

    /**
     * Danh sách roles cùng group, level thấp hơn
     *
     * @return static[]
     */
    public function inferiorities() {
        $result = [];
        $roles = config( "authority.roles.{$this->group}" );
        foreach ( $roles as $name => $level ) {
            if ( $level < $this->level ) {
                $result[] = Authority::role( $this->group . '.' . $name );
            }
        }

        return $result;
    }

    /**
     * Users đã được gán role này
     *
     * @return \Datlv\User\User[]|\Illuminate\Database\Eloquent\Collection
     */
    public function users() {
        if ( is_null( $this->users ) ) {
            $this->users = User::with( 'group' )
                               ->leftJoin( $this->table, "{$this->table}.user_id", '=', 'users.id' )
                               ->where( "{$this->table}.role_group", '=', $this->group )
                               ->where( "{$this->table}.role_name", '=', $this->name )
                               ->select( 'users.*' )->get();
        }

        return $this->users;
    }

    /**
     * Danh sách permissions đã cấp cho role này, $immediate =
     * - true:  Các permissions được cấp trực tiếp
     * - false: Bao gồm các permissions đã cấp cho các role "cùng group, level thấp hơn"
     *
     * @param bool $immediate
     *
     * @return \Illuminate\Support\Collection
     */
    public function permissions( $immediate = false ) {
        return $immediate ? $this->immediatePermissions() : $this->allPermissions();
    }

    /**
     * @param string $id
     * @param bool $immediate
     *
     * @return bool
     */
    public function hasPermission( $id, $immediate = false ) {
        return $this->permissions( $immediate )->contains( 'permission_id', $id );
    }

    /**
     * Danh sách tất cả permissions được cấp
     *
     * @return \Illuminate\Support\Collection
     */
    public function allPermissions() {
        if ( is_null( $this->allPermissions ) ) {
            $this->allPermissions = $this->immediatePermissions();
            foreach ( $this->inferiorities() as $role ) {
                $this->allPermissions->merge( $role->immediatePermissions() );
            }
        }

        return $this->allPermissions;
    }

    /**
     * Các permissions được cấp trực tiếp cho role
     *
     * @return \Illuminate\Support\Collection
     */
    public function immediatePermissions() {
        if ( is_null( $this->immediatePermissions ) ) {
            $this->immediatePermissions = DB::table( 'permission_role' )->where( 'role_id', $this->id )->get();
        }

        return $this->immediatePermissions;
    }


    /**
     * Đếm số users đã được gán role này
     *
     * @return int
     */
    public function countUsers() {
        if ( $this->count_users < 0 ) {
            $this->count_users = $this->users()->count();
        }

        return $this->count_users;
    }

    /**
     * @param int $value
     */
    public function setCountUsers( $value ) {
        $this->count_users = $value;
    }

    /**
     * @param int $user_id
     *
     * @return bool
     */
    public function attachUser( $user_id ) {
        return in_array( $user_id, $this->users()->pluck( 'id' )->all() ) ?
            true :
            DB::table( $this->table )->insert(
                [ 'user_id' => $user_id, 'role_group' => $this->group, 'role_name' => $this->name ]
            );
    }

    /**
     * Không có $user = detach all users
     *
     * @param int|null $user_id
     *
     * @return bool
     */
    public function detachUser( $user_id = null ) {
        $query = DB::table( $this->table )->where( 'role_group', $this->group )->where( 'role_name', $this->name );
        if ( $user_id ) {
            $query->where( 'user_id', '=', $user_id );
        }

        return $query->delete();
    }

    /**
     * @param string $id
     *
     * @return bool
     */
    public function attachPermission( $id ) {
        return $this->hasPermission( $id, true ) ||
               (
                   Authority::permission()->has( $id ) &&
                   DB::table( $this->table_permission )->insert( [ 'permission_id' => $id, 'role_id' => $this->id ] )
               );
    }

    /**
     * Không có permission -> detach all permissions
     *
     * @param string $id
     *
     * @return bool
     */
    public function detachPermission( $id = null ) {
        $query = DB::table( $this->table_permission )->where( 'role_id', $this->id );
        if ( $id ) {
            $query->where( 'permission_id', $id );
        }

        return $query->delete();
    }

    /**
     * Lấy giá trị các thuộc tính ($attributes)
     *
     * @param string $name
     * @param mixed $default
     *
     * @return mixed
     */
    public function get( $name = null, $default = null ) {
        return is_null( $name ) ? $this : ( in_array( $name, $this->attributes ) ? $this->{$name} : $default );
    }

    /**
     * @param string $name
     *
     * @return null|string
     */
    function __get( $name ) {
        return $this->get( $name );
    }
}
