<?php

namespace Oxygen\Console\Commands;

use Oxygen\Console\Command;
use Oxygen\Core\Support\Str;

/**
 * ScaffoldResourceCommand - Professional CRUD Generator
 * 
 * Generates complete CRUD resources using ONLY OxygenFramework components.
 * 
 * Features:
 * - Smart pluralization using Oxygen\Core\Support\Str
 * - OxygenSchema for migrations
 * - Oxygen\Core\Model for models
 * - Oxygen\Core\Validator for validation
 * - Relationships (belongsTo, hasMany, hasOne, belongsToMany)
 * - Soft deletes support
 * - File uploads with OxygenStorage
 * - Search & pagination
 * - Flash messages
 * - CSRF protection
 * 
 * @package    Oxygen\Console\Commands
 * @author     Redwan Aouni
 * @version    4.0.0 - 100% OxygenFramework Components
 */
class ScaffoldResourceCommand extends Command
{
    // Resource properties
    protected $resourceName;
    protected $modelName;
    protected $controllerName;
    protected $tableName;
    protected $routePath;

    // Schema
    protected $columns = [];
    protected $relationships = [];
    protected $validationRules = [];
    protected $fileUploads = [];

    // Features
    protected $useSoftDeletes = false;
    protected $enableSearch = true;

    // Supported column types (matching OxygenSchema)
    protected $columnTypes = [
        'string',
        'text',
        'integer',
        'bigInteger',
        'decimal',
        'float',
        'double',
        'boolean',
        'date',
        'datetime',
        'timestamp',
        'enum',
        'json',
        'file',
        'image'
    ];

    /**
     * Execute the scaffold command
     */
    public function execute($arguments)
    {
        $this->printHeader();

        // Step 1: Gather basic information
        $this->getBasicInfo();

        // Step 2: Define schema
        $this->buildSchema();

        // Step 3: Define relationships
        $this->buildRelationships();

        // Step 4: Configure features
        $this->configureFeatures();

        // Step 5: Generate validation rules
        $this->generateValidationRules();

        // Step 6: Generate all files
        echo "\n";
        $this->info("🔨 Generating files...");
        echo "\n";

        $this->generateMigration();
        $this->generateModel();
        $this->generateController();
        $this->generateViews();
        $this->addRoutes();

        // Step 7: Success
        $this->printSuccess();
    }

    /**
     * Print header
     */
    protected function printHeader()
    {
        $this->info("╔════════════════════════════════════════════════════════════════════╗");
        $this->info("║  🚀 OxygenFramework - Professional Scaffolder v4.0 (Pure Oxygen)  ║");
        $this->info("╚════════════════════════════════════════════════════════════════════╝");
        echo "\n";
    }

    /**
     * Get basic resource information
     * Uses Oxygen\Core\Support\Str for string operations
     */
    protected function getBasicInfo()
    {
        $this->resourceName = $this->ask("Resource name (singular, e.g., 'Post')");
        if (empty($this->resourceName)) {
            $this->error("Resource name is required!");
            exit(1);
        }

        // Use Oxygen Str helper
        $this->modelName = Str::studly($this->resourceName);
        $this->controllerName = $this->modelName . 'Controller';

        // Smart pluralization using Oxygen Str
        $defaultTable = Str::snake(Str::plural($this->resourceName));
        $this->tableName = $this->ask("Table name (plural)", $defaultTable) ?: $defaultTable;

        // Route path
        $defaultRoute = str_replace('_', '-', $defaultTable);
        $this->routePath = $this->ask("Route path (URL prefix)", $defaultRoute) ?: $defaultRoute;

        echo "\n";
        $this->success("✓ Resource: {$this->modelName}");
        $this->success("✓ Table: {$this->tableName}");
        $this->success("✓ Route: /{$this->routePath}");
        echo "\n";
    }

