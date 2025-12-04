<?php

namespace Oxygen\Models;

use Oxygen\Core\Model;

/**
 * Post Model
 * 
 * Represents blog posts with user relationship.
 * Demonstrates proper belongsTo and hasMany relationships.
 */
class Post extends Model
{
    /**
     * The database table name
     */
    protected $table = 'postss';

    /**
     * The attributes that are mass assignable
     */
    protected $fillable = [
        'user_id',
        'title',
        'slug',
        'content',
        'excerpt',
        'status',
        'published_at'
    ];

    /**
     * The attributes that should be cast
     */
    protected $casts = [
        'user_id' => 'integer',
        'published_at' => 'datetime',
    ];

    /**
     * The attributes that should be mutated to dates
     */
    protected $dates = ['created_at', 'updated_at', 'published_at'];

    // ===== RELATIONSHIPS =====

    /**
     * Get the author of the post
     * 
     * @return \Oxygen\Core\Database\Relations\BelongsTo
     */
    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    /**
     * Alias for user() - get the author
     * 
     * @return \Oxygen\Core\Database\Relations\BelongsTo
     */
    public function author()
    {
        return $this->user();
    }

    // ===== SCOPES =====

    /**
     * Get published posts
     * 
     * @return \Oxygen\Core\Database\Collection
     */
    public static function published()
    {
        return static::where('status', '=', 'published');
    }

    /**
     * Get draft posts
     * 
     * @return \Oxygen\Core\Database\Collection
     */
    public static function drafts()
    {
        return static::where('status', '=', 'draft');
    }

    /**
     * Get posts by a specific user
     * 
     * @param int $userId
     * @return \Oxygen\Core\Database\Collection
     */
    public static function byUser($userId)
    {
        return static::where('user_id', '=', $userId);
    }

    // ===== HELPERS =====

    /**
     * Check if the post is published
     * 
     * @return bool
     */
    public function isPublished()
    {
        return $this->status === 'published';
    }

    /**
     * Check if the post is a draft
     * 
     * @return bool
     */
    public function isDraft()
    {
        return $this->status === 'draft';
    }

    /**
     * Generate slug from title
     * 
     * @param string $title
     * @return string
     */
    public static function generateSlug($title)
    {
        $slug = strtolower(trim($title));
        $slug = preg_replace('/[^a-z0-9-]/', '-', $slug);
        $slug = preg_replace('/-+/', '-', $slug);
        return trim($slug, '-');
    }
}
