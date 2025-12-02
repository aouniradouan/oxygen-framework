<?php

namespace Oxygen\Console\Commands;

use Oxygen\Console\Command;
use Oxygen\Console\Commands\Generator\Generators\RelationshipDetector;
use Oxygen\Console\Commands\Generator\Questions\QuestionEngine;

/**
 * GenerateAppCommand - Intelligent Application Generator
 * 
 * The most powerful code generator that creates complete applications
 * with multiple resources, relationships, auth, APIs, and more.
 * 
 * @package    Oxygen\Console\Commands
 * @author     Redwan Aouni
 * @version    1.0.0
 */
class GenerateAppCommand extends Command
{
    protected $signature = 'generate:app';
    protected $description = 'Generate a complete application with intelligent scaffolding';

    /**
     * Question engine
     */
    protected $questions;

    /**
     * Relationship detector
     */
    protected $relationshipDetector;

    /**
     * Application configuration
     */
    protected $config = [];

    /**
     * Resources to generate
     */
    protected $resources = [];

    /**
     * Detected relationships
     */
    protected $relationships = [];

    /**
     * Execute the command
     */
    public function execute($arguments)
    {
        $this->questions = new QuestionEngine($this);
        $this->relationshipDetector = new RelationshipDetector();

        $this->displayWelcome();

        // Step 1: Choose application type or template
        $this->chooseApplicationType();

        // Step 2: Define resources
        $this->defineResources();

        // Step 3: Detect and confirm relationships
        $this->detectRelationships();

        // Step 4: Configure features
        $this->configureFeatures();

        // Step 5: Show summary and confirm
        $this->showSummary();

        if (!$this->questions->confirm('Proceed with generation?', true)) {
            $this->questions->warning('Generation cancelled');
            return;
        }

        // Step 6: Generate everything
        $this->generateApplication();

        $this->displayCompletion();
    }

    /**
     * Display welcome message
     */
    protected function displayWelcome()
    {
        $this->line('');
        $this->info('╔════════════════════════════════════════════════════════╗');
        $this->info('║     🚀 Oxygen Intelligent Application Generator 🚀    ║');
        $this->info('╚════════════════════════════════════════════════════════╝');
        $this->line('');
        $this->line('  Build complete applications in minutes!');
        $this->line('  - Multiple resources with relationships');
        $this->line('  - Authentication & Authorization');
        $this->line('  - REST APIs with JWT');
        $this->line('  - Admin panels');
        $this->line('  - File uploads, search, pagination');
        $this->line('');
    }

    /**
     * Choose application type
     */
    protected function chooseApplicationType()
    {
        $this->questions->header('Application Type');

        $types = [
            'Blog',
            'E-commerce',
            'CRM',
            'Custom Application'
        ];

        $type = $this->questions->choice('What would you like to build?', $types);
        $this->config['type'] = $type;

        if ($type !== 'Custom Application') {
            $this->questions->success("Selected: {$type}");
            $this->loadTemplate($type);

            if ($this->questions->confirm('Customize this template?', false)) {
                // Allow customization
                $this->config['customize'] = true;
            }
        }
    }

    /**
     * Load application template
     */
    protected function loadTemplate($type)
    {
        $templateClass = "Oxygen\\Console\\Commands\\Generator\\Templates\\{$type}Template";

        if (class_exists($templateClass)) {
            $template = new $templateClass();
            $this->resources = $template->getResources();
            $this->config['features'] = $template->getFeatures();
        }

        if (!empty($this->resources)) {
            $this->questions->info('');
            $this->questions->info('Template loaded with:');
            foreach ($this->resources as $resource) {
                $fieldCount = count($resource['fields']);
                $this->questions->line("  - {$resource['name']} ({$fieldCount} fields)");
            }
        }
    }

    /**
     * Define resources interactively
     */
    protected function defineResources()
    {
        if (!empty($this->resources) && !($this->config['customize'] ?? false)) {
            return; // Using template without customization
        }

        $this->questions->header('Define Resources');

        if (empty($this->resources)) {
            $this->questions->info('Let\'s define the resources (tables) for your application.');
        }

        $addMore = true;
        while ($addMore) {
            $resource = $this->defineResource();
            if ($resource) {
                $this->resources[] = $resource;
                $this->questions->success("Added resource: {$resource['name']}");
            }

            $addMore = $this->questions->confirm('Add another resource?', true);
        }
    }

