<?php

namespace Oxygen\Http\Middleware;

use Oxygen\Core\Middleware\Middleware;
use Oxygen\Core\Request;
use Oxygen\Core\Response;

class RoleMiddleware implements Middleware
{
    /**
     * Simple RBAC middleware for CRM routes.
     * Grants access if the authenticated user has one of the allowed role slugs.
     */
    protected $allowed = ['super-admin', 'admin', 'crm_user'];

    public function handle(Request $request, $next = null)
    {
        // Ensure JWT middleware already ran
        $user = $_SERVER['JWT_USER'] ?? null;

        if (!$user || !isset($user->id)) {
            $this->unauthorized('Authentication required');
            return;
        }

        // Query roles for user
        $db = \Oxygen\Core\Application::getInstance()->make('db');
        $roles = $db->query('SELECT r.slug FROM roles r JOIN role_user ru ON ru.role_id = r.id WHERE ru.user_id = ?', $user->id)->fetchAll();

        $has = false;
        foreach ($roles as $r) {
            if (in_array($r->slug, $this->allowed)) {
                $has = true;
                break;
            }
        }

        if (!$has) {
            $this->unauthorized('Insufficient role');
            return;
        }
    }

    protected function unauthorized($message)
    {
        header('Content-Type: application/json');
        http_response_code(403);
        echo json_encode(['success' => false, 'message' => $message, 'timestamp' => date('c')]);
        exit;
    }
}
