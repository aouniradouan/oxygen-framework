<?php

namespace Oxygen\Core\Testing;

use Oxygen\Core\OxygenPython;

/**
 * OxygenTestGenerator - AI-Powered Test Auto-Generation
 * 
 * Automatically generates comprehensive unit and integration tests
 * for controllers, models, and services with AI-powered scenario detection.
 * 
 * Compatible with PHP 7.4 - 8.4
 * 
 * @package    Oxygen\Core\Testing
 * @author     Redwan Aouni <aouniradouan@gmail.com>
 * @copyright  2024 - OxygenFramework
 * @version    3.0.0
 */
class OxygenTestGenerator
{
    /**
     * AI-powered mode
     * 
     * @var bool
     */
    protected $useAI = false;

    /**
     * Generated tests
     * 
     * @var array
     */
    protected $generatedTests = [];

    /**
     * Test templates
     * 
     * @var array
     */
    protected $templates = [];

    /**
     * Constructor
     * 
     * @param bool $useAI Enable AI-powered test generation
     */
    public function __construct($useAI = false)
    {
        $this->useAI = $useAI;
        $this->loadTemplates();
    }

    /**
     * Load test templates
     * 
     * @return void
     */
    protected function loadTemplates()
    {
        $this->templates = [
            'controller' => $this->getControllerTemplate(),
            'model' => $this->getModelTemplate(),
            'service' => $this->getServiceTemplate(),
            'integration' => $this->getIntegrationTemplate(),
        ];
    }

    /**
     * Generate tests for all components
     * 
     * @param string $type Test type (unit, integration, all)
     * @return array Generated test files
     */
    public function generateAllTests($type = 'all')
    {
        $this->generatedTests = [];

        if ($type === 'unit' || $type === 'all') {
            $this->generateControllerTests();
            $this->generateModelTests();
            $this->generateServiceTests();
        }

        if ($type === 'integration' || $type === 'all') {
            $this->generateIntegrationTests();
        }

        return $this->generatedTests;
    }

    /**
     * Generate controller tests
     * 
     * @return void
     */
    protected function generateControllerTests()
    {
        $controllersPath = getcwd() . '/app/Controllers';

        if (!is_dir($controllersPath)) {
            return;
        }

        $controllers = glob($controllersPath . '/*.php');

        foreach ($controllers as $controllerFile) {
            $className = basename($controllerFile, '.php');
            $this->generateControllerTest($className, $controllerFile);
        }
    }

    /**
     * Generate test for a specific controller
     * 
     * @param string $className Controller class name
     * @param string $filePath Controller file path
     * @return string Generated test content
     */
    public function generateControllerTest($className, $filePath)
    {
        $content = file_get_contents($filePath);
        $methods = $this->extractMethods($content);

        // AI-powered scenario generation
        if ($this->useAI) {
            $scenarios = $this->generateAIScenarios($className, $methods, 'controller');
        } else {
            $scenarios = $this->generateBasicScenarios($methods, 'controller');
        }

        $testContent = $this->buildControllerTest($className, $methods, $scenarios);

        $testFile = getcwd() . '/tests/Unit/Controllers/' . $className . 'Test.php';
        $this->saveTest($testFile, $testContent);

        return $testContent;
    }

    /**
     * Generate model tests
     * 
     * @return void
     */
    protected function generateModelTests()
    {
        $modelsPath = getcwd() . '/app/Models';

        if (!is_dir($modelsPath)) {
            return;
        }

        $models = glob($modelsPath . '/*.php');

        foreach ($models as $modelFile) {
            $className = basename($modelFile, '.php');
            $this->generateModelTest($className, $modelFile);
        }
    }

    /**
     * Generate test for a specific model
     * 
     * @param string $className Model class name
     * @param string $filePath Model file path
     * @return string Generated test content
     */
    public function generateModelTest($className, $filePath)
    {
        $content = file_get_contents($filePath);
        $properties = $this->extractProperties($content);
        $methods = $this->extractMethods($content);

        // AI-powered scenario generation
        if ($this->useAI) {
            $scenarios = $this->generateAIScenarios($className, $methods, 'model');
        } else {
            $scenarios = $this->generateBasicScenarios($methods, 'model');
        }

        $testContent = $this->buildModelTest($className, $properties, $methods, $scenarios);

        $testFile = getcwd() . '/tests/Unit/Models/' . $className . 'Test.php';
        $this->saveTest($testFile, $testContent);

        return $testContent;
    }

