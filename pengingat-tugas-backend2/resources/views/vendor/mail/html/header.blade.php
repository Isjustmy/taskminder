@props(['url'])
<tr>
    <td class="header">
        <a href="{{ $url }}" style="display: inline-block;">
            @if (trim($slot) === 'Taskminder')
            <img src="{{ asset('assets/taskminder_logo.png') }}" class="logo" alt="Logo Taskminder" style="max-width: 150px;">
            @else
            {{ $slot }}
            @endif
        </a>
    </td>
</tr>



