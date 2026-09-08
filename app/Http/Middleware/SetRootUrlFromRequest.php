<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\URL;
use Symfony\Component\HttpFoundation\Response;

class SetRootUrlFromRequest
{
    /**
     * Use the current request host (including Cursor/VS Code port-forward URLs)
     * so CSS, JS, images, and form actions are not stuck on APP_URL localhost.
     */
    public function handle(Request $request, Closure $next): Response
    {
        URL::forceRootUrl($request->root());

        if ($request->secure()) {
            URL::forceScheme('https');
        }

        return $next($request);
    }
}
