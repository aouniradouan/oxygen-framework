<?php

namespace Oxygen\Console\Commands;

use Oxygen\Console\Command;

/**
 * MakeControllerCommand - Generate a new controller class
 * 
 * This command creates a new controller file with the proper namespace
 * and boilerplate code.
 * 
 * @package    Oxygen\Console\Commands
 * @author     Redwan Aouni <aouniradouan@gmail.com>
 * @copyright  2024 - OxygenFramework
 * @version    2.0.0
 * 
 * Usage:
 *   php oxygen controller:create UserController
 *   php oxygen controller:create Admin/PostController
 */
class MakeControllerCommand extends Command
{
    /**
     * Execute the command
     * 
     * @param array $arguments Command arguments
     * @return void
     */
    public function execute($arguments)
    {
        // Check if name argument is provided
        if (empty($arguments[0])) {
            $this->error('Controller name is required.');
            $this->info('Usage: php oxygen controller:create ControllerName');
            return;
        }

        $name = $arguments[0];

        // Remove "Controller" suffix if provided, we'll add it back
        $name = str_replace('Controller', '', $name);
        $className = $name . 'Controller';

        // Determine the path
        $basePath = __DIR__ . '/../../../app/Controllers/';

        // Handle subdirectories (e.g., Admin/PostController)
        if (strpos($name, '/') !== false) {
            $parts = explode('/', $name);
            $className = array_pop($parts) . 'Controller';
            $namespace = 'Oxygen\\Controllers\\' . implode('\\', $parts);
            $path = $basePath . implode('/', $parts) . '/' . $className . '.php';
        } else {
            $namespace = 'Oxygen\\Controllers';
            $path = $basePath . $className . '.php';
        }

        // Generate controller content
        $content = $this->getStub($className, $namespace);

        // Create the file
        if ($this->createFile($path, $content)) {
            $this->success("Controller created successfully: {$className}");
            $this->info("Location: {$path}");
        }
    }

    /**
     * Get the controller stub/template
     * 
     * @param string $className Class name
     * @param string $namespace Namespace
     * @return string
     */
    protected function getStub($className, $namespace)
    {
        return <<<PHP
<?php

namespace {$namespace};

use Oxygen\Core\Controller;
use Oxygen\Core\Request;
use Oxygen\Core\Response;
use Oxygen\Core\Validator;
use Oxygen\Core\Flash;
use Oxygen\Core\Storage;
use Oxygen\Models\\{$className}; // Assuming model name matches controller

/**
 * {$className}
 * 
 * Controller for handling {$className} related requests.
 * 
 * @package    {$namespace}
 * @author     OxygenFramework
 * @copyright  2024 - OxygenFramework
 */
class {$className} extends Controller
{
    /**
     * Display a listing of the resource
     * 
     * @return mixed
     */
    public function index()
    {
        // \$items = {$className}::paginate(15);
        return \$this->view('index.twig', [
            'title' => '{$className}',
            // 'items' => \$items
        ]);
    }

    /**
     * Show the form for creating a new resource
     * 
     * @return mixed
     */
    public function create()
    {
        return \$this->view('create.twig');
    }

    /**
     * Store a newly created resource
     * 
     * @param Request \$request
     * @return mixed
     */
    public function store()
    {
        \$request = \$this->app->make(Request::class);
        \$data = \$request->all();

        // Handle file uploads (example)
        // \$path = Storage::upload('image', 'uploads');
        // if (\$path) {
        //     \$data['image'] = \$path;
        // }

        // Validate data
        \$validator = Validator::make(\$request->clean(), [
            // 'title' => 'required|string|max:255',
        ]);

        if (\$validator->fails()) {
            Flash::error('Validation failed!');
            \$_SESSION['errors'] = \$validator->errors();
            \$_SESSION['old'] = \$request->all();
            Response::redirect('/back');
            return;
        }

        // Add timestamps
        \$data['created_at'] = date('Y-m-d H:i:s');
        \$data['updated_at'] = date('Y-m-d H:i:s');

        // Create record
        // {$className}::create(\$data);

        Flash::success('Created successfully!');
        Response::redirect('/index');
    }

    /**
     * Display the specified resource
     * 
     * @param int \$id Resource ID
     * @return mixed
     */
    public function show(\$id)
    {
        // \$item = {$className}::find(\$id);
        return \$this->view('show.twig', [
            'id' => \$id,
            // 'item' => \$item
        ]);
    }

    /**
     * Show the form for editing the specified resource
     * 
     * @param int \$id Resource ID
     * @return mixed
     */
    public function edit(\$id)
    {
        // \$item = {$className}::find(\$id);
        return \$this->view('edit.twig', [
            'id' => \$id,
            // 'item' => \$item
        ]);
    }

    /**
     * Update the specified resource
     * 
     * @param int \$id Resource ID
     * @return mixed
     */
    public function update(\$id)
    {
        \$request = \$this->app->make(Request::class);
        \$data = \$request->all();

        // Handle file uploads (example)
        // \$path = Storage::upload('image', 'uploads');
        // if (\$path) {
        //     // Delete old file
        //     // \$old = {$className}::find(\$id);
        //     // if (\$old->image) Storage::delete(\$old->image);
        //     \$data['image'] = \$path;
        // }

        // Validate data
        \$validator = Validator::make(\$request->clean(), [
            // 'title' => 'required|string|max:255',
        ]);

        if (\$validator->fails()) {
            Flash::error('Validation failed!');
            \$_SESSION['errors'] = \$validator->errors();
            \$_SESSION['old'] = \$request->all();
            Response::redirect('/back');
            return;
        }

        // Add updated_at timestamp
        \$data['updated_at'] = date('Y-m-d H:i:s');

        // Update record
        // {$className}::update(\$id, \$data);

        Flash::success('Updated successfully!');
        Response::redirect('/index');
    }

    /**
     * Remove the specified resource
     * 
     * @param int \$id Resource ID
     * @return mixed
     */
    public function destroy(\$id)
    {
        // Delete associated files
        // \$item = {$className}::find(\$id);
        // if (\$item->image) Storage::delete(\$item->image);

        // {$className}::delete(\$id);

        Flash::success('Deleted successfully!');
        Response::redirect('/index');
    }
}

PHP;
    }
}