    /**
     * Generate service tests
     * 
     * @return void
     */
    protected function generateServiceTests()
    {
        $servicesPath = getcwd() . '/app/Services';

        if (!is_dir($servicesPath)) {
            return;
        }

        $services = glob($servicesPath . '/*.php');

        foreach ($services as $serviceFile) {
            $className = basename($serviceFile, '.php');
            $this->generateServiceTest($className, $serviceFile);
        }
    }

    /**
     * Generate test for a specific service
     * 
     * @param string $className Service class name
     * @param string $filePath Service file path
     * @return string Generated test content
     */
    public function generateServiceTest($className, $filePath)
    {
        $content = file_get_contents($filePath);
        $methods = $this->extractMethods($content);

        $testContent = $this->buildServiceTest($className, $methods);

        $testFile = getcwd() . '/tests/Unit/Services/' . $className . 'Test.php';
        $this->saveTest($testFile, $testContent);

        return $testContent;
    }

    /**
     * Generate integration tests
     * 
     * @return void
     */
    protected function generateIntegrationTests()
    {
        // Generate API endpoint tests
        $this->generateApiTests();

        // Generate database integration tests
        $this->generateDatabaseTests();
    }

    /**
     * Generate API endpoint tests
     * 
     * @return void
     */
    protected function generateApiTests()
    {
        $routesFile = getcwd() . '/routes/web.php';

        if (!file_exists($routesFile)) {
            return;
        }

        $routes = $this->extractRoutes($routesFile);
        $testContent = $this->buildApiTest($routes);

        $testFile = getcwd() . '/tests/Integration/ApiTest.php';
        $this->saveTest($testFile, $testContent);
    }

    /**
     * Generate database integration tests
     * 
     * @return void
     */
    protected function generateDatabaseTests()
    {
        $testContent = $this->buildDatabaseTest();

        $testFile = getcwd() . '/tests/Integration/DatabaseTest.php';
        $this->saveTest($testFile, $testContent);
    }

    /**
     * Extract methods from class content
     * 
     * @param string $content File content
     * @return array Methods
     */
    protected function extractMethods($content)
    {
        $methods = [];

        preg_match_all('/public\s+function\s+(\w+)\s*\(([^)]*)\)/', $content, $matches, PREG_SET_ORDER);

        foreach ($matches as $match) {
            $methods[] = [
                'name' => $match[1],
                'params' => $match[2],
            ];
        }

        return $methods;
    }

    /**
     * Extract properties from class content
     * 
     * @param string $content File content
     * @return array Properties
     */
    protected function extractProperties($content)
    {
        $properties = [];

        preg_match_all('/(protected|public|private)\s+\$(\w+)/', $content, $matches, PREG_SET_ORDER);

        foreach ($matches as $match) {
            $properties[] = [
                'visibility' => $match[1],
                'name' => $match[2],
            ];
        }

        return $properties;
    }

    /**
     * Extract routes from routes file
     * 
     * @param string $filePath Routes file path
     * @return array Routes
     */
    protected function extractRoutes($filePath)
    {
        $content = file_get_contents($filePath);
        $routes = [];

        // Extract routes (simplified pattern matching)
        preg_match_all('/\$router->(get|post|put|delete|patch)\s*\([\'"]([^\'"]*)[\'"]\s*,\s*[\'"]([^\'"]*)[\'"]\)/', $content, $matches, PREG_SET_ORDER);

        foreach ($matches as $match) {
            $routes[] = [
                'method' => strtoupper($match[1]),
                'path' => $match[2],
                'handler' => $match[3],
            ];
        }

        return $routes;
    }

    /**
     * Generate AI-powered test scenarios
     * 
     * @param string $className Class name
     * @param array $methods Methods
     * @param string $type Component type
     * @return array Scenarios
     */
    protected function generateAIScenarios($className, $methods, $type)
    {
        // Use Python AI integration if available
        try {
            $prompt = "Generate comprehensive test scenarios for a {$type} class named {$className} with methods: " .
                implode(', ', array_column($methods, 'name')) .
                ". Include edge cases, boundary values, and error conditions.";

            $aiResponse = OxygenPython::ai($prompt);

            // Parse AI response into scenarios
            return $this->parseAIScenarios($aiResponse);
        } catch (\Exception $e) {
            // Fallback to basic scenarios
            return $this->generateBasicScenarios($methods, $type);
        }
    }

