<?php

use Pace\RestBuilder;
use Pace\RestModel;
use Pace\RestClient;

it('generates correct case-insensitive xpath', function () {
    $builder = new RestBuilder();
    $builder->filterIgnoreCase('@name', 'DTF');
    
    $reflection = new ReflectionClass($builder);
    $method = $reflection->getMethod('toXPath');
    $method->setAccessible(true);
    
    $xpath = $method->invoke($builder);
    
    $upper = 'ABCDEFGHIJKLMNOPQRSTUVWXYZ';
    $lower = 'abcdefghijklmnopqrstuvwxyz';
    
    expect($xpath)->toBe("translate(@name, '{$upper}', '{$lower}') = \"dtf\"");
});

it('generates correct case-insensitive xpath with or boolean', function () {
    $builder = new RestBuilder();
    $builder->filterIgnoreCase('@name', 'DTF');
    $builder->filterIgnoreCase('@code', 'dtf', 'or');
    
    $reflection = new ReflectionClass($builder);
    $method = $reflection->getMethod('toXPath');
    $method->setAccessible(true);
    
    $xpath = $method->invoke($builder);
    
    $upper = 'ABCDEFGHIJKLMNOPQRSTUVWXYZ';
    $lower = 'abcdefghijklmnopqrstuvwxyz';
    
    expect($xpath)->toBe("translate(@name, '{$upper}', '{$lower}') = \"dtf\" or translate(@code, '{$upper}', '{$lower}') = \"dtf\"");
});

it('combines case-insensitive with regular filters', function () {
    $builder = new RestBuilder();
    $builder->filterIgnoreCase('@name', 'DTF')
            ->filter('category/@department', 5006);
    
    $reflection = new ReflectionClass($builder);
    $method = $reflection->getMethod('toXPath');
    $method->setAccessible(true);
    
    $xpath = $method->invoke($builder);
    
    $upper = 'ABCDEFGHIJKLMNOPQRSTUVWXYZ';
    $lower = 'abcdefghijklmnopqrstuvwxyz';
    
    expect($xpath)->toBe("translate(@name, '{$upper}', '{$lower}') = \"dtf\" and category/@department = 5006");
});

