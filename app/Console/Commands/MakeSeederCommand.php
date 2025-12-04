<?php

namespace Oxygen\Console\Commands;

use Oxygen\Console\Command;

/**
 * MakeSeederCommand - Generate a new seeder file
 * 
 * Usage: php oxygen make:seeder UserSeeder
 */
class MakeSeederCommand extends Command
{
    public function execute($arguments)
    {
        if (empty($arguments[0])) {
            $this->error('Seeder name is required.');
            $this->info('Usage: php oxygen make:seeder UserSeeder');
            return;
        }

        $name = $arguments[0];
        $className = $name;

        $path = __DIR__ . '/../../../database/seeders/' . $className . '.php';
        $content = $this->getStub($className);

        if ($this->createFile($path, $content)) {
            $this->success("Seeder created successfully: {$className}");
            $this->info("Location: {$path}");
        }
    }

    protected function getStub($className)
    {
        return <<<PHP
<?php

namespace Database\Seeders;

use Oxygen\Core\Database\Seeder;
use Oxygen\Models\User;

class {$className} extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        // User::create([
        //     'name' => 'John Doe',
        //     'email' => 'john@example.com',
        //     'password' => password_hash('password', PASSWORD_DEFAULT),
        // ]);
    }
}

PHP;
    }
}