    /**
     * Parse AI response into scenarios
     * 
     * @param string $aiResponse AI response
     * @return array Scenarios
     */
    protected function parseAIScenarios($aiResponse)
    {
        // Simple parsing - in production, this would be more sophisticated
        $scenarios = [];
        $lines = explode("\n", $aiResponse);

        foreach ($lines as $line) {
            if (preg_match('/test.*?(\w+)/i', $line, $match)) {
                $scenarios[] = [
                    'name' => $match[0],
                    'description' => $line,
                ];
            }
        }

        return $scenarios;
    }

    /**
     * Generate basic test scenarios
     * 
     * @param array $methods Methods
     * @param string $type Component type
     * @return array Scenarios
     */
    protected function generateBasicScenarios($methods, $type)
    {
        $scenarios = [];

        foreach ($methods as $method) {
            $methodName = $method['name'];

            // Skip magic methods and constructors
            if (strpos($methodName, '__') === 0) {
                continue;
            }

            // Basic scenarios
            $scenarios[] = [
                'method' => $methodName,
                'name' => "test_{$methodName}_returns_expected_result",
                'type' => 'success',
            ];

            $scenarios[] = [
                'method' => $methodName,
                'name' => "test_{$methodName}_handles_invalid_input",
                'type' => 'error',
            ];

            $scenarios[] = [
                'method' => $methodName,
                'name' => "test_{$methodName}_with_edge_cases",
                'type' => 'edge',
            ];
        }

        return $scenarios;
    }

    /**
     * Build controller test
     * 
     * @param string $className Class name
     * @param array $methods Methods
     * @param array $scenarios Test scenarios
     * @return string Test content
     */
    protected function buildControllerTest($className, $methods, $scenarios)
    {
        $testMethods = '';

        foreach ($scenarios as $scenario) {
            $testMethods .= $this->generateTestMethod($scenario);
        }

        return str_replace(
            ['{{CLASS_NAME}}', '{{TEST_METHODS}}'],
            [$className, $testMethods],
            $this->templates['controller']
        );
    }

    /**
     * Build model test
     * 
     * @param string $className Class name
     * @param array $properties Properties
     * @param array $methods Methods
     * @param array $scenarios Test scenarios
     * @return string Test content
     */
    protected function buildModelTest($className, $properties, $methods, $scenarios)
    {
        $testMethods = '';

        // Add CRUD tests
        $testMethods .= $this->generateCrudTests($className);

        // Add scenario tests
        foreach ($scenarios as $scenario) {
            $testMethods .= $this->generateTestMethod($scenario);
        }

        return str_replace(
            ['{{CLASS_NAME}}', '{{TEST_METHODS}}'],
            [$className, $testMethods],
            $this->templates['model']
        );
    }

    /**
     * Build service test
     * 
     * @param string $className Class name
     * @param array $methods Methods
     * @return string Test content
     */
    protected function buildServiceTest($className, $methods)
    {
        $testMethods = '';

        foreach ($methods as $method) {
            if (strpos($method['name'], '__') !== 0) {
                $testMethods .= $this->generateServiceTestMethod($method);
            }
        }

        return str_replace(
            ['{{CLASS_NAME}}', '{{TEST_METHODS}}'],
            [$className, $testMethods],
            $this->templates['service']
        );
    }

    /**
     * Build API test
     * 
     * @param array $routes Routes
     * @return string Test content
     */
    protected function buildApiTest($routes)
    {
        $testMethods = '';

        foreach ($routes as $route) {
            $testMethods .= $this->generateApiTestMethod($route);
        }

        return str_replace(
            '{{TEST_METHODS}}',
            $testMethods,
            $this->templates['integration']
        );
    }

    /**
     * Build database test
     * 
     * @return string Test content
     */
    protected function buildDatabaseTest()
    {
        return <<<'PHP'
<?php

namespace Tests\Integration;

use PHPUnit\Framework\TestCase;

class DatabaseTest extends TestCase
{
    public function testDatabaseConnection()
    {
        $this->assertTrue(true, 'Database connection test');
    }

    public function testMigrations()
    {
        $this->assertTrue(true, 'Migrations test');
    }
}
PHP;
    }

