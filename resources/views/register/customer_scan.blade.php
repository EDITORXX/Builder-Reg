<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Scan QR — {{ $builder->name }}</title>
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=instrument-sans:400,500,600,700" rel="stylesheet" />
    <link rel="stylesheet" href="{{ asset('css/app.css') }}">
    <script src="https://unpkg.com/html5-qrcode@2.3.8/html5-qrcode.min.js"></script>
</head>
<body>
    <div class="login-page">
        <div class="login-container register-wide">
            <div class="login-card register-card">
                <div class="login-logo">
                    <span class="login-logo-icon">BP</span>
                    <span class="login-logo-text">{{ $builder->name }}</span>
                </div>
                <h1 class="login-title">Scan QR to check-in</h1>
                <p class="login-subtitle">Point your camera at the check-in QR code from your channel partner.</p>
                <p id="scan-error" style="display: none; margin: 0 0 1rem 0; padding: 0.75rem; background: rgb(220 38 38 / 0.08); border-radius: var(--radius); color: var(--error); font-size: 0.875rem;"></p>
                <div id="reader" style="width: 100%; max-width: 280px; margin: 0 auto 1rem;"></div>
                <p style="font-size: 0.875rem;"><a href="{{ route('register.customer', $builder->slug) }}">← Back to registration</a></p>
            </div>
        </div>
    </div>
    <script>
(function () {
    var checkInPath = '{{ url("/visit/checkin/") }}';
    var reader = new Html5Qrcode('reader');
    var errorEl = document.getElementById('scan-error');
    function showError(msg) {
        errorEl.textContent = msg;
        errorEl.style.display = 'block';
    }
    reader.start(
        { facingMode: 'environment' },
        { fps: 5 },
        function (decodedText) {
            try {
                var url = decodedText;
                if (url.indexOf('/visit/checkin/') !== -1) {
                    reader.stop();
                    window.location.href = url;
                    return;
                }
                var u = new URL(url);
                if (u.pathname.indexOf('/visit/checkin/') !== -1) {
                    reader.stop();
                    window.location.href = url;
                    return;
                }
            } catch (e) {}
            showError('Invalid QR. Please scan the check-in QR from your channel partner.');
        },
        function () {}
    ).catch(function (err) {
        showError('Could not start camera. Please allow camera access or try again.');
    });
})();
    </script>
</body>
</html>
