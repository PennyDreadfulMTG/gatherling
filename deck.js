function deckAjaxResult(data) {
    if (data.id != 0) {
        $("#deck-name").val(data.name);
        $("#deck-archetype").val(data.archetype);
        var decktext =  "";
        for (var card in data.maindeck) {
            decktext = decktext + data.maindeck[card] + " " + card + "\n";
        }
        $("#deck-contents").val(decktext);
        decktext = "";
        for (var card in data.sideboard) {
            decktext = decktext + data.sideboard[card] + " " + card + "\n";
        }
        $("#deck-sideboard").val(decktext);
    }
}

$(document).ready(function() {
    $("#autoenter-deck").change(function() {
        var selid = $("#autoenter-deck").val();
        $.ajax({
            url: 'ajax.php?deck=' + selid,
            success: deckAjaxResult
        });
        $("#autoenter-deck").val(0);
    });
});

String.prototype.beforeLastIndex = function (delimiter) {
    return this.substr(0,this.lastIndexOf(delimiter)) || this + ""
}

$('input[type=file]').on("change", function(e) {
    var file = e.target.files[0];
    reader = new FileReader();
    reader.onload = function (e) {
        txt = e.target.result.replace(/\n\r/g, '\n');
        split = txt.split('\n\n');
        $("#deck-contents").val(split[0]);
        if (split.length > 1) {
            $("#deck-sideboard").val(split[1]);
        }
        $("#deck-name").val(file.name.beforeLastIndex('.'));
        // TODO: Put sideboard in the right place
    };
    reader.readAsText(file);
});
