(function(){
  "use strict";
  if (typeof Event !== "undefined" && !Event.prototype.querySelectorAll) {
    Object.defineProperty(Event.prototype, "querySelectorAll", {
      configurable: true,
      value: function(selector){ return document.querySelectorAll(selector); }
    });
  }
})();
