<?php

namespace Oxygen\Console\Commands\Generator\Generators;

use Oxygen\Core\Support\Str;

/**
 * ResourceGenerator - Generate Complete Resource
 * 
 * Generates migration, model, controller, and views for a single resource
 * with all relationships and features integrated.
 * 
 * @package    Oxygen\Console\Commands\Generator\Generators
 * @author     Redwan Aouni
 * @version    1.0.0
 */
class ResourceGenerator
{
    /**
     * The command instance
     */
    protected $command;

    /**
     * Resource configuration
     */
    protected $resource;

    /**
     * Relationships for this resource
     */
    protected $relationships = [];

    /**
     * Features configuration
     */
    protected $features = [];

    public function __construct($command)
    {
        $this->command = $command;
    }

    /**
     * Ask user a question (delegate to command)
     */
    protected function ask($question, $default = null)
    {
        if ($this->command && method_exists($this->command, 'ask')) {
            return $this->command->ask($question, $default);
        }
        return $default;
    }

    /**
     * Generate complete resource
     */
    /**
     * Generate complete resource
     */
    public function generate(array $resource, array $relationships = [], array $features = [], $timestamp = null)
    {
        $this->resource = $resource;
        $this->relationships = $relationships;
        $this->features = $features;

        $this->generateMigration($timestamp);
        $this->generateModel();
        $this->generateController();
        $this->generateViews();
        $this->generateRoutes();

        if ($features['api'] ?? false) {
            $this->generateAPIController();
        }

        if ($features['seeders'] ?? false) {
            $this->generateSeeder();
        }

        if ($features['tests'] ?? false) {
            $this->generateTests();
        }
    }

    /**
     * Generate migration file
     */
    protected function generateMigration($timestamp = null)
    {
        $name = $this->resource['name'];
        $table = Str::plural(strtolower($name));
        $timestamp = $timestamp ?? date('Y_m_d_His');
        $filename = "{$timestamp}_create_{$table}_table.php";
        $className = 'Create' . Str::studly($table) . 'Table';

        $fields = $this->buildMigrationFields();

        $content = "<?php\n\n";
        $content .= "use Oxygen\\Core\\Database\\Migration;\n\n";
        $content .= "class {$className} extends Migration\n";
        $content .= "{\n";
        $content .= "    public function up()\n";
        $content .= "    {\n";
        $content .= "        \$this->schema->createTable('{$table}', function(\$table) {\n";
        $content .= "            \$table->id();\n";
        $content .= $fields;
        $content .= "            \$table->timestamps();\n";
        $content .= "        });\n";
        $content .= "    }\n\n";
        $content .= "    public function down()\n";
        $content .= "    {\n";
        $content .= "        \$this->schema->dropIfExists('{$table}');\n";
        $content .= "    }\n";
        $content .= "}\n";

        $path = "database/migrations/{$filename}";
        file_put_contents($path, $content);
    }

    /**
     * Build migration fields
     */
    protected function buildMigrationFields()
    {
        $fields = '';

        foreach ($this->resource['fields'] as $field) {
            $name = $field['name'];
            $type = $field['type'];

            // 🔥 SMART FK DETECTION: Auto-detect foreign keys
            if ($this->isForeignKey($name) && $type !== 'foreignKey') {
                echo "\n\033[33m🔗 Detected potential foreign key: {$name}\033[0m\n";
                $addFK = $this->ask("Add foreign key constraint? (yes/no)", "yes");

                if (strtolower($addFK) === 'yes') {
                    $relatedTable = $this->guessRelatedTable($name);
                    $onDelete = $this->ask("On delete behavior (cascade/restrict/set null)", "cascade");

                    $fields .= "            \$table->foreignId('{$name}')->constrained('{$relatedTable}')->onDelete('{$onDelete}');\n";
                    continue;
                }
            }

            switch ($type) {
                case 'string':
                    $fields .= "            \$table->string('{$name}');\n";
                    break;
                case 'text':
                    $fields .= "            \$table->text('{$name}');\n";
                    break;
                case 'integer':
                    // Check if it's an ID field
                    if (str_ends_with($name, '_id')) {
                        $fields .= "            \$table->unsignedBigInteger('{$name}');\n";
                    } else {
                        $fields .= "            \$table->integer('{$name}');\n";
                    }
                    break;
                case 'decimal':
                    $fields .= "            \$table->decimal('{$name}', 10, 2);\n";
                    break;
                case 'boolean':
                    $fields .= "            \$table->boolean('{$name}')->default(false);\n";
                    break;
                case 'timestamp':
                    $fields .= "            \$table->timestamp('{$name}')->nullable();\n";
                    break;
                case 'date':
                    $fields .= "            \$table->date('{$name}')->nullable();\n";
                    break;
                case 'datetime':
                    $fields .= "            \$table->dateTime('{$name}')->nullable();\n";
                    break;
                case 'file':
                    $fields .= "            \$table->string('{$name}', 500)->nullable();\n";
                    break;
                case 'json':
                    $fields .= "            \$table->json('{$name}')->nullable();\n";
                    break;
                case 'foreignKey':
                    $relatedTable = str_replace('_id', 's', $name);
                    $fields .= "            \$table->foreignId('{$name}')->constrained('{$relatedTable}')->onDelete('cascade');\n";
                    break;
                case 'enum':
                    $fields .= "            \$table->enum('{$name}', ['active', 'inactive'])->default('active');\n";
                    break;
                default:
                    $fields .= "            \$table->string('{$name}');\n";
            }
        }

        return $fields;
    }

