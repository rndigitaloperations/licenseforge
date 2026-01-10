<?php
$currentPath = $_SERVER['REQUEST_URI'];
$recaptchaAction = 'default';
if (preg_match('#/login(\.php)?$#', $currentPath)) {
    $recaptchaAction = 'login';
} elseif (preg_match('#/register(\.php)?$#', $currentPath)) {
    $recaptchaAction = 'register';
}
?>

<?php if (in_array($recaptchaProvider, ['recaptcha_v2'])): ?>
    <script src="https://www.google.com/recaptcha/api.js" async defer></script>
<?php elseif ($recaptchaProvider === 'recaptcha_v3'): ?>
    <script src="https://www.google.com/recaptcha/api.js?render=<?= htmlspecialchars($recaptchaSiteKey) ?>"></script>
    <script>
        grecaptcha.ready(function() {
            grecaptcha.execute('<?= htmlspecialchars($recaptchaSiteKey) ?>', {action: '<?= $recaptchaAction ?>'}).then(function(token) {
                document.getElementById('recaptcha-token').value = token;
            });
        });
    </script>
<?php elseif ($recaptchaProvider === 'cloudflare_turnstile'): ?>
    <script src="https://challenges.cloudflare.com/turnstile/v0/api.js" async defer></script>
<?php elseif ($recaptchaProvider === 'hcaptcha'): ?>
    <script src="https://js.hcaptcha.com/1/api.js" async defer></script>
<?php elseif ($recaptchaProvider === 'mtcaptcha'): ?>
    <script>
    var mtcaptchaConfig = {
      "sitekey": "<?= htmlspecialchars($recaptchaSiteKey) ?>"
    };
   (function(){
      var mt_service = document.createElement('script');
      mt_service.async = true;
      mt_service.src = 'https://service.mtcaptcha.com/mtcv1/client/mtcaptcha.min.js';
      (document.getElementsByTagName('head')[0] 
       || document.getElementsByTagName('body')[0]).appendChild(mt_service);

      var mt_service2 = document.createElement('script');
      mt_service2.async = true;
      mt_service2.src = 'https://service2.mtcaptcha.com/mtcv1/client/mtcaptcha2.min.js';
      (document.getElementsByTagName('head')[0] 
       || document.getElementsByTagName('body')[0]).appendChild(mt_service2);
   })();
   </script>
<?php endif; ?>