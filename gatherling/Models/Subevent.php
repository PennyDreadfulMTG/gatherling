<?php

declare(strict_types=1);

namespace Gatherling\Models;

use Exception;
use Gatherling\Data\Db;
use Gatherling\Exceptions\NotFoundException;

class Subevent
{
    public ?string $parent;
    public ?int $rounds;
    public ?int $timing;
    public ?string $type;
    public ?int $id;

    public function __construct(int $id)
    {
        $sql = 'SELECT parent, rounds, timing, type FROM subevents WHERE id = :id';
        $params = ['id' => $id];
        $subevent = Db::selectOnly($sql, SubeventDto::class, $params);
        $this->id = $id;
        foreach (get_object_vars($subevent) as $key => $value) {
            $this->$key = $value;
        }
    }

    public function save(): void
    {
        $sql = '
            UPDATE
                subevents
            SET
                parent = :parent, rounds = :rounds, timing = :timing, type = :type
            WHERE
                id = :id';
        $params = [
            'parent' => $this->parent,
            'rounds' => $this->rounds,
            'timing' => $this->timing,
            'type' => $this->type,
            'id' => $this->id,
        ];
        Db::execute($sql, $params);
    }
}
