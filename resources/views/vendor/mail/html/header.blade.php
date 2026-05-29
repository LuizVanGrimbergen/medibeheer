@props(['url'])

<table role="presentation" cellpadding="0" cellspacing="0" width="100%">
    <tr>
        <td align="center" style="padding: 0;">
            <a
                href="{{ $url }}"
                style="display: inline-block; text-decoration: none;"
            >
                <img
                    src="{{ url('/images/medibeheer-pwa.png') }}"
                    alt="{{ trim($slot) !== '' ? trim($slot) : config('mail.brand') }}"
                    width="56"
                    height="56"
                    style="display: block; margin: 0 auto; border: 0; outline: none; text-decoration: none;"
                >
            </a>
        </td>
    </tr>
</table>
