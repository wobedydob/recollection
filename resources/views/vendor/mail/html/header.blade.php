@props(['url'])
<tr>
<td class="header">
<a href="{{ $url }}" style="display: inline-block;">
<span style="font-size: 32px; margin-right: 8px;">✨</span>
<span style="background: linear-gradient(135deg, #e879a9 0%, #a855f7 100%); -webkit-background-clip: text; -webkit-text-fill-color: transparent; background-clip: text;">{{ $slot }}</span>
</a>
</td>
</tr>
