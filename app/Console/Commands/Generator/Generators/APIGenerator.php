<?php

namespace Oxygen\Console\Commands\Generator\Generators;

use Oxygen\Console\Command;
use Oxygen\Core\Support\Str;

/**
 * APIGenerator - Generates REST API
 * 
 * Scaffolds API controllers and routes for resources.
 * 
 * @package    Oxygen\Console\Commands\Generator\Generators
 * @author     Redwan Aouni
 * @version    1.0.0
 */
class APIGenerator
{
    /**
     * The command instance
     */
    protected $command;

    public function __construct(Command $command)
    {
        $this->command = $command;
    }

    /**
     * Generate API for resources
     */
    public function generate(array $resources)
    {
        $this->command->info('Generating API endpoints...');

        foreach ($resources as $resource) {
            $this->generateController($resource);
            $this->generateRoutes($resource);
        }

        $this->command->success('API generated successfully.');
    }

    /**
     * Generate API controller
     */
    protected function generateController($resource)
    {
        $name = $resource['name'];
        $modelName = Str::studly($name);
        $controllerName = $modelName . 'Controller';

        $this->command->info("  - Generating API controller for {$name}...");

        $content = $this->getControllerStub($modelName, $controllerName);

        $path = "app/Controllers/API/{$controllerName}.php";
        $dir = dirname($path);

        if (!is_dir($dir)) {
            mkdir($dir, 0755, true);
        }

        file_put_contents($path, $content);
    }

    /**
     * Generate API routes
     */
    protected function generateRoutes($resource)
    {
        $name = $resource['name'];
        $modelName = Str::studly($name);
        $pluralName = Str::plural(Str::snake($name));
        $routePath = str_replace('_', '-', $pluralName);
        $controllerName = $modelName . 'Controller';

        $routesFile = 'routes/api.php';

        // Create api.php if it doesn't exist
        if (!file_exists($routesFile)) {
            file_put_contents($routesFile, "<?php\n\nuse Oxygen\Core\Support\Facades\Route;\n\n");
        }

        $content = file_get_contents($routesFile);

        if (strpos($content, "// {$modelName} API Routes") === false) {
            $routes = "\n// {$modelName} API Routes\n";
            $routes .= "Route::get(\$router, '/api/{$routePath}', 'API\\{$controllerName}@index');\n";
            $routes .= "Route::post(\$router, '/api/{$routePath}', 'API\\{$controllerName}@store');\n";
            $routes .= "Route::get(\$router, '/api/{$routePath}/(\\d+)', 'API\\{$controllerName}@show');\n";
            $routes .= "Route::put(\$router, '/api/{$routePath}/(\\d+)', 'API\\{$controllerName}@update');\n";
            $routes .= "Route::delete(\$router, '/api/{$routePath}/(\\d+)', 'API\\{$controllerName}@destroy');\n";

            file_put_contents($routesFile, $content . $routes);
        }
    }

    /**
     * Get controller stub
     */
    protected function getControllerStub($modelName, $controllerName)
    {
        return <<<EOT
<?php

namespace Oxygen\Controllers\API;

use Oxygen\Core\Controller;
use Oxygen\Core\Http\Request;
use Oxygen\Models\\{$modelName};

class {$controllerName} extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        \$items = {$modelName}::all();
        return \$this->json(['data' => \$items]);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request \$request)
    {
        \$data = \$request->all();
        // TODO: Add validation
        
        \$item = {$modelName}::create(\$data);
        
        return \$this->json(['data' => \$item, 'message' => 'Created successfully'], 201);
    }

    /**
     * Display the specified resource.
     */
    public function show(\$id)
    {
        \$item = {$modelName}::find(\$id);
        
        if (!\$item) {
            return \$this->json(['error' => 'Not found'], 404);
        }
        
        return \$this->json(['data' => \$item]);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request \$request, \$id)
    {
        \$item = {$modelName}::find(\$id);
        
        if (!\$item) {
            return \$this->json(['error' => 'Not found'], 404);
        }
        
        \$item->update(\$request->all());
        
        return \$this->json(['data' => \$item, 'message' => 'Updated successfully']);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(\$id)
    {
        \$item = {$modelName}::find(\$id);
        
        if (!\$item) {
            return \$this->json(['error' => 'Not found'], 404);
        }
        
        \$item->delete();
        
        return \$this->json(['message' => 'Deleted successfully']);
    }
}
EOT;
    }
}
