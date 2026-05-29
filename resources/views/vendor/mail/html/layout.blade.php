<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="x-apple-disable-message-reformatting">
    <meta name="color-scheme" content="light">
    <meta name="supported-color-schemes" content="light">
    <title>{{ config('mail.brand') }}</title>
    {!! $head ?? '' !!}
    <style>
        body {
            margin: 0 !important;
            padding: 0 !important;
            background-color: #f7f9fc;
            color: #1f334a;
            font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, Arial, "Noto Sans", "Apple Color Emoji", "Segoe UI Emoji", sans-serif;
        }

        a {
            color: #2f6fae;
        }

        .wrapper {
            width: 100%;
            background-color: #f7f9fc;
            padding: 24px 12px;
        }

        .container {
            width: 100%;
            max-width: 600px;
            margin: 0 auto;
        }

        .card {
            background-color: #ffffff;
            border: 2px solid #d7e1ec;
            border-radius: 18px;
            padding: 20px;
        }

        .mail-header {
            text-align: center;
            padding: 4px 0 22px 0;
        }

        .mail-header img {
            display: block;
            margin: 0 auto;
            border: 0;
            outline: none;
            text-decoration: none;
        }

        .content {
            font-size: 16px;
            line-height: 1.6;
        }

        .content h1 {
            color: #1a2b40;
            font-size: 22px;
            font-weight: 800;
            margin: 0 0 16px 0;
            line-height: 1.3;
        }

        .content p {
            margin: 0 0 16px 0;
            color: #1f334a;
        }

        .content p:last-child {
            margin-bottom: 0;
        }

        .muted {
            color: #667d94;
            font-size: 14px;
        }

        .divider {
            height: 1px;
            background-color: #d7e1ec;
            margin: 18px 0;
        }

        @media (min-width: 640px) {
            .wrapper {
                padding: 28px 16px;
            }

            .card {
                padding: 26px;
            }

            .content {
                font-size: 18px;
            }
        }
    </style>
</head>

<body>
    <table class="wrapper" role="presentation" cellpadding="0" cellspacing="0" width="100%">
        <tr>
            <td>
                <table class="container" role="presentation" cellpadding="0" cellspacing="0" width="100%">
                    <tr>
                        <td class="card">
                            @isset($header)
                                <div class="mail-header">
                                    {{ $header }}
                                </div>
                            @endisset

                            <div class="content">
                                {!! Illuminate\Mail\Markdown::parse($slot) !!}
                            </div>

                            @isset($subcopy)
                                <div class="divider"></div>
                                <div class="muted">
                                    {!! Illuminate\Mail\Markdown::parse($subcopy) !!}
                                </div>
                            @endisset
                        </td>
                    </tr>

                    @isset($footer)
                        <tr>
                            <td style="padding: 14px 0 0 0;">
                                {{ $footer }}
                            </td>
                        </tr>
                    @endisset
                </table>
            </td>
        </tr>
    </table>
</body>

</html>
