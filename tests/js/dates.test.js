import { test, expect } from 'bun:test';
import { DateTime } from 'luxon';
import { formatDate } from '../../gatherling/gatherling';

const locations = [
  { timeZone: 'America/New_York', locale: 'en-US' },
  { timeZone: 'Europe/London', locale: 'en-GB' },
  { timeZone: 'UTC', locale: 'en-US' }
];

// All tests based on June 1st 2024 at midnight UTC
// That's 1am in London and May 31st at 8pm in New York
const now = DateTime.fromISO('2024-06-01T00:00:00Z');

const tests = {
  now: [
    now,
    ['Today • 8pm', 'Friday, May 31 • 8pm EDT'],
    ['Today • 1am', 'Saturday, June 1 • 1am BST'],
    ['Today • midnight', 'Saturday, June 1 • midnight UTC']
  ],
  twelveHoursAgo: [
    DateTime.fromISO('2024-05-31T12:00:00Z'),
    ['Today • 8am', 'Friday, May 31, 2024 • 8am EDT'],
    ['Yesterday • 1pm', 'Friday, May 31, 2024 • 1pm BST'],
    ['Yesterday • noon', 'Friday, May 31, 2024 • noon UTC']
  ],
  nearlyTwentyFourHoursFromNow: [
    DateTime.fromISO('2024-06-01T23:30:00Z'),
    ['Tomorrow • 7.30pm', 'Saturday, June 1 • 7.30pm EDT'],
    ['Tomorrow • 12.30am', 'Sunday, June 2 • 12.30am BST'],
    ['Today • 11.30pm', 'Saturday, June 1 • 11.30pm UTC']
  ],
  sixteenAndAHalfHoursFromNow: [
    DateTime.fromISO('2024-06-01T16:30:00Z'),
    ['Tomorrow • 12.30pm', 'Saturday, June 1 • 12.30pm EDT'],
    ['Today • 5.30pm', 'Saturday, June 1 • 5.30pm BST'],
    ['Today • 4.30pm', 'Saturday, June 1 • 4.30pm UTC']
  ],
  fortyAndAHalfHoursFromNow: [
    DateTime.fromISO('2024-06-02T16:30:00Z'),
    ['Sunday • 12.30pm', 'Sunday, June 2 • 12.30pm EDT'],
    ['Tomorrow • 5.30pm', 'Sunday, June 2 • 5.30pm BST'],
    ['Tomorrow • 4.30pm', 'Sunday, June 2 • 4.30pm UTC']
  ],
  yesterday: [
    DateTime.fromISO('2024-05-31T00:00:00Z'),
    ['Yesterday • 8pm', 'Thursday, May 30, 2024 • 8pm EDT'],
    ['Yesterday • 1am', 'Friday, May 31, 2024 • 1am BST'],
    ['Yesterday • midnight', 'Friday, May 31, 2024 • midnight UTC']
  ],
  tomorrow: [
    DateTime.fromISO('2024-06-02T00:00:00Z'),
    ['Tomorrow • 8pm', 'Saturday, June 1 • 8pm EDT'],
    ['Tomorrow • 1am', 'Sunday, June 2 • 1am BST'],
    ['Tomorrow • midnight', 'Sunday, June 2 • midnight UTC']
  ],
  nextWeek: [
    DateTime.fromISO('2024-06-05T08:30:00Z'),
    ['Wednesday • 4.30am', 'Wednesday, June 5 • 4.30am EDT'],
    ['Wednesday • 9.30am', 'Wednesday, June 5 • 9.30am BST'],
    ['Wednesday • 8.30am', 'Wednesday, June 5 • 8.30am UTC']
  ],
  lastWeek: [
    DateTime.fromISO('2024-05-25T14:45:00Z'),
    ['Last Saturday • 10.45am', 'Saturday, May 25, 2024 • 10.45am EDT'],
    ['Last Saturday • 3.45pm', 'Saturday, May 25, 2024 • 3.45pm BST'],
    ['Last Saturday • 2.45pm', 'Saturday, May 25, 2024 • 2.45pm UTC']
  ],
  farFuture: [
    DateTime.fromISO('2025-12-31T23:59:59Z'),
    ['Wed, Dec 31, 2025 • 6.59pm', 'Wednesday, December 31, 2025 • 6.59pm EST'],
    ['Wed, Dec 31, 2025 • 11.59pm', 'Wednesday, December 31, 2025 • 11.59pm GMT'],
    ['Wed, Dec 31, 2025 • 11.59pm', 'Wednesday, December 31, 2025 • 11.59pm UTC']
  ],
  farPast: [
    DateTime.fromISO('2023-01-01T00:00:00Z'),
    ['Sat, Dec 31, 2022 • 7pm', 'Saturday, December 31, 2022 • 7pm EST'],
    ['Sun, Jan 1, 2023 • midnight', 'Sunday, January 1, 2023 • midnight GMT'],
    ['Sun, Jan 1, 2023 • midnight', 'Sunday, January 1, 2023 • midnight UTC']
  ]
};

Object.entries(tests).forEach(([name, [date, ...expected]]) => {
  locations.forEach(({ timeZone, locale }, locationIndex) => {
    expected[locationIndex].forEach((expectedFormat, index) => {
      const isShort = index === 0;
      test(`${name} in ${timeZone}`, () => {
        const formattedTime = formatDate(date, now, timeZone, locale, isShort);
        expect(formattedTime).toBe(expectedFormat);
      });
    });
  });
});

// Test expected behavior when you have a locale that "differs" from your timezone
const germanTests = {
  'America/New_York': 'Sunday, June 2 • 12.30pm GMT-4',
  'Europe/London': 'Sunday, June 2 • 5.30pm GMT+1',
  'UTC': 'Sunday, June 2 • 4.30pm UTC'
};

Object.entries(germanTests).forEach(([timeZone, expected]) => {
  test(`German locale in ${timeZone} shows 'correct' timezone`, () => {
    const date = DateTime.fromISO('2024-06-02T16:30:00Z');
    const formattedTime = formatDate(date, now, timeZone, 'de-DE', false);
    expect(formattedTime).toBe(expected);
  });
});