    /**
     * Build database schema
     */
    protected function buildSchema()
    {
        $this->info("📋 Define database columns (leave empty to finish):");
        echo "\n";

        // Add ID automatically
        $this->columns[] = [
            'name' => 'id',
            'type' => 'bigInteger',
            'nullable' => false,
            'primary' => true,
        ];

        while (true) {
            $columnName = $this->ask("Column name");
            if (empty($columnName))
                break;

            echo "\n";
            $this->info("Available types: " . implode(', ', $this->columnTypes));
            $columnType = $this->ask("Column type", "string");

            if (!in_array($columnType, $this->columnTypes)) {
                $this->warning("Invalid type, using 'string'");
                $columnType = 'string';
            }

            $nullable = $this->ask("Nullable? (yes/no)", "no");

            $column = [
                'name' => Str::snake($columnName),
                'type' => $columnType,
                'nullable' => in_array(strtolower($nullable), ['yes', 'y']),
            ];

            // Handle special types
            if ($columnType === 'string') {
                $column['length'] = $this->ask("Length", "255");
            } elseif ($columnType === 'decimal') {
                $column['precision'] = $this->ask("Total digits", "8");
                $column['scale'] = $this->ask("Decimal places", "2");
            } elseif ($columnType === 'enum') {
                $options = $this->ask("Enum options (comma separated)");
                $column['options'] = array_map('trim', explode(',', $options));
            } elseif (in_array($columnType, ['file', 'image'])) {
                $this->fileUploads[] = Str::snake($columnName);
                $column['type'] = 'string'; // Store as path
                $column['length'] = 500;
            }

            $this->columns[] = $column;
            $this->success("✓ Added: {$column['name']} ({$column['type']})");
            echo "\n";
        }

        // Add timestamps
        $addTimestamps = $this->ask("Add timestamps? (yes/no)", "yes");
        if (in_array(strtolower($addTimestamps), ['yes', 'y'])) {
            $this->columns[] = ['name' => 'created_at', 'type' => 'timestamp', 'nullable' => true];
            $this->columns[] = ['name' => 'updated_at', 'type' => 'timestamp', 'nullable' => true];
        }
    }

    /**
     * Build relationships
     */
    protected function buildRelationships()
    {
        echo "\n";
        $this->info("🔗 Define relationships:");
        $addRelationships = $this->ask("Add relationships? (yes/no)", "no");

        if (!in_array(strtolower($addRelationships), ['yes', 'y'])) {
            return;
        }

        echo "\n";
        while (true) {
            $type = $this->ask("Relationship type (belongsTo/hasMany/hasOne/belongsToMany) [empty to finish]");

            if (empty($type))
                break;

            $relatedModel = $this->ask("Related model name");

            if (empty($relatedModel)) {
                $this->warning("⚠️  Model name cannot be empty!");
                continue;
            }

            $methodName = $this->ask("Method name", Str::snake($relatedModel));

            $relationship = [
                'type' => $type,
                'model' => Str::studly($relatedModel),
                'method' => $methodName,
            ];

            // Add foreign key for belongsTo
            if ($type === 'belongsTo') {
                $foreignKey = $this->ask("Foreign key", Str::snake($relatedModel) . '_id');
                $relationship['foreignKey'] = $foreignKey;

                // Add foreign key column if not exists
                $exists = false;
                foreach ($this->columns as $col) {
                    if ($col['name'] === $foreignKey) {
                        $exists = true;
                        break;
                    }
                }

                if (!$exists) {
                    $this->columns[] = [
                        'name' => $foreignKey,
                        'type' => 'bigInteger',
                        'nullable' => true,
                    ];
                    $this->success("✓ Added foreign key column: {$foreignKey}");
                }
            }

            $this->relationships[] = $relationship;
            $this->success("✓ Added relationship: {$methodName} ({$type})");
            echo "\n";
        }
    }

    /**
     * Configure features
     */
    protected function configureFeatures()
    {
        echo "\n";
        $this->info("✨ Configure features:");

        $softDeletes = $this->ask("Enable soft deletes? (yes/no)", "no");
        $this->useSoftDeletes = in_array(strtolower($softDeletes), ['yes', 'y']);

        if ($this->useSoftDeletes) {
            $this->columns[] = ['name' => 'deleted_at', 'type' => 'timestamp', 'nullable' => true];
            $this->success("✓ Soft deletes enabled");
        }

        $search = $this->ask("Enable search? (yes/no)", "yes");
        $this->enableSearch = in_array(strtolower($search), ['yes', 'y']);

        if ($this->enableSearch) {
            $this->success("✓ Search enabled");
        }

        echo "\n";
    }

