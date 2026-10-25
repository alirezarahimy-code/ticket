const fs = require('fs');
const assert = require('assert');

const html = fs.readFileSync('food-ticket-web/index.html', 'utf8');
const reducer = html.match(/if\(sources\.monitoring\)events\.forEach\(event=>\{[^\n]*?\}\);/);
assert(reducer, 'dashboard monitoring reducer should exist');
const kindStart = html.indexOf('function dashboardEventKind(event)');
const kindEnd = html.indexOf('\n', kindStart);
assert(kindStart >= 0 && kindEnd > kindStart, 'dashboard event classifier should exist');
const classify = html.slice(kindStart, kindEnd);
const dashboardEventKind = new Function(`${classify}; return dashboardEventKind;`)();

const daily = {
  '2026-10-25': { attendance: 0, printed: 0, guests: 0, repeat: 0, noFood: 0, errors: 0, pending: 0 },
};
const events = [
  { date: '2026-10-25', status: 'printed', result: 'فیش چاپ شد' },
  { date: '2026-10-25', status: 'printed', result: 'فیش مهمان چاپ شد' },
];
const sources = { monitoring: true };
const isoDateFor = value => value;
new Function('daily', 'events', 'sources', 'isoDateFor', 'dashboardEventKind', reducer[0])(daily, events, sources, isoDateFor, dashboardEventKind);
assert.strictEqual(daily['2026-10-25'].printed, 2, 'each printed event must contribute one receipt, not two');
assert.strictEqual(daily['2026-10-25'].attendance, 2);
assert.strictEqual(daily['2026-10-25'].guests, 1, 'guest classification remains independent of receipt counting');
console.log('food dashboard printed-event counting: PASS');
