<?php

namespace Oxygen\Console\Commands;

use Oxygen\Console\Command;

/**
 * Install Command - One-command installation for OxygenFramework
 * 
 * Handles complete framework installation including:
 * - Dependency installation
 * - Environment configuration
 * - Database setup
 * - Migrations and seeding
 * - Admin user creation
 * - Development server startup
 */
class InstallCommand extends Command
{
    protected $signature = 'install';
    protected $description = 'Install OxygenFramework with one command';

    public function execute($arguments)
    {
        $this->displayWelcome();

        // Step 1: Check requirements
        $this->info("\n🔍 Checking system requirements...");
        if (!$this->checkRequirements()) {
            return;
        }

        // Step 2: Install dependencies
        $this->info("\n📦 Installing dependencies...");
        $this->runComposerInstall();

        // Step 3: Setup environment
        $this->info("\n⚙️  Setting up environment...");
        $this->setupEnvironment();

        // Step 4: Configure database
        $this->info("\n🗄️  Configuring database...");
        $this->configureDatabase();

        // Step 5: Run migrations
        $this->info("\n🔄 Running migrations...");
        $this->runMigrations();

        // Step 6: Seed database
        $this->info("\n🌱 Seeding database...");
        $this->seedDatabase();

        // Step 7: Create admin user
        $this->info("\n👤 Creating admin user...");
        $this->createAdminUser();

        // Step 8: Success message
        $this->displaySuccess();

        // Step 9: Ask to start server
        $startServer = $this->ask("\n🚀 Start development server? (yes/no)", "yes");

        if (strtolower($startServer) === 'yes') {
            $this->startServer();
        } else {
            $this->info("\n✅ Installation complete!");
            $this->info("Run 'php oxygen serve' to start the development server.");
        }
    }

    protected function displayWelcome()
    {
        $this->info("╔════════════════════════════════════════╗");
        $this->info("║  🚀 OxygenFramework Installation  🚀  ║");
        $this->info("╚════════════════════════════════════════╝");
        $this->info("");
        $this->info("This will install and configure OxygenFramework.");
        $this->info("The process takes about 2-3 minutes.");
    }

    protected function checkRequirements()
    {
        $errors = [];

        // Check PHP version
        $phpVersion = phpversion();
        if (version_compare($phpVersion, '7.4.0', '<')) {
            $errors[] = "PHP 7.4.0 or higher required (current: {$phpVersion})";
        } else {
            $this->success("✓ PHP version: {$phpVersion}");
        }

        // Check required extensions
        $requiredExtensions = ['pdo', 'pdo_mysql', 'mbstring', 'json', 'curl'];

        foreach ($requiredExtensions as $ext) {
            if (!extension_loaded($ext)) {
                $errors[] = "Required PHP extension missing: {$ext}";
            } else {
                $this->success("✓ Extension {$ext}: installed");
            }
        }

        // Check composer
        exec('composer --version 2>&1', $output, $returnCode);
        if ($returnCode !== 0) {
            $errors[] = "Composer not found. Please install Composer first.";
        } else {
            $this->success("✓ Composer: installed");
        }

        if (!empty($errors)) {
            $this->error("\n❌ Installation cannot continue:");
            foreach ($errors as $error) {
                $this->error("  - {$error}");
            }
            return false;
        }

        return true;
    }

    protected function runComposerInstall()
    {
        if (!file_exists('vendor')) {
            $this->info("Installing Composer dependencies...");
            exec('composer install --no-interaction 2>&1', $output, $returnCode);

            if ($returnCode === 0) {
                $this->success("✓ Dependencies installed");
            } else {
                $this->error("✗ Failed to install dependencies");
                $this->error(implode("\n", $output));
            }
        } else {
            $this->success("✓ Dependencies already installed");
        }
    }

    protected function setupEnvironment()
    {
        if (!file_exists('.env')) {
            if (file_exists('.env.example')) {
                copy('.env.example', '.env');
                $this->success("✓ Created .env file");
            } else {
                $this->createDefaultEnv();
                $this->success("✓ Created default .env file");
            }
        } else {
            $this->success("✓ .env file already exists");
        }
    }

    protected function createDefaultEnv()
    {
        $env = <<<ENV
APP_NAME=OxygenFramework
APP_ENV=local
APP_DEBUG=true
APP_URL=http://localhost:8000

DB_CONNECTION=mysql
DB_HOST=localhost
DB_PORT=3306
DB_DATABASE=oxygen
DB_USERNAME=root
DB_PASSWORD=

SESSION_DRIVER=file
SESSION_LIFETIME=120

CACHE_DRIVER=file
QUEUE_CONNECTION=sync
ENV;

        file_put_contents('.env', $env);
    }

