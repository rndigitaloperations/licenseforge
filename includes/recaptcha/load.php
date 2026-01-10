<?php if ($recaptchaProvider === 'recaptcha_v2'): ?>
    <div class="g-recaptcha" data-sitekey="<?= htmlspecialchars($recaptchaSiteKey) ?>"></div>
<?php elseif ($recaptchaProvider === 'recaptcha_v3'): ?>
    <input type="hidden" name="g-recaptcha-response" id="recaptcha-token">
<?php elseif ($recaptchaProvider === 'cloudflare_turnstile'): ?>
    <div class="cf-turnstile" data-sitekey="<?= htmlspecialchars($recaptchaSiteKey) ?>"></div>
<?php elseif ($recaptchaProvider === 'hcaptcha'): ?>
    <div class="h-captcha" data-sitekey="<?= htmlspecialchars($recaptchaSiteKey) ?>"></div>
<?php elseif ($recaptchaProvider === 'mtcaptcha'): ?>
    <div class="mtcaptcha"></div>
<?php endif; ?>