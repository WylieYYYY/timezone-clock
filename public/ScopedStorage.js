'use strict';

window.ScopedStorage = function(namespace) {
  this.varPrefix = `var:${namespace},`;
  if (ScopedStorage._items[this.varPrefix] === undefined) {
    ScopedStorage._items[this.varPrefix] = {};
    for (let i = 0; i < sessionStorage.length; i++) {
      const key = sessionStorage.key(i);
      if (!key.startsWith(this.varPrefix)) continue;
      ScopedStorage._items[this.varPrefix][
          key.substring(this.varPrefix.length)] = sessionStorage.getItem(key);
    }
  }
  this.clear = function() {
    ScopedStorage._items[this.varPrefix] = {};
    for (let i = 0; i < sessionStorage.length; i++) {
      const key = sessionStorage.key(i);
      if (!key.startsWith(this.varPrefix)) continue;
      sessionStorage.removeItem(key);
      i--;
    }
  };
  this.getItem = function(key) {
    return ScopedStorage._items[this.varPrefix][key] ===
        undefined ? null : ScopedStorage._items[this.varPrefix][key];
  };
  this.key = function() {
    return Object.keys(ScopedStorage._items[this.varPrefix]);
  };
  this.setItem = function(key, value) {
    ScopedStorage._items[this.varPrefix][key] = value;
    sessionStorage.setItem(this.varPrefix + key, value);
  };
  this.removeItem = function(key) {
    delete ScopedStorage._items[this.varPrefix][key];
    sessionStorage.removeItem(this.varPrefix + key);
  };
};
window.ScopedStorage._items = {};
