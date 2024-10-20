<?php

declare(strict_types=1);

namespace Gatherling;

use Gatherling\Exceptions\DatabaseException;
use Gatherling\Models\Database;
use Gatherling\Models\Player;
use Gatherling\Views\Components\EditCard;
use Gatherling\Views\Components\EditSet;
use Gatherling\Views\Components\NullComponent;
use Gatherling\Views\Components\SetList;
use Gatherling\Views\Pages\CardsAdmin;
use Gatherling\Views\Redirect;

use function Gatherling\Helpers\db;
use function Gatherling\Helpers\get;
use function Gatherling\Helpers\post;
use function Gatherling\Helpers\request;
use function Gatherling\Helpers\server;

require_once 'lib.php';
include 'lib_form_helper.php';

function main(): void
{
    if (!(Player::getSessionPlayer()?->isSuper() ?? false)) {
        (new Redirect('index.php'))->send();
    }

    $action = post()->optionalString('action');

    if ($action == 'modify_set' && post()->listInt('delentries')) {
        deleteCards(post()->listInt('delentries'));
    } elseif ($action == 'modify_card') {
        updateCard(request()->int('id'), request()->string('name'), request()->string('type'), request()->string('rarity'), request()->string('sfId'), (bool) request()->optionalInt('is_changeling'));
    }

    $view = post()->optionalString('view') ?? get()->optionalString('view') ?? 'list_sets';

    switch ($view) {
        case 'edit_card':
            $viewComponent = new EditCard(request()->int('id'));
            break;
        case 'edit_set':
            $viewComponent = new EditSet(request()->string('set'));
            break;
        case 'list_sets':
            $viewComponent = new SetList();
            break;
        case 'no_view':
        default:
            $viewComponent = new NullComponent();
            break;
    }
    $page = new CardsAdmin($viewComponent);
    $page->send();
}

/** @param list<int> $cardIds */
function deleteCards(array $cardIds): void
{
    $sql = 'DELETE FROM `cards` WHERE `id` = :id';
    foreach ($cardIds as $id) {
        db()->execute($sql, ['id' => $id]);
    }
}

function updateCard(int $cardId, string $name, string $type, string $rarity, string $sfId, bool $isChangeling): void
{
    $db = Database::getConnection();
    $stmt = $db->prepare('UPDATE `cards` SET `name` = ?, `type` = ?, `rarity` = ?, `scryfallId` = ?, `is_changeling` = ? WHERE `id` = ?');
    if (!$stmt) {
        throw new DatabaseException($db->error);
    }
    $stmt->bind_param('ssssdi', $name, $type, $rarity, $sfId, $isChangeling, $cardId);
    $stmt->execute();
}

if (basename(__FILE__) == basename(server()->string('PHP_SELF'))) {
    main();
}
