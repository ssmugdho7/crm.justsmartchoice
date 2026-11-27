/**
 * Unit tests for sc-address-parser.js
 * Run with: node tests/sc_address_parser_test.js
 */

const { parseAddress, normalizeState, ENTITY_FIELD_MAPS, PLAIN_FIELDS } = require('../assets/js/sc-address-parser.js');

function test(description, fn) {
  try {
    fn();
    console.log('✓ ' + description);
  } catch (err) {
    console.error('✗ ' + description);
    console.error('  Error: ' + err.message);
    process.exit(1);
  }
}

function assert(condition, message) {
  if (!condition) throw new Error(message || 'Assertion failed');
}

function assertDeepEqual(actual, expected, message) {
  if (JSON.stringify(actual) !== JSON.stringify(expected)) {
    throw new Error((message || 'Deep equal failed') + '\n  Expected: ' + JSON.stringify(expected) + '\n  Got: ' + JSON.stringify(actual));
  }
}

console.log('Testing sc-address-parser...\n');

// Test 1: Standard format with commas and ZIP
test('Parse "123 Main St, Tampa, FL 33601"', function () {
  var result = parseAddress('123 Main St, Tampa, FL 33601');
  assertDeepEqual(result, { city: 'Tampa', state: 'FL', zip: '33601' });
});

// Test 2: Format with commas, no ZIP
test('Parse "456 Oak Ave, Miami, FL"', function () {
  var result = parseAddress('456 Oak Ave, Miami, FL');
  assertDeepEqual(result, { city: 'Miami', state: 'FL' });
});

// Test 3: Format without commas, with ZIP and state code
test('Parse "789 Elm Road Jacksonville FL 32099"', function () {
  var result = parseAddress('789 Elm Road Jacksonville FL 32099');
  assertDeepEqual(result, { city: 'Jacksonville', state: 'FL', zip: '32099' });
});

// Test 4: Screenshot example with lowercase and dot
test('Parse "123 Main street Tampa Fl."', function () {
  var result = parseAddress('123 Main street Tampa Fl.');
  assertDeepEqual(result, { city: 'Tampa', state: 'FL' });
});

// Test 5: With trailing "USA"
test('Parse "321 Pine St, Orlando, FL 34711, USA"', function () {
  var result = parseAddress('321 Pine St, Orlando, FL 34711, USA');
  assertDeepEqual(result, { city: 'Orlando', state: 'FL', zip: '34711' });
});

// Test 6: With "United States"
test('Parse "987 Maple Dr, Clearwater, FL 33755, United States"', function () {
  var result = parseAddress('987 Maple Dr, Clearwater, FL 33755, United States');
  assertDeepEqual(result, { city: 'Clearwater', state: 'FL', zip: '33755' });
});

// Test 7: Full state name (FLORIDA)
test('Parse "111 Cedar Ln, Tampa, FLORIDA 33602"', function () {
  var result = parseAddress('111 Cedar Ln, Tampa, FLORIDA 33602');
  assertDeepEqual(result, { city: 'Tampa', state: 'FL', zip: '33602' });
});

// Test 8: State with trailing dot
test('Parse "222 Birch Ct, Miami, FL. 33101"', function () {
  var result = parseAddress('222 Birch Ct, Miami, FL. 33101');
  assertDeepEqual(result, { city: 'Miami', state: 'FL', zip: '33101' });
});

// Test 9: Format with AVENUE suffix (Ave)
test('Parse "333 Broadway Ave St Petersburg FL 33701"', function () {
  var result = parseAddress('333 Broadway Ave St Petersburg FL 33701');
  assertDeepEqual(result, { city: 'St Petersburg', state: 'FL', zip: '33701' });
});

// Test 10: Format with STREET suffix
test('Parse "444 Main Street Gainesville FL 32601"', function () {
  var result = parseAddress('444 Main Street Gainesville FL 32601');
  assertDeepEqual(result, { city: 'Gainesville', state: 'FL', zip: '32601' });
});

// Test 11: Hyphenated ZIP
test('Parse "555 Park Dr, Jacksonville, FL 32099-1234"', function () {
  var result = parseAddress('555 Park Dr, Jacksonville, FL 32099-1234');
  assertDeepEqual(result, { city: 'Jacksonville', state: 'FL', zip: '32099-1234' });
});

// Test 12: No ZIP, no commas (should use street suffix)
test('Parse "666 Oak Avenue Tallahassee FL"', function () {
  var result = parseAddress('666 Oak Avenue Tallahassee FL');
  assertDeepEqual(result, { city: 'Tallahassee', state: 'FL' });
});

// Test 13: Reject invalid state
test('Parse invalid state returns null', function () {
  var result = parseAddress('777 Elm St, Daytona, XX 32015');
  assert(result === null, 'Should return null for invalid state');
});

