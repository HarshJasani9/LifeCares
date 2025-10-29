const srole = document.getElementById('srole');
const pills = document.querySelectorAll('.role-pill');
if (srole && pills.length) {
  pills.forEach(p=>p.addEventListener('click', ()=>{
    const v = p.getAttribute('data-role');
    srole.value = v;
    pills.forEach(x=>x.classList.toggle('is-active', x===p));
  }));
  // sync on manual select change as well
  srole.addEventListener('change', ()=>{
    pills.forEach(x=>x.classList.toggle('is-active', x.getAttribute('data-role')===srole.value));
  });
}
