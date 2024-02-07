@props(['url'])
<tr>
<td class="header">
<a href="{{ $url }}" style="display: inline-block;">
@if (trim($slot) === 'Taskminder')
<!-- untuk sementara, gambar icon Taskminder tidak bisa ditampilkan. (ON TARGET FIXING BUG) -->
<img src="../../../../../public/assets/taskminder_logo 1 (mini 150x150).png" class="logo" alt="Taskminder Logo">
@else
{{ $slot }}
@endif
</a>
</td>
</tr>
