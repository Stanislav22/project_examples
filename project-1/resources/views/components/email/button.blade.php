@props(['text'])
<table style="border-left: 1px solid #E7E7E9; border-right: 1px solid #E7E7E9;" class="action" align="center" width="100%" cellpadding="0" cellspacing="0" role="presentation">
    <tr>
        <td align="center">
            <table width="100%" border="0" cellpadding="0" cellspacing="0" role="presentation">
                <tr>
                    <td align="center">
                        <table style="font-size: 14px; padding: 0 32px" width="100%" border="0" cellpadding="0" cellspacing="0" role="presentation">
                            <tr>
                                <td>
                                    <a style="width: 100%; text-align: center" href="{{ $slot }}" class="button button-brand" target="_blank" rel="noopener">{{ $text }}</a>
                                </td>
                            </tr>
                        </table>
                    </td>
                </tr>
            </table>
        </td>
    </tr>
</table>
