import assert from 'node:assert/strict';
import fs from 'node:fs';
import vm from 'node:vm';
import { execFileSync } from 'node:child_process';
import path from 'node:path';

const root = path.resolve(__dirname, '..');
const source = fs.readFileSync(path.join(root, 'assets/jalali-calendar.js'), 'utf8');
const sandbox = {
  Date,
  Math,
  Number,
  Array,
  String,
  Object,
  Intl,
  console,
  document: {
    addEventListener() {},
    querySelectorAll() { return []; },
    getElementById() { return null; },
  },
};
sandbox.window = sandbox;
sandbox.addEventListener = () => {};
vm.runInNewContext(source, sandbox, { filename: 'assets/jalali-calendar.js' });

const api = sandbox.ItsmJalali;
assert.ok(api && typeof api.j2g === 'function' && typeof api.g2j === 'function',
  'assets/jalali-calendar.js must expose ItsmJalali.j2g/g2j');

const dates = [];
const utc = (g) => Date.UTC(g.gy, g.gm - 1, g.gd);
for (let jy = 1300; jy <= 1500; jy++) {
  for (let jm = 1; jm <= 12; jm++) {
    const first = api.j2g(jy, jm, 1);
    const next = jm === 12 ? api.j2g(jy + 1, 1, 1) : api.j2g(jy, jm + 1, 1);
    const days = Math.round((utc(next) - utc(first)) / 86400000);
    assert.ok(days === 29 || days === 30 || days === 31, `invalid month length ${jy}/${jm}: ${days}`);
    for (let jd = 1; jd <= days; jd++) dates.push([jy, jm, jd]);
  }
}

const phpScript = path.join(__dirname, 'fixtures/jalali-php-convert.php');
const raw = execFileSync(process.env.PHP_BINARY || 'php', [phpScript], {
  input: JSON.stringify(dates),
  encoding: 'utf8',
  maxBuffer: 32 * 1024 * 1024,
});
const php = JSON.parse(raw);
assert.equal(php.dates.length, dates.length, 'PHP output count must match generated Jalali dates');

const pad = (n, width = 2) => String(n).padStart(width, '0');
for (let i = 0; i < dates.length; i++) {
  const [jy, jm, jd] = dates[i];
  const jsGregorian = api.j2g(jy, jm, jd);
  const jsRoundTrip = api.g2j(jsGregorian.gy, jsGregorian.gm, jsGregorian.gd);
  const phpRow = php.dates[i];
  const expectedGregorian = [jsGregorian.gy, jsGregorian.gm, jsGregorian.gd];
  const expectedRoundTrip = [jy, jm, jd];
  assert.deepEqual(phpRow.gregorian, expectedGregorian,
    `PHP/JS Gregorian mismatch at Jalali ${jy}/${jm}/${jd}: PHP=${phpRow.gregorian}, JS=${expectedGregorian}`);
  assert.deepEqual(phpRow.round_trip, expectedRoundTrip,
    `PHP round-trip mismatch at Jalali ${jy}/${jm}/${jd}: ${phpRow.round_trip}`);
  assert.deepEqual([jsRoundTrip.jy, jsRoundTrip.jm, jsRoundTrip.jd], expectedRoundTrip,
    `JS round-trip mismatch at Jalali ${jy}/${jm}/${jd}`);

  const expectedInput = `${pad(jsGregorian.gy, 4)}-${pad(jsGregorian.gm)}-${pad(jsGregorian.gd)} 00:00:00`;
  const expectedEnd = `${pad(jsGregorian.gy, 4)}-${pad(jsGregorian.gm)}-${pad(jsGregorian.gd)} 23:59:59`;
  assert.equal(phpRow.input, expectedInput, `PHP start-of-day mismatch at ${jy}/${jm}/${jd}`);
  assert.equal(phpRow.end_of_day, expectedEnd, `PHP end-of-day mismatch at ${jy}/${jm}/${jd}`);
}

for (const [name, value] of Object.entries(php.invalid)) {
  assert.equal(value, null, `invalid Jalali input must be rejected: ${name}`);
}
assert.ok(php.valid_persian_digits, 'Persian digit input must be accepted');
assert.equal(php.valid_persian_digits, php.valid_hyphen, 'Persian digits and hyphen separators must normalize consistently');

const nowruz1403 = api.j2g(1403, 1, 1);
assert.deepEqual([nowruz1403.gy, nowruz1403.gm, nowruz1403.gd], [2024, 3, 20],
  'known Nowruz 1403 reference must remain 2024-03-20');

console.log(`PASS T11: ${dates.length} valid Jalali dates across 1300–1500 match PHP and ItsmJalali JS; invalid inputs rejected.`);
