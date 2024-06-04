@php $htmlMode = false @endphp
@component('mail::message')
{{-- Greeting --}}
@if (! empty($greeting))
# {{ $greeting }}
@else
@if ($level === 'error')
# @lang('Whoops!')
@else
# @lang('Hello!')
@endif
@endif

{{-- Intro Lines --}}
@foreach ($introLines as $line)
@if ($htmlMode)
@if ($htmlMode = (trim($line) !== '<!-- /HTML -->'))
{!! $line !!}
@endif
@else
@if (! ($htmlMode = (trim($line) === '<!-- HTML -->')))
{{ $line }}
@endif
@endif

@endforeach

{{-- Action Button --}}
@isset($actionText)
<?php
    $color = match ($level) {
        'success', 'error' => $level,
        default => 'primary',
    };
?>
@component('mail::button', ['url' => $actionUrl, 'color' => $color])
{{ $actionText }}
@endcomponent
@endisset

{{-- Outro Lines --}}
@foreach ($outroLines as $line)
@if ($htmlMode)
@if ($htmlMode = (trim($line) !== '<!-- /HTML -->'))
{!! $line !!}
@endif
@else
@if (! ($htmlMode = (trim($line) === '<!-- HTML -->')))
{{ $line }}
@endif
@endif

@endforeach

{{-- Subcopy --}}
@isset($actionText)
@slot('subcopy')
@lang(
    "If you're having trouble clicking the \":actionText\" button, copy and paste the URL below\n".
    'into your web browser:',
    [
        'actionText' => $actionText,
    ]
) <span class="break-all">[{{ $displayableActionUrl }}]({{ $actionUrl }})</span>
@endslot
@endisset
@endcomponent
