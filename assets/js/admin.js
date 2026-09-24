document.querySelectorAll('[data-confirm]').forEach(form=>form.addEventListener('submit',event=>{if(!confirm(form.dataset.confirm))event.preventDefault()}));
const toggle=document.querySelector('.nav-toggle'),nav=document.querySelector('.admin-nav');
if(toggle&&nav)toggle.addEventListener('click',()=>{const open=toggle.getAttribute('aria-expanded')==='true';toggle.setAttribute('aria-expanded',String(!open));nav.classList.toggle('open',!open)});

