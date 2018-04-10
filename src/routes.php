<?php
Route::group(
    ['prefix' => 'backend', 'namespace' => 'Datlv\Authority\Controllers'],
    function () {
        // Role Manage
        Route::group(
            ['prefix' => 'role', 'as' => 'backend.role.', 'middleware' => config('authority.middlewares.role')],
            function () {
                Route::get('/', ['as' => 'index', 'uses' => 'RoleController@index']);
                Route::get('{role}', ['as' => 'show', 'uses' => 'RoleController@show']);
                // Link User
                Route::group(
                    ['prefix' => '{role}/user', 'as' => 'user.'],
                    function () {
                        Route::post('{user}', ['as' => 'attach', 'uses' => 'RoleController@attachUser']);
                        Route::delete('{user}', ['as' => 'detach', 'uses' => 'RoleController@detachUser']);
                        Route::delete('/', ['as' => 'detach_all', 'uses' => 'RoleController@detachAllUser']);
                    }
                );
                // Link Permission
                Route::group(
                    ['prefix' => '{role}/permission', 'as' => 'permission.'],
                    function () {
                        Route::post('{permission}', ['as' => 'attach', 'uses' => 'RoleController@attachPermission']);
                        Route::delete('{permission}', ['as' => 'detach', 'uses' => 'RoleController@detachPermission']);
                        Route::delete('/', ['as' => 'detach_all', 'uses' => 'RoleController@detachAllPermission']);
                    }
                );
            }
        );
    }
);
