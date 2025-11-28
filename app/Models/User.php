<?php

namespace Oxygen\Models;

// Assuming the Role model exists in the same namespace or is imported
// We must import the Role class to use it for relationships and type-hinting.
use Oxygen\Core\Model;
use Oxygen\Models\Role; // <-- ASSUMED: You must import the Role class

class User extends Model
{
    /**
     * The attributes that should be hidden for serialization.
     *
     * @var array
     */
    protected $hidden = ['password', 'remember_token'];

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'password',
        'name',
        'email',
        'role_id',
        'created_at',
        'updated_at',
        'remember_token',
    ];

    // --- RELATIONSHIPS ---

    /**
     * Get the user's role.
     * * IMPORTANT FIX: This method is now a proper ORM relationship.
     * The ORM will automatically cache the result after the first fetch.
     *
     * @return \Oxygen\Core\Relationship
     */
    public function role()
    {
        // Assumes a 'role_id' foreign key points to the Role model
        return $this->belongsTo(Role::class, 'role_id');
    }

    // --- ROLE AND PERMISSION CHECKS ---

    /**
     * Check if user has a specific role
     * * @param string $roleSlug
     * @return bool
     */
    public function hasRole($roleSlug)
    {
        // Access the relationship result as a dynamic property ($this->role)
        // This triggers the 'role()' method once, and then uses the cached object.
        $role = $this->role;

        if (!$role) {
            return false;
        }

        // Use object property access ($role->slug), not array access
        return $role->slug === $roleSlug;
    }

    /**
     * Check if user has a specific permission
     * * FIX: Delegates the check directly to the fully-loaded Role object.
     * This avoids creating a new, un-hydrated Role object every time.
     * * @param string $permissionSlug
     * @return bool
     */
    public function hasPermission($permissionSlug)
    {
        $role = $this->role;

        if (!$role) {
            return false;
        }

        // This assumes the Role Model also has a working hasPermission() method
        return $role->hasPermission($permissionSlug);
    }

    /**
     * Alias for hasPermission
     * * @param string $permissionSlug
     * @return bool
     */
    public function can($permissionSlug)
    {
        return $this->hasPermission($permissionSlug);
    }

    /**
     * Check if user is an administrator
     * * @return bool
     */
    public function isAdmin()
    {
        return $this->hasRole('admin');
    }

    /**
     * Check if user is a moderator
     * * @return bool
     */
    public function isModerator()
    {
        return $this->hasRole('moderator');
    }

    /**
     * Get all permissions for this user
     * * FIX: Delegates the collection of permissions directly to the Role object.
     * * @return array
     */
    public function permissions()
    {
        $role = $this->role;

        if (!$role) {
            return [];
        }

        // This assumes the Role Model has a working permissions() method
        return $role->permissions();
    }
}