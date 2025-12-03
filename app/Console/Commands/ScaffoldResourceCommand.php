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
                $isMultiple = $this->ask("Support multiple files? (yes/no)", "no");
                $isMultiple = in_array(strtolower($isMultiple), ['yes', 'y']);

                $this->fileUploads[$columnName] = [
                    'multiple' => $isMultiple,
                    'type' => $columnType
                ];

                if ($isMultiple) {
                    $column['type'] = 'json'; // Store as JSON array
                } else {
                    $column['type'] = 'string'; // Store as path
                    $column['length'] = 500;
                }
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

        $uploadLogicStore = $this->generateUploadLogic(false);
        $uploadLogicUpdate = $this->generateUploadLogic(true);

        $content = <<<PHP
<?php

namespace Oxygen\Controllers;

use Oxygen\Core\Controller;
use Oxygen\Core\Request;
use Oxygen\Core\Response;
use Oxygen\Core\Validator;
use Oxygen\Core\Flash;
use Oxygen\Core\Storage;
use Oxygen\Models\\{$this->modelName};

/**
 * {$this->controllerName}
 * 
 * Generated by OxygenFramework Scaffolder
 */
class {$this->controllerName} extends Controller
{
    public function index()
    {
{$searchLogic}        
        return \$this->view('{$viewPath}/index', ['items' => \$items]);
    }

    public function create()
    {
{$relatedDataFetch}        return \$this->view('{$viewPath}/create'{$relatedDataPass});
    }

    public function store()
    {
        \$request = \$this->app->make(Request::class);
        
        // Use clean() to strip HTML tags (Security)
        \$validator = Validator::make(\$request->clean(), [
{$validationRulesStr}        ]);
        
        if (\$validator->fails()) {
            Flash::error('Validation failed!');
            \$_SESSION['errors'] = \$validator->errors();
            \$_SESSION['old'] = \$request->all();
            Response::redirect('/{$viewPath}/create');
            return;
        }

        \$data = \$validator->validated();

        // Handle File Uploads
{$uploadLogicStore}
        
        {$this->modelName}::create(\$data);
        Flash::success('{$this->modelName} created successfully!');
        Response::redirect('/{$viewPath}');
    }

    public function show(\$id)
    {
        \$item = {$this->modelName}::find(\$id);
        return \$this->view('{$viewPath}/show', ['item' => \$item]);
    }

    public function edit(\$id)
    {
        \$item = {$this->modelName}::find(\$id);
{$relatedDataFetch}        return \$this->view('{$viewPath}/edit', ['item' => \$item{$relatedDataPass}]);
    }

    public function update(\$id)
    {
        \$request = \$this->app->make(Request::class);
        
        // Use clean() to strip HTML tags (Security)
        \$validator = Validator::make(\$request->clean(), [
{$validationRulesStr}        ]);
        
        if (\$validator->fails()) {
            Flash::error('Validation failed!');
            \$_SESSION['errors'] = \$validator->errors();
            \$_SESSION['old'] = \$request->all();
            Response::redirect('/{$viewPath}/' . \$id . '/edit');
            return;
        }

        \$data = \$validator->validated();

        // Handle File Uploads
{$uploadLogicUpdate}
        
        {$this->modelName}::update(\$id, \$data);
        Flash::success('{$this->modelName} updated successfully!');
        Response::redirect('/{$viewPath}');
    }