    /**
     * Check if field name suggests it's a foreign key
     */
    protected function isForeignKey($fieldName)
    {
        return str_ends_with($fieldName, '_id') && $fieldName !== 'id';
    }

    /**
     * Guess related table name from foreign key field
     */
    protected function guessRelatedTable($fieldName)
    {
        // Remove '_id' suffix and pluralize
        $singular = str_replace('_id', '', $fieldName);
        return Str::plural($singular);
    }

    /**
     * Generate model file
     */
    protected function generateModel()
    {
        $name = $this->resource['name'];
        $table = Str::plural(strtolower($name));

        $fillable = $this->buildFillableArray();
        $hidden = $this->buildHiddenArray();
        $guarded = $this->buildGuardedArray();
        $casts = $this->buildCastsArray();
        $dates = $this->buildDatesArray();
        $relationshipMethods = $this->buildRelationshipMethods();

        $content = "<?php\n\n";
        $content .= "namespace Oxygen\\Models;\n\n";
        $content .= "use Oxygen\\Core\\Model;\n\n";
        $content .= "class {$name} extends Model\n";
        $content .= "{\n";
        $content .= "    protected \$table = '{$table}';\n\n";

        // 🔒 SECURITY: Fillable (whitelist)
        $content .= "    // Fillable fields (mass assignment whitelist)\n";
        $content .= "    protected \$fillable = {$fillable};\n\n";

        // 🔒 SECURITY: Hidden (never in JSON/array)
        if (!empty($hidden)) {
            $content .= "    // Hidden fields (never exposed in JSON/array)\n";
            $content .= "    protected \$hidden = {$hidden};\n\n";
        }

        // 🔒 SECURITY: Guarded (blacklist)
        if (!empty($guarded)) {
            $content .= "    // Guarded fields (mass assignment blacklist)\n";
            $content .= "    protected \$guarded = {$guarded};\n\n";
        }

        // ⚡ TYPE CASTING: Auto-cast field types
        if (!empty($casts)) {
            $content .= "    // Type casting\n";
            $content .= "    protected \$casts = {$casts};\n\n";
        }

        // 📅 DATES: Timestamp fields
        if (!empty($dates)) {
            $content .= "    // Date fields\n";
            $content .= "    protected \$dates = {$dates};\n\n";
        }

        $content .= $relationshipMethods;
        $content .= "}\n";

        $path = "app/Models/{$name}.php";
        file_put_contents($path, $content);
    }

    /**
     * Build hidden array for sensitive fields
     */
    protected function buildHiddenArray()
    {
        $hidden = [];

        foreach ($this->resource['fields'] as $field) {
            $name = $field['name'];

            // Auto-hide sensitive fields
            if (in_array($name, ['password', 'remember_token', 'api_token', 'secret'])) {
                $hidden[] = "'{$name}'";
            }
        }

        return !empty($hidden) ? '[' . implode(', ', $hidden) . ']' : '';
    }

    /**
     * Build guarded array for protected fields
     */
    protected function buildGuardedArray()
    {
        $guarded = [];

        foreach ($this->resource['fields'] as $field) {
            $name = $field['name'];

            // Auto-guard system fields
            if (in_array($name, ['id', 'created_at', 'updated_at', 'deleted_at'])) {
                $guarded[] = "'{$name}'";
            }
        }

        return !empty($guarded) ? '[' . implode(', ', $guarded) . ']' : '';
    }

