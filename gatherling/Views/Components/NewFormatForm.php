<?php

declare(strict_types=1);

namespace Gatherling\Views\Components;

use Gatherling\Views\Components\Component;

class NewFormatForm extends Component
{
    public string $action;

    public function __construct(public string $seriesName)
    {
        parent::__construct('partials/newFormatForm');
        echo "<h4>New Format</h4>\n";
        echo '<form action="admincp.php" method="post">';
        echo '<input type="hidden" name="view" value="no_view" />';
        echo '<table class="form c">';
        echo '<tr><td colspan="2">New Format Name: <input type="text" name="newformatname" STYLE="width: 175px"/></td></tr>';
        echo '<td colspan="2" class="buttons">';
        echo '<input class="inputbutton" type="submit" value="Create New Format" name ="action" /></td></tr>';
        echo'</table></form>';
    }
}
