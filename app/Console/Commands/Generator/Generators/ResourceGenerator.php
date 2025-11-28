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
        $content .= "        \$this->schema->create('{$table}', function(\$table) {\n";
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

            switch ($type) {
                case 'string':
                    $fields .= "            \$table->string('{$name}');\n";
                    break;
                case 'text':
                    $fields .= "            \$table->text('{$name}');\n";
                    break;
                case 'integer':
                    $fields .= "            \$table->integer('{$name}');\n";
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
                case 'file':
                    $fields .= "            \$table->string('{$name}', 500)->nullable();\n";
                    break;
                case 'foreignKey':
                    $relatedTable = str_replace('_id', 's', $name);
                    $fields .= "            \$table->foreignId('{$name}')->constrained('{$relatedTable}')->onDelete('cascade');\n";
                    break;
                case 'enum':
                    $fields .= "            \$table->enum('{$name}', ['active', 'inactive'])->default('active');\n";
                    break;
            }
        }

        return $fields;
    }

    /**
     * Generate model file
     */
    protected function generateModel()
    {
        $name = $this->resource['name'];
        $table = Str::plural(strtolower($name));

        $fillable = $this->buildFillableArray();
        $relationshipMethods = $this->buildRelationshipMethods();

        $content = "<?php\n\n";
        $content .= "namespace Oxygen\\Models;\n\n";
        $content .= "use Oxygen\\Core\\Model;\n\n";
        $content .= "class {$name} extends Model\n";
        $content .= "{\n";
        $content .= "    protected \$table = '{$table}';\n\n";
        $content .= "    protected \$fillable = {$fillable};\n\n";
        $content .= $relationshipMethods;
        $content .= "}\n";

        $path = "app/Models/{$name}.php";
        file_put_contents($path, $content);
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
        $content .= "use Controller;\n";
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
        $content .= "        \n";

        if ($hasFiles) {
            $content .= "        // Handle file uploads\n";
            $content .= "        \$storage = \$this->app->make(OxygenStorageService::class);\n";
            $content .= "        \$data = \$request->all();\n\n";

            foreach ($fileFields as $field) {
                $content .= "        if (isset(\$_FILES['{$field}']) && \$_FILES['{$field}']['error'] === UPLOAD_ERR_OK) {\n";
                $content .= "            \$uploadResult = \$storage->upload(\$_FILES['{$field}'], 'uploads');\n";
                $content .= "            if (\$uploadResult['success']) {\n";
                $content .= "                \$data['{$field}'] = \$uploadResult['path'];\n";
                $content .= "            } else {\n";
                $content .= "                Flash::error('File upload failed: ' . \$uploadResult['error']);\n";
                $content .= "                \$_SESSION['old'] = \$request->all();\n";
                $content .= "                Response::redirect('/{$routePath}/create');\n";
                $content .= "                return;\n";
                $content .= "            }\n";
                $content .= "        }\n\n";
            }
        } else {
            $content .= "        \$data = \$request->all();\n";
        }

        $content .= "        \$validator = Validator::make(\$data, [\n";
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
        $content .= "        {$modelName}::create(\$validator->validated());\n";
        $content .= "        Flash::success('{$modelName} created successfully!');\n";
        $content .= "        Response::redirect('/{$routePath}');\n";
        $content .= "    }\n\n";

        // Show, Edit, Update, Destroy methods...
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
        $content .= "        \n";

        if ($hasFiles) {
            $content .= "        // Handle file uploads\n";
            $content .= "        \$storage = \$this->app->make(OxygenStorageService::class);\n";
            $content .= "        \$data = \$request->all();\n";
            $content .= "        \$item = {$modelName}::find(\$id);\n\n";

            foreach ($fileFields as $field) {
                $content .= "        if (isset(\$_FILES['{$field}']) && \$_FILES['{$field}']['error'] === UPLOAD_ERR_OK) {\n";
                $content .= "            if (\$item && !empty(\$item->{$field})) {\n";
                $content .= "                \$storage->delete(\$item->{$field});\n";
                $content .= "            }\n";
                $content .= "            \$uploadResult = \$storage->upload(\$_FILES['{$field}'], 'uploads');\n";
                $content .= "            if (\$uploadResult['success']) {\n";
                $content .= "                \$data['{$field}'] = \$uploadResult['path'];\n";
                $content .= "            }\n";
                $content .= "        }\n\n";
            }
        } else {
            $content .= "        \$data = \$request->all();\n";
        }

        $content .= "        \$validator = Validator::make(\$data, [\n";
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
        $content .= "        {$modelName}::update(\$id, \$validator->validated());\n";
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
     * Generate index view (simplified version)
     */
    protected function generateIndexView($dir)
    {
        $name = $this->resource['name'];
        $title = Str::plural($name);

        $content = "<!DOCTYPE html>\n<html>\n<head>\n    <title>{$title}</title>\n</head>\n<body>\n";
        $content .= "    <h1>{$title}</h1>\n";
        $content .= "    <a href=\"/" . strtolower($title) . "/create\">Create New</a>\n";
        $content .= "    {% for item in items %}\n";
        $content .= "        <div>{{ item.name }}</div>\n";
        $content .= "    {% endfor %}\n";
        $content .= "</body>\n</html>";

        file_put_contents("{$dir}/index.twig.html", $content);
    }

    /**
     * Generate create view (simplified)
     */
    protected function generateCreateView($dir)
    {
        $content = "<!DOCTYPE html>\n<html>\n<body>\n    <h1>Create</h1>\n    <form method=\"POST\">\n        <!-- Form fields -->\n    </form>\n</body>\n</html>";
        file_put_contents("{$dir}/create.twig.html", $content);
    }

    /**
     * Generate edit view (simplified)
     */
    protected function generateEditView($dir)
    {
        $content = "<!DOCTYPE html>\n<html>\n<body>\n    <h1>Edit</h1>\n    <form method=\"POST\">\n        <!-- Form fields -->\n    </form>\n</body>\n</html>";
        file_put_contents("{$dir}/edit.twig.html", $content);
    }

    /**
     * Generate show view (simplified)
     */
    protected function generateShowView($dir)
    {
        $content = "<!DOCTYPE html>\n<html>\n<body>\n    <h1>Show</h1>\n    <div>{{ item.name }}</div>\n</body>\n</html>";
        file_put_contents("{$dir}/show.twig.html", $content);
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
        $routes .= "Route::resource(\$router, '/{$path}', '{$controller}');\n";

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
