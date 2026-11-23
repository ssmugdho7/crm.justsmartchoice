const assert = require('node:assert/strict');
const {sample,sunny}=require('../modules/solar_pro/assets/js/solar-roof.js');
const g=require('../modules/solar_pro/assets/vendor/geotiff/geotiff.js');
(async()=>{
 const bytes=g.writeArrayBuffer(new Uint8Array([0,1,128,255]),{width:2,height:2,ModelPixelScale:[1,1,0],ModelTiepoint:[0,0,0,100,202,0],ProjectedCSTypeGeoKey:32610});
 const image=await (await g.fromArrayBuffer(bytes)).getImage();
 const layer={width:image.getWidth(),height:image.getHeight(),bounds:image.getBoundingBox(),bands:await image.readRasters()};
 assert.equal(sample(layer,100.5,201.5),0);
 assert.equal(sample(layer,101.5,200.5),255);
 assert.ok(Number.isNaN(sample(layer,99,201)));
 assert.equal(sunny(1,1),true); assert.equal(sunny(1,2),false);
 assert.equal(sunny(1<<30,31),true); assert.equal(sunny(-9999,21),null);
 assert.equal(sunny(1<<21,22),true); assert.equal(sunny(4294957297,21),null);
 console.log('PASS real GeoTIFF decoding, geospatial orientation, nodata and Google day-bit shading');
})().catch(e=>{console.error(e);process.exit(1);});