    /**
     * Build casts array for type casting
     */
    protected function buildCastsArray()
    {
        $casts = [];

        foreach ($this->resource['fields'] as $field) {
            $name = $field['name'];
            $type = $field['type'];

            // Auto-cast based on type
            switch ($type) {
                case 'boolean':
                    $casts[] = "'{$name}' => 'boolean'";
                    break;
                case 'integer':
                    if (!str_ends_with($name, '_id')) {
                        $casts[] = "'{$name}' => 'integer'";
                    }
                    break;
                case 'decimal':
                    $casts[] = "'{$name}' => 'decimal:2'";
                    break;
                case 'json':
                    $casts[] = "'{$name}' => 'array'";
                    break;
                case 'timestamp':
                case 'datetime':
                    $casts[] = "'{$name}' => 'datetime'";
                    break;
                case 'date':
                    $casts[] = "'{$name}' => 'date'";
                    break;
            }

            // Special field names
            if (str_ends_with($name, '_at') && !in_array($name, ['created_at', 'updated_at', 'deleted_at'])) {
                $casts[] = "'{$name}' => 'datetime'";
            }
            if (str_starts_with($name, 'is_') || str_starts_with($name, 'has_')) {
                $casts[] = "'{$name}' => 'boolean'";
            }
        }

        return !empty($casts) ? "[\n        " . implode(",\n        ", $casts) . "\n    ]" : '';
    }

    /**
     * Build dates array for timestamp fields
     */
    protected function buildDatesArray()
    {
        $dates = ['created_at', 'updated_at'];

        foreach ($this->resource['fields'] as $field) {
            $name = $field['name'];
            $type = $field['type'];

            // Add timestamp/datetime fields
            if (in_array($type, ['timestamp', 'datetime', 'date']) && !in_array($name, $dates)) {
                $dates[] = $name;
            }

            // Add fields ending with _at
            if (str_ends_with($name, '_at') && !in_array($name, $dates)) {
                $dates[] = $name;
            }
        }

        $dateStrings = array_map(function ($date) {
            return "'{$date}'";
        }, $dates);

        return '[' . implode(', ', $dateStrings) . ']';
    }

    /**
     * Build fillable array
     */
    protected function buildFillableArray()
    {
        $fillable = [];

        foreach ($this->resource['fields'] as $field) {
            if ($field['name'] !== 'id') {
                $fillable[] = "'{$field['name']}'";
            }
        }

        // Add timestamps
        $fillable[] = "'created_at'";
        $fillable[] = "'updated_at'";

        return '[' . implode(', ', $fillable) . ']';
    }

    /**
     * Build relationship methods
     */
    protected function buildRelationshipMethods()
    {
        $methods = '';
        $detector = new RelationshipDetector();

        foreach ($this->relationships as $rel) {
            $methods .= $detector->generateRelationshipMethod($rel);
            $methods .= "\n";
        }

        return $methods;
    }

