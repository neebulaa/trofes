<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
</head>
<body style="margin:0; padding:0; background:#f6f7fb; font-family:Arial, sans-serif;">
    <table width="100%" cellpadding="0" cellspacing="0" style="background:#f6f7fb; padding:24px 0;">
        <tr>
        <td align="center">
            <table width="600" cellpadding="0" cellspacing="0" style="background:#ffffff; border-radius:14px; overflow:hidden; box-shadow:0 10px 30px rgba(0,0,0,.06);">
            <tr>
                <td style="padding:22px 24px; background:#0f172a;">
                <div style="color:#fff; font-size:18px; font-weight:700;">
                    {{ config('app.name') }}
                </div>
                </td>
            </tr>

            <tr>
                <td style="padding:26px 24px; color:#111827;">
                <h1 style="margin:0 0 10px; font-size:22px;">Reset Password</h1>
                <p style="margin:0 0 16px; color:#4b5563; font-size:14px; line-height:1.6;">
                    Halo {{ $user->name ?? 'Pengguna' }}, kami menerima permintaan untuk reset password akun kamu.
                    Klik tombol di bawah ini untuk membuat password baru.
                </p>

                <div style="margin:22px 0;">
                    <a href="{{ $url }}"
                    style="display:inline-block; background:#16a34a; color:#fff; text-decoration:none; padding:12px 18px; border-radius:10px; font-weight:700; font-size:14px;">
                    Reset Password
                    </a>
                </div>

                <p style="margin:0 0 8px; color:#6b7280; font-size:13px; line-height:1.6;">
                    Jika tombol tidak bisa diklik, copy link ini:
                </p>
                <p style="word-break:break-all; margin:0; color:#111827; font-size:12px;">
                    {{ $url }}
                </p>

                <hr style="border:none; border-top:1px solid #e5e7eb; margin:22px 0;">

                <p style="margin:0; color:#6b7280; font-size:12px; line-height:1.6;">
                    Jika kamu tidak meminta reset password, abaikan email ini.
                </p>
                </td>
            </tr>

            <tr>
                <td style="padding:16px 24px; background:#f9fafb; color:#6b7280; font-size:12px;">
                © {{ date('Y') }} {{ config('app.name') }}. All rights reserved.
                </td>
            </tr>
            </table>
        </td>
        </tr>
    </table>
</body>
</html>
