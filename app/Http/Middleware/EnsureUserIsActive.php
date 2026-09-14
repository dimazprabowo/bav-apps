<?php

namespace App\Http\Middleware;

use App\Enums\UserApprovalStatus;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class EnsureUserIsActive
{
    /**
     * Handle an incoming request.
     *
     * Check if the authenticated user is still active and approved.
     * If not, log them out and redirect to login with an error message.
     */
    public function handle(Request $request, Closure $next): Response
    {
        if (Auth::check()) {
            $user = Auth::user();

            if (! $user->is_active) {
                Auth::logout();
                $request->session()->invalidate();
                $request->session()->regenerateToken();

                $message = match ($user->approval_status) {
                    UserApprovalStatus::Pending => 'Akun Anda masih menunggu approval administrator. Silakan hubungi administrator.',
                    UserApprovalStatus::Rejected => 'Akun Anda telah ditolak oleh administrator. Silakan hubungi administrator.',
                    default => 'Akun Anda telah dinonaktifkan. Silakan hubungi administrator.',
                };

                return redirect()->route('login')->with('status', $message);
            }
        }

        return $next($request);
    }
}
