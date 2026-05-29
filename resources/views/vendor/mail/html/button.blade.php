@props([
    'url',
    'color' => 'primary',
    'align' => 'center',
])

@php
    $bg = '#2f6fae';
    $hoverBg = '#265d93';

    if ($color === 'success') {
        $bg = '#10b981';
        $hoverBg = '#0e9f6e';
    }

    if ($color === 'danger') {
        $bg = '#c65a58';
        $hoverBg = '#b14f4d';
    }
@endphp

<table role="presentation" cellpadding="0" cellspacing="0" width="100%">
    <tr>
        <td align="{{ $align }}" style="padding: 28px 0;">
            <a
                href="{{ $url }}"
                style="
                    display: inline-block;
                    background: {{ $bg }};
                    color: #ffffff;
                    text-decoration: none;
                    font-weight: 700;
                    font-size: 16px;
                    line-height: 1.1;
                    padding: 14px 18px;
                    border-radius: 16px;
                    border: 2px solid {{ $bg }};
                "
            >
                {{ $slot }}
            </a>
        </td>
    </tr>
</table>

