import React from 'react';
import {createRoot} from 'react-dom/client';
import {Toaster, toast} from 'sonner';
window.SCAFSonner = toast;
function mount() {
  if (document.getElementById('scaf-sonner-root')) return;
  const node = document.createElement('div'); node.id = 'scaf-sonner-root'; document.body.appendChild(node);
  createRoot(node).render(<Toaster position="bottom-right" richColors closeButton expand duration={4500} toastOptions={{className:'scaf-toast'}} />);
}
if(document.readyState==='loading') document.addEventListener('DOMContentLoaded',mount,{once:true}); else mount();