    /**
     * Define a single resource
     */
    protected function defineResource()
    {
        $name = $this->questions->ask('Resource name (e.g., Post, Product)');

        if (empty($name)) {
            return null;
        }

        $name = ucfirst($name);

        // Suggest fields
        $suggestedFields = $this->questions->suggestFields($name);

        $this->questions->info("Suggested fields for {$name}:");
        $this->questions->listing($suggestedFields);

        $usesuggested = $this->questions->confirm('Use suggested fields?', true);

        $fields = [];

        if ($usesuggested) {
            foreach ($suggestedFields as $fieldName) {
                $type = $this->questions->detectFieldType($fieldName);
                $fields[] = [
                    'name' => $fieldName,
                    'type' => $type
                ];
            }
        }

        // Add custom fields
        if ($this->questions->confirm('Add custom fields?', !$usesuggested)) {
            $addField = true;
            while ($addField) {
                $fieldName = $this->questions->ask('Field name');
                if ($fieldName) {
                    $type = $this->questions->detectFieldType($fieldName);
                    $this->questions->info("Detected type: {$type}");

                    $confirmType = $this->questions->confirm("Use type '{$type}'?", true);
                    if (!$confirmType) {
                        $types = ['string', 'text', 'integer', 'decimal', 'boolean', 'timestamp', 'file', 'foreignKey', 'enum'];
                        $type = $this->questions->choice('Select field type', $types);
                    }

                    $fields[] = [
                        'name' => $fieldName,
                        'type' => $type
                    ];
                }

                $addField = $this->questions->confirm('Add another field?', true);
            }
        }

        return [
            'name' => $name,
            'fields' => $fields
        ];
    }

    /**
     * Detect relationships between resources
     */
    protected function detectRelationships()
    {
        $this->questions->header('Relationships');

        $this->relationships = $this->relationshipDetector->detect($this->resources);

        if (empty($this->relationships)) {
            $this->questions->warning('No relationships detected');
            return;
        }

        $this->questions->success('Detected ' . count($this->relationships) . ' relationships:');

        foreach ($this->relationships as $rel) {
            $this->questions->line("  - {$rel['description']}");
        }

        if ($this->questions->confirm('Review and modify relationships?', false)) {
            // Allow user to modify relationships
            $this->modifyRelationships();
        }
    }

    /**
     * Modify relationships interactively
     */
    protected function modifyRelationships()
    {
        // Implementation for modifying relationships
        $this->questions->info('Relationship modification coming soon...');
    }

    /**
     * Configure application features
     */
    protected function configureFeatures()
    {
        if (!empty($this->config['features'])) {
            $this->questions->header('Features');
            $this->questions->info('Template includes these features:');
            foreach ($this->config['features'] as $feature => $enabled) {
                if ($enabled) {
                    $this->questions->line("  ✓ " . ucfirst($feature));
                }
            }

            if (!$this->questions->confirm('Modify features?', false)) {
                return;
            }
        }

        $this->questions->header('Configure Features');

        // Authentication
        $this->config['features']['auth'] = $this->questions->confirm('Include authentication system?', true);

        if ($this->config['features']['auth']) {
            $this->config['features']['roles'] = $this->questions->confirm('Include role-based access control?', false);
        }

        // API
        $this->config['features']['api'] = $this->questions->confirm('Generate REST API?', true);

        if ($this->config['features']['api']) {
            $this->config['features']['api_auth'] = $this->questions->confirm('Include API authentication (JWT)?', true);
        }

        // Admin Panel
        $this->config['features']['admin'] = $this->questions->confirm('Generate admin panel?', true);

        // Other features
        $this->config['features']['search'] = $this->questions->confirm('Include search functionality?', true);
        $this->config['features']['pagination'] = $this->questions->confirm('Include pagination?', true);
        $this->config['features']['tests'] = $this->questions->confirm('Generate tests?', false);
        $this->config['features']['seeders'] = $this->questions->confirm('Generate seeders?', true);
    }

    /**
     * Show generation summary
     */
    protected function showSummary()
    {
        $this->questions->header('Generation Summary');

        $this->line('');
        $this->info('Application Type: ' . ($this->config['type'] ?? 'Custom'));
        $this->line('');

        $this->info('Resources (' . count($this->resources) . '):');
        foreach ($this->resources as $resource) {
            $fieldCount = count($resource['fields']);
            $this->line("  - {$resource['name']} ({$fieldCount} fields)");
        }

        $this->line('');
        $this->info('Relationships (' . count($this->relationships) . '):');
        $grouped = $this->relationshipDetector->groupByResource($this->relationships);
        foreach ($grouped as $resourceName => $rels) {
            $this->line("  {$resourceName}:");
            foreach ($rels as $rel) {
                $this->line("    - {$rel['method']}() → {$rel['type']}");
            }
        }

        $this->line('');
        $this->info('Features:');
        foreach ($this->config['features'] as $feature => $enabled) {
            if ($enabled) {
                $this->line("  ✓ " . ucfirst(str_replace('_', ' ', $feature)));
            }
        }

        $this->line('');
        $this->info('Will Generate:');
        $this->line("  - " . count($this->resources) . " Migrations");
        $this->line("  - " . count($this->resources) . " Models");
        $this->line("  - " . (count($this->resources) * 3) . "+ Controllers");
        $this->line("  - " . (count($this->resources) * 4) . "+ Views");
        $this->line("  - Routes");
        if ($this->config['features']['seeders'] ?? false) {
            $this->line("  - Seeders");
        }
        if ($this->config['features']['tests'] ?? false) {
            $this->line("  - Tests");
        }
        $this->line('');
    }

