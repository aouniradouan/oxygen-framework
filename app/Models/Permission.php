<?php

namespace Oxygen\Models;

use Oxygen\Core\Model;

/**
 * Permission Model
 * 
 * Represents granular permissions that can be assigned to roles
 */
class Permission extends Model
{
    protected $table = 'permissions';

    protected $fillable = ['name', 'slug', 'description'];

    protected $dates = ['created_at', 'updated_at'];

    /**
     * Get all roles that have this permission
     * 
     * @return array
     */
    public function roles()
    {
        $sql = "
            SELECT r.* 
            FROM roles r
            INNER JOIN role_permission rp ON r.id = rp.role_id
            WHERE rp.permission_id = ?
        ";

        return static::db()->query($sql, $this->id)->fetchAll();
    }

    /**
     * Check if permission is assigned to a specific role
     * 
     * @param int $roleId
     * @return bool
     */
    public function assignedToRole($roleId)
    {
        $sql = "
            SELECT COUNT(*) as count
            FROM role_permission
            WHERE role_id = ? AND permission_id = ?
        ";

        $result = static::db()->query($sql, $roleId, $this->id)->fetch();
        return $result && $result->count > 0;
    }
}
