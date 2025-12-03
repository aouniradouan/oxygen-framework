<?php

namespace Oxygen\Http\Middleware;

use Oxygen\Core\Middleware\Middleware;
use Oxygen\Core\Request;
use Closure;

/**
 * CheckAgeMiddleware
 * 
 * Custom middleware for OxygenFramework.
 * 
 * @package    Oxygen\Http\Middleware
 * @author     OxygenFramework
 * @copyright  2024 - OxygenFramework
 */
class CheckAgeMiddleware implements Middleware
{
    /**
     * Handle an incoming request
     * 
     * @param Request $request The incoming HTTP request
     * @param Closure $next The next middleware in the pipeline
     * @return mixed
     */
    public function handle(Request $request, $next)
    {
        $age = $request->input('age');
        
        if ($age < 18) {
            redirect('/underage');
            return;
        }
        
        return $next($request);
    }
}
