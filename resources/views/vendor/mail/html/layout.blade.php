<!DOCTYPE html>
<html lang="nl">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="x-apple-disable-message-reformatting">
    <title>{{ config('app.name') }}</title>
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

        .content {
            font-size: 16px;
            line-height: 1.6;
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
    <table class="wrapper" role="presentation" cellpadding="0" cellspacing="0">
        <tr>
            <td>
                <table class="container" role="presentation" cellpadding="0" cellspacing="0">
                    @isset($header)
                        <tr>
                            <td style="padding: 0 0 14px 0;">
                                {{ $header }}
                            </td>
                        </tr>
                    @endisset

                    <tr>
                        <td class="card">
                            <div class="content">
                                {{ $slot }}
                            </div>

                            @isset($subcopy)
                                <div class="divider"></div>
                                <div class="muted">
                                    {{ $subcopy }}
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
