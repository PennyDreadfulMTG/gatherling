<?php

declare(strict_types=1);

namespace Gatherling\Views;

use Gatherling\Models\Player;

use function Safe\json_encode;

class JsonResponse extends Response
{
    /** @param array<array-key, mixed> $data */
    public function __construct(private array|string $data, bool $addHeaders = false)
    {
        $this->setHeader('Content-type', 'application/json');
        if ($addHeaders) {
            $this->setHeader('Cache-Control', 'no-cache');
            $this->setHeader('Expires', 'Mon, 26 Jul 1997 05:00:00 GMT');
            $this->setHeader('Access-Control-Allow-Origin', '*');
            $loginName = Player::loginName();
            if ($loginName === false) {
                $loginName = '';
            }
            $this->setHeader('HTTP_X_USERNAME', $loginName);
        }
    }

    public function body(): string
    {
        return json_encode($this->data);
    }
}
