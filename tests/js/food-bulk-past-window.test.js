const fs = require('fs');
const assert = require('assert');

const source = fs.readFileSync('food-ticket-web/assets/food-menu.js', 'utf8');
const start = source.indexOf('  function bulkCanSelect(person) {');
const end = source.indexOf('\n  function loadBulkSnapshot', start);
assert(start >= 0 && end > start, 'bulk selection policy should exist');
const selectionPolicy = source.slice(start, end);

function canSelect(snapshot, operation, person, extras = {}) {
  const state = {
    bulk: {
      snapshot,
      operation,
      target: operation === 'order' ? '7' : '2',
      items: [{ item_id: 7 }],
      groups: [{ id: 2, locked: false }],
      ...extras,
    },
  };
  return new Function('state', `${selectionPolicy}; return bulkCanSelect;`)(state)(person);
}

const pastOpen = { is_past: true, late_order_window_open: true, has_calendar: true };
const pastClosed = { is_past: true, late_order_window_open: false, has_calendar: true };
const activeOrder = { order: { status: 'active' } };
assert.strictEqual(canSelect(pastOpen, 'order', activeOrder), true, 'active emergency window permits a past-date group order');
assert.strictEqual(canSelect(pastOpen, 'cancel', activeOrder), true, 'active emergency window permits past-date cancellation');
assert.strictEqual(canSelect(pastClosed, 'order', activeOrder), false, 'expired window blocks a past-date group order');
assert.strictEqual(canSelect(pastClosed, 'cancel', activeOrder), false, 'expired window blocks a past-date cancellation');
assert.strictEqual(canSelect(pastOpen, 'canteen', activeOrder), false, 'past-date canteen changes stay blocked');
console.log('food bulk past-date emergency-window UI policy: PASS');
