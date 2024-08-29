
window.onload = function () {
    $('.timeclear').each(function (i, obj) {
        $(this).html('');
    });
    $('.eventtime').each(function (i, obj) {
        $strStartTime = $(this).attr("start");
        mStart = moment.tz($strStartTime,"America/New_York");
        $(this).html(mStart.tz(moment.tz.guess()).format("D MMM Y HH:mm z") + " <br/> " + $(this).html());
    });
}
