'use strict';

const assert = require('node:assert/strict');
const fs = require('node:fs');
const path = require('node:path');

const source = fs.readFileSync(path.join(__dirname, '../../assets/food-menu.js'), 'utf8');
const start = source.indexOf('  function orderSortValue(');
const end = source.indexOf('  function orderTableHtml(', start);
assert(start >= 0 && end > start, 'order sort functions should be present');
const state = { ordersSort: { key: 'full_name', dir: 'asc' } };
const sortOrderRows = new Function('state', source.slice(start, end) + '\nreturn sortOrderRows;')(state);

const rows = [
  { id: 'surname-first', first_name: 'زارا', last_name: 'آزاد', full_name: 'زارا آزاد' },
  { id: 'firstname-first', first_name: 'آنا', last_name: 'زمانی', full_name: 'آنا زمانی' },
  { id: 'surname-last', first_name: 'بهاره', last_name: 'آزاد', full_name: 'بهاره آزاد' },
];

assert.deepEqual(
  sortOrderRows(rows).map((row) => row.id),
  ['surname-last', 'surname-first', 'firstname-first'],
  'clicking the full name column should sort primarily by surname and secondarily by first name'
);
state.ordersSort.dir = 'desc';
assert.deepEqual(
  sortOrderRows(rows).map((row) => row.id),
  ['firstname-first', 'surname-first', 'surname-last'],
  'the second click should reverse surname order'
);

console.log('food-order-name-sort: surname-first ascending/descending assertions passed');
