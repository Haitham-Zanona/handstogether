<?php
namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class CheckAdmissionPermissions
{
    /**
     * Handle an incoming request.
     */
    public function handle(Request $request, Closure $next, ?string $permission = null): Response
    {
        $user = $request->user();

        // التحقق من تسجيل الدخول
        if (! $user) {
            return redirect()->route('login');
        }

        // التحقق من الصلاحيات حسب الدور
        switch ($permission) {
            case 'reset_status':
                if ($user->role !== 'admin') {
                    abort(403, 'ليس لديك صلاحية لتنفيذ هذا الإجراء');
                }
                break;

            case 'bulk_operations':
                if ($user->role !== 'admin') {
                    abort(403, 'ليس لديك صلاحية للعمليات المتعددة');
                }
                break;

            case 'export_data':
                if ($user->role !== 'admin') {
                    abort(403, 'ليس لديك صلاحية لتصدير البيانات');
                }
                break;

            default:
                if ($user->role !== 'admin') {
                    abort(403, 'ليس لديك صلاحية للوصول لهذا القسم');
                }
        }

        return $next($request);
    }
}