    /**
     * Generate controller file
     */
    protected function generateController()
    {
        $name = $this->resource['name'];
        $modelName = $name;
        $controllerName = $name . 'Controller';
        $routePath = strtolower(Str::plural($name));
        $viewPath = $routePath;

        // Build validation rules
        $validationRules = $this->buildValidationRules();

        // Build file upload handling
        $fileFields = $this->getFileFields();
        $hasFiles = !empty($fileFields);

        // Build relationship eager loading
        $eagerLoad = $this->buildEagerLoadString();

        $content = "<?php\n\n";
        $content .= "namespace Oxygen\\Controllers;\n\n";
        $content .= "use Oxygen\\Core\\Controller;\n";
        $content .= "use Oxygen\\Core\\Request;\n";
        $content .= "use Oxygen\\Core\\Response;\n";
        $content .= "use Oxygen\\Core\\Validator;\n";
        $content .= "use Oxygen\\Core\\Flash;\n";
        $content .= "use Oxygen\\Models\\{$modelName};\n";

        if ($hasFiles) {
            $content .= "use Oxygen\\Services\\OxygenStorageService;\n";
        }

        $content .= "\nclass {$controllerName} extends Controller\n";
        $content .= "{\n";

        // Index method
        $content .= "    public function index()\n";
        $content .= "    {\n";
        $content .= "        \$search = \$_GET['search'] ?? null;\n";
        $content .= "        \n";
        $content .= "        if (\$search) {\n";
        $content .= "            \$items = {$modelName}::where('name', 'LIKE', \"%{\$search}%\")";
        if ($eagerLoad) {
            $content .= "->with({$eagerLoad})";
        }
        $content .= ";\n";
        $content .= "        } else {\n";
        $content .= "            \$items = {$modelName}::";
        if ($eagerLoad) {
            $content .= "with({$eagerLoad})->paginate(15)";
        } else {
            $content .= "paginate(15)";
        }
        $content .= ";\n";
        $content .= "        }\n";
        $content .= "        \n";
        $content .= "        return \$this->view('{$viewPath}/index', ['items' => \$items]);\n";
        $content .= "    }\n\n";

        // Create method
        $content .= "    public function create()\n";
        $content .= "    {\n";
        $content .= $this->buildRelationshipDataLoading();
        $content .= "        return \$this->view('{$viewPath}/create'";
        if ($this->hasRelationshipSelects()) {
            $content .= ", \$data";
        }
        $content .= ");\n";
        $content .= "    }\n\n";

        // Store method
        $content .= "    public function store()\n";
        $content .= "    {\n";
        $content .= "        \$request = \$this->app->make(Request::class);\n";
        $content .= "        \$data = \$request->all();\n\n";

        if ($hasFiles) {
            $content .= "        // Handle file uploads\n";
            foreach ($fileFields as $field) {
                $content .= "        \$data['{$field}'] = Storage::uploadImage('{$field}', 'uploads');\n";
            }
            $content .= "\n";
        }

        $content .= "        // Use clean() to strip HTML tags (Security)\n";
        $content .= "        \$validator = Validator::make(\$request->clean(), [\n";
        $content .= $validationRules;
        $content .= "        ]);\n";
        $content .= "        \n";
        $content .= "        if (\$validator->fails()) {\n";
        $content .= "            Flash::error('Validation failed!');\n";
        $content .= "            \$_SESSION['errors'] = \$validator->errors();\n";
        $content .= "            \$_SESSION['old'] = \$request->all();\n";
        $content .= "            Response::redirect('/{$routePath}/create');\n";
        $content .= "            return;\n";
        $content .= "        }\n";
        $content .= "        \n";
        $content .= "        // Add timestamps\n";
        $content .= "        \$data['created_at'] = date('Y-m-d H:i:s');\n";
        $content .= "        \$data['updated_at'] = date('Y-m-d H:i:s');\n";
        $content .= "        \n";
        $content .= "        {$modelName}::create(\$data);\n";
        $content .= "        Flash::success('{$modelName} created successfully!');\n";
        $content .= "        Response::redirect('/{$routePath}');\n";
        $content .= "    }\n\n";

        // Show, Edit...
        $content .= "    public function show(\$id)\n";
        $content .= "    {\n";
        $content .= "        \$item = {$modelName}::";
        if ($eagerLoad) {
            $content .= "with({$eagerLoad})->find(\$id)";
        } else {
            $content .= "find(\$id)";
        }
        $content .= ";\n";
        $content .= "        return \$this->view('{$viewPath}/show', ['item' => \$item]);\n";
        $content .= "    }\n\n";

        $content .= "    public function edit(\$id)\n";
        $content .= "    {\n";
        $content .= "        \$item = {$modelName}::find(\$id);\n";
        $content .= $this->buildRelationshipDataLoading();
        $content .= "        return \$this->view('{$viewPath}/edit', array_merge(['item' => \$item], \$data ?? []));\n";
        $content .= "    }\n\n";

        $content .= "    public function update(\$id)\n";
        $content .= "    {\n";
        $content .= "        \$request = \$this->app->make(Request::class);\n";
        $content .= "        \$data = \$request->all();\n\n";

        if ($hasFiles) {
            $content .= "        // Handle file uploads\n";
            foreach ($fileFields as $field) {
                $content .= "        \$path = Storage::uploadImage('{$field}', 'uploads');\n";
                $content .= "        if (\$path) {\n";
                $content .= "            // Delete old file\n";
                $content .= "            \$old = {$modelName}::find(\$id);\n";
                $content .= "            if (\$old && \$old->{$field}) {\n";
                $content .= "                Storage::delete(\$old->{$field});\n";
                $content .= "            }\n";
                $content .= "            \$data['{$field}'] = \$path;\n";
                $content .= "        }\n";
            }
            $content .= "\n";
        }

        $content .= "        // Use clean() to strip HTML tags (Security)\n";
        $content .= "        \$validator = Validator::make(\$request->clean(), [\n";
        $content .= $validationRules;
        $content .= "        ]);\n";
        $content .= "        \n";
        $content .= "        if (\$validator->fails()) {\n";
        $content .= "            Flash::error('Validation failed!');\n";
        $content .= "            \$_SESSION['errors'] = \$validator->errors();\n";
        $content .= "            \$_SESSION['old'] = \$request->all();\n";
        $content .= "            Response::redirect('/{$routePath}/' . \$id . '/edit');\n";
        $content .= "            return;\n";
        $content .= "        }\n";
        $content .= "        \n";
        $content .= "        // Add updated_at timestamp\n";
        $content .= "        \$data['updated_at'] = date('Y-m-d H:i:s');\n";
        $content .= "        \n";
        $content .= "        {$modelName}::update(\$id, \$data);\n";
        $content .= "        Flash::success('{$modelName} updated successfully!');\n";
        $content .= "        Response::redirect('/{$routePath}');\n";
        $content .= "    }\n\n";

        $content .= "    public function destroy(\$id)\n";
        $content .= "    {\n";
        $content .= "        {$modelName}::delete(\$id);\n";
        $content .= "        Flash::success('{$modelName} deleted successfully!');\n";
        $content .= "        Response::redirect('/{$routePath}');\n";
        $content .= "    }\n";
        $content .= "}\n";

        $path = "app/Controllers/{$controllerName}.php";
        file_put_contents($path, $content);
    }

