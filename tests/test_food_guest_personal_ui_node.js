'use strict';
// Run with NODE_PATH pointing to a jsdom installation; no network or live server.
const assert = require('assert');
const fs = require('fs');
const path = require('path');
const { JSDOM } = require('jsdom');
const root = path.join(__dirname, '..');
const source = fs.readFileSync(path.join(root, 'assets/food-order-guest.js'), 'utf8');
const css = fs.readFileSync(path.join(root, 'assets/food-order.css'), 'utf8');
const dom = new JSDOM('<!doctype html><html><body><div id="food-guest-app" data-api="index.php?food_api=" data-csrf="token" data-self-name="Self"></div></body></html>', { runScripts: 'outside-only', url: 'https://example.test/' });
const { window } = dom;
window.HTMLDialogElement.prototype.showModal = function () { this.open = true; };
window.HTMLDialogElement.prototype.close = function () { this.open = false; };
window.HTMLElement.prototype.scrollIntoView = function () {};
window.confirm = () => true;
const items = [
  { id: 10, request_jalali: '1405/08/13', deputy_name:'واحد', organization:'<script>unsafe</script>', guest_count:3, requester_name:'نام', created_by_name:'ثبت کننده', status:'active', can_edit:true, can_cancel:true, can_delete:true },
  { id: 11, request_jalali: '1405/08/14', organization:'سازمان دیگر', guest_count:1, status:'cancelled', can_edit:false, can_cancel:false, can_delete:false }
];
const calls = [];
window.fetch = async (url, options) => { calls.push([url,options]); return { ok:true, status:200, text:async () => JSON.stringify(url.includes('guest-requests') ? {items, can_create:true, today_jalali:'1405/08/12', same_day_open:true} : {ok:true}) }; };
window.eval(source);
(async () => {
  window.FoodGuest.open();
  await new Promise(resolve => setTimeout(resolve, 20));
  const dialog = window.document.getElementById('fg-dialog');
  const list = dialog.querySelector('.fg-request-list');
  assert(list && list.getAttribute('aria-label').includes('من'));
  assert.strictEqual(list.querySelectorAll('.fg-request-item').length, 2);
  assert.strictEqual(list.querySelectorAll('tbody tr').length, 2);
  assert.strictEqual(list.querySelector('tbody tr').querySelectorAll('td').length, 9);
  assert(!list.querySelector('script'), 'untrusted organization must be escaped');
  assert(list.textContent.includes('<script>unsafe</script>'));
  assert.strictEqual(list.querySelectorAll('[data-fg-action="edit"]').length, 1);
  assert.strictEqual(list.querySelectorAll('[data-fg-action="cancel"]').length, 1);
  assert.strictEqual(list.querySelectorAll('[data-fg-action="delete"]').length, 1);
  assert(css.includes('max-height:min(36vh, 270px)') && css.includes('overflow:auto'));
  dialog.querySelector('[data-fg-action="edit"]').click();
  assert.strictEqual(dialog.querySelector('[name="organization"]').value, '<script>unsafe</script>');
  assert(dialog.querySelector('[data-fg-action="edit-cancel"]'));
  dialog.querySelector('[data-fg-action="edit-cancel"]').click();
  assert.strictEqual(dialog.querySelector('[name="organization"]').value, '');
  dialog.querySelector('[data-fg-action="cancel"]').click();
  await new Promise(resolve => setTimeout(resolve, 20));
  const cancel = calls.find(([url]) => url.includes('guest-request-cancel'));
  assert(cancel && cancel[1].headers['X-CSRF-Token'] === 'token');
  assert.strictEqual(JSON.parse(cancel[1].body).id, 10);
  console.log('PASS guest dialog compact list, escaping, edit and cancel');
  window.close();
})().catch(err => { console.error(err); process.exitCode = 1; window.close(); });
