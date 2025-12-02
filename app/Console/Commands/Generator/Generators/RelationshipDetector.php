<?php

namespace Oxygen\Console\Commands\Generator\Generators;

/**
 * RelationshipDetector - Smart Relationship Detection
 * 
 * Automatically detects relationships between resources based on
 * field names, patterns, and conventions.
 * 
 * @package    Oxygen\Console\Commands\Generator\Generators
 * @author     Redwan Aouni
 * @version    1.0.0
 */
class RelationshipDetector
{
    /**
     * Detect all relationships between resources
     */
    public function detect(array $resources)
    {
        $relationships = [];

        // Detect foreign key relationships
        $relationships = array_merge($relationships, $this->detectForeignKeyRelationships($resources));

        // Detect pivot table relationships
        $relationships = array_merge($relationships, $this->detectPivotTableRelationships($resources));

        // Detect polymorphic relationships
        $relationships = array_merge($relationships, $this->detectPolymorphicRelationships($resources));

        return $this->deduplicateRelationships($relationships);
    }

    /**
     * Detect foreign key based relationships (belongsTo/hasMany/hasOne)
     */
    protected function detectForeignKeyRelationships(array $resources)
    {
        $relationships = [];

        foreach ($resources as $resource) {
            foreach ($resource['fields'] as $field) {
                if ($this->isForeignKey($field['name'])) {
                    $relatedModel = $this->getRelatedModelFromForeignKey($field['name']);

                    // Check if related model exists
                    if ($this->resourceExists($relatedModel, $resources)) {
                        // BelongsTo relationship
                        $relationships[] = [
                            'from' => $resource['name'],
                            'to' => $relatedModel,
                            'type' => 'belongsTo',
                            'foreignKey' => $field['name'],
                            'method' => $this->getMethodName($relatedModel, 'belongsTo'),
                            'description' => "{$resource['name']} belongs to {$relatedModel}"
                        ];

                        // Determine if hasOne or hasMany
                        $inverseType = $this->determineInverseType($resource['name'], $relatedModel, $field['name']);

                        $relationships[] = [
                            'from' => $relatedModel,
                            'to' => $resource['name'],
                            'type' => $inverseType,
                            'foreignKey' => $field['name'],
                            'method' => $this->getMethodName($resource['name'], $inverseType),
                            'description' => "{$relatedModel} {$inverseType} {$resource['name']}"
                        ];
                    }
                }
            }
        }

        return $relationships;
    }

    /**
     * Detect pivot table relationships (belongsToMany)
     */
    protected function detectPivotTableRelationships(array $resources)
    {
        $relationships = [];

        foreach ($resources as $resource) {
            $resourceName = strtolower($resource['name']);

            // Check if this looks like a pivot table
            if ($this->isPivotTable($resourceName, $resource['fields'])) {
                $models = $this->extractModelsFromPivotTable($resourceName, $resource['fields']);

                if (count($models) === 2) {
                    [$model1, $model2] = $models;

                    // Check if both models exist
                    if ($this->resourceExists($model1, $resources) && $this->resourceExists($model2, $resources)) {
                        // First model belongsToMany second model
                        $relationships[] = [
                            'from' => $model1,
                            'to' => $model2,
                            'type' => 'belongsToMany',
                            'pivotTable' => $resourceName,
                            'foreignPivotKey' => strtolower($model1) . '_id',
                            'relatedPivotKey' => strtolower($model2) . '_id',
                            'method' => $this->getMethodName($model2, 'belongsToMany'),
                            'description' => "{$model1} belongs to many {$model2}"
                        ];

                        // Second model belongsToMany first model
                        $relationships[] = [
                            'from' => $model2,
                            'to' => $model1,
                            'type' => 'belongsToMany',
                            'pivotTable' => $resourceName,
                            'foreignPivotKey' => strtolower($model2) . '_id',
                            'relatedPivotKey' => strtolower($model1) . '_id',
                            'method' => $this->getMethodName($model1, 'belongsToMany'),
                            'description' => "{$model2} belongs to many {$model1}"
                        ];
                    }
                }
            }
        }

        return $relationships;
    }

    /**
     * Detect polymorphic relationships
     */
    protected function detectPolymorphicRelationships(array $resources)
    {
        $relationships = [];

        foreach ($resources as $resource) {
            $polymorphicFields = $this->findPolymorphicFields($resource['fields']);

            foreach ($polymorphicFields as $prefix) {
                $relationships[] = [
                    'from' => $resource['name'],
                    'to' => null, // Polymorphic
                    'type' => 'morphTo',
                    'morphName' => $prefix,
                    'method' => $prefix,
                    'description' => "{$resource['name']} morphs to {$prefix}"
                ];
            }
        }

        return $relationships;
    }

    /**
     * Check if field name is a foreign key
     */
    protected function isForeignKey($fieldName)
    {
        return substr($fieldName, -3) === '_id' && $fieldName !== 'id';
    }

    /**
     * Get related model name from foreign key
     */
    protected function getRelatedModelFromForeignKey($foreignKey)
    {
        $name = substr($foreignKey, 0, -3); // Remove '_id'
        return ucfirst($name);
    }

