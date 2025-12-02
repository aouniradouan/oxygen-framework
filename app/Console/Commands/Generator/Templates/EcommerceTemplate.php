<?php

namespace Oxygen\Console\Commands\Generator\Templates;

class EcommerceTemplate implements TemplateInterface
{
    public function getName()
    {
        return 'E-commerce';
    }

    public function getDescription()
    {
        return 'A full e-commerce solution with products, orders, cart, and payment integration.';
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
                ]
            ],
            [
                'name' => 'Product',
                'fields' => [
                    ['name' => 'name', 'type' => 'string'],
                    ['name' => 'description', 'type' => 'text'],
                    ['name' => 'price', 'type' => 'decimal'],
                    ['name' => 'stock', 'type' => 'integer'],
                    ['name' => 'sku', 'type' => 'string'],
                    ['name' => 'category_id', 'type' => 'foreignKey'],
                    ['name' => 'images', 'type' => 'file'],
                ]
            ],
            [
                'name' => 'Category',
                'fields' => [
                    ['name' => 'name', 'type' => 'string'],
                    ['name' => 'slug', 'type' => 'string'],
                ]
            ],
            [
                'name' => 'Order',
                'fields' => [
                    ['name' => 'user_id', 'type' => 'foreignKey'],
                    ['name' => 'total', 'type' => 'decimal'],
                    ['name' => 'status', 'type' => 'enum'],
                ]
            ],
            [
                'name' => 'OrderItem',
                'fields' => [
                    ['name' => 'order_id', 'type' => 'foreignKey'],
                    ['name' => 'product_id', 'type' => 'foreignKey'],
                    ['name' => 'quantity', 'type' => 'integer'],
                    ['name' => 'price', 'type' => 'decimal'],
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
            'cart' => true,
            'payment' => true,
        ];
    }
}
