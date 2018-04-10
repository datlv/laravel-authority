<?php
namespace Minhbang\Authority\Middleware;

use Closure;

/**
 * Class Role
 * Sử dụng:
 * - 'middleware' => 'role:sys.admin'
 * - 'middleware' => 'role:sys.admin,all'
 * - 'middleware' => 'role:sys.*,all,exact'
 *
 * @package Minhbang\User\Middleware
 */
class Role
{
    /**
     * @param \Illuminate\Http\Request $request
     * @param \Closure $next
     * @param string $role Một hoặc nhiều Role
     * @param string $all Được gán tất cả roles
     * @param string $exact Chính xác được gán $role hay có role level cao hơn
     *
     * @return mixed
     */
    public function handle($request, Closure $next, $role, $all = null, $exact = null)
    {
        if (auth()->check()) {
            if (user_is($role, $all === 'all', $exact === 'exact')) {
                return $next($request);
            } else {
                return response(trans('common.forbidden'), 403);
            }
        } else {
            if ($request->ajax()) {
                return response('Unauthorized.', 401);
            } else {
                return redirect()->guest(route('auth.login'));
            }
        }
    }
}
