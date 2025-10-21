<?php

namespace App\Http\Middleware;

use Illuminate\Auth\Middleware\Authenticate as Middleware;

class Authenticate extends Middleware
{
    /**
     * Get the path the user should be redirected to when they are not authenticated.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return string|null
     */
    protected function redirectTo($request)
    {
        if (! $request->expectsJson()) {
            // もしリクエストされたURLが 'admin/' で始まる場合
            if ($request->is('admin/*')) {
                // 管理者用のログインルートにリダイレクト
                return route('admin.login');
            }
    
            // それ以外の場合は、通常のログインルートにリダイレクト
            return route('login');
        }
    }
}