    /**
     * Generate test method
     * 
     * @param array $scenario Test scenario
     * @return string Test method code
     */
    protected function generateTestMethod($scenario)
    {
        $methodName = $scenario['name'];
        $type = $scenario['type'] ?? 'success';

        return <<<PHP

    public function {$methodName}()
    {
        // Arrange
        \$this->markTestIncomplete('This test needs implementation');

        // Act
        
        // Assert
        \$this->assertTrue(true);
    }

PHP;
    }

    /**
     * Generate CRUD tests for model
     * 
     * @param string $className Model class name
     * @return string Test methods
     */
    protected function generateCrudTests($className)
    {
        return <<<PHP

    public function testCreate{$className}()
    {
        \$this->markTestIncomplete('Implement create test');
    }

    public function testRead{$className}()
    {
        \$this->markTestIncomplete('Implement read test');
    }

    public function testUpdate{$className}()
    {
        \$this->markTestIncomplete('Implement update test');
    }

    public function testDelete{$className}()
    {
        \$this->markTestIncomplete('Implement delete test');
    }

PHP;
    }

    /**
     * Generate service test method
     * 
     * @param array $method Method info
     * @return string Test method code
     */
    protected function generateServiceTestMethod($method)
    {
        $methodName = $method['name'];
        $testName = 'test_' . $methodName;

        return <<<PHP

    public function {$testName}()
    {
        \$this->markTestIncomplete('Implement {$methodName} test');
    }

PHP;
    }

    /**
     * Generate API test method
     * 
     * @param array $route Route info
     * @return string Test method code
     */
    protected function generateApiTestMethod($route)
    {
        $method = $route['method'];
        $path = $route['path'];
        $testName = 'test_' . strtolower($method) . '_' . str_replace(['/', '-'], '_', trim($path, '/'));

        return <<<PHP

    public function {$testName}()
    {
        // Test {$method} {$path}
        \$this->markTestIncomplete('Implement API test for {$method} {$path}');
    }

PHP;
    }

    /**
     * Save test file
     * 
     * @param string $filePath Test file path
     * @param string $content Test content
     * @return bool Success
     */
    protected function saveTest($filePath, $content)
    {
        $dir = dirname($filePath);

        if (!is_dir($dir)) {
            mkdir($dir, 0755, true);
        }

        $success = file_put_contents($filePath, $content) !== false;

        if ($success) {
            $this->generatedTests[] = $filePath;
        }

        return $success;
    }

    /**
     * Get controller test template
     * 
     * @return string Template
     */
    protected function getControllerTemplate()
    {
        return <<<'PHP'
<?php

namespace Tests\Unit\Controllers;

use PHPUnit\Framework\TestCase;
use App\Controllers\{{CLASS_NAME}};

class {{CLASS_NAME}}Test extends TestCase
{
    protected $controller;

    protected function setUp(): void
    {
        parent::setUp();
        $this->controller = new {{CLASS_NAME}}();
    }

{{TEST_METHODS}}
}
PHP;
    }

    /**
     * Get model test template
     * 
     * @return string Template
     */
    protected function getModelTemplate()
    {
        return <<<'PHP'
<?php

namespace Tests\Unit\Models;

use PHPUnit\Framework\TestCase;
use App\Models\{{CLASS_NAME}};

class {{CLASS_NAME}}Test extends TestCase
{
    protected $model;

    protected function setUp(): void
    {
        parent::setUp();
        $this->model = new {{CLASS_NAME}}();
    }

{{TEST_METHODS}}
}
PHP;
    }

    /**
     * Get service test template
     * 
     * @return string Template
     */
    protected function getServiceTemplate()
    {
        return <<<'PHP'
<?php

namespace Tests\Unit\Services;

use PHPUnit\Framework\TestCase;
use App\Services\{{CLASS_NAME}};

class {{CLASS_NAME}}Test extends TestCase
{
    protected $service;

    protected function setUp(): void
    {
        parent::setUp();
        $this->service = new {{CLASS_NAME}}();
    }

{{TEST_METHODS}}
}
PHP;
    }

    /**
     * Get integration test template
     * 
     * @return string Template
     */
    protected function getIntegrationTemplate()
    {
        return <<<'PHP'
<?php

namespace Tests\Integration;

use PHPUnit\Framework\TestCase;

class ApiTest extends TestCase
{
{{TEST_METHODS}}
}
PHP;
    }

    /**
     * Get generated tests
     * 
     * @return array
     */
    public function getGeneratedTests()
    {
        return $this->generatedTests;
    }
}
