import { test } from 'node:test';
import assert from 'node:assert/strict';
import { initHomepagePopup } from '../../resources/front/js/homepage-popup.js';
function fixture() {
 const doc = { body: { style: { overflow: 'auto' } }, activeElement: { focus() { doc.restored = true; } } };
 const close = { focus() { doc.focused = true; }, addEventListener(type, callback) { this[type] = callback; } };
 const dialog = { open: false, listeners: {}, querySelector() { return close; }, addEventListener(type, callback) { this.listeners[type] = callback; }, showModal() { this.open = true; }, close() { this.open = false; this.listeners.close(); }, getBoundingClientRect() { return { left: 10, right: 100, top: 10, bottom: 100 }; } };
 doc.getElementById = () => dialog;
 const win = { listeners: {}, addEventListener(type, callback) { this.listeners[type] = callback; } };
 initHomepagePopup(doc, win); return { doc, dialog, close, win };
}
test('opens immediately focuses close and locks scrolling', () => { const {doc,dialog}=fixture(); assert.equal(dialog.open,true); assert.equal(doc.focused,true); assert.equal(doc.body.style.overflow,'hidden'); });
test('close button and native Escape close restore focus and scrolling', () => { for (const method of ['button','escape']) { const {doc,dialog,close}=fixture(); if(method==='button') close.click(); else dialog.close(); assert.equal(dialog.open,false); assert.equal(doc.restored,true); assert.equal(doc.body.style.overflow,'auto'); } });
test('backdrop dismisses but clicking inside dialog does not', () => { const {dialog}=fixture(); dialog.listeners.click({ target:dialog,clientX:50,clientY:50 }); assert.equal(dialog.open,true); dialog.listeners.click({ target:dialog,clientX:0,clientY:0 }); assert.equal(dialog.open,false); });
test('fresh visit and browser back show popup again without dismissal storage', () => { const first=fixture(); first.close.click(); assert.equal(fixture().dialog.open,true); first.win.listeners.pageshow({persisted:true}); assert.equal(first.dialog.open,true); first.win.listeners.pagehide(); assert.equal(first.dialog.open,false); assert.equal(first.doc.body.style.overflow,'auto'); });
test('missing popup or unsupported dialog leaves homepage usable', () => { initHomepagePopup({getElementById:()=>null},{}); initHomepagePopup({getElementById:()=>({})},{}); });