    /**
     * Build validation rules string
     */
    protected function buildValidationRules()
    {
        $rules = '';
        $fileFields = $this->getFileFields();

        foreach ($this->resource['fields'] as $field) {
            if (in_array($field['name'], $fileFields)) {
                continue; // Skip file fields
            }

            $fieldName = $field['name'];
            $type = $field['type'];

            $rule = $this->getValidationRule($fieldName, $type);
            $rules .= "            '{$fieldName}' => '{$rule}',\n";
        }

        return $rules;
    }

    /**
     * Get validation rule for field
     */
    protected function getValidationRule($fieldName, $type)
    {
        $rules = ['required'];

        switch ($type) {
            case 'string':
                $rules[] = 'string';
                $rules[] = 'max:255';
                break;
            case 'text':
                $rules[] = 'string';
                break;
            case 'integer':
                $rules[] = 'integer';
                break;
            case 'decimal':
                $rules[] = 'numeric';
                break;
            case 'boolean':
                $rules = ['boolean'];
                break;
            case 'foreignKey':
                $rules[] = 'integer';
                $table = str_replace('_id', 's', $fieldName);
                $rules[] = "exists:{$table},id";
                break;
            case 'enum':
                $rules[] = 'in:active,inactive';
                break;
        }

        return implode('|', $rules);
    }

    /**
     * Get file upload fields
     */
    protected function getFileFields()
    {
        $files = [];
        foreach ($this->resource['fields'] as $field) {
            if ($field['type'] === 'file') {
                $files[] = $field['name'];
            }
        }
        return $files;
    }

    /**
     * Build eager load string for relationships
     */
    protected function buildEagerLoadString()
    {
        $belongsTo = [];

        foreach ($this->relationships as $rel) {
            if ($rel['type'] === 'belongsTo') {
                $belongsTo[] = "'{$rel['method']}'";
            }
        }

        return empty($belongsTo) ? '' : implode(', ', $belongsTo);
    }

    /**
     * Build relationship data loading for forms
     */
    protected function buildRelationshipDataLoading()
    {
        $code = '';
        $hasRelationships = false;

        foreach ($this->relationships as $rel) {
            if ($rel['type'] === 'belongsTo') {
                $hasRelationships = true;
                $model = $rel['to'];
                $varName = strtolower(Str::plural($model));
                $code .= "        \$data['{$varName}'] = \\Oxygen\\Models\\{$model}::all();\n";
            }
        }

        if ($hasRelationships) {
            $code = "        \$data = [];\n" . $code;
        }

        return $code;
    }

    /**
     * Check if has relationship selects
     */
    protected function hasRelationshipSelects()
    {
        foreach ($this->relationships as $rel) {
            if ($rel['type'] === 'belongsTo') {
                return true;
            }
        }
        return false;
    }

    /**
     * Generate views
     */
    protected function generateViews()
    {
        $name = $this->resource['name'];
        $viewPath = strtolower(Str::plural($name));
        $dir = "resources/views/{$viewPath}";

        if (!is_dir($dir)) {
            mkdir($dir, 0755, true);
        }

        $this->generateIndexView($dir);
        $this->generateCreateView($dir);
        $this->generateEditView($dir);
        $this->generateShowView($dir);
    }

