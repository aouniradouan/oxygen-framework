<?php

namespace Oxygen\Http\Middleware;

use Oxygen\Core\Middleware\Middleware;
use Oxygen\Core\Request;
use Oxygen\Core\Application;
use Oxygen\Core\Lang;
use Oxygen\Core\OxygenSession;
use Closure;

/**
 * OxygenLocaleMiddleware - Locale Management Middleware
 * 
 * Sets the application locale based on session or request input.
 * 
 * @package    Oxygen\Http\Middleware
 * @author     Redwan Aouni <aouniradouan@gmail.com>
 * @copyright  2024 - OxygenFramework
 * @version    1.0.0
 */
class OxygenLocaleMiddleware implements Middleware
{
    /**
     * Handle an incoming request
     * 
     * @param Request $request The incoming HTTP request
     * @param Closure $next The next middleware in the pipeline
     * @return mixed
     */
    public function handle(Request $request, Closure $next)
    {
        $lang = Application::getInstance()->make(Lang::class);

        // Check for locale in request (e.g. ?lang=fr)
        if ($request->input('lang')) {
            $locale = $request->input('lang');
            OxygenSession::put('locale', $locale);
        }

        // Check for locale in session
        if (OxygenSession::has('locale')) {
            $lang->setLocale(OxygenSession::get('locale'));
        }

        return $next($request);
    }
}
