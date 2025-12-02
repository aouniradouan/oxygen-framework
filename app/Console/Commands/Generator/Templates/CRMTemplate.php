<?php

namespace Oxygen\Console\Commands\Generator\Templates;

class CRMTemplate implements TemplateInterface
{
    public function getName()
    {
        return 'CRM';
    }

    public function getDescription()
    {
        return 'Customer Relationship Management system with contacts, deals, and tasks.';
    }

    public function getResources()
    {
        return [
            [
                'name' => 'User',
                'fields' => [
                    ['name' => 'name', 'type' => 'string'],
                    ['name' => 'email', 'type' => 'string'],
                    ['name' => 'password', 'type' => 'string'],
                    ['name' => 'role', 'type' => 'enum'],
                ]
            ],
            [
                'name' => 'Contact',
                'fields' => [
                    ['name' => 'name', 'type' => 'string'],
                    ['name' => 'email', 'type' => 'string'],
                    ['name' => 'phone', 'type' => 'string'],
                    ['name' => 'company', 'type' => 'string'],
                    ['name' => 'user_id', 'type' => 'foreignKey'],
                ]
            ],
            [
                'name' => 'Deal',
                'fields' => [
                    ['name' => 'title', 'type' => 'string'],
                    ['name' => 'amount', 'type' => 'decimal'],
                    ['name' => 'status', 'type' => 'enum'],
                    ['name' => 'contact_id', 'type' => 'foreignKey'],
                    ['name' => 'user_id', 'type' => 'foreignKey'],
                ]
            ],
            [
                'name' => 'Task',
                'fields' => [
                    ['name' => 'title', 'type' => 'string'],
                    ['name' => 'description', 'type' => 'text'],
                    ['name' => 'due_date', 'type' => 'timestamp'],
                    ['name' => 'status', 'type' => 'enum'],
                    ['name' => 'user_id', 'type' => 'foreignKey'],
                ]
            ],
        ];
    }

    public function getFeatures()
    {
        return [
            'auth' => true,
            'api' => true,
            'admin' => true,
            'permissions' => true,
        ];
    }
}
