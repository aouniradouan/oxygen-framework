<?php

namespace Oxygen\Models;

use Oxygen\Core\Model;

/**
 * Role Model
 * 
 * Represents user roles with permissions
 */
class Role extends Model
{
    protected $table = 'roles';

    protected $fillable = ['name', 'slug', 'description'];

    protected $dates = ['created_at', 'updated_at'];

    /**
     * Get all permissions for this role
     * 
     * @return array
     */
    public function permissions()
    {
        $sql = "
            SELECT p.* 
            FROM permissions p
            INNER JOIN role_permission rp ON p.id = rp.permission_id
            WHERE rp.role_id = ?
        ";

        return static::db()->query($sql, $this->id)->fetchAll();
    }

    /**
     * Check if role has a specific permission
     * 
     * @param string $permissionSlug
     * @return bool
     */
    public function hasPermission($permissionSlug)
    {
        $sql = "
            SELECT COUNT(*) as count
            FROM permissions p
            INNER JOIN role_permission rp ON p.id = rp.permission_id
            WHERE rp.role_id = ? AND p.slug = ?
        ";

        $result = static::db()->query($sql, $this->id, $permissionSlug)->fetch();
        return $result && $result->count > 0;
    }

    /**
     * Give permission to this role
     * 
     * @param int $permissionId
     * @return void
     */
    public function givePermissionTo($permissionId)
    {
        $sql = "INSERT IGNORE INTO role_permission (role_id, permission_id) VALUES (?, ?)";
        static::db()->query($sql, $this->id, $permissionId);
    }

    /**
     * Revoke permission from this role
     * 
     * @param int $permissionId
     * @return void
     */
    public function revokePermissionTo($permissionId)
    {
        $sql = "DELETE FROM role_permission WHERE role_id = ? AND permission_id = ?";
        static::db()->query($sql, $this->id, $permissionId);
    }

    /**
     * Get all users with this role
     * 
     * @return \Oxygen\Core\Database\Collection
     */
    public function users()
    {
        return User::where('role_id', '=', $this->id);
    }
}
