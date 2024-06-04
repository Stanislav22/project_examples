@php
use App\Facades\Settings;
@endphp

<tr>
<td class="header">
<a href="{{ $url }}" style="display: inline-block;">
    @if(Settings::get('emailing.logo'))
        <img src="{{ Settings::get('emailing.logo')->getFullUrl() }}" height="70" width="120" style="object-fit: contain;" alt="logo" title="logo">
    @else
        <img src="{{asset('ogo.png')}}" height="50" width="100" style="object-fit: contain;" alt="logo" title="logo">
    @endif
</a>
</td>
</tr>
