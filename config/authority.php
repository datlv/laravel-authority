<?php
return [
    // Định nghĩa menus cho authority
    'menus'       => [
        'backend.sidebar.user.role' => [
            'priority' => 3,
            'url'      => 'route:backend.role.index',
            'label'    => 'trans:authority::common.roles',
            'icon'     => 'fa-male',
            'active'   => 'backend/role*',
        ],
    ],
    // Ai được phép thay đổi các chính sách phân quyền
    'middlewares' => [
        'role' => ['web', 'role:sys.sadmin'],
    ],
    /**
     * Định nghĩa các chức vụ
     * - Group: nhóm chức vụ, tên nhóm phải duy nhất
     * - Role: chức vụ, tên chức vụ duy nhất trong nhóm
     * - Trong một nhóm, Role có level cao hơn sẽ kế thừa các permission của role thấp hơn
     *
     * ==> Định danh một Role: 'group.role'
     */
    'roles'       => [
        // hệ thống
        'sys' => [
            // Admin
            'admin'  => 100,
            'sadmin'  => 200,
        ],
    ],
    /**
     * Định nghĩa các nhóm chức vụ, nhóm 1 số roles để ghi ngắn gọn (có nghĩa)
     */
    'role_groups' => [
        'administrator' => ['sys.admin'],
    ],
];