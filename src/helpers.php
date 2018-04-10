<?php
if (!function_exists('authority')) {
    /**
     * @return \Minhbang\Authority\Manager
     */
    function authority()
    {
        return app('authority');
    }
}

if (!function_exists('user_is')) {
    /**
     *
     * @param string $role
     * @param bool $all
     * @param bool $exact
     *
     * @return \Minhbang\Authority\Manager
     */
    function user_is($role, $all = false, $exact = false)
    {
        return user_model(null, false) && app('authority')->user()->is($role, $all, $exact);
    }
}