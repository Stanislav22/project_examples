@props(['store'])
@php
use App\Helpers\Format;
@endphp
<table width="100%" class="store-details-container">
    <tbody>
    <tr>
        <td>
            <table width="100%" class="store-details">
                <tr>
                    <td><strong>Dispatch location:</strong> {{Format::address($store->addresses->first())}}</td>
                </tr>
                <tr>
                    <td><strong>Store name:</strong> {{$store->name}}</td>
                </tr>
                <tr>
                    <td><strong>Store e-mail:</strong> {{$store->email}}</td>
                </tr>
                <tr>
                    <td><strong>Location phone:</strong> {{Format::phone($store->phone)}}</td>
                </tr>
            </table>
        </td>
    </tr>
    </tbody>
</table>

<style>
    table {
        font-size: 12px;
    }
    .store-details-container {
        padding: 32px;
        border-left: 1px solid #E7E7E9;
        border-right: 1px solid #E7E7E9;
    }
    .store-details {
        border-top: 1px solid #E7E7E9;
        border-bottom: 1px solid #E7E7E9;
    }
    .store-details td {
        padding: 7px 0;
    }
</style>
