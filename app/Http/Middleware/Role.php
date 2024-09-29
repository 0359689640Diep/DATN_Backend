<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class Role
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next, $role): Response
    {
        /**
         * * 0 = user
         * * 1 = admin
         */
        // dd((int)$role !== $request->user()->role);

        $userRole = $request->user()->role;
        $role = (int) $role;

        //* chuyển luôn sang 403 khi k có quyền access
        // if ($request->user()->role !== $role) {
        //     abort(403, 'You don\'t have permission to access this page.');
        // }

        /* Nếu vai trò của người dùng hiện tại 
            là user (0) và vai trò yêu cầu không phải 
            là user, chuyển hướng người dùng đến trang dashboard.
        */
        if ($userRole === 0 && $role !== 0) {
            return redirect(''); //! Route User bên FE
        }
        if ($userRole === 1 && $role !== 1) {
            return redirect('/admin/dashboard');
        }
        if ($userRole === 2 && $role !== 2) {
            return redirect('/role3/dashboard');
        }

        return $next($request);
    }
}