    /**
     * Generate validation rules using Oxygen Validator rules
     */
    protected function generateValidationRules()
    {
        foreach ($this->columns as $column) {
            if (in_array($column['name'], ['id', 'created_at', 'updated_at', 'deleted_at'])) {
                continue;
            }

            $rules = [];

            if (!$column['nullable']) {
                $rules[] = 'required';
            }

            // Use Oxygen Validator rules
            switch ($column['type']) {
                case 'string':
                case 'text':
                    $rules[] = 'string';
                    if (isset($column['length'])) {
                        $rules[] = 'max:' . $column['length'];
                    }
                    break;
                case 'integer':
                case 'bigInteger':
                    $rules[] = 'integer';
                    break;
                case 'decimal':
                case 'float':
                case 'double':
                    $rules[] = 'numeric';
                    break;
                case 'boolean':
                    $rules[] = 'boolean';
                    break;
                case 'date':
                case 'datetime':
                case 'timestamp':
                    $rules[] = 'date';
                    break;
                case 'enum':
                    if (isset($column['options'])) {
                        $rules[] = 'in:' . implode(',', $column['options']);
                    }
                    break;
            }

            if (!empty($rules)) {
                $this->validationRules[$column['name']] = implode('|', $rules);
            }
        }
    }

    /**
     * Generate migration using OxygenSchema
     */
    protected function generateMigration()
    {
        $timestamp = date('Y_m_d_His');
        $filename = "database/migrations/{$timestamp}_create_{$this->tableName}_table.php";
        $className = 'Create' . Str::studly($this->tableName) . 'Table';

        $columnsCode = '';
        foreach ($this->columns as $column) {
            if ($column['name'] === 'id' && isset($column['primary'])) {
                $columnsCode .= "            \$table->id();\n";
                continue;
            }

            if (in_array($column['name'], ['created_at', 'updated_at', 'deleted_at'])) {
                continue;
            }

            // Use OxygenSchema methods
            $line = "            \$table->{$column['type']}('{$column['name']}'";

            if ($column['type'] === 'string' && isset($column['length'])) {
                $line .= ", {$column['length']}";
            }

            if ($column['type'] === 'decimal' && isset($column['precision'])) {
                $line .= ", {$column['precision']}, {$column['scale']}";
            }

            if ($column['type'] === 'enum' && isset($column['options'])) {
                $options = "'" . implode("', '", $column['options']) . "'";
                $line = "            \$table->enum('{$column['name']}', [{$options}]";
            }

            $line .= ')';

            if ($column['nullable']) {
                $line .= '->nullable()';
            }

            $line .= ";\n";
            $columnsCode .= $line;
        }

        // Add timestamps using OxygenSchema
        $hasTimestamps = false;
        foreach ($this->columns as $column) {
            if ($column['name'] === 'created_at') {
                $hasTimestamps = true;
                break;
            }
        }

        if ($hasTimestamps) {
            $columnsCode .= "            \$table->timestamps();\n";
        }

        // Add soft deletes using OxygenSchema
        if ($this->useSoftDeletes) {
            $columnsCode .= "            \$table->softDeletes();\n";
        }

        $content = "<?php\n\nuse Oxygen\\Core\\Database\\Migration;\n\nclass {$className} extends Migration\n{\n    public function up()\n    {\n        \$this->schema->createTable('{$this->tableName}', function(\$table) {\n{$columnsCode}        });\n    }\n\n    public function down()\n    {\n        \$this->schema->dropTable('{$this->tableName}');\n    }\n}\n";

        $this->createFile($filename, $content);
        $this->success("✓ Migration created");
    }

