<?php

declare(strict_types=1);

use Rector\Core\Configuration\Option;
use Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator;

return static function (ContainerConfigurator $containerConfigurator): void {
    $parameters = $containerConfigurator->parameters();
    $parameters->set(Option::OUTPUT_FORMAT, \Rector\ChangesReporting\Output\JsonOutputFormatter::NAME);

    $services = $containerConfigurator->services();
    $services->set(\LeoVie\PhpConstructNormalize\Rector\ArrayMapToForeachRector::class);
    $services->set(\LeoVie\PhpConstructNormalize\Rector\ForToWhileRector::class);
};