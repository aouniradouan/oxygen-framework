<?php

namespace Oxygen\Console\Commands;

use Oxygen\Console\Command;

/**
 * MakeMigrationCommand - Generate a new migration file
 * 
 * Usage: php oxygen make:migration create_users_table
 */
class MakeMigrationCommand extends Command
{
    public function execute($arguments)
    {
        if (empty($arguments[0])) {
            $this->error('Migration name is required.');
            $this->info('Usage: php oxygen make:migration create_users_table');
            return;
        }

        $name = $arguments[0];
        $timestamp = date('Y_m_d_His');
        $filename = "{$timestamp}_{$name}.php";
        $className = str_replace('_', '', ucwords($name, '_'));

        $path = __DIR__ . '/../../../database/migrations/' . $filename;
        $content = $this->getStub($className);

        if ($this->createFile($path, $content)) {
            $this->success("Migration created: {$filename}");
            $this->info("Location: {$path}");
        }
    }

    protected function getStub($className)
    {
        // Try to extract table name from class name
        $tableName = 'table_name';
        if (preg_match('/Create(\w+)Table/', $className, $matches)) {
            $tableName = strtolower(preg_replace('/([a-z])([A-Z])/', '$1_$2', $matches[1]));
        }

        return <<<PHP
<?php

use Oxygen\Core\Database\Migration;

/**
 * {$className} Migration
 * 
 * @see https://github.com/redwan-aouni/oxygen-framework
 */
class {$className} extends Migration
{
    /**
     * Run the migration
     */
    public function up()
    {
        \$this->schema->createTable('{$tableName}', function(\$table) {
            // Primary key
            \$table->id();
            
            // Example columns - modify as needed
            \$table->string('name', 255);
            \$table->string('slug', 255)->unique();
            \$table->text('description')->nullable();
            \$table->boolean('is_active')->default(true);
            
            // Foreign key example (uncomment if needed)
            // \$table->foreignId('user_id')->constrained()->onDelete('CASCADE');
            
            // Timestamps (created_at, updated_at)
            \$table->timestamps();
        });
    }

    /**
     * Reverse the migration
     */
    public function down()
    {
        \$this->schema->dropTable('{$tableName}');
    }
}

PHP;
    }
}

