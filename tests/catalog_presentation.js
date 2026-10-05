// Run with NODE_PATH pointing to a test runtime containing jsdom and jquery.
const assert = require('node:assert/strict');
const fs = require('node:fs');
const {JSDOM} = require('jsdom');
const dom = new JSDOM(`<section id="sc-service-catalog"><select id="product_categories" multiple></select><div class="no_product hidden"></div><div id="filter_html" class="sc-product-grid" aria-busy="false"></div></section>`, {runScripts:'outside-only',url:'https://catalog.example/'});
const w = dom.window;
w.eval(fs.readFileSync(require.resolve('jquery'),'utf8'));
const $ = w.jQuery;
const products = [
 {id:1,product_name:'Zeta service',product_description:'Paint your walls',p_category_name:'Painting',rate:'100',quantity_number:8,is_digital:0,recurring:0,product_gallery_urls:[],product_image_url:'/uploads/sc-zeta-service.jpg',variations:[],add_to_cart:'Add to Cart'},
 {id:2,product_name:'Alpha "safe" service',product_description:'Doors',p_category_name:'Doors',rate:'120',quantity_number:9,is_digital:0,recurring:0,product_gallery_urls:['/uploads/door.jpg','/uploads/door2.jpg'],variations:[{id:41,variation_id:5,variation_name:'Door Width',variation_value:'32 inch',rate:'150',quantity_number:3}],add_to_cart:'Add to Cart'},
 {id:3,product_name:'Unavailable',product_description:'No stock',p_category_name:'Roofing',rate:'50',quantity_number:0,is_digital:0,recurring:0,product_image_url:'/uploads/',out_of_stock:'Out of stock'},
];
let posted;
w.site_url='https://catalog.example/'; w.appSelectPicker=()=>{}; w.alert_float=()=>{};
$.fn.selectpicker=function(action,value){if(action==='val')this.val(value);return this;};
$.fn.modal=function(){return this;};
$.ajax=function(options){options.success(products);};
$.getJSON=()=>({done(fn){fn([{quantity:2},{quantity:3}]);return this;}});
$.post=function(url,payload,callback){posted={url,payload};callback(JSON.stringify([{quantity:payload.quantity}]));return {always(fn){fn();return this;}};};
w.eval(fs.readFileSync('modules/products/assets/js/client_products.js','utf8') + '\nwindow.__testEscape = scEscape;');
let escapeNodes = 0;
const originalCreateElement = w.document.createElement.bind(w.document);
w.document.createElement = function(...args) { escapeNodes++; return originalCreateElement(...args); };
assert.equal(w.__testEscape('<>&"\''), '&lt;&gt;&amp;&quot;&#39;');
assert.equal(w.__testEscape(null), ''); assert.equal(w.__testEscape(0), '0');
assert.equal(w.__testEscape('é 😀'), 'é 😀');
assert.equal(escapeNodes, 0, 'Escaping does not allocate a DOM node per field');
w.document.createElement = originalCreateElement;
w.document.dispatchEvent(new w.Event('DOMContentLoaded'));
setTimeout(()=>{
 try {
  const doc=w.document;
  const rows=()=>Array.from(doc.querySelectorAll('.product-row'));
  assert.equal(rows().length,3);
  assert.equal(rows()[0].querySelector('img'),null,'an explicitly empty gallery never falls back to a discarded template');
  assert.equal(rows()[0].querySelector('.sc-product-cover').textContent,'Zeta service');
  assert.equal(rows()[2].querySelector('img'),null,'empty upload URL becomes cover');
  const alpha=rows()[1];
  assert.equal(alpha.dataset.catalogName,'Alpha "safe" service','quotes cannot break attributes');
  assert.equal(alpha.querySelector('img').getAttribute('src'),'/uploads/door.jpg');
  assert.equal(alpha.querySelector('.sc-product-cover').hidden,false,'name cover remains visible while a photo is loading');
  alpha.querySelector('img').dispatchEvent(new w.Event('error'));
  assert.equal(alpha.querySelector('.sc-product-cover').hidden,false,'broken image shows fallback');
  alpha.querySelector('img').dispatchEvent(new w.Event('load'));
  assert.equal(alpha.querySelector('.sc-product-cover').hidden,true,'gallery recovery restores photo');
  $(alpha.querySelector('.sc-slider-next')).trigger('click');
  assert.equal(alpha.querySelector('img').getAttribute('src'),'/uploads/door2.jpg');
  assert.equal(alpha.querySelector('.sc-product-cover').hidden,false,'gallery transition keeps name cover visible');
  const select=alpha.querySelector('select'); select.value='41'; $(select).trigger('change');
  assert.equal(alpha.querySelector('[name=product_variation_id]').value,'41');
  assert.equal(alpha.querySelector('.product-price').textContent,'150');
  assert.equal(alpha.querySelector('[name=quantity]').max,'3');
  $(alpha.querySelector('.qty-plus')).trigger('click');
  assert.equal(alpha.querySelector('[name=quantity]').value,'2');
  $(alpha.querySelector('.add_cart')).trigger('click');
  assert.equal(posted.url,'https://catalog.example/products/client/add_cart');
  assert.deepEqual(JSON.parse(JSON.stringify(posted.payload)),{quantity:2,product_id:'2',product_variation_id:'41'});
  assert.equal(alpha.classList.contains('col-md-4'),true,'original three-column Bootstrap cards are restored');
  assert.equal(alpha.querySelector('.thumbnail.shadow.sc-product-card') !== null,true,'original thumbnail styling is restored');
  assert.equal(alpha.querySelector('.sc-product-title').tagName,'H4','original title markup is restored');
  assert.equal(alpha.querySelector('.sc-product-image-wrap .sc-share-product'),null,'share leaves the image area');
  assert.equal(alpha.querySelector('.sc-product-body > .sc-share-product').textContent.trim(),'Share','original bottom Share button is restored');
  assert.equal(doc.querySelector('[data-catalog-name=Unavailable] button.sc-cart-btn').disabled,true);
  products.length=0; $('#product_categories').trigger('change');
  assert.equal(doc.querySelector('.no_product').classList.contains('hidden'),false,'empty response displays guidance');
  console.log('PASS catalog covers, escaping, gallery recovery, options, stock, stepper, cart payload, original card layout and empty state');
 } catch(error) { console.error(error);process.exitCode=1; }
 finally {dom.window.close();}
},30);
