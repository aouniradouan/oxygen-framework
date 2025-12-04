<?php

namespace Oxygen\Console\Commands;

use Oxygen\Console\Command;

/**
 * MakeFactoryCommand - Generate a new factory file
 * 
 * Usage: php oxygen make:factory UserFactory --model=User
 */
class MakeFactoryCommand extends Command
{
    public function execute($arguments)
    {
        if (empty($arguments[0])) {
            $this->error('Factory name is required.');
            $this->info('Usage: php oxygen make:factory UserFactory --model=User');
            return;
        }

        $name = $arguments[0];
        $className = $name;

        // Parse --model option
        $modelName = 'Model';
        foreach ($arguments as $arg) {
            if (strpos($arg, '--model=') === 0) {
                $modelName = substr($arg, 8);
            }
        }

        $path = __DIR__ . '/../../../database/factories/' . $className . '.php';

        // Ensure directory exists
        if (!is_dir(dirname($path))) {
            mkdir(dirname($path), 0755, true);
        }

        $content = $this->getStub($className, $modelName);

        if ($this->createFile($path, $content)) {
            $this->success("Factory created successfully: {$className}");
            $this->info("Location: {$path}");
        }
    }

    protected function getStub($className, $modelName)
    {
        return <<<PHP
<?php

namespace Database\Factories;

use Oxygen\Core\Database\Factory;
use Oxygen\Models\\{$modelName};

class {$className} extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected \$model = {$modelName}::class;

    /**
     * Define the model's default state.
     *
     * @return array
     */
    public function definition()
    {
        return [
            // 'name' => \$this->faker->name,
            // 'email' => \$this->faker->unique()->safeEmail,
        ];
    }
}

PHP;
    }
}