    /**
     * Generate the complete application
     */
    /**
     * Generate the complete application
     */
    /**
     * Generate the complete application
     */
    protected function generateApplication()
    {
        $this->questions->header('Generating Application');

        $this->line('');
        $this->info('⏳ Starting generation process...');
        $this->line('');

        // 1. Sort resources by dependency
        $sortedResources = $this->sortResourcesByDependency($this->resources);

        // 2. Generate Resources
        $this->info('Step 1: Generating Resources');
        $resourceGenerator = new \Oxygen\Console\Commands\Generator\Generators\ResourceGenerator($this);

        $baseTimestamp = time();

        foreach ($sortedResources as $index => $resource) {
            $this->line("  - Processing {$resource['name']}...");

            // Prepare resource data for generator
            $resourceRels = [];
            foreach ($this->relationships as $rel) {
                if ($rel['from'] === $resource['name']) {
                    $resourceRels[] = $rel;
                }
            }

            // Calculate timestamp (increment by 1 second for each resource)
            $timestamp = date('Y_m_d_His', $baseTimestamp + $index);

            // Call generator
            $resourceGenerator->generate($resource, $resourceRels, $this->config['features'], $timestamp);
        }

        // 3. Generate Authentication
        if ($this->config['features']['auth'] ?? false) {
            $this->line('');
            $this->info('Step 2: Generating Authentication');
            $authGenerator = new \Oxygen\Console\Commands\Generator\Generators\AuthGenerator($this);
            $authGenerator->generate();
        }

        // 4. Generate API
        // if ($this->config['features']['api'] ?? false) {
        //     $this->line('');
        //     $this->info('Step 3: Generating API');
        //     $apiGenerator = new \Oxygen\Console\Commands\Generator\Generators\APIGenerator($this);
        //     $apiGenerator->generate($this->resources);
        // }

        // 5. Generate Admin Panel
        if ($this->config['features']['admin'] ?? false) {
            $this->line('');
            $this->info('Step 4: Generating Admin Panel');
            // Placeholder for AdminGenerator
            $this->line("  - Admin panel generation coming soon...");
        }
    }

    /**
     * Sort resources by dependency (Topological Sort)
     */
    protected function sortResourcesByDependency(array $resources)
    {
        $sorted = [];
        $visited = [];
        $resourceMap = [];

        // Index resources by name
        foreach ($resources as $resource) {
            $resourceMap[$resource['name']] = $resource;
        }

        // Build dependency graph
        $dependencies = [];
        foreach ($resources as $resource) {
            $name = $resource['name'];
            $dependencies[$name] = [];

            foreach ($resource['fields'] as $field) {
                if ($field['type'] === 'foreignKey') {
                    $relatedModel = ucfirst(str_replace('_id', '', $field['name']));
                    // Only add dependency if related model is being generated
                    if (isset($resourceMap[$relatedModel])) {
                        $dependencies[$name][] = $relatedModel;
                    }
                }
            }
        }

        // Topological sort
        while (count($sorted) < count($resources)) {
            $added = false;

            foreach ($resources as $resource) {
                $name = $resource['name'];

                if (in_array($name, $visited)) {
                    continue;
                }

                $canAdd = true;
                foreach ($dependencies[$name] as $dep) {
                    if (!in_array($dep, $visited)) {
                        $canAdd = false;
                        break;
                    }
                }

                if ($canAdd) {
                    $sorted[] = $resource;
                    $visited[] = $name;
                    $added = true;
                }
            }

            if (!$added) {
                // Cycle detected or remaining items have circular dependencies
                // Add remaining items to break cycle
                foreach ($resources as $resource) {
                    if (!in_array($resource['name'], $visited)) {
                        $sorted[] = $resource;
                        $visited[] = $resource['name'];
                    }
                }
                break;
            }
        }

        return $sorted;
    }

    /**
     * Display completion message
     */
    protected function displayCompletion()
    {
        $this->line('');
        $this->info('╔════════════════════════════════════════════════════════╗');
        $this->info('║            🎉 Generation Complete! 🎉                 ║');
        $this->info('╚════════════════════════════════════════════════════════╝');
        $this->line('');
        $this->info('Next steps:');
        $this->line('  1. Run: php oxygen migrate');
        if ($this->config['features']['seeders'] ?? false) {
            $this->line('  2. Run: php oxygen db:seed');
        }
        if ($this->config['features']['admin'] ?? false) {
            $this->line('  3. Visit: http://localhost/admin');
        }
        $this->line('');
        $this->success('Your application is ready! 🚀');
    }
}
