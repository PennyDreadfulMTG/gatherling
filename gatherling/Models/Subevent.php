<?php

declare(strict_types=1);

namespace Gatherling\Models;

use Exception;

class Subevent
{
    public ?string $parent;
    public ?int $rounds;
    public ?int $timing;
    public ?string $type;
    public ?int $id;

    public function __construct(int $id)
    {
        $db = Database::getConnection();
        $stmt = $db->prepare('SELECT parent, rounds, timing, type
      FROM subevents WHERE id = ?');
        $stmt->bind_param('d', $id);
        $stmt->execute();
        $stmt->bind_result($this->parent, $this->rounds, $this->timing, $this->type);
        if ($stmt->fetch()) {
            $this->id = $id;
        } else {
            throw new Exception("Can't instantiate subevent with id $id");
        }
    }

    public function save(): void
    {
        $db = Database::getConnection();
        $stmt = $db->prepare('UPDATE subevents SET parent = ?, rounds = ?,
      timing = ?, type = ? WHERE id = ?');
        $stmt->bind_param('sddss', $this->parent, $this->rounds, $this->timing, $this->type, $this->id);
        if (!$stmt->execute()) {
            throw new Exception($stmt->error, 1);
        }
        $stmt->close();
    }
}
