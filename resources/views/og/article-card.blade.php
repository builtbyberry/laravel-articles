<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <style>
        :root {
            --page-bg: #0c0d11;
            --page-fg: #edf1f5;
            --accent-soft: #c0a37a;
            --hairline: rgba(255, 255, 255, 0.10);
            --quiet: #737373;
        }
        * { box-sizing: border-box; }
        html, body {
            margin: 0;
            padding: 0;
            width: 1200px;
            height: 630px;
            background: var(--page-bg);
            color: var(--page-fg);
            font-family: ui-sans-serif, system-ui, sans-serif;
            font-weight: 400;
        }
        .card {
            width: 1200px;
            height: 630px;
            padding: 72px 80px;
            display: flex;
            flex-direction: column;
            justify-content: space-between;
        }
        .brand-name {
            font-size: 36px;
            font-weight: 600;
            color: var(--accent-soft);
        }
        .eyebrow {
            font-size: 18px;
            font-weight: 600;
            text-transform: uppercase;
            letter-spacing: 0.32em;
            color: var(--accent-soft);
            margin-bottom: 32px;
        }
        .title {
            font-size: 72px;
            font-weight: 600;
            line-height: 1.05;
            margin: 0;
            max-width: 1040px;
        }
        .footer {
            padding-top: 28px;
            border-top: 1px solid var(--hairline);
            font-size: 18px;
            color: var(--quiet);
        }
    </style>
</head>
<body>
    <div class="card">
        <div class="brand-name">{{ $siteName ?? 'Articles' }}</div>
        <div>
            @if (! empty($eyebrow))
                <div class="eyebrow">{{ $eyebrow }}</div>
            @endif
            <h1 class="title">{{ $title }}</h1>
        </div>
        <div class="footer">{{ $footerUrl ?? '' }}</div>
    </div>
</body>
</html>
