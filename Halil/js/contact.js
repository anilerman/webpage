(function () {
  var form = document.getElementById('contactForm');
  if (!form) return;

  var responseBox = document.getElementById('responseMessage');
  var submitBtn = form.querySelector('button[type="submit"]');
  var message = document.getElementById('message');
  var charCount = document.getElementById('charCount');
  var MIN_CHARS = 20;

  function updateCharCount() {
    if (!charCount) return;
    var len = message.value.length;
    charCount.textContent = len + ' / ' + MIN_CHARS;
    charCount.classList.toggle('ok', len >= MIN_CHARS);
  }

  if (message && charCount) {
    message.addEventListener('input', updateCharCount);
    updateCharCount();
  }

  form.addEventListener('input', function (e) {
    if (e.target.matches('input, textarea')) {
      e.target.classList.add('touched');
    }
  });

  form.addEventListener('submit', function (e) {
    e.preventDefault();

    if (!form.checkValidity()) {
      Array.prototype.forEach.call(form.querySelectorAll('input, textarea'), function (el) {
        el.classList.add('touched');
      });
      form.reportValidity();
      return;
    }

    // Honeypot: if a bot filled the hidden "website" field, pretend it worked
    // and stop, so it doesn't retry with a smarter payload.
    var honeypot = form.querySelector('#website');
    if (honeypot && honeypot.value) {
      responseBox.innerHTML = "<div style='color: green; font-weight: bold;'>Mesajınız başarıyla gönderildi. Teşekkür ederiz!</div>";
      form.reset();
      updateCharCount();
      return;
    }

    submitBtn.disabled = true;
    submitBtn.classList.add('is-loading');
    responseBox.innerHTML = '';

    fetch('send_email.php', {
      method: 'POST',
      headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
      body: new URLSearchParams(new FormData(form)).toString()
    })
      .then(function (res) { return res.text(); })
      .then(function (html) {
        responseBox.innerHTML = html;
        form.reset();
        updateCharCount();
        Array.prototype.forEach.call(form.querySelectorAll('input, textarea'), function (el) {
          el.classList.remove('touched');
        });
      })
      .catch(function () {
        responseBox.innerHTML = "<div style='color: red; font-weight: bold;'>Bir hata oluştu. Lütfen tekrar deneyin.</div>";
      })
      .finally(function () {
        submitBtn.disabled = false;
        submitBtn.classList.remove('is-loading');
      });
  });
})();
