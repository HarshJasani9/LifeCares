// auth-endpoints.js — include on auth.html after auth.js
function wireAjax(selector, endpoint){
  const form = document.querySelector(selector);
  if (!form) return;
  form.addEventListener('submit', async (e) => {
    e.preventDefault();
    const btn = form.querySelector('button[type="submit"]');
    if (btn) { btn.disabled = true; btn.textContent = 'Please wait…'; }
    try {
      const res = await fetch(endpoint, {
        method: 'POST',
        headers: { 'Accept': 'application/json' },
        body: new FormData(form)
      });
      const data = await res.json();
      if (data.ok) {
        location.href = data.redirect || 'dashboard.php';
      } else {
        alert(data.error || 'Request failed.');
      }
    } catch (err) {
      alert('Network error. Try again.');
    } finally {
      if (btn) { btn.disabled = false; btn.textContent = btn.dataset.label || btn.textContent; }
    }
  });
}

wireAjax('form.signup', 'register.php');
wireAjax('form.signin', 'login.php');
