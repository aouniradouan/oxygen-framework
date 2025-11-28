<?php

namespace Oxygen\Console\Commands\Generator\Templates;

class BlogTemplate implements TemplateInterface
{
    public function getName()
    {
        return 'Blog';
    }

    public function getDescription()
    {
        return 'A complete blog system with posts, comments, categories, and user management.';
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
                    ['name' => 'avatar', 'type' => 'file'],
                    ['name' => 'bio', 'type' => 'text'],
                ]
            ],
            [
                'name' => 'Post',
                'fields' => [
                    ['name' => 'title', 'type' => 'string'],
                    ['name' => 'slug', 'type' => 'string'],
                    ['name' => 'content', 'type' => 'text'],
                    ['name' => 'excerpt', 'type' => 'text'],
                    ['name' => 'featured_image', 'type' => 'file'],
                    ['name' => 'user_id', 'type' => 'foreignKey'],
                    ['name' => 'category_id', 'type' => 'foreignKey'],
                    ['name' => 'published_at', 'type' => 'timestamp'],
                ]
            ],
            [
                'name' => 'Comment',
                'fields' => [
                    ['name' => 'content', 'type' => 'text'],
                    ['name' => 'user_id', 'type' => 'foreignKey'],
                    ['name' => 'post_id', 'type' => 'foreignKey'],
                    ['name' => 'approved', 'type' => 'boolean'],
                ]
            ],
            [
                'name' => 'Category',
                'fields' => [
                    ['name' => 'name', 'type' => 'string'],
                    ['name' => 'slug', 'type' => 'string'],
                    ['name' => 'description', 'type' => 'text'],
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
            'search' => true,
            'pagination' => true,
        ];
    }
}