// Test 14: Empty string
test('Parse empty string returns null', function () {
  var result = parseAddress('');
  assert(result === null, 'Should return null for empty string');
});

// State normalization tests
test('normalizeState("FL") returns "FL"', function () {
  var result = normalizeState('FL');
  assert(result === 'FL', 'Got: ' + result);
});

test('normalizeState("fl") returns "FL"', function () {
  var result = normalizeState('fl');
  assert(result === 'FL', 'Got: ' + result);
});

test('normalizeState("FLORIDA") returns "FL"', function () {
  var result = normalizeState('FLORIDA');
  assert(result === 'FL', 'Got: ' + result);
});

test('normalizeState("florida") returns "FL"', function () {
  var result = normalizeState('florida');
  assert(result === 'FL', 'Got: ' + result);
});

test('normalizeState("FL.") returns "FL"', function () {
  var result = normalizeState('FL.');
  assert(result === 'FL', 'Got: ' + result);
});

test('normalizeState("XX") returns null', function () {
  var result = normalizeState('XX');
  assert(result === null, 'Got: ' + result);
});


// Edge cases: no commas, ambiguous street suffix. Rule: the LAST street-suffix
// token that still leaves a city wins.
test('Parse "12 Court Ln Tampa FL" (Court is street name, Ln is suffix)', function () {
  assertDeepEqual(parseAddress('12 Court Ln Tampa FL'), { city: 'Tampa', state: 'FL' });
});

test('Parse "12 Court Ln Tampa FL 33602"', function () {
  assertDeepEqual(parseAddress('12 Court Ln Tampa FL 33602'), { city: 'Tampa', state: 'FL', zip: '33602' });
});

test('Parse "50 Park Way Dr Orlando FL"', function () {
  assertDeepEqual(parseAddress('50 Park Way Dr Orlando FL'), { city: 'Orlando', state: 'FL' });
});

test('Parse "9 Lake Dr St Augustine FL 32080" keeps saint city', function () {
  assertDeepEqual(parseAddress('9 Lake Dr St Augustine FL 32080'), { city: 'St Augustine', state: 'FL', zip: '32080' });
});

test('Parse "100 Main St Suite 5 Tampa FL" skips unit', function () {
  assertDeepEqual(parseAddress('100 Main St Suite 5 Tampa FL'), { city: 'Tampa', state: 'FL' });
});

test('Parse "100 Main St. Tampa, FL 33602"', function () {
  assertDeepEqual(parseAddress('100 Main St. Tampa, FL 33602'), { city: 'Tampa', state: 'FL', zip: '33602' });
});

test('Parse one-comma "45 Oak Ave, Orlando FL 32801"', function () {
  assertDeepEqual(parseAddress('45 Oak Ave, Orlando FL 32801'), { city: 'Orlando', state: 'FL', zip: '32801' });
});

test('Parse "45 Oak Ave, Orlando, FL 32801" (browser test value)', function () {
  assertDeepEqual(parseAddress('45 Oak Ave, Orlando, FL 32801'), { city: 'Orlando', state: 'FL', zip: '32801' });
});

test('Parse "10 Pine Rd, Tampa, FL 33602" (lead/client browser value)', function () {
  assertDeepEqual(parseAddress('10 Pine Rd, Tampa, FL 33602'), { city: 'Tampa', state: 'FL', zip: '33602' });
});

test('Parse "123 Main St, Tampa, Florida"', function () {
  assertDeepEqual(parseAddress('123 Main St, Tampa, Florida'), { city: 'Tampa', state: 'FL' });
});

test('No street suffix and no commas returns null', function () {
  assert(parseAddress('123 Something Tampa FL') === null, 'should not guess');
});

test('Incomplete address "123 Main" returns null', function () {
  assert(parseAddress('123 Main') === null);
});

// Field maps use the same entity keys as sc_google_map_field() in PHP.
test('Field maps: project and contract keys, plain lead/client', function () {
  assert(ENTITY_FIELD_MAPS.project, 'project map missing');
  assert(!ENTITY_FIELD_MAPS.projects, 'stale projects key');
  assertDeepEqual(ENTITY_FIELD_MAPS.project.address, ['[name="custom_fields[projects][89]"]', '[name="custom_fields[projects][140]"]']);
  assert(ENTITY_FIELD_MAPS.project.city === '[name="custom_fields[projects][141]"]');
  assert(ENTITY_FIELD_MAPS.contract.zip === '[name="custom_fields[contracts][217]"]');
  assert(ENTITY_FIELD_MAPS.lead === PLAIN_FIELDS && ENTITY_FIELD_MAPS.client === PLAIN_FIELDS);
  assertDeepEqual(PLAIN_FIELDS.address, ['[name="address"]']);
});

console.log('\n✓ All tests passed!');