    /**
     * Generate model using Oxygen\Core\Model
     */
    protected function generateModel()
    {
        $fillable = [];
        foreach ($this->columns as $column) {
            if (!in_array($column['name'], ['id', 'created_at', 'updated_at', 'deleted_at'])) {
                $fillable[] = "'{$column['name']}'";
            }
        }

        $fillableStr = implode(",\n        ", $fillable);

        // Build use statements
        $uses = "use Oxygen\\Core\\Model;\n";
        if ($this->useSoftDeletes) {
            $uses .= "use Oxygen\\Core\\Traits\\SoftDeletes;\n";
        }

        // Build traits
        $traits = '';
        if ($this->useSoftDeletes) {
            $traits = "    use SoftDeletes;\n\n";
        }

        // Build relationships using Oxygen Model methods
        $relationshipMethods = '';
        foreach ($this->relationships as $rel) {
            $relationshipMethods .= "    /**\n";
            $relationshipMethods .= "     * {$rel['type']} relationship\n";
            $relationshipMethods .= "     */\n";
            $relationshipMethods .= "    public function {$rel['method']}()\n";
            $relationshipMethods .= "    {\n";
            $relationshipMethods .= "        return \$this->{$rel['type']}({$rel['model']}::class);\n";
            $relationshipMethods .= "    }\n\n";
        }

        $content = "<?php\n\nnamespace Oxygen\\Models;\n\n{$uses}\n/**\n * {$this->modelName} Model\n * \n * Generated by OxygenFramework Scaffolder\n */\nclass {$this->modelName} extends Model\n{\n{$traits}    protected \$table = '{$this->tableName}';\n    \n    protected \$fillable = [\n        {$fillableStr}\n    ];\n\n{$relationshipMethods}}\n";

        $filename = "app/Models/{$this->modelName}.php";
        $this->createFile($filename, $content);
        $this->success("✓ Model created with " . count($this->relationships) . " relationship(s)");
    }

    /**
     * Generate controller using Oxygen components
     */
    protected function generateController()
    {
        $viewPath = $this->routePath;

        // Build validation rules string (using Oxygen Validator)
        $validationRulesStr = '';
        foreach ($this->validationRules as $field => $rules) {
            $validationRulesStr .= "            '{$field}' => '{$rules}',\n";
        }

        // Build search logic
        $searchLogic = '';
        if ($this->enableSearch) {
            $searchableColumn = 'name'; // Default
            foreach ($this->columns as $col) {
                if ($col['type'] === 'string' && !in_array($col['name'], ['id', 'password'])) {
                    $searchableColumn = $col['name'];
                    break;
                }
            }

            $searchLogic = "        \$search = \$_GET['search'] ?? null;\n        \n        if (\$search) {\n            \$items = {$this->modelName}::where('{$searchableColumn}', 'LIKE', \"%{\$search}%\");\n        } else {\n            \$items = {$this->modelName}::paginate(15);\n        }\n";
        } else {
            $searchLogic = "        \$items = {$this->modelName}::paginate(15);\n";
        }

        // Prepare related data for views (for dropdowns)
        $relatedDataFetch = "";
        $relatedDataPass = "";
        foreach ($this->relationships as $rel) {
            if ($rel['type'] === 'belongsTo') {
                $varName = Str::plural(Str::snake($rel['model'])); // e.g. users
                $modelClass = "\\Oxygen\\Models\\" . $rel['model'];
                $relatedDataFetch .= "        \${$varName} = {$modelClass}::all();\n";
                $relatedDataPass .= ", '{$varName}' => \${$varName}";
            }
        }

        $content = "<?php\n\nnamespace Oxygen\\Controllers;\n\nuse Controller;\nuse Oxygen\\Core\\Request;\nuse Oxygen\\Core\\Response;\nuse Oxygen\\Core\\Validator;\nuse Oxygen\\Core\\Flash;\nuse Oxygen\\Models\\{$this->modelName};\n\n/**\n * {$this->controllerName}\n * \n * Generated by OxygenFramework Scaffolder\n */\nclass {$this->controllerName} extends Controller\n{\n    public function index()\n    {\n{$searchLogic}        \n        return \$this->view('{$viewPath}/index', ['items' => \$items]);\n    }\n\n    public function create()\n    {\n{$relatedDataFetch}        return \$this->view('{$viewPath}/create'{$relatedDataPass});\n    }\n\n    public function store()\n    {\n        \$request = \$this->app->make(Request::class);\n        \n        \$validator = Validator::make(\$request->all(), [\n{$validationRulesStr}        ]);\n        \n        if (\$validator->fails()) {\n            Flash::error('Validation failed!');\n            \$_SESSION['errors'] = \$validator->errors();\n            \$_SESSION['old'] = \$request->all();\n            Response::redirect('/{$viewPath}/create');\n            return;\n        }\n        \n        {$this->modelName}::create(\$validator->validated());\n        Flash::success('{$this->modelName} created successfully!');\n        Response::redirect('/{$viewPath}');\n    }\n\n    public function show(\$id)\n    {\n        \$item = {$this->modelName}::find(\$id);\n        return \$this->view('{$viewPath}/show', ['item' => \$item]);\n    }\n\n    public function edit(\$id)\n    {\n        \$item = {$this->modelName}::find(\$id);\n{$relatedDataFetch}        return \$this->view('{$viewPath}/edit', ['item' => \$item{$relatedDataPass}]);\n    }\n\n    public function update(\$id)\n    {\n        \$request = \$this->app->make(Request::class);\n        \n        \$validator = Validator::make(\$request->all(), [\n{$validationRulesStr}        ]);\n        \n        if (\$validator->fails()) {\n            Flash::error('Validation failed!');\n            \$_SESSION['errors'] = \$validator->errors();\n            \$_SESSION['old'] = \$request->all();\n            Response::redirect('/{$viewPath}/' . \$id . '/edit');\n            return;\n        }\n        \n        {$this->modelName}::update(\$id, \$validator->validated());\n        Flash::success('{$this->modelName} updated successfully!');\n        Response::redirect('/{$viewPath}');\n    }\n\n    public function destroy(\$id)\n    {\n        {$this->modelName}::delete(\$id);\n        Flash::success('{$this->modelName} deleted successfully!');\n        Response::redirect('/{$viewPath}');\n    }\n}\n";

        $filename = "app/Controllers/{$this->controllerName}.php";
        $this->createFile($filename, $content);
        $this->success("✓ Controller created with validation & pagination");
    }

