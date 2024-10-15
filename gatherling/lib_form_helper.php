<?php

declare(strict_types=1);

use Gatherling\Models\Event;
use Gatherling\Models\Player;
use Gatherling\Models\Standings;
use Gatherling\Views\Components\FileInput;
use Gatherling\Views\Components\TextInput;
use Gatherling\Views\Components\SelectInput;
use Gatherling\Views\Components\CheckboxInput;

require_once 'lib.php';

function textInput(string $label, string $name, mixed $value = '', int $size = 0, ?string $reminderText = null, ?string $id = null): string
{
    return (new TextInput($label, $name, $value, $size, $reminderText, $id))->render();
}

function checkboxInput(string $label, string $name, bool $isChecked = false, ?string $reminderText = null): string
{
    return (new CheckboxInput($label, $name, $isChecked, $reminderText))->render();
}

function print_file_input(string $label, string $name): string
{
    return (new FileInput($label, $name))->render();
}

/** @param array<string, string> $options */
function selectInput(string $label, string $name, ?array $options, mixed $selected = null, ?string $id = null): string
{
    return (new SelectInput($label, $name, $options, $selected, $id))->render();
}

function leagueOpponentDropMenu(string $eventname, int $round, Player $player, int $subevent): void
{
    $event = new Event($eventname);
    $player_standings = new Standings($eventname, $player->name);
    $playernames = $player_standings->getAvailableLeagueOpponents($subevent, $round, $event->leagueLength());

    echo '<select class="inputbox" name="opponent"> Opponent';

    if (count($playernames)) {
        foreach ($playernames as $playername) {
            $oppplayer = new Player($playername);
            echo "<option value=\"{$playername}\">{$oppplayer->gameName($event->client, false)}</option>";
        }
    } else {
        echo '<option value="">-No Available Opponents-</option>';
    }
    echo '</select>';
}

/**
 * @param list<string|int> $items
 * @return list<array{value: string, text: string}>
 */
function opts(array $items): array
{
    return array_map(fn (string|int $item) => ['value' => (string) $item, 'text' => (string) $item], $items);
}
