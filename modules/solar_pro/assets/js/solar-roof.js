/* Google Solar GeoTIFFs stay in page memory; API credentials stay on the server. */
(function () {
  'use strict';
  const days = [31,28,31,30,31,30,31,31,30,31,30,31];
  function sample(layer, x, y, band = 0) {
    const b = layer.bounds;
    const col = Math.floor((x - b[0]) / (b[2] - b[0]) * layer.width);
    const row = Math.floor((b[3] - y) / (b[3] - b[1]) * layer.height);
    if (col < 0 || row < 0 || col >= layer.width || row >= layer.height) return NaN;
    return layer.bands[band][row * layer.width + col];
  }
  function sunny(value, day) { return value >= 0 && value <= 0x7fffffff && Number.isFinite(value) ? !!(value & (1 << (day - 1))) : null; }
  // Expose pure sampling for fixture tests, without requiring the DOM.
  if (typeof module === 'object' && module.exports) { module.exports = { sample, sunny }; return; }
  document.querySelectorAll('[data-roof-url]').forEach(root => {
    const $ = selector => root.querySelector(selector);
    const spanish = document.documentElement.lang.startsWith('es') || $('.sp-roof-load').textContent.includes('Cargar');
    const t = (en, es) => spanish ? es : en;
    const status = $('.sp-roof-status'), button = $('.sp-roof-load'), canvas = $('canvas');
    const mode = $('.sp-roof-mode'), month = $('.sp-roof-month'), day = $('.sp-roof-day'), hour = $('.sp-roof-hour');
    const base = root.dataset.roofUrl;
    let rgb, mask, annual, shade, shadeMonth, metadata, sequence = 0;
    async function request(layer) {
      const response = await fetch(base + layer, { credentials: 'same-origin', cache: 'no-store' });
      if (!response.ok) throw new Error('unavailable');
      return response;
    }
    async function raster(name, samples) {
      const response = await request(name);
      const tiff = await GeoTIFF.fromArrayBuffer(await response.arrayBuffer());
      const image = await tiff.getImage();
      if (image.getWidth() * image.getHeight() > 2000000) throw new Error('size');
      const bands = await image.readRasters(samples ? { samples } : {});
      return { image, bands, width:image.getWidth(), height:image.getHeight(), bounds:image.getBoundingBox(), crs:image.getGeoKeys().ProjectedCSTypeGeoKey };
    }
    async function render() {
      const revision = ++sequence;
      $('.sp-roof-time').hidden = mode.value !== 'shade';
      const m = Number(month.value), d = Math.max(1, Math.min(days[m-1], Number(day.value) || 1));
      day.max = days[m-1]; day.value = d;
      $('output').value = hour.value.padStart(2, '0') + ':00';
      status.textContent = t('Loading imagery…','Cargando imágenes…');
      try {
        let layer = annual;
        if (mode.value === 'shade') {
          if (!metadata.shadeMonths.includes(m)) throw new Error('shade');
          if (shadeMonth !== m) {
            const loaded = await raster('shade-' + m);
            if (revision !== sequence) return;
            shade = loaded; shadeMonth = m;
          }
          layer = shade;
        }
        if (revision !== sequence) return;
        if (![rgb,mask,layer].every(l => l.crs === annual.crs)) throw new Error('alignment');
        const w = annual.width, h = annual.height, b = annual.bounds;
        canvas.width = w; canvas.height = h;
        const context = canvas.getContext('2d'), pixels = context.createImageData(w,h);
        for (let row=0;row<h;row++) for(let col=0;col<w;col++) {
          const x=b[0]+(col+.5)/w*(b[2]-b[0]), y=b[3]-(row+.5)/h*(b[3]-b[1]);
          const at=(row*w+col)*4;
          const photo=[0,1,2].map(band => sample(rgb,x,y,band));
          let color=null;
          if (sample(mask,x,y)>0) {
            const value=sample(layer,x,y,mode.value==='shade'?Number(hour.value):0);
            if (mode.value==='shade') {
              const lit=sunny(value,d);
              if(lit!==null) color=lit?[255,222,62]:[55,32,115];
            } else if(Number.isFinite(value)&&value>=0) {
              const a=Math.min(1,value/2000);
              color=a<.5?[67+a*2*144,21+a*2*57,140-a*2*60]:[211+(a-.5)*2*44,78+(a-.5)*2*162,80-(a-.5)*2*19];
            }
          }
          for(let band=0;band<3;band++) pixels.data[at+band]=color ? .75*color[band]+.25*(photo[band]||0) : photo[band]||0;
          pixels.data[at+3]=255;
        }
        context.putImageData(pixels,0,0);
        canvas.hidden=false;
        $('.sp-roof-legend').hidden=false;
        $('.sp-roof-legend span:last-child').textContent=mode.value==='shade'
          ? t('Purple: shaded / no direct sun · Yellow: direct sun','Morado: sombra / sin sol directo · Amarillo: sol directo')
          : t('0 → 2,000+ kWh/kW/year · Less → more sunlight','0 → 2.000+ kWh/kW/año · Menos → más luz solar');
        $('.sp-roof-gradient').style.background=mode.value==='shade'?'linear-gradient(to right,#372073 50%,#ffde3e 50%)':'';
        status.textContent='';
      } catch (_) {
        if(revision!==sequence)return;
        canvas.hidden=true; $('.sp-roof-legend').hidden=true;
        status.textContent=t('This layer is unavailable. Load imagery again or try another month.','Esta capa no está disponible. Cargue de nuevo o pruebe otro mes.');
      }
    }
    button.addEventListener('click', async () => {
      button.disabled=true; status.textContent=t('Loading rooftop imagery…','Cargando imágenes del techo…');
      try {
        metadata=await (await request('manifest')).json();
        [rgb,mask,annual]=await Promise.all([raster('rgb',[0,1,2]),raster('mask',[0]),raster('annual',[0])]);
        shade=null; shadeMonth=null;
        $('.sp-roof-controls').hidden=false; $('.sp-roof-credit').hidden=false;
        const date=metadata.date;
        $('.sp-roof-credit span').textContent=[metadata.quality,[date.year,date.month,date.day].filter(Boolean).join('-')].filter(Boolean).join(' · ');
        await render();
      } catch (_) {
        status.textContent=t('Google rooftop imagery is unavailable for this property. The production estimate is unchanged. You can try again shortly.','Las imágenes de Google no están disponibles para esta propiedad. La estimación de producción no cambia. Puede reintentar en breve.');
      } finally {button.disabled=false;}
    });
    [mode,month].forEach(el=>el.addEventListener('change',()=>{if(annual)render();}));
    [day,hour].forEach(el=>el.addEventListener('input',()=>{if(annual)render();}));
  });
})();
