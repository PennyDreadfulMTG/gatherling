<?php

declare(strict_types=1);

namespace Gatherling\Views;

use Mustache_Engine;
use Mustache_Loader_FilesystemLoader;

class TemplateHelper
{
    /** @param array<string, mixed>|object $context */
    public static function render(string $template_name, array|object $context = [], bool $isHtml = true): string
    {
        $options = [
            'cache'            => '/tmp/gatherling/mustache/templates',
            'loader'           => new Mustache_Loader_FilesystemLoader(dirname(__FILE__) . '/../templates'),
            'partials_loader'  => new Mustache_Loader_FilesystemLoader(dirname(__FILE__) . '/../templates/partials'),
            'entity_flags'     => ENT_QUOTES,
            'strict_callables' => true,
        ];
        if (!$isHtml) {
            $options['escape'] = fn ($value) => $value;
        }
        $m = new Mustache_Engine($options);
        return $m->render($template_name, $context);
    }
}