    /**
     * Check if resource exists in the list
     */
    protected function resourceExists($modelName, array $resources)
    {
        foreach ($resources as $resource) {
            if (strtolower($resource['name']) === strtolower($modelName)) {
                return true;
            }
        }
        return false;
    }

    /**
     * Determine if inverse relationship is hasOne or hasMany
     */
    protected function determineInverseType($fromModel, $toModel, $foreignKey)
    {
        // Common patterns for hasOne
        $hasOnePatterns = ['profile', 'setting', 'detail', 'info', 'account'];

        $fromLower = strtolower($fromModel);
        foreach ($hasOnePatterns as $pattern) {
            if (strpos($fromLower, $pattern) !== false) {
                return 'hasOne';
            }
        }

        // Default to hasMany
        return 'hasMany';
    }

    /**
     * Check if table looks like a pivot table
     */
    protected function isPivotTable($tableName, array $fields)
    {
        // Pivot tables typically have exactly 2 foreign keys
        $foreignKeyCount = 0;
        foreach ($fields as $field) {
            if ($this->isForeignKey($field['name'])) {
                $foreignKeyCount++;
            }
        }

        // Must have exactly 2 foreign keys and name contains underscore
        return $foreignKeyCount === 2 && strpos($tableName, '_') !== false;
    }

    /**
     * Extract model names from pivot table
     */
    protected function extractModelsFromPivotTable($tableName, array $fields)
    {
        $models = [];

        foreach ($fields as $field) {
            if ($this->isForeignKey($field['name'])) {
                $models[] = $this->getRelatedModelFromForeignKey($field['name']);
            }
        }

        return $models;
    }

    /**
     * Find polymorphic field groups
     */
    protected function findPolymorphicFields(array $fields)
    {
        $polymorphic = [];
        $fieldNames = array_column($fields, 'name');

        // Look for *_type and *_id pairs
        foreach ($fieldNames as $fieldName) {
            if (substr($fieldName, -5) === '_type') {
                $prefix = substr($fieldName, 0, -5);
                $idField = $prefix . '_id';

                if (in_array($idField, $fieldNames)) {
                    $polymorphic[] = $prefix;
                }
            }
        }

        return $polymorphic;
    }

    /**
     * Get method name for relationship
     */
    protected function getMethodName($modelName, $type)
    {
        $name = strtolower($modelName);

        if ($type === 'hasMany' || $type === 'belongsToMany') {
            // Pluralize
            return $this->pluralize($name);
        }

        return $name;
    }

    /**
     * Simple pluralization
     */
    protected function pluralize($word)
    {
        // Simple rules
        if (substr($word, -1) === 'y') {
            return substr($word, 0, -1) . 'ies';
        }

        if (substr($word, -1) === 's') {
            return $word . 'es';
        }

        return $word . 's';
    }

    /**
     * Remove duplicate relationships
     */
    protected function deduplicateRelationships(array $relationships)
    {
        $unique = [];
        $keys = [];

        foreach ($relationships as $rel) {
            $key = $rel['from'] . '-' . $rel['to'] . '-' . $rel['type'] . '-' . ($rel['method'] ?? '');

            if (!in_array($key, $keys)) {
                $keys[] = $key;
                $unique[] = $rel;
            }
        }

        return $unique;
    }

    /**
     * Group relationships by resource
     */
    public function groupByResource(array $relationships)
    {
        $grouped = [];

        foreach ($relationships as $rel) {
            $from = $rel['from'];

            if (!isset($grouped[$from])) {
                $grouped[$from] = [];
            }

            $grouped[$from][] = $rel;
        }

        return $grouped;
    }

    /**
     * Generate relationship code for model
     */
    public function generateRelationshipMethod(array $relationship)
    {
        $type = $relationship['type'];
        $method = $relationship['method'];
        $to = $relationship['to'];

        $code = "    public function {$method}()\n";
        $code .= "    {\n";

        switch ($type) {
            case 'belongsTo':
                $foreignKey = $relationship['foreignKey'] ?? null;
                if ($foreignKey) {
                    $code .= "        return \$this->belongsTo({$to}::class, '{$foreignKey}');\n";
                } else {
                    $code .= "        return \$this->belongsTo({$to}::class);\n";
                }
                break;

            case 'hasOne':
                $foreignKey = $relationship['foreignKey'] ?? null;
                if ($foreignKey) {
                    $code .= "        return \$this->hasOne({$to}::class, '{$foreignKey}');\n";
                } else {
                    $code .= "        return \$this->hasOne({$to}::class);\n";
                }
                break;

            case 'hasMany':
                $foreignKey = $relationship['foreignKey'] ?? null;
                if ($foreignKey) {
                    $code .= "        return \$this->hasMany({$to}::class, '{$foreignKey}');\n";
                } else {
                    $code .= "        return \$this->hasMany({$to}::class);\n";
                }
                break;

            case 'belongsToMany':
                $pivotTable = $relationship['pivotTable'] ?? null;
                if ($pivotTable) {
                    $code .= "        return \$this->belongsToMany({$to}::class, '{$pivotTable}');\n";
                } else {
                    $code .= "        return \$this->belongsToMany({$to}::class);\n";
                }
                break;

            case 'morphTo':
                $morphName = $relationship['morphName'];
                $code .= "        return \$this->morphTo('{$morphName}');\n";
                break;
        }

        $code .= "    }\n";

        return $code;
    }
}