    /**
     * Generate views using Oxygen Twig functions
     */
    protected function generateViews()
    {
        $viewPath = $this->routePath;
        $dir = "resources/views/{$viewPath}";

        if (!is_dir($dir)) {
            mkdir($dir, 0755, true);
        }

        $this->generateIndexView($dir);
        $this->generateCreateView($dir);
        $this->generateEditView($dir);
        $this->generateShowView($dir);

        $this->success("✓ Views created with search & pagination");
    }

    /**
     * Generate index view
     */
    protected function generateIndexView($dir)
    {
        $title = Str::studly($this->tableName);
        $createUrl = '/' . $this->routePath . '/create';

        // Build search form
        $searchForm = '';
        if ($this->enableSearch) {
            $searchForm = "        <form method=\"GET\" class=\"mb-4\">\n            <input type=\"search\" name=\"search\" value=\"{{ _GET.search }}\" placeholder=\"Search...\" class=\"border rounded px-4 py-2\">\n            <button type=\"submit\" class=\"bg-gray-500 text-white px-4 py-2 rounded hover:bg-gray-600\">Search</button>\n        </form>\n\n";
        }

        $tableHeaders = '';
        $tableRows = '';

        foreach ($this->columns as $column) {
            if (!in_array($column['name'], ['id', 'created_at', 'updated_at', 'deleted_at'])) {
                $label = ucfirst(str_replace('_', ' ', $column['name']));
                $tableHeaders .= "                        <th class=\"px-6 py-3 text-left\">{$label}</th>\n";
                $tableRows .= "                        <td class=\"px-6 py-4\">{{ item.{$column['name']} }}</td>\n";
            }
        }

        $content = "<!DOCTYPE html>\n<html>\n<head>\n    <title>{$title}</title>\n    <script src=\"https://cdn.tailwindcss.com\"></script>\n</head>\n<body class=\"bg-gray-50\">\n    <div class=\"container mx-auto px-4 py-8\">\n        {{ flash_display()|raw }}\n        \n        <div class=\"flex justify-between items-center mb-6\">\n            <h1 class=\"text-3xl font-bold\">{$title}</h1>\n            <a href=\"{$createUrl}\" class=\"bg-blue-500 text-white px-4 py-2 rounded hover:bg-blue-600\">Create New</a>\n        </div>\n        \n{$searchForm}        <div class=\"bg-white shadow-md rounded\">\n            <table class=\"min-w-full\">\n                <thead class=\"bg-gray-100\">\n                    <tr>\n{$tableHeaders}                        <th class=\"px-6 py-3 text-left\">Actions</th>\n                    </tr>\n                </thead>\n                <tbody>\n                    {% for item in items.items() %}\n                    <tr class=\"border-t hover:bg-gray-50\">\n{$tableRows}                        <td class=\"px-6 py-4\">\n                            <a href=\"/{$this->routePath}/{{ item.id }}\" class=\"text-blue-500 hover:text-blue-700 mr-3\">View</a>\n                            <a href=\"/{$this->routePath}/{{ item.id }}/edit\" class=\"text-green-500 hover:text-green-700 mr-3\">Edit</a>\n                            <a href=\"/{$this->routePath}/{{ item.id }}/delete\" class=\"text-red-500 hover:text-red-700\" onclick=\"return confirm('Are you sure?')\">Delete</a>\n                        </td>\n                    </tr>\n                    {% endfor %}\n                </tbody>\n            </table>\n        </div>\n        \n        {{ items.links()|raw }}\n    </div>\n</body>\n</html>";

        file_put_contents("{$dir}/index.twig.html", $content);
    }

