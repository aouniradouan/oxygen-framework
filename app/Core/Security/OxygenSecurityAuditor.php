<?php

namespace Oxygen\Core\Security;

use Oxygen\Core\OxygenConfig;

class OxygenSecurityAuditor
{
    protected $results = [];
    protected $config;

    public function __construct()
    {
        $this->config = OxygenConfig::get('security');
    }

    public function audit()
    {
        $this->checkPermissions();
        $this->checkConfiguration();
        $this->checkDependencies();

        return $this->results;
    }

    protected function checkPermissions()
    {
        $criticalPaths = [
            'config',
            'storage',
            '.env',
            'public/index.php'
        ];

        $baseDir = dirname(__DIR__, 3);

        foreach ($criticalPaths as $path) {
            $fullPath = $baseDir . '/' . $path;
            if (file_exists($fullPath)) {
                $perms = substr(sprintf('%o', fileperms($fullPath)), -4);
                if ($this->isInsecurePerms($perms)) {
                    $this->results[] = [
                        'type' => 'permission',
                        'severity' => 'high',
                        'message' => "Insecure permissions ($perms) for $path",
                        'file' => $path
                    ];
                }
            }
        }
    }

    protected function checkConfiguration()
    {
        if (OxygenConfig::get('app.debug')) {
            $this->results[] = [
                'type' => 'configuration',
                'severity' => 'medium',
                'message' => 'Debug mode is enabled in production',
                'file' => 'config/app.php'
            ];
        }
    }

    protected function checkDependencies()
    {
        $baseDir = dirname(__DIR__, 3);
        $composerLock = $baseDir . '/composer.lock';
        if (file_exists($composerLock)) {
            // Basic check for outdated packages would go here
            // For now, we just check if it exists
        } else {
            $this->results[] = [
                'type' => 'dependency',
                'severity' => 'low',
                'message' => 'composer.lock not found',
                'file' => 'composer.lock'
            ];
        }
    }

    protected function isInsecurePerms($perms)
    {
        return octdec($perms) > octdec('0755');
    }
}