    /**
     * Generate index view
     */
    protected function generateIndexView($dir)
    {
        $name = $this->resource['name'];
        $title = Str::plural($name);
        $routePath = strtolower(Str::plural($name));

        // Build table headers and rows
        $tableHeaders = '';
        $tableRows = '';

        foreach ($this->resource['fields'] as $field) {
            if (!in_array($field['name'], ['id', 'created_at', 'updated_at', 'deleted_at'])) {
                $label = ucfirst(str_replace('_', ' ', $field['name']));
                $tableHeaders .= "                    <th class=\"px-6 py-3 text-left\">{$label}</th>\n";

                // Check if it's a file
                if ($field['type'] === 'file') {
                    $tableRows .= "                    <td class=\"px-6 py-4\">\n                        {% if item.{$field['name']} %}\n                            <a href=\"{{ storage(item.{$field['name']}) }}\" target=\"_blank\" class=\"text-blue-500 hover:underline\">View File</a>\n                        {% else %}\n                            <span class=\"text-gray-400\">No file</span>\n                        {% endif %}\n                    </td>\n";
                } else {
                    $tableRows .= "                    <td class=\"px-6 py-4\">{{ item.{$field['name']} }}</td>\n";
                }
            }
        }

        $content = "{% extends \"layouts/app.twig.html\" %}\n\n{% block title %}{$title}{% endblock %}\n\n{% block content %}\n    <div class=\"flex justify-between items-center mb-6\">\n        <h1 class=\"text-3xl font-bold\">{$title}</h1>\n        <a href=\"{{ url('{$routePath}/create') }}\" class=\"bg-blue-500 text-white px-4 py-2 rounded hover:bg-blue-600\">Create New</a>\n    </div>\n    \n    <form method=\"GET\" class=\"mb-4\">\n        <div class=\"flex gap-2\">\n            <input type=\"search\" name=\"search\" value=\"{{ _GET.search }}\" placeholder=\"Search...\" class=\"border rounded px-4 py-2 w-full md:w-1/3\">\n            <button type=\"submit\" class=\"bg-gray-500 text-white px-4 py-2 rounded hover:bg-gray-600\">Search</button>\n        </div>\n    </form>\n\n    <div class=\"bg-white shadow-md rounded overflow-hidden\">\n        <table class=\"min-w-full\">\n            <thead class=\"bg-gray-100\">\n                <tr>\n{$tableHeaders}                    <th class=\"px-6 py-3 text-left\">Actions</th>\n                </tr>\n            </thead>\n            <tbody>\n                {% for item in items.items() %}\n                <tr class=\"border-t hover:bg-gray-50\">\n{$tableRows}                    <td class=\"px-6 py-4\">\n                        <a href=\"{{ url('{$routePath}/' ~ item.id) }}\" class=\"text-blue-500 hover:text-blue-700 mr-3\">View</a>\n                        <a href=\"{{ url('{$routePath}/' ~ item.id ~ '/edit') }}\" class=\"text-green-500 hover:text-green-700 mr-3\">Edit</a>\n                        <a href=\"{{ url('{$routePath}/' ~ item.id ~ '/delete') }}\" class=\"text-red-500 hover:text-red-700\" onclick=\"return confirm('Are you sure?')\">Delete</a>\n                    </td>\n                </tr>\n                {% endfor %}\n            </tbody>\n        </table>\n    </div>\n    \n    <div class=\"mt-4\">\n        {{ items.links()|raw }}\n    </div>\n{% endblock %}";

        file_put_contents("{$dir}/index.twig.html", $content);
    }

    /**
     * Generate create view
     */
    protected function generateCreateView($dir)
    {
        $name = $this->resource['name'];
        $title = "Create " . $name;
        $routePath = strtolower(Str::plural($name));

        $formFields = $this->buildFormFields(false);

        $content = "{% extends \"layouts/app.twig.html\" %}\n\n{% block title %}{$title}{% endblock %}\n\n{% block content %}\n    <div class=\"max-w-2xl mx-auto\">\n        <h1 class=\"text-3xl font-bold mb-6\">{$title}</h1>\n        \n        <div class=\"bg-white shadow-md rounded p-6\">\n            <form method=\"POST\" action=\"{{ url('{$routePath}/store') }}\" enctype=\"multipart/form-data\">\n                {{ csrf_field|raw }}\n{$formFields}                <div class=\"flex gap-4\">\n                    <button type=\"submit\" class=\"bg-blue-500 text-white px-6 py-2 rounded hover:bg-blue-600\">Create</button>\n                    <a href=\"{{ url('{$routePath}') }}\" class=\"bg-gray-300 text-gray-700 px-6 py-2 rounded hover:bg-gray-400\">Cancel</a>\n                </div>\n            </form>\n        </div>\n    </div>\n{% endblock %}";

        file_put_contents("{$dir}/create.twig.html", $content);
    }

