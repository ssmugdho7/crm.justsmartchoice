/**
 * Appointly - Core JavaScript
 * This file contains core functionality that should be loaded on all Appointly pages
 */

// Control console logging (set to false to disable logs)

(function () {
  var enableLogging = false;
  var appointlyEnv = null;
  
  setTimeout(() => {
    appointlyEnv = window.AppointlyEnv || 'production'; // fallback to production if not defined
    enableLogging = appointlyEnv === 'development';

  
  if (window.location.href.includes('appointly')) {
    if (appointlyEnv === 'development' || appointlyEnv === 'testing') {
      enableLogging = true;
    }
  } else {
    enableLogging = false;
  }
  
  if (!enableLogging) {
    var noOp = function () {};

    // Store original console methods
    var originalConsole = {
      log: console.log,
      info: console.info,
      warn: console.warn,
      error: console.error,
      debug: console.debug,
    };

    // Create a backup reference for when actual logging is needed
    window.appointlyConsole = Object.assign({}, originalConsole);

    // Replace all console methods with empty functions
    console.log = noOp;
    console.info = noOp;
    console.warn = noOp;
    console.debug = noOp;

    // Optionally keep console.error functioning for critical errors
    // console.error = noOp; // Uncomment to disable error logs too
  }
}, 1000);
})();

/**
 * Appointly Utilities - Collection of useful common functions
 */
var AppointlyUtils = (function () {
  /**
   * Convert PHP date format to Moment.js format
   * @param {string} phpFormat The PHP date format string
   * @return {string} The equivalent Moment.js format string
   */
  function convertPHPDateFormatToMoment(phpFormat) {
    // Common PHP to Moment.js format mappings
    var replacements = {
      d: "DD",
      j: "D",
      m: "MM",
      n: "M",
      Y: "YYYY",
      y: "YY",
      F: "MMMM",
      M: "MMM",
    };

    // Replace each PHP format character with its Moment.js equivalent
    var momentFormat = phpFormat;
    for (var phpChar in replacements) {
      momentFormat = momentFormat.replace(
        new RegExp(phpChar, "g"),
        replacements[phpChar]
      );
    }

    return momentFormat;
  }

  // Public API
  return {
    convertPHPDateFormatToMoment: convertPHPDateFormatToMoment,
  };
})();
