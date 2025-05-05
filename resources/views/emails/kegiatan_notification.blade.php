<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Notifikasi Kegiatan Baru</title>
</head>
<body style="font-family: Arial, sans-serif; background-color: #f4f4f4; margin: 0; padding: 10px;">
    <table align="center" width="100%" style="max-width: 480px; background-color: #ffffff; border-radius: 8px; padding: 15px; box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);">
        <tr>
            <td style="text-align: left; font-size: 14px; color: #333;">
                <h2 style="color: #333; text-align: center; margin: 10px 0;">Pengajuan Kegiatan Baru</h2>
                <p style="color: #666; font-size: 14px; margin: 10px 0;">Halo, ada pengajuan kegiatan baru yang perlu ditinjau.</p>
                
                <table width="100%" style="margin: 10px 0; border-collapse: collapse;">
                    <tr>
                        <td style="padding: 5px 0;"><strong>Kegiatan:</strong></td>
                        <td style="padding: 5px 0;">{{ $kegiatan->nama_kegiatan }}</td>
                    </tr>
                    <tr>
                        <td style="padding: 5px 0;"><strong>Ruangan:</strong></td>
                        <td style="padding: 5px 0;"><span style="background-color: #ffc107; padding: 3px 6px; border-radius: 4px;">{{ $kegiatan->ruangan->nama }}</td>
                    </tr>
                    <tr>
                        <td style="padding: 5px 0;"><strong>Waktu:</strong></td>
                        <td style="padding: 5px 0;">{{ $kegiatan->waktu_mulai }} - {{ $kegiatan->waktu_selesai }}</td>
                    </tr>
                    <tr>
                        <td style="padding: 5px 0;"><strong>Pemohon:</strong></td>
                        <td style="padding: 5px 0;">{{ $kegiatan->user->name }}</td>
                    </tr>
                </table>

                <div style="text-align: center; margin-top: 10px;">
                    <a href="http://10.10.10.27/admin/kegiatan" target="_blank" style="display: inline-block; background-color: #007bff; color: #ffffff; padding: 8px 16px; border-radius: 4px; text-decoration: none; font-size: 14px;">Lihat Kegiatan</a>
                </div>

                <p style="color: #888; font-size: 12px; margin-top: 10px; text-align: center;">Email ini dikirim otomatis, mohon tidak membalas.</p>
            </td>
        </tr>
    </table>
</body>
</html>
