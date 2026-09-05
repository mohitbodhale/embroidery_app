<?php
require dirname(__DIR__) . '/vendor/autoload.php';
require dirname(__DIR__) . '/config/bootstrap.php';

use Cake\ORM\Locator\LocatorAwareTrait;
use Cake\ORM\Table;

echo "Checking Table class methods...\n";

// Check if Table has fetchTable
$ref = new ReflectionClass(Table::class);
$methods = $ref->getMethods();
$methodNames = array_map(fn($m) => $m->name, $methods);

echo "fetchTable exists: " . (in_array('fetchTable', $methodNames) ? 'YES' : 'NO') . "\n";
echo "getTableLocator exists: " . (in_array('getTableLocator', $methodNames) ? 'YES' : 'NO') . "\n";

// Check if LocatorAwareTrait is used
$traits = $ref->getTraitNames();
echo "Traits used: " . implode(', ', $traits) . "\n";
echo "LocatorAwareTrait in traits: " . (in_array('Cake\ORM\Locator\LocatorAwareTrait', $traits) ? 'YES' : 'NO') . "\n";

// Check parent classes
echo "Parent classes: " . implode(', ', array_map(fn($p) => $p->name, $ref->getTraitNames() ?: [])) . "\n";
$parents = [];
$p = $ref;
while ($p = $p->getParentClass()) {
    $parents[] = $p->name;
    $parentMethods = array_map(fn($m) => $m->name, $p->getMethods());
    if (in_array('fetchTable', $parentMethods)) {
        echo "fetchTable found in parent: " . $p->name . "\n";
    }
}
echo "Inheritance chain: " . implode(' -> ', $parents) . "\n";