    /**
     * Generate create view
     */
    protected function generateCreateView($dir)
    {
        $title = "Create " . Str::studly($this->resourceName);
        $formFields = $this->buildFormFields(false);

        $content = "<!DOCTYPE html>\n<html>\n<head>\n    <title>{$title}</title>\n    <script src=\"https://cdn.tailwindcss.com\"></script>\n</head>\n<body class=\"bg-gray-50\">\n    <div class=\"container mx-auto px-4 py-8 max-w-2xl\">\n        {{ flash_display()|raw }}\n        \n        <h1 class=\"text-3xl font-bold mb-6\">{$title}</h1>\n        \n        <div class=\"bg-white shadow-md rounded p-6\">\n            <form method=\"POST\" action=\"/{$this->routePath}/store\" enctype=\"multipart/form-data\">\n                {{ csrf_field|raw }}\n{$formFields}                <div class=\"flex gap-4\">\n                    <button type=\"submit\" class=\"bg-blue-500 text-white px-6 py-2 rounded hover:bg-blue-600\">Create</button>\n                    <a href=\"/{$this->routePath}\" class=\"bg-gray-300 text-gray-700 px-6 py-2 rounded hover:bg-gray-400\">Cancel</a>\n                </div>\n            </form>\n        </div>\n    </div>\n</body>\n</html>";

        file_put_contents("{$dir}/create.twig.html", $content);
    }

    /**
     * Generate edit view
     */
    protected function generateEditView($dir)
    {
        $title = "Edit " . Str::studly($this->resourceName);
        $formFields = $this->buildFormFields(true);

        $content = "<!DOCTYPE html>\n<html>\n<head>\n    <title>{$title}</title>\n    <script src=\"https://cdn.tailwindcss.com\"></script>\n</head>\n<body class=\"bg-gray-50\">\n    <div class=\"container mx-auto px-4 py-8 max-w-2xl\">\n        {{ flash_display()|raw }}\n        \n        <h1 class=\"text-3xl font-bold mb-6\">{$title}</h1>\n        \n        <div class=\"bg-white shadow-md rounded p-6\">\n            <form method=\"POST\" action=\"/{$this->routePath}/{{ item.id }}/update\" enctype=\"multipart/form-data\">\n                {{ csrf_field|raw }}\n{$formFields}                <div class=\"flex gap-4\">\n                    <button type=\"submit\" class=\"bg-blue-500 text-white px-6 py-2 rounded hover:bg-blue-600\">Update</button>\n                    <a href=\"/{$this->routePath}\" class=\"bg-gray-300 text-gray-700 px-6 py-2 rounded hover:bg-gray-400\">Cancel</a>\n                </div>\n            </form>\n        </div>\n    </div>\n</body>\n</html>";

        file_put_contents("{$dir}/edit.twig.html", $content);
    }

