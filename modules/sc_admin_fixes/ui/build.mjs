import {build} from 'esbuild';
import {copyFile} from 'node:fs/promises';
await build({entryPoints:['toasts.jsx'],bundle:true,minify:true,define:{'process.env.NODE_ENV':'"production"'},format:'iife',target:'es2020',outfile:'../assets/js/vendor-sonner.js'});
await copyFile('node_modules/sonner/dist/styles.css','../assets/css/vendor-sonner.css');
