<?php

namespace Oxygen\Core;

use Oxygen\Core\Validation\OxygenValidator;

/**
 * Validator - Simple validation system
 * 
 * This is an alias for OxygenValidator for backward compatibility.
 * Use OxygenValidator directly for the full-featured validation API.
 * 
 * @package    Oxygen\\Core
 * @author     OxygenFramework
 * @version    2.1.0
 * @see        \Oxygen\Core\Validation\OxygenValidator
 */
class Validator extends OxygenValidator
{
    // Inherits all methods from OxygenValidator
    // No need to override make() - parent's method signature is compatible
}