    /**
     * Generate show view
     */
    protected function generateShowView($dir)
    {
        $title = Str::studly($this->resourceName) . " Details";
        $fields = '';

        foreach ($this->columns as $column) {
            if ($column['name'] !== 'id' && !in_array($column['name'], ['created_at', 'updated_at', 'deleted_at'])) {
                $label = ucfirst(str_replace('_', ' ', $column['name']));
                $fields .= "            <div class=\"mb-4\">\n                <label class=\"block text-gray-600 text-sm font-semibold mb-1\">{$label}</label>\n                <div class=\"text-gray-900\">{{ item.{$column['name']} }}</div>\n            </div>\n\n";
            }
        }

        $content = "<!DOCTYPE html>\n<html>\n<head>\n    <title>{$title}</title>\n    <script src=\"https://cdn.tailwindcss.com\"></script>\n</head>\n<body class=\"bg-gray-50\">\n    <div class=\"container mx-auto px-4 py-8 max-w-2xl\">\n        {{ flash_display()|raw }}\n        \n        <h1 class=\"text-3xl font-bold mb-6\">{$title}</h1>\n        \n        <div class=\"bg-white shadow-md rounded p-6\">\n{$fields}            <div class=\"flex gap-4 mt-6\">\n                <a href=\"/{$this->routePath}/{{ item.id }}/edit\" class=\"bg-blue-500 text-white px-6 py-2 rounded hover:bg-blue-600\">Edit</a>\n                <a href=\"/{$this->routePath}\" class=\"bg-gray-300 text-gray-700 px-6 py-2 rounded hover:bg-gray-400\">Back to List</a>\n            </div>\n        </div>\n    </div>\n</body>\n</html>";

        file_put_contents("{$dir}/show.twig.html", $content);
    }

    /**
     * Build form fields for create/edit views
     */
    protected function buildFormFields($isEdit)
    {
        $formFields = '';

        foreach ($this->columns as $column) {
            if (in_array($column['name'], ['id', 'created_at', 'updated_at', 'deleted_at'])) {
                continue;
            }

            $label = ucfirst(str_replace('_', ' ', $column['name']));
            $required = !$column['nullable'] ? 'required' : '';
            $value = $isEdit ? "{{ item.{$column['name']} }}" : '';

            // Check if this is a foreign key
            $isForeignKey = false;
            $relatedVar = '';
            foreach ($this->relationships as $rel) {
                if ($rel['type'] === 'belongsTo' && isset($rel['foreignKey']) && $rel['foreignKey'] === $column['name']) {
                    $isForeignKey = true;
                    $relatedVar = Str::plural(Str::snake($rel['model']));
                    break;
                }
            }

            if ($isForeignKey) {
                $formFields .= "                <div class=\"mb-4\">\n                    <label class=\"block text-gray-700 mb-2\">{$label}</label>\n                    <select name=\"{$column['name']}\" class=\"w-full border rounded px-3 py-2\" {$required}>\n                        <option value=\"\">Select {$label}</option>\n                        {% for relItem in {$relatedVar} %}\n                            <option value=\"{{ relItem.id }}\" {% if item.{$column['name']} == relItem.id %}selected{% endif %}>{{ relItem.name }}</option>\n                        {% endfor %}\n                    </select>\n                </div>\n\n";
            } elseif (in_array($column['name'], $this->fileUploads)) {
                $formFields .= "                <div class=\"mb-4\">\n                    <label class=\"block text-gray-700 mb-2\">{$label}</label>\n                    <input type=\"file\" name=\"{$column['name']}\" class=\"w-full border rounded px-3 py-2\" {$required}>\n                </div>\n\n";
            } elseif ($column['type'] === 'text') {
                $formFields .= "                <div class=\"mb-4\">\n                    <label class=\"block text-gray-700 mb-2\">{$label}</label>\n                    <textarea name=\"{$column['name']}\" class=\"w-full border rounded px-3 py-2\" rows=\"4\" {$required}>{$value}</textarea>\n                </div>\n\n";
            } elseif ($column['type'] === 'boolean') {
                $checked = $isEdit ? "{% if item.{$column['name']} %}checked{% endif %}" : '';
                $formFields .= "                <div class=\"mb-4\">\n                    <label class=\"flex items-center\">\n                        <input type=\"checkbox\" name=\"{$column['name']}\" value=\"1\" class=\"mr-2\" {$checked}>\n                        <span class=\"text-gray-700\">{$label}</span>\n                    </label>\n                </div>\n\n";
            } elseif ($column['type'] === 'enum' && isset($column['options'])) {
                $formFields .= "                <div class=\"mb-4\">\n                    <label class=\"block text-gray-700 mb-2\">{$label}</label>\n                    <select name=\"{$column['name']}\" class=\"w-full border rounded px-3 py-2\" {$required}>\n";
                foreach ($column['options'] as $option) {
                    $selected = $isEdit ? "{% if item.{$column['name']} == '{$option}' %}selected{% endif %}" : '';
                    $formFields .= "                        <option value=\"{$option}\" {$selected}>{$option}</option>\n";
                }
                $formFields .= "                    </select>\n                </div>\n\n";
            } else {
                $type = in_array($column['type'], ['date', 'datetime', 'integer', 'decimal', 'float']) ? $column['type'] : 'text';
                if ($type === 'datetime')
                    $type = 'datetime-local';
                if ($type === 'decimal' || $type === 'float')
                    $type = 'number';

                $formFields .= "                <div class=\"mb-4\">\n                    <label class=\"block text-gray-700 mb-2\">{$label}</label>\n                    <input type=\"{$type}\" name=\"{$column['name']}\" value=\"{$value}\" class=\"w-full border rounded px-3 py-2\" {$required}>\n                </div>\n\n";
            }
        }

        return $formFields;
    }

