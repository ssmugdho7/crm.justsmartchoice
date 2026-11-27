/**
 * Smart address parser for form autofill
 * Supports multiple address formats and custom field mappings
 */

(function (global) {
  // US states: 2-letter codes and full names
  const US_STATES = {
    'AL': 'AL', 'AK': 'AK', 'AZ': 'AZ', 'AR': 'AR', 'CA': 'CA', 'CO': 'CO', 'CT': 'CT', 'DE': 'DE',
    'FL': 'FL', 'GA': 'GA', 'HI': 'HI', 'ID': 'ID', 'IL': 'IL', 'IN': 'IN', 'IA': 'IA', 'KS': 'KS',
    'KY': 'KY', 'LA': 'LA', 'ME': 'ME', 'MD': 'MD', 'MA': 'MA', 'MI': 'MI', 'MN': 'MN', 'MS': 'MS',
    'MO': 'MO', 'MT': 'MT', 'NE': 'NE', 'NV': 'NV', 'NH': 'NH', 'NJ': 'NJ', 'NM': 'NM', 'NY': 'NY',
    'NC': 'NC', 'ND': 'ND', 'OH': 'OH', 'OK': 'OK', 'OR': 'OR', 'PA': 'PA', 'RI': 'RI', 'SC': 'SC',
    'SD': 'SD', 'TN': 'TN', 'TX': 'TX', 'UT': 'UT', 'VT': 'VT', 'VA': 'VA', 'WA': 'WA', 'WV': 'WV',
    'WI': 'WI', 'WY': 'WY', 'DC': 'DC',
    'ALABAMA': 'AL', 'ALASKA': 'AK', 'ARIZONA': 'AZ', 'ARKANSAS': 'AR', 'CALIFORNIA': 'CA',
    'COLORADO': 'CO', 'CONNECTICUT': 'CT', 'DELAWARE': 'DE', 'FLORIDA': 'FL', 'GEORGIA': 'GA',
    'HAWAII': 'HI', 'IDAHO': 'ID', 'ILLINOIS': 'IL', 'INDIANA': 'IN', 'IOWA': 'IA', 'KANSAS': 'KS',
    'KENTUCKY': 'KY', 'LOUISIANA': 'LA', 'MAINE': 'ME', 'MARYLAND': 'MD', 'MASSACHUSETTS': 'MA',
    'MICHIGAN': 'MI', 'MINNESOTA': 'MN', 'MISSISSIPPI': 'MS', 'MISSOURI': 'MO', 'MONTANA': 'MT',
    'NEBRASKA': 'NE', 'NEVADA': 'NV', 'NEW HAMPSHIRE': 'NH', 'NEW JERSEY': 'NJ', 'NEW MEXICO': 'NM',
    'NEW YORK': 'NY', 'NORTH CAROLINA': 'NC', 'NORTH DAKOTA': 'ND', 'OHIO': 'OH', 'OKLAHOMA': 'OK',
    'OREGON': 'OR', 'PENNSYLVANIA': 'PA', 'RHODE ISLAND': 'RI', 'SOUTH CAROLINA': 'SC',
    'SOUTH DAKOTA': 'SD', 'TENNESSEE': 'TN', 'TEXAS': 'TX', 'UTAH': 'UT', 'VERMONT': 'VT',
    'VIRGINIA': 'VA', 'WASHINGTON': 'WA', 'WEST VIRGINIA': 'WV', 'WISCONSIN': 'WI', 'WYOMING': 'WY',
    'DISTRICT OF COLUMBIA': 'DC'
  };

  // Street suffixes used to split "street city" when the address has no commas.
  var STREET_SUFFIX_RE = /^(ST|STREET|AVE|AV|AVENUE|RD|ROAD|DR|DRIVE|BLVD|BOULEVARD|LN|LANE|CT|COURT|WAY|HWY|HIGHWAY|PL|PLACE|TER|TERRACE|CIR|CIRCLE|PKWY|PARKWAY|TRL|TRAIL)\.?$/i;
  // Unit designators that may follow the street suffix ("Suite 5", "Apt 2B", "#4").
  var UNIT_RE = /^(SUITE|STE|APT|APARTMENT|UNIT|BLDG|FL|FLOOR|RM|ROOM)\.?$/i;
  // Words that follow "St" in a city name ("St Petersburg", "Port St Lucie").
  // When "St" is followed by one of these and an earlier street suffix exists,
  // that "St" is read as part of the city, not as the street suffix.
  var SAINT_CITY_WORDS = /^(PETERSBURG|PETE|AUGUSTINE|CLOUD|LUCIE|JAMES|MARKS|LOUIS|PAUL|GEORGE|JOSEPH|CHARLES|HELENA|SIMONS|MARYS|JOHNS|CROIX|ALBANS|CLAIR|MICHAEL|MATTHEWS|ROSE|FRANCISVILLE|MARTINVILLE)$/i;

  function cleanToken(token) {
    return String(token || '').replace(/[,.]+$/, '');
  }

  /**
   * Split "street city" (no commas) into the city part.
   * Rule: use the LAST street-suffix token that still leaves a city after it,
   * so "12 Court Ln Tampa" splits after "Ln" (city "Tampa"), not after "Court".
   * Exception: "St" followed by a saint-city word (Petersburg, Augustine, Lucie...)
   * is treated as part of the city when an earlier suffix exists, so
   * "333 Broadway Ave St Petersburg" gives "St Petersburg".
   * Unit designators right after the suffix ("Suite 5") stay with the street.
   * Returns the city string, or null when no safe split exists.
   */
  function cityFromStreetCity(tokens) {
    var suffixIdx = [];
    for (var i = 1; i < tokens.length; i++) {
      if (STREET_SUFFIX_RE.test(cleanToken(tokens[i]))) suffixIdx.push(i);
    }
    for (var k = suffixIdx.length - 1; k >= 0; k--) {
      var idx = suffixIdx[k];
      var word = cleanToken(tokens[idx]).toUpperCase();
      var next = cleanToken(tokens[idx + 1]);
      if (word === 'ST' && k > 0 && SAINT_CITY_WORDS.test(next)) continue;
      var cityStart = idx + 1;
      if (tokens[cityStart] && UNIT_RE.test(cleanToken(tokens[cityStart])) && tokens[cityStart + 1]) {
        cityStart += 2;
      } else if (tokens[cityStart] && /^#/.test(tokens[cityStart])) {
        cityStart += 1;
      }
      var city = tokens.slice(cityStart).map(cleanToken).join(' ').trim();
      if (city && !/^\d/.test(city)) return city;
    }
    return null;
  }

  /**
   * Parse various address formats
   * Supports:
   *   - 'street, city, ST zip'
   *   - 'street, city, ST' (no zip)
   *   - 'street, city ST zip' (one comma)
   *   - 'street city ST zip' (no commas, requires a state and a street suffix)
   *   - Full state names ('Florida')
   *   - Trailing 'USA' or 'United States'
   *   - State followed by dot or comma
   *
   * Returns { city: string, state: string, zip?: string } or null
   */
  function parseAddress(value) {
    var text = String(value || '').replace(/\s+/g, ' ').trim();
    if (!text) return null;

    // Remove trailing country names and their dots
    text = text.replace(/,?\s*(?:United States(?: of America)?|USA|US)\s*\.?\s*$/i, '');

    // Format 1a: "street, city, FULLSTATE zip"
    var m1a = text.match(/^(.+?),\s*([^,]+?),\s*([A-Za-z\s]+)\.?,?\s+(\d{5}(?:-\d{4})?)$/i);
    if (m1a) {
      var state1a = normalizeState(m1a[3]);
      if (state1a) return { city: m1a[2].trim(), state: state1a, zip: m1a[4] };
    }

    // Format 1: "street, city, ST zip"
    var m1 = text.match(/^(.+?),\s*([^,]+?),\s*([A-Za-z]{2})\.?,?\s+(\d{5}(?:-\d{4})?)$/i);
    if (m1) {
      var state = normalizeState(m1[3]);
      if (state) return { city: m1[2].trim(), state: state, zip: m1[4] };
    }

    // Format 2a: "street, city, FULLSTATE" (no zip)
    var m2a = text.match(/^(.+?),\s*([^,]+?),\s*([A-Za-z\s]+)\.?$/i);
    if (m2a) {
      var state2a = normalizeState(m2a[3]);
      if (state2a) return { city: m2a[2].trim(), state: state2a };
    }

    // Format 2: "street, city, ST" (no zip)
    var m2 = text.match(/^(.+?),\s*([^,]+?),\s*([A-Za-z]{2})\.?$/i);
    if (m2) {
      var state2 = normalizeState(m2[3]);
      if (state2) return { city: m2[2].trim(), state: state2 };
    }

    // Format 3: work backwards from the end: optional zip, then a state token.
    var tokens = text.split(/\s+/);
    if (tokens.length < 3) return null;

    var zip;
    var stateIdx = tokens.length - 1;
    if (/^\d{5}(?:-\d{4})?$/.test(tokens[stateIdx])) {
      zip = tokens[stateIdx];
      stateIdx -= 1;
    }
    if (stateIdx < 2) return null;

    var normalizedState = normalizeState(cleanToken(tokens[stateIdx]));
    if (!normalizedState) return null;

    var before = tokens.slice(0, stateIdx);
    var streetCity = before.join(' ');
    var city = null;

    // One comma: "street, city ST zip" -> the city follows the last comma.
    var lastComma = streetCity.replace(/,\s*$/, '').lastIndexOf(',');
    if (lastComma > 0) {
      city = streetCity.substring(lastComma + 1).replace(/,\s*$/, '').trim() || null;
    } else {
      city = cityFromStreetCity(before);
    }
    if (!city) return null;

    var result = { city: city, state: normalizedState };
    if (zip) result.zip = zip;
    return result;
  }

  /**
   * Normalize state to 2-letter code
   * Returns null if not a valid US state
   */
  function normalizeState(value) {
    if (!value) return null;
    var normalized = String(value).toUpperCase().trim();
    // Remove trailing dot
    normalized = normalized.replace(/\.+$/, '');
    return US_STATES[normalized] || null;
  }

  function cf(fieldto, id) {
    return '[name="custom_fields[' + fieldto + '][' + id + ']"]';
  }

  // Lead/client style: plain inputs named address, city, state, zip.
  // Used for leads, clients, and every entity without its own mapping
  // (invoice, estimate, proposal, credit_note), the same as the old inline script.
  var PLAIN_FIELDS = {
    address: ['[name="address"]'],
    city: '[name="city"]',
    state: '[name="state"]',
    zip: '[name="zip"]'
  };

  /**
   * Fallback field maps keyed by the entity passed to sc_google_map_field() in PHP.
   * The PHP helper normally emits a data-sc-fields map resolved from custom field
   * slugs; these IDs are used only when that attribute is missing.
   *   projects: 89 = Project Address, 140 = Property Address, 141-143 = City/State/Zip
   *   contracts: 214 = Property Address, 215-217 = City/State/Zip
   * Address fields are listed in priority order for "Create map link from address".
   */
  var ENTITY_FIELD_MAPS = {
    lead: PLAIN_FIELDS,
    client: PLAIN_FIELDS,
    project: {
      address: [cf('projects', 89), cf('projects', 140)],
      city: cf('projects', 141),
      state: cf('projects', 142),
      zip: cf('projects', 143)
    },
    contract: {
      address: [cf('contracts', 214)],
      city: cf('contracts', 215),
      state: cf('contracts', 216),
      zip: cf('contracts', 217)
    }
  };

  function validMap(map) {
    return map && Array.isArray(map.address) && map.address.length &&
      typeof map.city === 'string' && typeof map.state === 'string' && typeof map.zip === 'string';
  }

  /**
   * Resolve the field map for the form that contains `el`.
   * Order: data-sc-fields on the form's .sc-google-map-field (slug-resolved by PHP),
   * then the built-in map for its data-sc-entity, then plain names.
   */
  function fieldConfig(el) {
    var root = (el && el.closest && el.closest('form')) || document;
    var wrapper = (el && el.closest && el.closest('.sc-google-map-field')) ||
      root.querySelector('.sc-google-map-field');
    var entity = wrapper ? (wrapper.getAttribute('data-sc-entity') || '') : '';
    var map = null;
    if (wrapper && wrapper.getAttribute('data-sc-fields')) {
      try { map = JSON.parse(wrapper.getAttribute('data-sc-fields')); } catch (e) { map = null; }
    }
    if (!validMap(map)) map = ENTITY_FIELD_MAPS[entity] || PLAIN_FIELDS;
    return { root: root, wrapper: wrapper, entity: entity, map: map };
  }

  function matchesAny(el, selectors) {
    for (var i = 0; i < selectors.length; i++) {
      try { if (el.matches(selectors[i])) return true; } catch (e) { /* bad selector */ }
    }
    return false;
  }

  function find(root, selector) {
    try { return root.querySelector(selector); } catch (e) { return null; }
  }

  function targetFields(cfg) {
    return {
      city: find(cfg.root, cfg.map.city),
      state: find(cfg.root, cfg.map.state),
      zip: find(cfg.root, cfg.map.zip)
    };
  }

  function addressFields(cfg) {
    var list = [];
    cfg.map.address.forEach(function (sel) {
      var el = find(cfg.root, sel);
      if (el && list.indexOf(el) === -1) list.push(el);
    });
    return list;
  }

  function notify(input) {
    input.dispatchEvent(new Event('change', { bubbles: true }));
  }

  /**
   * Fill city/state/zip from the given address input. A field is only written
   * when it is empty or still holds the value we filled last time.
   */
  function completeAddress(input, cfg) {
    var data = parseAddress(input.value);
    if (!data) return;
    var fields = targetFields(cfg);
    ['city', 'state', 'zip'].forEach(function (key) {
      var field = fields[key];
      if (!field || !data[key]) return;
      if (field.value.trim() && field.value !== field.dataset.scAutofilled) return;
      if (field.value === data[key]) { field.dataset.scAutofilled = data[key]; return; }
      field.value = data[key];
      field.dataset.scAutofilled = data[key];
      notify(field);
    });
  }

  function setup() {
    document.querySelectorAll('.sc-google-map-field').forEach(function (wrapper) {
      var cfg = fieldConfig(wrapper);
      var map = wrapper.querySelector('[name="google_map_link"]');
      if (map) {
        map.placeholder = 'https://maps.app.goo.gl/…';
        map.setAttribute('inputmode', 'url');
      }
      addressFields(cfg).forEach(function (address) {
        if (address.dataset.scAddressHint) return;
        address.dataset.scAddressHint = '1';
        var hint = document.createElement('p');
        hint.className = 'text-muted small sc-address-format-hint';
        hint.textContent = 'Paste a full address, e.g. 123 Main St, Tampa, FL 33601. City, state and ZIP fill automatically. You can edit them.';
        var group = address.closest('.form-group') || address.parentElement;
        group.appendChild(hint);
      });
    });
  }

  function handleAddressEvent(event, isInput) {
    var target = event.target;
    if (!target || !target.matches || !/^(INPUT|TEXTAREA)$/.test(target.tagName)) return;
    var cfg = fieldConfig(target);
    if (matchesAny(target, cfg.map.address)) {
      completeAddress(target, cfg);
      return;
    }
    // The user edited a mapped city/state/zip field: stop treating it as ours.
    if (isInput && event.isTrusted !== false &&
        matchesAny(target, [cfg.map.city, cfg.map.state, cfg.map.zip])) {
      delete target.dataset.scAutofilled;
    }
  }

  if (typeof document !== 'undefined') {
    setup();
  }

  var alreadyBound = typeof window !== 'undefined' && window.scAddressAutofillReady;
  if (typeof window !== 'undefined') window.scAddressAutofillReady = true;

  if (typeof document !== 'undefined' && !alreadyBound) {
    document.addEventListener('input', function (event) { handleAddressEvent(event, true); });
    document.addEventListener('change', function (event) { handleAddressEvent(event, false); });

    document.addEventListener('click', function (event) {
      var button = event.target.closest && event.target.closest('.sc-map-from-address');
      if (!button) return;
      var cfg = fieldConfig(button);
      var wrapper = button.closest('.sc-google-map-field');
      var map = wrapper && wrapper.querySelector('[name="google_map_link"]');
      var candidates = addressFields(cfg);
      // First mapped address field with a value (projects: Project Address, then Property Address).
      var address = null;
      for (var i = 0; i < candidates.length; i++) {
        if (candidates[i].value.trim()) { address = candidates[i]; break; }
      }
      if (!address || !map) {
        if (candidates[0]) candidates[0].focus();
        return;
      }
      var fields = targetFields(cfg);
      var text = address.value.replace(/\s+/g, ' ').trim();
      if (!parseAddress(text)) {
        text = [text, fields.city && fields.city.value.trim(), fields.state && fields.state.value.trim(), fields.zip && fields.zip.value.trim()].filter(Boolean).join(', ');
      }
      map.value = 'https://www.google.com/maps/search/?api=1&query=' + encodeURIComponent(text);
      notify(map);
    });

    if (typeof window !== 'undefined' && window.jQuery) {
      window.jQuery(document).on('shown.bs.modal', setup);
    }
    if (document.readyState === 'loading') {
      document.addEventListener('DOMContentLoaded', setup);
    }
  }

  var api = {
    parseAddress: parseAddress,
    normalizeState: normalizeState,
    ENTITY_FIELD_MAPS: ENTITY_FIELD_MAPS,
    PLAIN_FIELDS: PLAIN_FIELDS
  };
  if (typeof module !== 'undefined' && module.exports) {
    module.exports = api;
  }
  global.scAddressParser = api;
})(typeof window !== 'undefined' ? window : global);