    protected function configureDatabase()
    {
        $this->info("\nDatabase Configuration:");

        $host = $this->ask("  Host", "localhost");
        $database = $this->ask("  Database name", "oxygen");
        $username = $this->ask("  Username", "root");
        $password = $this->ask("  Password", "");

        // Update .env file
        $env = file_get_contents('.env');
        $env = preg_replace('/DB_HOST=.*/', "DB_HOST={$host}", $env);
        $env = preg_replace('/DB_DATABASE=.*/', "DB_DATABASE={$database}", $env);
        $env = preg_replace('/DB_USERNAME=.*/', "DB_USERNAME={$username}", $env);
        $env = preg_replace('/DB_PASSWORD=.*/', "DB_PASSWORD={$password}", $env);
        file_put_contents('.env', $env);

        // Test connection
        try {
            $pdo = new \PDO(
                "mysql:host={$host}",
                $username,
                $password
            );

            // Create database if it doesn't exist
            $pdo->exec("CREATE DATABASE IF NOT EXISTS `{$database}` CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci");

            $this->success("✓ Database connection successful");
            $this->success("✓ Database '{$database}' ready");
        } catch (\PDOException $e) {
            $this->error("✗ Database connection failed: " . $e->getMessage());
            $this->error("Please check your database credentials and try again.");
            exit(1);
        }
    }

    protected function runMigrations()
    {
        exec('php oxygen migrate 2>&1', $output, $returnCode);

        if ($returnCode === 0) {
            $this->success("✓ Migrations completed");
        } else {
            $this->warning("⚠ Some migrations may have failed");
            foreach ($output as $line) {
                $this->info("  " . $line);
            }
        }
    }

    protected function seedDatabase()
    {
        // Check if seeder exists
        if (file_exists('database/seeders/DatabaseSeeder.php')) {
            exec('php oxygen db:seed 2>&1', $output, $returnCode);

            if ($returnCode === 0) {
                $this->success("✓ Database seeded");
            } else {
                $this->warning("⚠ Seeding skipped or failed");
            }
        } else {
            $this->info("  No seeders found, skipping...");
        }
    }

    protected function createAdminUser()
    {
        $this->info("\nAdmin User Setup:");

        $name = $this->ask("  Name", "Admin");
        $email = $this->ask("  Email", "admin@oxygen.local");
        $password = $this->ask("  Password", "password");

        try {
            // Check if User model exists
            if (!class_exists('Oxygen\Models\User')) {
                $this->warning("⚠ User model not found, skipping admin creation");
                return;
            }

            $userClass = 'Oxygen\Models\User';

            // Check if user already exists
            $existing = $userClass::where('email', '=', $email);
            if (!$existing->isEmpty()) {
                $this->warning("⚠ User with email '{$email}' already exists");
                return;
            }

            // Create admin user
            $user = $userClass::create([
                'name' => $name,
                'email' => $email,
                'password' => password_hash($password, PASSWORD_BCRYPT),
                'role_id' => 1, // Admin role
                'created_at' => date('Y-m-d H:i:s'),
                'updated_at' => date('Y-m-d H:i:s')
            ]);

            $this->success("✓ Admin user created");
            $this->info("  Email: {$email}");
            $this->info("  Password: {$password}");
        } catch (\Exception $e) {
            $this->warning("⚠ Could not create admin user: " . $e->getMessage());
        }
    }

    protected function displaySuccess()
    {
        $this->info("\n╔════════════════════════════════════════╗");
        $this->info("║     ✅ Installation Complete! ✅      ║");
        $this->info("╚════════════════════════════════════════╝");
        $this->info("");
        $this->info("Your OxygenFramework installation is ready!");
    }

    protected function startServer()
    {
        $this->info("\n🚀 Starting development server...");
        $this->info("Server running at: http://localhost:8000");
        $this->info("Press Ctrl+C to stop the server.\n");

        // Open browser (cross-platform)
        $url = "http://localhost:8000";

        if (PHP_OS_FAMILY === 'Windows') {
            exec("start {$url}");
        } elseif (PHP_OS_FAMILY === 'Darwin') {
            exec("open {$url}");
        } else {
            exec("xdg-open {$url} 2>/dev/null &");
        }

        // Start PHP built-in server
        passthru('php -S localhost:8000 -t public');
    }
}
