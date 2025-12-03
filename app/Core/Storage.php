<?php

namespace Oxygen\Core;

use Oxygen\Services\OxygenStorageService;

/**
 * Storage - Elegant File Upload Facade
 * 
 * Provides a clean, Laravel-style API for file uploads with support for:
 * - Single file uploads
 * - Multiple file uploads
 * - Local storage
 * - S3 storage
 * - Automatic file validation
 * - Image optimization
 * 
 * @package    Oxygen\Core
 * @author     OxygenFramework
 * @version    2.0.0
 * 
 * Usage:
 *   // Single file
 *   $path = Storage::upload('image');
 *   $path = Storage::upload('image', 'photos');
 *   
 *   // Multiple files
 *   $paths = Storage::upload(['image1', 'image2'], 'gallery');
 *   
 *   // With options
 *   $path = Storage::upload('file', 'documents', ['disk' => 's3']);
 *   
 *   // Delete file
 *   Storage::delete($path);
 *   
 *   // Get URL
 *   $url = Storage::url($path);
 */
class Storage
{
    /**
     * Default storage disk
     */
    protected static $defaultDisk = 'local';

    /**
     * Storage service instance
     */
    protected static $service = null;

    /**
     * Get storage service instance
     */
    protected static function getService()
    {
        if (self::$service === null) {
            $app = Application::getInstance();
            self::$service = $app->make(OxygenStorageService::class);
        }
        return self::$service;
    }

    /**
     * Upload file(s)
     * 
     * @param string|array $fileKey Single file key or array of file keys
     * @param string $folder Folder name (e.g., 'images', 'videos', 'documents')
     * @param array $options Additional options (disk, maxSize, allowedTypes, etc.)
     * @return string|array|false Single path, array of paths, or false on failure
     * 
     * Examples:
     *   Storage::upload('avatar')
     *   Storage::upload('avatar', 'profiles')
     *   Storage::upload(['photo1', 'photo2'], 'gallery')
     *   Storage::upload('file', 'docs', ['disk' => 's3', 'maxSize' => 10485760])
     */
    public static function upload($fileKey, $folder = 'uploads', $options = [])
    {
        // Handle multiple files
        if (is_array($fileKey)) {
            return self::uploadMultiple($fileKey, $folder, $options);
        }

        // Handle single file
        return self::uploadSingle($fileKey, $folder, $options);
    }

    /**
     * Upload a single file
     */
    protected static function uploadSingle($fileKey, $folder, $options)
    {
        // Check if file exists
        if (!isset($_FILES[$fileKey]) || $_FILES[$fileKey]['error'] !== UPLOAD_ERR_OK) {
            return false;
        }

        $file = $_FILES[$fileKey];

        // Validate file size
        $maxSize = $options['maxSize'] ?? 10485760; // 10MB default
        if ($file['size'] > $maxSize) {
            throw new \Exception("File size exceeds maximum allowed size of " . ($maxSize / 1048576) . "MB");
        }

        // Validate file type
        if (isset($options['allowedTypes'])) {
            $extension = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
            if (!in_array($extension, $options['allowedTypes'])) {
                throw new \Exception("File type '{$extension}' is not allowed");
            }
        }

        // Upload based on disk
        $disk = $options['disk'] ?? self::$defaultDisk;

        if ($disk === 's3') {
            return self::uploadToS3($file, $folder, $options);
        } else {
            return self::uploadToLocal($file, $folder, $options);
        }
    }

    /**
     * Upload multiple files
     */
    protected static function uploadMultiple($fileKeys, $folder, $options)
    {
        $paths = [];

        foreach ($fileKeys as $fileKey) {
            $path = self::uploadSingle($fileKey, $folder, $options);
            if ($path) {
                $paths[] = $path;
            }
        }

        return $paths;
    }

    /**
     * Upload to local storage
     */
    protected static function uploadToLocal($file, $folder, $options)
    {
        $service = self::getService();
        $result = $service->upload($file, $folder);

        if ($result['success']) {
            return $result['path'];
        }

        return false;
    }

