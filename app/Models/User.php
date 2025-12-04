<?php

namespace Oxygen\Models;

use Oxygen\Core\Model;

/**
 * User Model
 * 
 * Represents application users with role-based permissions and relationships.
 */
class User extends Model
{
    /**
     * The database table name
     */
    protected $table = 'users';

    /**
     * The attributes that are mass assignable
     */
    protected $fillable = [
        'name',
        'email',
        'password',
        'role_id',
        'email_verified_at',
        'remember_token'
    ];

    /**
     * The attributes that should be hidden for serialization
     */
    protected $hidden = ['password', 'remember_token'];

    /**
     * The attributes that should be cast
     */
    protected $casts = [
        'role_id' => 'integer',
        'email_verified_at' => 'datetime',
    ];

    /**
     * The attributes that should be mutated to dates
     */
    protected $dates = ['created_at', 'updated_at', 'email_verified_at'];

    // ===== RELATIONSHIPS =====

    /**
     * Get the user's role
     * 
     * @return \Oxygen\Core\Database\Relations\BelongsTo
     */
    public function role()
    {
        return $this->belongsTo(Role::class, 'role_id');
    }

    /**
     * Get all posts by this user
     * 
     * @return \Oxygen\Core\Database\Relations\HasMany
     */
    public function posts()
    {
        return $this->hasMany(Post::class, 'user_id');
    }

    // ===== ROLE AND PERMISSION CHECKS =====

    /**
     * Check if user has a specific role
     * 
     * @param string $roleSlug
     * @return bool
     */
    public function hasRole($roleSlug)
    {
        $role = $this->role;

        if (!$role) {
            return false;
        }

        return $role->slug === $roleSlug;
    }

    /**
     * Check if user has a specific permission
     * 
     * @param string $permissionSlug
     * @return bool
     */
    public function hasPermission($permissionSlug)
    {
        $role = $this->role;

        if (!$role) {
            return false;
        }

        return $role->hasPermission($permissionSlug);
    }

    /**
     * Alias for hasPermission
     * 
     * @param string $permissionSlug
     * @return bool
     */
    public function can($permissionSlug)
    {
        return $this->hasPermission($permissionSlug);
    }

    /**
     * Check if user is an administrator
     * 
     * @return bool
     */
    public function isAdmin()
    {
        return $this->hasRole('admin');
    }

    /**
     * Check if user is a moderator
     * 
     * @return bool
     */
    public function isModerator()
    {
        return $this->hasRole('moderator');
    }

    /**
     * Get all permissions for this user
     * 
     * @return array
     */
    public function permissions()
    {
        $role = $this->role;

        if (!$role) {
            return [];
        }

        return $role->permissions();
    }
}