<?php

namespace Oxygen\Console\Commands\Generator\Templates;

/**
 * TemplateInterface - Contract for application templates
 */
interface TemplateInterface
{
    /**
     * Get template name
     * 
     * @return string
     */
    public function getName();

    /**
     * Get template description
     * 
     * @return string
     */
    public function getDescription();

    /**
     * Get resources definition
     * 
     * @return array
     */
    public function getResources();

    /**
     * Get default features configuration
     * 
     * @return array
     */
    public function getFeatures();
}
