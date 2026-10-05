// Run with NODE_PATH pointing to a test runtime containing jsdom and jquery.
const assert = require('node:assert/strict');
const fs = require('node:fs');
const {JSDOM} = require('jsdom');
const dom = new JSDOM(`<section id="sc-service-catalog"><select id="product_categories" multiple></select><div class="no_product hidden"></div><div id="filter_html" class="sc-product-grid" aria-busy="false"></div></section><div id="scProductOptionsModal"><div class="product-row"><h4 class="modal-title"></h4><div id="scProductOptionsBody"></div><button class="add_cart">Add to Cart</button></div></div>`, {runScripts:'outside-only',url:'https://catalog.example/'});
const w = dom.window;
w.eval(fs.readFileSync(require.resolve('jquery'),'utf8'));
const $ = w.jQuery;
const products = [
 {id:1,product_name:'Zeta service',product_description:'Paint your walls',p_category_name:'Painting',rate:'100',quantity_number:8,is_digital:0,recurring:0,product_gallery_urls:[],product_image_url:'/uploads/sc-zeta-service.jpg',variations:[],add_to_cart:'Add to Cart'},
 {id:2,product_name:'Alpha "safe" service',product_description:'Doors',p_category_name:'Doors',rate:'120',quantity_number:9,is_digital:0,recurring:0,product_gallery_urls:['/uploads/door.jpg','/uploads/door2.jpg'],variations:[{id:41,variation_id:5,variation_name:'Door Width',variation_value:'32 inch',rate:'150',quantity_number:3}],add_to_cart:'Add to Cart'},
 {id:3,product_name:'Unavailable',product_description:'No stock',p_category_name:'Roofing',rate:'50',quantity_number:0,is_digital:0,recurring:0,product_image_url:'/uploads/',out_of_stock:'Out of stock'},
];
let posted, postMode='success', lastAlert;
w.site_url='https://catalog.example/'; w.appSelectPicker=()=>{}; w.alert_float=(type,message)=>{lastAlert={type,message};};
$.fn.selectpicker=function(action,value){if(action==='val')this.val(value);return this;};
$.fn.modal=function(action){this.attr('data-open',action==='show'?'true':'false');this.trigger(action==='show'?'shown.bs.modal':'hidden.bs.modal');return this;};
$.ajax=function(options){options.success(products);};
$.getJSON=()=>({done(fn){fn([{quantity:2},{quantity:3}]);return this;}});
$.post=function(url,payload,callback){posted={url,payload};if(postMode!=='failure')callback(postMode==='invalid'?'<!doctype html>':JSON.stringify([{quantity:payload.quantity}]));return {fail(fn){if(postMode==='failure')fn();return this;},always(fn){fn();return this;}};};
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
  const rows=()=>Array.from(doc.querySelectorAll('#filter_html > .product-row'));
  assert.equal(rows().length,3);
  assert.deepEqual(rows().map(row=>row.dataset.catalogName),['Alpha "safe" service','Zeta service','Unavailable'],'cards with images come first, preserving order within each group');
  assert.equal(rows()[1].querySelector('img'),null,'an explicitly empty gallery never falls back to a discarded template');
  assert.equal(rows()[1].querySelector('.sc-product-cover').textContent,'Zeta service');
  assert.equal(rows()[2].querySelector('img'),null,'empty upload URL becomes cover');
  const alpha=rows()[0];
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
  $(alpha.querySelector('.qty-plus')).trigger('click');
  assert.equal(alpha.querySelector('[name=quantity]').value,'2');
  assert.equal(alpha.querySelector('select'),null,'cards have no inline dropdowns');
  assert.equal(alpha.querySelector('.variations'),null,'cards do not reserve an options area');
  $(alpha.querySelector('.add_cart')).trigger('click');
  const modal=doc.querySelector('#scProductOptionsModal');
  assert.equal(modal.dataset.open,'true','Add to Cart opens options dialog');
  assert.equal(posted,undefined,'opening dialog does not add an unconfigured product');
  assert.equal(modal.querySelector('.modal-title').textContent,'Alpha "safe" service');
  assert.equal(modal.querySelector('[name=quantity]').value,'2','dialog starts with card quantity');
  $(modal.querySelector('.add_cart')).trigger('click');
  assert.equal(posted,undefined,'a selection is required before posting');
  const select=modal.querySelector('select'); select.value='41'; $(select).trigger('change');
  assert.equal(modal.querySelector('[name=product_variation_id]').value,'41');
  assert.equal(modal.querySelector('.product-price').textContent,'150');
  assert.equal(modal.querySelector('[name=quantity]').max,'3');
  modal.querySelector('[name=quantity]').value='4';
  $(modal.querySelector('.add_cart')).trigger('click');
  assert.equal(posted,undefined,'stock validation remains active in dialog');
  modal.querySelector('[name=quantity]').value='2';
  postMode='failure'; $(modal.querySelector('.add_cart')).trigger('click');
  assert.equal(modal.dataset.open,'true','failed request keeps dialog open');
  assert.equal(modal.querySelector('.add_cart').disabled,false,'failed request can be retried');
  assert.equal(lastAlert.type,'danger');
  postMode='invalid'; $(modal.querySelector('.add_cart')).trigger('click');
  assert.equal(modal.dataset.open,'true','invalid response is not treated as success');
  postMode='success'; $(modal.querySelector('.add_cart')).trigger('click');
  assert.equal(posted.url,'https://catalog.example/products/client/add_cart');
  assert.deepEqual(JSON.parse(JSON.stringify(posted.payload)),{quantity:2,product_id:'2',product_variation_id:'41'});
  assert.equal(modal.dataset.open,'false','successful add closes the dialog');
  assert.equal(modal.querySelector('select'),null,'closing clears the dialog');
  assert.equal(alpha.querySelector('.add_cart').textContent,'Update Cart');
  $(alpha.querySelector('.add_cart')).trigger('click');
  assert.equal(modal.querySelector('select').value,'','reopening does not reuse stale selections');
  $(modal).modal('hide');
  $(rows()[1].querySelector('.add_cart')).trigger('click');
  assert.equal(posted.payload.product_id,'1','products without options still add directly');
  assert.equal(alpha.classList.contains('col-md-4'),true,'original three-column Bootstrap cards are restored');
  assert.equal(alpha.querySelector('.thumbnail.shadow.sc-product-card') !== null,true,'original thumbnail styling is restored');
  assert.equal(alpha.querySelector('.sc-product-title').tagName,'H4','original title markup is restored');
  assert.equal(alpha.querySelector('.sc-product-image-wrap .sc-share-product'),null,'share leaves the image area');
  assert.equal(alpha.querySelector('.sc-product-body > .sc-share-product').textContent.trim(),'Share','original bottom Share button is restored');
  assert.equal(doc.querySelector('[data-catalog-name=Unavailable] button.sc-cart-btn').disabled,true);
  products.length=0; $('#product_categories').trigger('change');
  assert.equal(doc.querySelector('.no_product').classList.contains('hidden'),false,'empty response displays guidance');
  console.log('PASS compact cards, option dialog, validation, failure/retry, successful close, cart payload, gallery, escaping and empty state');
 } catch(error) { console.error(error);process.exitCode=1; }
 finally {dom.window.close();}
},30);
