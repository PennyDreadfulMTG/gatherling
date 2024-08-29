function update_cta()
{

    $.get("action.php", function (data) {
        $("#action").html(data)
    });
}

update_cta();
setInterval(update_cta, 300000);
