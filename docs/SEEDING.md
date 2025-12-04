# Database Seeding & Factories

OxygenFramework includes a simple method of seeding your database with test data using seed classes and model factories.

## Table of Contents

- [Writing Seeders](#writing-seeders)
- [Using Factories](#using-factories)
- [Running Seeders](#running-seeders)

---

## Writing Seeders

Seeders are stored in `database/seeders`. By default, a `DatabaseSeeder` class is defined for you. From this class, you may use the `call` method to run other seed classes.

### Generating Seeders

```bash
php oxygen make:seeder UserSeeder
```

### Example Seeder

```php
<?php

namespace Database\Seeders;

use Oxygen\Core\Database\Seeder;
use Oxygen\Models\User;

class UserSeeder extends Seeder
{
    public function run()
    {
        User::create([
            'name' => 'John Doe',
            'email' => 'john@example.com',
            'password' => password_hash('secret', PASSWORD_DEFAULT),
        ]);
    }
}
```

### Calling Additional Seeders

Within the `DatabaseSeeder` class, you may use the `call` method to execute additional seed classes:

```php
public function run()
{
    $this->call(UserSeeder::class);
    $this->call(PostSeeder::class);
}
```

---

## Using Factories

Model factories allow you to define a default set of attributes for each of your models. Factories are stored in `database/factories`.

### Generating Factories

```bash
php oxygen make:factory UserFactory --model=User
```

### Defining A Factory

```php
<?php

namespace Database\Factories;

use Oxygen\Core\Database\Factory;
use Oxygen\Models\User;

class UserFactory extends Factory
{
    protected $model = User::class;

    public function definition()
    {
        return [
            'name' => 'User ' . rand(1, 1000),
            'email' => 'user' . rand(1, 1000) . '@example.com',
            'password' => password_hash('password', PASSWORD_DEFAULT),
        ];
    }
}
```

### Using Factories in Seeders

```php
use Database\Factories\UserFactory;

public function run()
{
    // Create 10 users
    $factory = new UserFactory();
    $factory->count(10)->create();
    
    // Create without saving
    $users = $factory->count(5)->make();
}
```

---

## Running Seeders

You may execute the `db:seed` command to seed your database. By default, the `db:seed` command runs the `Database\Seeders\DatabaseSeeder` class:

```bash
php oxygen db:seed
```

You can also specify a specific seeder class to run using the `--class` option:

```bash
php oxygen db:seed --class=UserSeeder
```
