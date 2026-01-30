<?php declare(strict_types=1);

namespace Paneon\PhpToTypeScriptBundle;

use Paneon\PhpToTypeScriptBundle\DependencyInjection\PhpToTypeScriptExtension;
use Symfony\Component\DependencyInjection\Extension\ExtensionInterface;
use Symfony\Component\HttpKernel\Bundle\Bundle;

class PhpToTypeScriptBundle extends Bundle
{
    public function getContainerExtension(): ?ExtensionInterface
    {
        // Symfony would normally infer the alias from the *bundle name* and expects
        // "php_to_type_script". We explicitly return our extension so the alias
        // is the one users configure under (php_to_typescript).
        return new PhpToTypeScriptExtension();
    }
}