    /**
     * Generate edit view
     */
    protected function generateEditView($dir)
    {
        $name = $this->resource['name'];
        $title = "Edit " . $name;
        $routePath = strtolower(Str::plural($name));

        $formFields = $this->buildFormFields(true);

        $content = "{% extends \"layouts/app.twig.html\" %}\n\n{% block title %}{$title}{% endblock %}\n\n{% block content %}\n    <div class=\"max-w-2xl mx-auto\">\n        <h1 class=\"text-3xl font-bold mb-6\">{$title}</h1>\n        \n        <div class=\"bg-white shadow-md rounded p-6\">\n            <form method=\"POST\" action=\"{{ url('{$routePath}/' ~ item.id ~ '/update') }}\" enctype=\"multipart/form-data\">\n                {{ csrf_field|raw }}\n{$formFields}                <div class=\"flex gap-4\">\n                    <button type=\"submit\" class=\"bg-blue-500 text-white px-6 py-2 rounded hover:bg-blue-600\">Update</button>\n                    <a href=\"{{ url('{$routePath}') }}\" class=\"bg-gray-300 text-gray-700 px-6 py-2 rounded hover:bg-gray-400\">Cancel</a>\n                </div>\n            </form>\n        </div>\n    </div>\n{% endblock %}";

        file_put_contents("{$dir}/edit.twig.html", $content);
    }

    /**
     * Generate show view
     */
    protected function generateShowView($dir)
    {
        $name = $this->resource['name'];
        $title = $name . " Details";
        $routePath = strtolower(Str::plural($name));

        $fields = '';
        foreach ($this->resource['fields'] as $field) {
            if ($field['name'] !== 'id' && !in_array($field['name'], ['created_at', 'updated_at', 'deleted_at'])) {
                $label = ucfirst(str_replace('_', ' ', $field['name']));
                $valueDisplay = "{{ item.{$field['name']} }}";

                // Check if it's a file
                if ($field['type'] === 'file') {
                    $valueDisplay = "{% if item.{$field['name']} %}\n                    <div class=\"mt-2\">\n                        <img src=\"{{ storage(item.{$field['name']}) }}\" alt=\"{$label}\" class=\"max-w-xs rounded shadow\">\n                    </div>\n                {% else %}\n                    <span class=\"text-gray-400\">No file</span>\n                {% endif %}";
                }

                $fields .= "            <div class=\"mb-4\">\n                <label class=\"block text-gray-600 text-sm font-semibold mb-1\">{$label}</label>\n                <div class=\"text-gray-900\">{$valueDisplay}</div>\n            </div>\n\n";
            }
        }

        $content = "{% extends \"layouts/app.twig.html\" %}\n\n{% block title %}{$title}{% endblock %}\n\n{% block content %}\n    <div class=\"max-w-2xl mx-auto\">\n        <h1 class=\"text-3xl font-bold mb-6\">{$title}</h1>\n        \n        <div class=\"bg-white shadow-md rounded p-6\">\n{$fields}            <div class=\"flex gap-4 mt-6\">\n                <a href=\"{{ url('{$routePath}/' ~ item.id ~ '/edit') }}\" class=\"bg-blue-500 text-white px-6 py-2 rounded hover:bg-blue-600\">Edit</a>\n                <a href=\"{{ url('{$routePath}') }}\" class=\"bg-gray-300 text-gray-700 px-6 py-2 rounded hover:bg-gray-400\">Back to List</a>\n            </div>\n        </div>\n    </div>\n{% endblock %}";

        file_put_contents("{$dir}/show.twig.html", $content);
    }