    public function destroy(\$id)
    {
        {$this->modelName}::delete(\$id);
        Flash::success('{$this->modelName} deleted successfully!');
        Response::redirect('/{$viewPath}');
    }
}
PHP;

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
        // Use url() helper for create link
        $createUrl = "{{ url('" . $this->routePath . "/create') }}";

        // Build search form
        $searchForm = '';
        if ($this->enableSearch) {
            $searchForm = "    <form method=\"GET\" class=\"mb-4\">\n        <div class=\"flex gap-2\">\n            <input type=\"search\" name=\"search\" value=\"{{ _GET.search }}\" placeholder=\"Search...\" class=\"border rounded px-4 py-2 w-full md:w-1/3\">\n            <button type=\"submit\" class=\"bg-gray-500 text-white px-4 py-2 rounded hover:bg-gray-600\">Search</button>\n        </div>\n    </form>\n\n";
        }

        $tableHeaders = '';
        $tableRows = '';

        foreach ($this->columns as $column) {
            if (!in_array($column['name'], ['id', 'created_at', 'updated_at', 'deleted_at'])) {
                $label = ucfirst(str_replace('_', ' ', $column['name']));
                $tableHeaders .= "                    <th class=\"px-6 py-3 text-left\">{$label}</th>\n";

                // Check if it's a file/image
                if (array_key_exists($column['name'], $this->fileUploads)) {
                    $config = $this->fileUploads[$column['name']];
                    if ($config['multiple']) {
                        $tableRows .= "                    <td class=\"px-6 py-4\">\n                        {% if item.{$column['name']} %}\n                            {% for file in json_decode(item.{$column['name']}) %}\n                                <a href=\"{{ storage(file) }}\" target=\"_blank\" class=\"text-blue-500 hover:underline block\">View File {{ loop.index }}</a>\n                            {% endfor %}\n                        {% else %}\n                            <span class=\"text-gray-400\">No files</span>\n                        {% endif %}\n                    </td>\n";
                    } else {
                        $tableRows .= "                    <td class=\"px-6 py-4\">\n                        {% if item.{$column['name']} %}\n                            <a href=\"{{ storage(item.{$column['name']}) }}\" target=\"_blank\" class=\"text-blue-500 hover:underline\">View File</a>\n                        {% else %}\n                            <span class=\"text-gray-400\">No file</span>\n                        {% endif %}\n                    </td>\n";
                    }
                } else {
                    $tableRows .= "                    <td class=\"px-6 py-4\">{{ item.{$column['name']} }}</td>\n";
                }
            }
        }

        $content = "{% extends \"layouts/app.twig.html\" %}\n\n{% block title %}{$title}{% endblock %}\n\n{% block content %}\n    <div class=\"flex justify-between items-center mb-6\">\n        <h1 class=\"text-3xl font-bold\">{$title}</h1>\n        <a href=\"{$createUrl}\" class=\"bg-blue-500 text-white px-4 py-2 rounded hover:bg-blue-600\">Create New</a>\n    </div>\n    \n{$searchForm}    <div class=\"bg-white shadow-md rounded overflow-hidden\">\n        <table class=\"min-w-full\">\n            <thead class=\"bg-gray-100\">\n                <tr>\n{$tableHeaders}                    <th class=\"px-6 py-3 text-left\">Actions</th>\n                </tr>\n            </thead>\n            <tbody>\n                {% for item in items.items() %}\n                <tr class=\"border-t hover:bg-gray-50\">\n{$tableRows}                    <td class=\"px-6 py-4\">\n                        <a href=\"{{ url('{$this->routePath}/' ~ item.id) }}\" class=\"text-blue-500 hover:text-blue-700 mr-3\">View</a>\n                        <a href=\"{{ url('{$this->routePath}/' ~ item.id ~ '/edit') }}\" class=\"text-green-500 hover:text-green-700 mr-3\">Edit</a>\n                        <a href=\"{{ url('{$this->routePath}/' ~ item.id ~ '/delete') }}\" class=\"text-red-500 hover:text-red-700\" onclick=\"return confirm('Are you sure?')\">Delete</a>\n                    </td>\n                </tr>\n                {% endfor %}\n            </tbody>\n        </table>\n    </div>\n    \n    <div class=\"mt-4\">\n        {{ items.links()|raw }}\n    </div>\n{% endblock %}";

        file_put_contents("{$dir}/index.twig.html", $content);
    }

    /**
     * Generate create view
     */
    protected function generateCreateView($dir)
    {
        $title = "Create " . Str::studly($this->resourceName);
        $formFields = $this->buildFormFields(false);

        $content = "{% extends \"layouts/app.twig.html\" %}\n\n{% block title %}{$title}{% endblock %}\n\n{% block content %}\n    <div class=\"max-w-2xl mx-auto\">\n        <h1 class=\"text-3xl font-bold mb-6\">{$title}</h1>\n        \n        <div class=\"bg-white shadow-md rounded p-6\">\n            <form method=\"POST\" action=\"{{ url('{$this->routePath}/store') }}\" enctype=\"multipart/form-data\">\n                {{ csrf_field|raw }}\n{$formFields}                <div class=\"flex gap-4\">\n                    <button type=\"submit\" class=\"bg-blue-500 text-white px-6 py-2 rounded hover:bg-blue-600\">Create</button>\n                    <a href=\"{{ url('{$this->routePath}') }}\" class=\"bg-gray-300 text-gray-700 px-6 py-2 rounded hover:bg-gray-400\">Cancel</a>\n                </div>\n            </form>\n        </div>\n    </div>\n{% endblock %}";

        file_put_contents("{$dir}/create.twig.html", $content);
    }

    /**
     * Generate edit view
     */
    protected function generateEditView($dir)
    {
        $title = "Edit " . Str::studly($this->resourceName);
        $formFields = $this->buildFormFields(true);

        $content = "{% extends \"layouts/app.twig.html\" %}\n\n{% block title %}{$title}{% endblock %}\n\n{% block content %}\n    <div class=\"max-w-2xl mx-auto\">\n        <h1 class=\"text-3xl font-bold mb-6\">{$title}</h1>\n        \n        <div class=\"bg-white shadow-md rounded p-6\">\n            <form method=\"POST\" action=\"{{ url('{$this->routePath}/' ~ item.id ~ '/update') }}\" enctype=\"multipart/form-data\">\n                {{ csrf_field|raw }}\n{$formFields}                <div class=\"flex gap-4\">\n                    <button type=\"submit\" class=\"bg-blue-500 text-white px-6 py-2 rounded hover:bg-blue-600\">Update</button>\n                    <a href=\"{{ url('{$this->routePath}') }}\" class=\"bg-gray-300 text-gray-700 px-6 py-2 rounded hover:bg-gray-400\">Cancel</a>\n                </div>\n            </form>\n        </div>\n    </div>\n{% endblock %}";

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

                $valueDisplay = "{{ item.{$column['name']} }}";

                // Check if it's a file/image
                if (array_key_exists($column['name'], $this->fileUploads)) {
                    $config = $this->fileUploads[$column['name']];
                    if ($config['multiple']) {
                        $valueDisplay = "{% if item.{$column['name']} %}\n                    <div class=\"mt-2 flex flex-wrap gap-2\">\n                        {% for file in json_decode(item.{$column['name']}) %}\n                            <img src=\"{{ storage(file) }}\" alt=\"{$label} {{ loop.index }}\" class=\"max-w-xs rounded shadow\">\n                        {% endfor %}\n                    </div>\n                {% else %}\n                    <span class=\"text-gray-400\">No files</span>\n                {% endif %}";
                    } else {
                        $valueDisplay = "{% if item.{$column['name']} %}\n                    <div class=\"mt-2\">\n                        <img src=\"{{ storage(item.{$column['name']}) }}\" alt=\"{$label}\" class=\"max-w-xs rounded shadow\">\n                    </div>\n                {% else %}\n                    <span class=\"text-gray-400\">No file</span>\n                {% endif %}";
                    }
                }

                $fields .= "            <div class=\"mb-4\">\n                <label class=\"block text-gray-600 text-sm font-semibold mb-1\">{$label}</label>\n                <div class=\"text-gray-900\">{$valueDisplay}</div>\n            </div>\n\n";
            }
        }

        $content = "{% extends \"layouts/app.twig.html\" %}\n\n{% block title %}{$title}{% endblock %}\n\n{% block content %}\n    <div class=\"max-w-2xl mx-auto\">\n        <h1 class=\"text-3xl font-bold mb-6\">{$title}</h1>\n        \n        <div class=\"bg-white shadow-md rounded p-6\">\n{$fields}            <div class=\"flex gap-4 mt-6\">\n                <a href=\"{{ url('{$this->routePath}/' ~ item.id ~ '/edit') }}\" class=\"bg-blue-500 text-white px-6 py-2 rounded hover:bg-blue-600\">Edit</a>\n                <a href=\"{{ url('{$this->routePath}') }}\" class=\"bg-gray-300 text-gray-700 px-6 py-2 rounded hover:bg-gray-400\">Back to List</a>\n            </div>\n        </div>\n    </div>\n{% endblock %}";

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
            } elseif (array_key_exists($column['name'], $this->fileUploads)) {
                $config = $this->fileUploads[$column['name']];
                $multiple = $config['multiple'] ? 'multiple' : '';
                $name = $config['multiple'] ? $column['name'] . '[]' : $column['name'];
                $formFields .= "                <div class=\"mb-4\">\n                    <label class=\"block text-gray-700 mb-2\">{$label}</label>\n                    <input type=\"file\" name=\"{$name}\" class=\"w-full border rounded px-3 py-2\" {$multiple} {$required}>\n                </div>\n\n";
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


    /**
     * Generate upload logic for controller
     */
    protected function generateUploadLogic($isUpdate)
    {
        $logic = "";
        foreach ($this->fileUploads as $field => $config) {
            $folder = Str::plural($field);

            if ($config['multiple']) {
                $logic .= "        if (isset(\$_FILES['{$field}'])) {\n";
                $logic .= "            \$files = Storage::upload('{$field}', '{$folder}');\n";
                $logic .= "            if (\$files) {\n";
                $logic .= "                \$data['{$field}'] = json_encode(\$files);\n";
                $logic .= "            }\n";
                $logic .= "        }\n\n";
            } else {
                $logic .= "        if (isset(\$_FILES['{$field}']) && \$_FILES['{$field}']['error'] === UPLOAD_ERR_OK) {\n";
                $logic .= "            \$path = Storage::upload('{$field}', '{$folder}');\n";
                $logic .= "            if (\$path) {\n";
                $logic .= "                \$data['{$field}'] = \$path;\n";
                $logic .= "            }\n";
                $logic .= "        }\n\n";
            }
        }
        return $logic;
    }

}
