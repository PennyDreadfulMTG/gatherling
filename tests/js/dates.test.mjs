import { test, expect } from 'bun:test';
import { DateTime } from 'luxon';
import { formatDate } from '../../gatherling/gatherling.mjs';

const now = DateTime.fromISO('2024-06-01T00:00:00Z');

const yesterday = DateTime.fromISO('2024-05-31T12:00:00Z');
const tomorrow = DateTime.fromISO('2024-06-02T16:30:00Z');
const nextWeek = DateTime.fromISO('2024-06-05T08:30:00Z');
const lastWeek = DateTime.fromISO('2024-05-25T14:45:00Z');
const farFuture = DateTime.fromISO('2025-12-31T23:59:59Z');
const farPast = DateTime.fromISO('2023-01-01T00:00:00Z');

const testCases = [
  [now, 'America/New_York', 'en-US', 'short', 'Today • 8pm'],
  [now, 'Europe/London', 'en-GB', 'short', 'Today • 1am'],
  [now, 'UTC', 'en-US', 'short', 'Today • midnight'],

  [yesterday, 'America/New_York', 'en-US', 'short', 'Yesterday • 8am'],
  [yesterday, 'Europe/London', 'en-GB', 'short', 'Yesterday • 1pm'],
  [yesterday, 'UTC', 'en-US', 'short', 'Yesterday • noon'],

  [tomorrow, 'America/New_York', 'en-US', 'short', 'Tomorrow • 12.30pm'],
  [tomorrow, 'Europe/London', 'en-GB', 'short', 'Tomorrow • 5.30pm'],
  [tomorrow, 'UTC', 'en-US', 'short', 'Tomorrow • 4.30pm'],

  [nextWeek, 'America/New_York', 'en-US', 'short', 'Wednesday • 4.30am'],
  [nextWeek, 'Europe/London', 'en-GB', 'short', 'Wednesday • 9.30am'],
  [nextWeek, 'UTC', 'en-US', 'short', 'Wednesday • 8.30am'],

  [lastWeek, 'America/New_York', 'en-US', 'short', 'Last Saturday • 10.45am'],
  [lastWeek, 'Europe/London', 'en-GB', 'short', 'Last Saturday • 3.45pm'],
  [lastWeek, 'UTC', 'en-US', 'short', 'Last Saturday • 2.45pm'],

  [farFuture, 'America/New_York', 'en-US', 'short', 'Wed, Dec 31, 2025 • 6.59pm'],
  [farFuture, 'Europe/London', 'en-GB', 'short', 'Wed, Dec 31, 2025 • 11.59pm'],
  [farFuture, 'UTC', 'en-US', 'short', 'Wed, Dec 31, 2025 • 11.59pm'],

  [farPast, 'America/New_York', 'en-US', 'short', 'Sat, Dec 31, 2022 • 7pm'],
  [farPast, 'Europe/London', 'en-GB', 'short', 'Sun, Jan 1, 2023 • midnight'],
  [farPast, 'UTC', 'en-US', 'short', 'Sun, Jan 1, 2023 • midnight'],

  [now, 'America/New_York', 'en-US', 'long', 'Friday, May 31 • 8pm EDT'],
  [now, 'Europe/London', 'en-GB', 'long', 'Saturday, June 1 • 1am BST'],
  [now, 'UTC', 'en-US', 'long', 'Saturday, June 1 • midnight UTC'],

  [yesterday, 'America/New_York', 'en-US', 'long', 'Friday, May 31, 2024 • 8am EDT'],
  [yesterday, 'Europe/London', 'en-GB', 'long', 'Friday, May 31, 2024 • 1pm BST'],
  [yesterday, 'UTC', 'en-US', 'long', 'Friday, May 31, 2024 • noon UTC'],

  [tomorrow, 'America/New_York', 'en-US', 'long', 'Sunday, June 2 • 12.30pm EDT'],
  [tomorrow, 'Europe/London', 'en-GB', 'long', 'Sunday, June 2 • 5.30pm BST'],
  [tomorrow, 'UTC', 'en-US', 'long', 'Sunday, June 2 • 4.30pm UTC'],

  [nextWeek, 'America/New_York', 'en-US', 'long', 'Wednesday, June 5 • 4.30am EDT'],
  [nextWeek, 'Europe/London', 'en-GB', 'long', 'Wednesday, June 5 • 9.30am BST'],
  [nextWeek, 'UTC', 'en-US', 'long', 'Wednesday, June 5 • 8.30am UTC'],

  [lastWeek, 'America/New_York', 'en-US', 'long', 'Saturday, May 25, 2024 • 10.45am EDT'],
  [lastWeek, 'Europe/London', 'en-GB', 'long', 'Saturday, May 25, 2024 • 3.45pm BST'],
  [lastWeek, 'UTC', 'en-US', 'long', 'Saturday, May 25, 2024 • 2.45pm UTC'],

  [farFuture, 'America/New_York', 'en-US', 'long', 'Wednesday, December 31, 2025 • 6.59pm EST'],
  [farFuture, 'Europe/London', 'en-GB', 'long', 'Wednesday, December 31, 2025 • 11.59pm GMT'],
  [farFuture, 'UTC', 'en-US', 'long', 'Wednesday, December 31, 2025 • 11.59pm UTC'],

  [farPast, 'America/New_York', 'en-US', 'long', 'Saturday, December 31, 2022 • 7pm EST'],
  [farPast, 'Europe/London', 'en-GB', 'long', 'Sunday, January 1, 2023 • midnight GMT'],
  [farPast, 'UTC', 'en-US', 'long', 'Sunday, January 1, 2023 • midnight UTC'],

  // Locale only affects timezone name
  [tomorrow, 'America/New_York', 'de-DE', 'long', 'Sunday, June 2 • 12.30pm GMT-4'],
  [tomorrow, 'Europe/London', 'de-DE', 'long', 'Sunday, June 2 • 5.30pm GMT+1'],
  [tomorrow, 'UTC', 'en-US', 'de-DE', 'Sunday, June 2 • 4.30pm UTC'],
];

testCases.forEach(([input, timeZone, locale, formatType, expected]) => {
  test(`formatDate correctly formats ${input} (${formatType}) in ${locale}/${timeZone}`, () => {
    const formattedTime = formatDate(input, now, timeZone, locale, formatType === 'short');
    expect(formattedTime).toBe(expected);
  });
});