    /**
     * Build form fields for create/edit views
     */
    protected function buildFormFields($isEdit)
    {
        $formFields = '';

        foreach ($this->resource['fields'] as $field) {
            if (in_array($field['name'], ['id', 'created_at', 'updated_at', 'deleted_at'])) {
                continue;
            }

            $label = ucfirst(str_replace('_', ' ', $field['name']));
            $required = 'required';
            $value = $isEdit ? "{{ item.{$field['name']} }}" : '';

            // Check if this is a foreign key
            $isForeignKey = false;
            $relatedVar = '';
            foreach ($this->relationships as $rel) {
                if ($rel['type'] === 'belongsTo' && isset($rel['foreignKey']) && $rel['foreignKey'] === $field['name']) {
                    $isForeignKey = true;
                    $relatedVar = strtolower(Str::plural($rel['to']));
                    break;
                }
            }

            if ($isForeignKey) {
                $formFields .= "                <div class=\"mb-4\">\n                    <label class=\"block text-gray-700 mb-2\">{$label}</label>\n                    <select name=\"{$field['name']}\" class=\"w-full border rounded px-3 py-2\" {$required}>\n                        <option value=\"\">Select {$label}</option>\n                        {% for relItem in {$relatedVar} %}\n                            <option value=\"{{ relItem.id }}\" {% if item.{$field['name']} == relItem.id %}selected{% endif %}>{{ relItem.name }}</option>\n                        {% endfor %}\n                    </select>\n                </div>\n\n";
            } elseif ($field['type'] === 'file') {
                $formFields .= "                <div class=\"mb-4\">\n                    <label class=\"block text-gray-700 mb-2\">{$label}</label>\n                    <input type=\"file\" name=\"{$field['name']}\" class=\"w-full border rounded px-3 py-2\" {$required}>\n                </div>\n\n";
            } elseif ($field['type'] === 'text') {
                $formFields .= "                <div class=\"mb-4\">\n                    <label class=\"block text-gray-700 mb-2\">{$label}</label>\n                    <textarea name=\"{$field['name']}\" class=\"w-full border rounded px-3 py-2\" rows=\"4\" {$required}>{$value}</textarea>\n                </div>\n\n";
            } elseif ($field['type'] === 'boolean') {
                $checked = $isEdit ? "{% if item.{$field['name']} %}checked{% endif %}" : '';
                $formFields .= "                <div class=\"mb-4\">\n                    <label class=\"flex items-center\">\n                        <input type=\"checkbox\" name=\"{$field['name']}\" value=\"1\" class=\"mr-2\" {$checked}>\n                        <span class=\"text-gray-700\">{$label}</span>\n                    </label>\n                </div>\n\n";
            } elseif ($field['type'] === 'enum') {
                $formFields .= "                <div class=\"mb-4\">\n                    <label class=\"block text-gray-700 mb-2\">{$label}</label>\n                    <select name=\"{$field['name']}\" class=\"w-full border rounded px-3 py-2\" {$required}>\n                        <option value=\"active\" {% if item.{$field['name']} == 'active' %}selected{% endif %}>Active</option>\n                        <option value=\"inactive\" {% if item.{$field['name']} == 'inactive' %}selected{% endif %}>Inactive</option>\n                    </select>\n                </div>\n\n";
            } else {
                $type = in_array($field['type'], ['integer', 'decimal']) ? 'number' : 'text';
                if ($field['type'] === 'timestamp') {
                    $type = 'datetime-local';
                }

                $formFields .= "                <div class=\"mb-4\">\n                    <label class=\"block text-gray-700 mb-2\">{$label}</label>\n                    <input type=\"{$type}\" name=\"{$field['name']}\" value=\"{$value}\" class=\"w-full border rounded px-3 py-2\" {$required}>\n                </div>\n\n";
            }
        }

        return $formFields;
    }

    /**
     * Generate routes
     */
    protected function generateRoutes()
    {
        $name = $this->resource['name'];
        $controller = $name . 'Controller';
        $path = strtolower(Str::plural($name));

        $routes = "\n// {$name} Resource Routes\n";
        $routes .= "Route::get(\$router, '/{$path}', '{$controller}@index');\n";
        $routes .= "Route::get(\$router, '/{$path}/create', '{$controller}@create');\n";
        $routes .= "Route::post(\$router, '/{$path}/store', '{$controller}@store');\n";
        $routes .= "Route::get(\$router, '/{$path}/(\\\\d+)', '{$controller}@show');\n";
        $routes .= "Route::get(\$router, '/{$path}/(\\\\d+)/edit', '{$controller}@edit');\n";
        $routes .= "Route::post(\$router, '/{$path}/(\\\\d+)/update', '{$controller}@update');\n";
        $routes .= "Route::get(\$router, '/{$path}/(\\\\d+)/delete', '{$controller}@destroy');\n";

        // Append to routes file
        file_put_contents('routes/web.php', $routes, FILE_APPEND);
    }

    /**
     * Generate API controller
     */
    protected function generateAPIController()
    {
        // TODO: Implement API controller generation
    }

    /**
     * Generate seeder
     */
    protected function generateSeeder()
    {
        // TODO: Implement seeder generation
    }

    /**
     * Generate tests
     */
    protected function generateTests()
    {
        // TODO: Implement test generation
    }
}
