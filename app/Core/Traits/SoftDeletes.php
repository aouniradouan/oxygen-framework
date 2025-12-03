<?php

namespace Oxygen\Core\Traits;

/**
 * SoftDeletes Trait
 * 
 * Adds soft delete functionality to models.
 * Instead of permanently deleting records, they are marked as deleted.
 * 
 * @package    Oxygen\Core\Traits
 */
trait SoftDeletes
{
    /**
     * Override the static delete method to soft delete
     * 
     * @param int $id
     * @return bool
     */
    public static function delete($id)
    {
        $table = (new static())->table;
        $db = static::db();

        // Soft delete by setting deleted_at timestamp
        $db->query("UPDATE {$table} SET deleted_at = NOW() WHERE id = ?", $id);

        return true;
    }

    /**
     * Permanently delete a record (hard delete)
     * 
     * @param int $id
     * @return bool
     */
    public static function forceDelete($id)
    {
        $table = (new static())->table;
        $db = static::db();

        $db->query("DELETE FROM {$table} WHERE id = ?", $id);

        return true;
    }

    /**
     * Restore a soft-deleted record
     * 
     * @param int $id
     * @return bool
     */
    public static function restore($id)
    {
        $table = (new static())->table;
        $db = static::db();

        $db->query("UPDATE {$table} SET deleted_at = NULL WHERE id = ?", $id);

        return true;
    }

    /**
     * Get all records excluding soft deleted
     * 
     * @return array
     */
    public static function all()
    {
        $table = (new static())->table;
        $db = static::db();

        return $db->query("SELECT * FROM {$table} WHERE deleted_at IS NULL")->fetchAll();
    }

    /**
     * Get only soft deleted records
     * 
     * @return array
     */
    public static function onlyTrashed()
    {
        $table = (new static())->table;
        $db = static::db();

        return $db->query("SELECT * FROM {$table} WHERE deleted_at IS NOT NULL")->fetchAll();
    }

    /**
     * Get all records including soft deleted
     * 
     * @return array
     */
    public static function withTrashed()
    {
        $table = (new static())->table;
        $db = static::db();

        return $db->query("SELECT * FROM {$table}")->fetchAll();
    }

    /**
     * Check if a record is soft deleted
     * 
     * @param int $id
     * @return bool
     */
    public static function isTrashed($id)
    {
        $table = (new static())->table;
        $db = static::db();

        $result = $db->query("SELECT deleted_at FROM {$table} WHERE id = ?", $id)->fetch();

        return $result && !is_null($result->deleted_at);
    }
}
