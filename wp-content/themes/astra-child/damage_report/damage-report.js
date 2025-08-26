(function(){
  function $(sel){ return document.querySelector(sel); }
  const form = $('#damage-report-form');
  if (!form) return;
  const fileInput = $('#damage_images');
  const fileInfo  = document.querySelector('.dr-file-info');
  const MAX_MB = 5;
  const MAX_BYTES = MAX_MB * 1024 * 1024;
  const ALLOWED = ['image/jpeg','image/png','image/webp'];

  if (fileInput && fileInfo) {
    fileInput.addEventListener('change', function(){
      const files = Array.from(fileInput.files || []);
      if (!files.length) { fileInfo.textContent = 'No files chosen'; return; }
      // Validate
      const badType = files.find(f => !ALLOWED.includes(f.type));
      const tooBig  = files.find(f => f.size > MAX_BYTES);
      if (badType) {
        alert('Only JPG, PNG, WEBP images are allowed.');
        fileInput.value = '';
        fileInfo.textContent = 'No files chosen';
        return;
      }
      if (tooBig) {
        alert('Each image must be 5MB or less.');
        fileInput.value = '';
        fileInfo.textContent = 'No files chosen';
        return;
      }
      const names = files.map(f => f.name).join(', ');
      fileInfo.textContent = names;
    });
  }

  form.addEventListener('submit', function(e){
    e.preventDefault();

    const fd = new FormData(form);
    fd.append('action', 'submit_damage_report');
    if (window.DamageReport && DamageReport.nonce) {
      fd.append('damage_report_nonce', DamageReport.nonce);
    }
    if (window.DamageReport && Number(DamageReport.userId) > 0) {
      fd.append('user_id', String(DamageReport.userId));
    }

    // Basic validation
    const pid = fd.get('property_id');
    const desc = (fd.get('damage_description') || '').toString().trim();
    const sev  = fd.get('severity');
    if (!pid || !desc || !sev) {
      alert('Please select property, set severity and enter description.');
      return;
    }

    // Validate files again before sending
    if (fileInput && fileInput.files && fileInput.files.length) {
      const files = Array.from(fileInput.files);
      const badType = files.find(f => !ALLOWED.includes(f.type));
      const tooBig  = files.find(f => f.size > MAX_BYTES);
      if (badType) { alert('Only JPG, PNG, WEBP images are allowed.'); return; }
      if (tooBig)  { alert('Each image must be 5MB or less.'); return; }
    }

    const ajaxUrl = (window.DamageReport && DamageReport.ajaxUrl) || '/wp-admin/admin-ajax.php';
    // Optional debug to console for diagnosing 401 due to origin mismatch
    try { if (window.console && console.debug) console.debug('DamageReport AJAX ->', ajaxUrl); } catch (e) {}

    fetch(ajaxUrl, {
      method: 'POST',
      body: fd,
      credentials: 'same-origin'
    })
    .then(r => r.json())
    .then(data => {
      if (data && data.success) {
        try { console.log('[DamageReport] Email recipient:', data.to_email); } catch(e) {}
        alert('Damage report submitted. ID: ' + data.report_id);
        window.location.reload();
      } else {
        alert((data && data.message) ? data.message : 'Failed to submit damage report.');
      }
    })
    .catch(err => {
      console.error('Damage report error:', err);
      alert('Unexpected error while submitting the report.');
    });
  });
})();
