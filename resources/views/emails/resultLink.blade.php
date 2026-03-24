<!DOCTYPE html>
<html>
<body style="font-family:sans-serif;background:#f8fafc;padding:32px 0;margin:0;">
  <div style="max-width:480px;margin:0 auto;background:#fff;border-radius:16px;
              border:1px solid #e2e8f0;padding:36px 32px;box-shadow:0 4px 24px rgba(0,0,0,.07);">

    <div style="font-size:12px;color:#64748b;text-transform:uppercase;
                letter-spacing:.08em;margin-bottom:8px;">Your Result</div>

    <h2 style="margin:0 0 8px;font-size:22px;color:#0f172a;">{{ $appName }}</h2>

    <p style="color:#475569;font-size:15px;margin:0 0 28px;">
      Your <strong>{{ $moduleLabel }}</strong> result is ready.
      Click the button below to view it.
    </p>

    <a href="{{ $fullUrl }}"
       style="display:inline-block;background:#9e363a;color:#fff;text-decoration:none;
              padding:14px 28px;border-radius:12px;font-weight:700;font-size:15px;
              margin-bottom:24px;">
      View My Result &rarr;
    </a>

    <p style="color:#94a3b8;font-size:12px;margin:0 0 8px;">Or copy this link:</p>
    <p style="color:#475569;font-size:12px;word-break:break-all;
              background:#f1f5f9;padding:10px 12px;border-radius:8px;margin:0 0 24px;">
      {{ $fullUrl }}
    </p>

    <hr style="border:none;border-top:1px solid #e2e8f0;margin:0 0 16px;">
    <p style="color:#94a3b8;font-size:12px;margin:0;">
      Sent by {{ $appName }}. Do not share this link.
    </p>

  </div>
</body>
</html>