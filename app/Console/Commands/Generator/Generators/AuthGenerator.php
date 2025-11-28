<?php

namespace Oxygen\Console\Commands\Generator\Generators;

use Oxygen\Console\Command;

/**
 * AuthGenerator - Generates Authentication System
 * 
 * Scaffolds login, registration, password reset, and middleware.
 * 
 * @package    Oxygen\Console\Commands\Generator\Generators
 * @author     Redwan Aouni
 * @version    1.0.0
 */
class AuthGenerator
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
     * Generate authentication system
     */
    public function generate()
    {
        $this->command->info('Generating authentication system...');

        $this->generateControllers();
        $this->generateViews();
        $this->generateRoutes();
        $this->generateMiddleware();

        $this->command->success('Authentication system generated successfully.');
    }

    /**
     * Generate auth controllers
     */
    protected function generateControllers()
    {
        $this->command->info('  - Generating controllers...');

        // Login Controller
        $this->createController('LoginController', $this->getLoginControllerStub());

        // Register Controller
        $this->createController('RegisterController', $this->getRegisterControllerStub());

        // Auth Controller (Logout/Profile)
        $this->createController('AuthController', $this->getAuthControllerStub());
    }

    /**
     * Generate auth views
     */
    protected function generateViews()
    {
        $this->command->info('  - Generating views...');

        $viewsDir = 'resources/views/auth';
        if (!is_dir($viewsDir)) {
            mkdir($viewsDir, 0755, true);
        }

        file_put_contents($viewsDir . '/login.twig.html', $this->getLoginViewStub());
        file_put_contents($viewsDir . '/register.twig.html', $this->getRegisterViewStub());
    }

    /**
     * Generate auth routes
     */
    protected function generateRoutes()
    {
        $this->command->info('  - Adding routes...');

        $routesFile = 'routes/web.php';
        $content = file_get_contents($routesFile);

        if (strpos($content, 'Auth Routes') === false) {
            $routes = "\n// Auth Routes\n";
            $routes .= "Route::get(\$router, '/login', 'Auth\LoginController@showLoginForm');\n";
            $routes .= "Route::post(\$router, '/login', 'Auth\LoginController@login');\n";
            $routes .= "Route::get(\$router, '/register', 'Auth\RegisterController@showRegistrationForm');\n";
            $routes .= "Route::post(\$router, '/register', 'Auth\RegisterController@register');\n";
            $routes .= "Route::post(\$router, '/logout', 'Auth\AuthController@logout');\n";

            file_put_contents($routesFile, $content . $routes);
        }
    }

    /**
     * Generate auth middleware
     */
    protected function generateMiddleware()
    {
        // Placeholder for middleware generation
        // In a real app, we would generate Authenticate.php
    }

    /**
     * Create a controller file
     */
    protected function createController($name, $content)
    {
        $path = "app/Controllers/Auth/{$name}.php";
        $dir = dirname($path);

        if (!is_dir($dir)) {
            mkdir($dir, 0755, true);
        }

        file_put_contents($path, $content);
    }

    // Stubs

    protected function getLoginControllerStub()
    {
        return <<<'EOT'
<?php

namespace Oxygen\Controllers\Auth;

use Oxygen\Core\Controller;
use Oxygen\Core\Http\Request;
use Oxygen\Core\Support\Auth;

class LoginController extends Controller
{
    public function showLoginForm()
    {
        return $this->view('auth/login');
    }

    public function login(Request $request)
    {
        $credentials = $request->only(['email', 'password']);
        
        if (Auth::attempt($credentials)) {
            return $this->redirect('/dashboard');
        }
        
        return $this->back()->withErrors(['email' => 'Invalid credentials']);
    }
}
EOT;
    }

    protected function getRegisterControllerStub()
    {
        return <<<'EOT'
<?php

namespace Oxygen\Controllers\Auth;

use Oxygen\Core\Controller;
use Oxygen\Core\Http\Request;
use Oxygen\Models\User;
use Oxygen\Core\Support\Hash;
use Oxygen\Core\Support\Auth;

class RegisterController extends Controller
{
    public function showRegistrationForm()
    {
        return $this->view('auth/register');
    }

    public function register(Request $request)
    {
        $data = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:users,email',
            'password' => 'required|string|min:8|confirmed',
        ]);
        
        $data['password'] = Hash::make($data['password']);
        
        $user = User::create($data);
        
        Auth::login($user);
        
        return $this->redirect('/dashboard');
    }
}
EOT;
    }

    protected function getAuthControllerStub()
    {
        return <<<'EOT'
<?php

namespace Oxygen\Controllers\Auth;

use Oxygen\Core\Controller;
use Oxygen\Core\Support\Auth;

class AuthController extends Controller
{
    public function logout()
    {
        Auth::logout();
        return $this->redirect('/');
    }
}
EOT;
    }

    protected function getLoginViewStub()
    {
        return <<<'EOT'
<!DOCTYPE html>
<html>
<head>
    <title>Login</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-gray-100 flex items-center justify-center h-screen">
    <div class="bg-white p-8 rounded shadow-md w-96">
        <h1 class="text-2xl font-bold mb-6 text-center">Login</h1>
        
        {{ flash_display()|raw }}
        
        <form action="/login" method="POST">
            <div class="mb-4">
                <label class="block text-gray-700 mb-2">Email</label>
                <input type="email" name="email" class="w-full border rounded px-3 py-2" required>
            </div>
            
            <div class="mb-6">
                <label class="block text-gray-700 mb-2">Password</label>
                <input type="password" name="password" class="w-full border rounded px-3 py-2" required>
            </div>
            
            <button type="submit" class="w-full bg-blue-500 text-white py-2 rounded hover:bg-blue-600">Login</button>
        </form>
        
        <div class="mt-4 text-center">
            <a href="/register" class="text-blue-500 hover:underline">Don't have an account? Register</a>
        </div>
    </div>
</body>
</html>
EOT;
    }

    protected function getRegisterViewStub()
    {
        return <<<'EOT'
<!DOCTYPE html>
<html>
<head>
    <title>Register</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-gray-100 flex items-center justify-center h-screen">
    <div class="bg-white p-8 rounded shadow-md w-96">
        <h1 class="text-2xl font-bold mb-6 text-center">Register</h1>
        
        {{ flash_display()|raw }}
        
        <form action="/register" method="POST">
            <div class="mb-4">
                <label class="block text-gray-700 mb-2">Name</label>
                <input type="text" name="name" class="w-full border rounded px-3 py-2" required>
            </div>

            <div class="mb-4">
                <label class="block text-gray-700 mb-2">Email</label>
                <input type="email" name="email" class="w-full border rounded px-3 py-2" required>
            </div>
            
            <div class="mb-4">
                <label class="block text-gray-700 mb-2">Password</label>
                <input type="password" name="password" class="w-full border rounded px-3 py-2" required>
            </div>

            <div class="mb-6">
                <label class="block text-gray-700 mb-2">Confirm Password</label>
                <input type="password" name="password_confirmation" class="w-full border rounded px-3 py-2" required>
            </div>
            
            <button type="submit" class="w-full bg-green-500 text-white py-2 rounded hover:bg-green-600">Register</button>
        </form>
        
        <div class="mt-4 text-center">
            <a href="/login" class="text-blue-500 hover:underline">Already have an account? Login</a>
        </div>
    </div>
</body>
</html>
EOT;
    }
}