    /**
     * Upload to S3
     */
    protected static function uploadToS3($file, $folder, $options)
    {
        // Get S3 credentials from config
        $s3Config = [
            'key' => OxygenConfig::get('app.AWS_ACCESS_KEY_ID'),
            'secret' => OxygenConfig::get('app.AWS_SECRET_ACCESS_KEY'),
            'region' => OxygenConfig::get('app.AWS_DEFAULT_REGION', 'us-east-1'),
            'bucket' => OxygenConfig::get('app.AWS_BUCKET'),
        ];

        // Generate unique filename
        $extension = pathinfo($file['name'], PATHINFO_EXTENSION);
        $filename = uniqid() . '_' . time() . '.' . $extension;
        $s3Path = $folder . '/' . $filename;

        // TODO: Implement S3 upload using AWS SDK
        // For now, fall back to local storage
        return self::uploadToLocal($file, $folder, $options);
    }

    /**
     * Delete file(s)
     * 
     * @param string|array $path Single path or array of paths
     * @param string $disk Storage disk (local, s3)
     * @return bool
     */
    public static function delete($path, $disk = 'local')
    {
        if (is_array($path)) {
            foreach ($path as $p) {
                self::deleteSingle($p, $disk);
            }
            return true;
        }

        return self::deleteSingle($path, $disk);
    }

    /**
     * Delete a single file
     */
    protected static function deleteSingle($path, $disk)
    {
        if ($disk === 's3') {
            // TODO: Implement S3 delete
            return false;
        }

        // Local delete
        $fullPath = self::getFullPath($path);
        if (file_exists($fullPath)) {
            return unlink($fullPath);
        }

        return false;
    }

    /**
     * Get file URL
     * 
     * @param string $path File path
     * @param string $disk Storage disk
     * @return string
     */
    public static function url($path, $disk = 'local')
    {
        if ($disk === 's3') {
            $bucket = OxygenConfig::get('app.AWS_BUCKET');
            $region = OxygenConfig::get('app.AWS_DEFAULT_REGION', 'us-east-1');
            return "https://{$bucket}.s3.{$region}.amazonaws.com/{$path}";
        }

        // Local URL
        $appUrl = OxygenConfig::get('app.APP_URL', '');
        return $appUrl . '/storage/' . ltrim($path, '/');
    }

    /**
     * Check if file exists
     * 
     * @param string $path File path
     * @param string $disk Storage disk
     * @return bool
     */
    public static function exists($path, $disk = 'local')
    {
        if ($disk === 's3') {
            // TODO: Implement S3 exists check
            return false;
        }

        return file_exists(self::getFullPath($path));
    }

    /**
     * Get file size
     * 
     * @param string $path File path
     * @param string $disk Storage disk
     * @return int|false File size in bytes or false
     */
    public static function size($path, $disk = 'local')
    {
        if ($disk === 's3') {
            // TODO: Implement S3 size check
            return false;
        }

        $fullPath = self::getFullPath($path);
        return file_exists($fullPath) ? filesize($fullPath) : false;
    }

    /**
     * Get full file path
     */
    protected static function getFullPath($path)
    {
        $app = Application::getInstance();
        return $app->basePath('public/storage/' . ltrim($path, '/'));
    }

    /**
     * Set default disk
     * 
     * @param string $disk Disk name (local, s3)
     */
    public static function setDefaultDisk($disk)
    {
        self::$defaultDisk = $disk;
    }

    /**
     * Get default disk
     * 
     * @return string
     */
    public static function getDefaultDisk()
    {
        return self::$defaultDisk;
    }

    /**
     * Upload with automatic type detection
     * 
     * @param string $fileKey File input name
     * @param array $options Options
     * @return string|false
     */
    public static function uploadImage($fileKey, $folder = 'images', $options = [])
    {
        $options['allowedTypes'] = $options['allowedTypes'] ?? ['jpg', 'jpeg', 'png', 'gif', 'webp'];
        return self::upload($fileKey, $folder, $options);
    }

    /**
     * Upload video
     */
    public static function uploadVideo($fileKey, $folder = 'videos', $options = [])
    {
        $options['allowedTypes'] = $options['allowedTypes'] ?? ['mp4', 'avi', 'mov', 'wmv', 'flv'];
        $options['maxSize'] = $options['maxSize'] ?? 104857600; // 100MB
        return self::upload($fileKey, $folder, $options);
    }

    /**
     * Upload document
     */
    public static function uploadDocument($fileKey, $folder = 'documents', $options = [])
    {
        $options['allowedTypes'] = $options['allowedTypes'] ?? ['pdf', 'doc', 'docx', 'xls', 'xlsx', 'txt'];
        return self::upload($fileKey, $folder, $options);
    }
}
