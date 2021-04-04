<?php

declare (strict_types=1);
namespace RayGlobalScoped;

use RayGlobalScoped\Rector\Core\Configuration\Option;
use RayGlobalScoped\Rector\Php74\Rector\Property\TypedPropertyRector;
use RayGlobalScoped\Rector\Set\ValueObject\SetList;
use RayGlobalScoped\Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator;
return static function (\RayGlobalScoped\Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator $containerConfigurator) : void {
    // get parameters
    $parameters = $containerConfigurator->parameters();
    // Define what rule sets will be applied
    $parameters->set(\RayGlobalScoped\Rector\Core\Configuration\Option::SETS, ['/../../../../config/set/downgrade-php73.php']);
    // get services (needed for register a single rule)
    // $services = $containerConfigurator->services();
    // register a single rule
    // $services->set(TypedPropertyRector::class);
};
