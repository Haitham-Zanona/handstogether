<!DOCTYPE html>
<html>

<head>
    <meta charset="utf-8">
    <title>رسالة تواصل جديدة</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            line-height: 1.6;
            color: #333;
            max-width: 600px;
            margin: 0 auto;
            padding: 20px;
            direction: rtl;
        }

        .header {
            background-color: #f8f9fa;
            padding: 20px;
            text-align: center;
            border-radius: 5px;
            margin-bottom: 20px;
        }

        .contact-info {
            background-color: #fff;
            border: 1px solid #dee2e6;
            border-radius: 5px;
            padding: 20px;
            margin-bottom: 20px;
        }

        .contact-item {
            margin-bottom: 10px;
        }

        .contact-label {
            font-weight: bold;
            color: #495057;
        }

        .message-content {
            background-color: #f8f9fa;
            border: 1px solid #dee2e6;
            border-radius: 5px;
            padding: 20px;
            margin-bottom: 20px;
        }

        .footer {
            text-align: center;
            color: #6c757d;
            font-size: 14px;
            margin-top: 30px;
        }
    </style>
</head>

<body>
    <div class="header">
        <h1>رسالة تواصل جديدة</h1>
        <p>تم استلام رسالة تواصل جديدة من خلال الموقع</p>
    </div>

    <div class="contact-info">
        <div class="contact-item">
            <span class="contact-label">الاسم:</span>
            <span>{{ $contact->name }}</span>
        </div>
        <div class="contact-item">
            <span class="contact-label">الهاتف:</span>
            <span>{{ $contact->phone }}</span>
        </div>
        <div class="contact-item">
            <span class="contact-label">البريد الإلكتروني:</span>
            <span>{{ $contact->email }}</span>
        </div>
        <div class="contact-item">
            <span class="contact-label">الموضوع:</span>
            <span>{{ $contact->subject }}</span>
        </div>
        <div class="contact-item">
            <span class="contact-label">عنوان IP:</span>
            <span>{{ $contact->ip_address }}</span>
        </div>
        <div class="contact-item">
            <span class="contact-label">التاريخ والوقت:</span>
            <span>{{ $contact->created_at->format('Y-m-d H:i:s') }}</span>
        </div>
    </div>

    <div class="message-content">
        <h3>محتوى الرسالة:</h3>
        <p>{{ $contact->message }}</p>
    </div>

    <div class="footer">
        <p>هذه الرسالة تم إنشاؤها تلقائياً من نظام إدارة الموقع</p>
        <p>© {{ date('Y') }} جميع الحقوق محفوظة</p>
    </div>
</body>

</html>