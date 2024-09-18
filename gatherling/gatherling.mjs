if (typeof window === 'undefined') {
  const luxon = await import('luxon');
}

// An extremely opinionated and US-centric date formatter.
//
// The main purpose of this function is to show you dates and times in your timezone.
//
// If `short` is true, you get a short unambigous format suitable for use in a list.
// If `short` is false, you get a long format suitable for use standalone in something like a detail page.
//
// We could rely entirely on JavaScript's Intl functionality for formatting:
//
// Fri, Oct 14, 1983, 1:30 PM / ven. 14 oct. 1983 à 13:30 / etc.
//
// But you don't say "Fri, Oct 14" on Friday October 14th. You say "Today".
// I think these are singificantly nicer, and mostly you will be looking at dates in the next week or so:
//
// Today • 1.30pm
// Yesterday • 1.30pm
// Tomorrow • 1.30pm
// Next Friday • 1.30pm
// Last Tuesday • 1.30pm
//
// Further afield it makes less difference but is still easier on they eye due to the handling of time:
//
// "Wed, Dec 31, 2022 • 7pm" versus "Wed, Dec 31, 2022 7:00 PM"
//
// What we sacrifice is any attempt at localization. But Gatherling is very much a site presented in US English.
// If we suddenly started saying "Mecredi" instead of "Wednesday" that might even be annoying to French users.
// It does mean people to whom "1 June" makes more sense than "June 1" will nonetheless see the former.
// Similarly if 13:00 makes more sense to you than 1pm you are out of luck.
//
// Long form gains less from abandoning Intl styles, but it's still better I think:
//
// Friday, October 14, 1983 at 1:30 PM EDT / 14 octobre 1983 à 19:30 CET
//
// versus always:
//
// Friday, October 14, 1983 • 1.30pm EDT
//
// Unforunately the way the underlying Intl stuff works we need to pass in a locale here to get
// the timezone name "right". Which is to say if you ask en-US the name of the timezone for London
// you get "GMT+1" not "BST". So we pass in the locale in order to try and get the right timezone
// name for you. Folks in a locale that doesn't "match" their timezone may see less nice timezone names.
function formatDate(originalDate, start, timeZone, locale, short) {
  const date = originalDate.setZone(timeZone);
  const now = start.setZone(timeZone);

  let formattedTime;
  if (date.hour == 12 && date.minute === 0) {
    formattedTime = 'noon';
  } else if (date.hour === 0 && date.minute === 0) {
    formattedTime = 'midnight';
  } else if (date.minute === 0) {
    formattedTime = date.toFormat('ha').toLowerCase();
  } else {
    formattedTime = date.toFormat('h.mma').toLowerCase();
  }
  if (!short) {
    // Although our dates are extremely US-centric we need to pass the locale here to get the timezone name
    // See https://github.com/moment/luxon/discussions/1041
    // See https://github.com/moment/luxon/issues/1019
    formattedTime += date.setLocale(locale).toFormat(' ZZZZ');
  }

  const isThisYear = date.hasSame(now, 'year');
  const diffInDays = Math.floor(date.diff(now, 'days').days);

  let formattedDate;
  if (!short) {
    let format = 'cccc, MMMM d';
    if (!isThisYear || diffInDays < 0) {
      format += ', yyyy';
    }
    formattedDate = date.toFormat(format);
  } else if (diffInDays === -1) {
    formattedDate = 'Yesterday';
  } else if (diffInDays === 0) {
    formattedDate = 'Today';
  } else if (diffInDays === 1) {
    formattedDate = 'Tomorrow';
  } else if (diffInDays > 1 && diffInDays < 7) {
    formattedDate = date.toFormat('cccc');
  } else if (diffInDays < -1 && diffInDays >= -7) {
    formattedDate = 'Last ' + date.toFormat('cccc');
  } else if (isThisYear && diffInDays >= 7) {
    formattedDate = date.toFormat('ccc, MMM d');
  } else if (date > now) {
    formattedDate = date.toFormat('ccc, MMM d, yyyy');
  } else {
    formattedDate = date.toFormat('ccc, MMM d, yyyy');
  }

  return `${formattedDate} • ${formattedTime}`;
}

export { formatDate };

if (typeof window !== 'undefined') {
    const timeElements = document.querySelectorAll('time[datetime]');
    timeElements.forEach(timeElement => {
        const isoDate = timeElement.getAttribute('datetime');
        const originalDate = luxon.DateTime.fromISO(isoDate);
        const start = luxon.DateTime.now();
        const timeZone = Intl.DateTimeFormat().resolvedOptions().timeZone;
        const locale = navigator.language || navigator.languages[0]; // Fallback to languages array if needed
        const short = !timeElement.classList.contains('long');
        const formattedDate = formatDate(originalDate, start, timeZone, locale, short);
        console.log(originalDate, formattedDate);
        timeElement.textContent = formattedDate;
    });
}
