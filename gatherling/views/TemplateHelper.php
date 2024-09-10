<?php

declare(strict_types=1);

namespace Gatherling\Views;

use Mustache_Engine;
use Mustache_Loader_FilesystemLoader;

class TemplateHelper
{
    public static function render(string $template_name, array|object $context = []): string
    {
        $m = new Mustache_Engine([
            'cache'            => '/tmp/gatherling/mustache/templates',
            'loader'           => new Mustache_Loader_FilesystemLoader(dirname(__FILE__) . '/../templates'),
            'partials_loader'  => new Mustache_Loader_FilesystemLoader(dirname(__FILE__) . '/../templates/partials'),
            'entity_flags'     => ENT_QUOTES,
            'strict_callables' => true,
        ]);
        return $m->render($template_name, $context);
    }
}
