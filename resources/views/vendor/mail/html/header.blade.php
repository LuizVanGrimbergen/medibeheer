@props(['url'])

<table role="presentation" cellpadding="0" cellspacing="0" width="100%">
    <tr>
        <td style="padding: 0;">
            <a
                href="{{ $url }}"
                style="
                    display: inline-block;
                    font-size: 18px;
                    font-weight: 800;
                    letter-spacing: 0.2px;
                    text-decoration: none;
                    color: #1a2b40;
                "
            >
                {{ $slot }}
            </a>
        </td>
    </tr>
</table>