    /**
     * Add routes to web.php
     */
    protected function addRoutes()
    {
        $routesFile = 'routes/web.php';
        $content = file_get_contents($routesFile);

        $route = $this->routePath;

        $newRoutes = "\n// {$this->modelName} Resource Routes\n";
        $newRoutes .= "Route::get(\$router, '/{$route}', '{$this->controllerName}@index');\n";
        $newRoutes .= "Route::get(\$router, '/{$route}/create', '{$this->controllerName}@create');\n";
        $newRoutes .= "Route::post(\$router, '/{$route}/store', '{$this->controllerName}@store');\n";
        $newRoutes .= "Route::get(\$router, '/{$route}/(\\d+)', '{$this->controllerName}@show');\n";
        $newRoutes .= "Route::get(\$router, '/{$route}/(\\d+)/edit', '{$this->controllerName}@edit');\n";
        $newRoutes .= "Route::post(\$router, '/{$route}/(\\d+)/update', '{$this->controllerName}@update');\n";
        $newRoutes .= "Route::get(\$router, '/{$route}/(\\d+)/delete', '{$this->controllerName}@destroy');\n";

        $content .= $newRoutes;

        file_put_contents($routesFile, $content);
        $this->success("✓ Routes added");
    }

    /**
     * Print success message
     */
    protected function printSuccess()
    {
        echo "\n";
        $this->success("╔══════════════════════════════════════════════════════════╗");
        $this->success("║          🎉 Resource Generated Successfully! 🎉         ║");
        $this->success("╚══════════════════════════════════════════════════════════╝");
        echo "\n";

        $this->info("📦 Files created:");
        $this->info("  ✓ Migration with " . count($this->columns) . " columns");
        $this->info("  ✓ Model with " . count($this->relationships) . " relationship(s)");
        $this->info("  ✓ Controller with validation & pagination");
        $this->info("  ✓ Views with search & flash messages");
        $this->info("  ✓ Routes");

        if ($this->useSoftDeletes) {
            $this->info("  ✓ Soft deletes enabled");
        }

        echo "\n";

        $runMigration = $this->ask("Run migration now? (yes/no)", "yes");
        if (in_array(strtolower($runMigration), ['yes', 'y'])) {
            system('php oxygen migrate');
        }

        echo "\n";
        $route = '/' . $this->routePath;
        $this->success("🚀 Visit: http://your-domain{$route}");
        echo "\n";
    }


}
