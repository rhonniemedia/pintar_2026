@props(['school', 'letterTitle', 'letterNumber' => null])

{{-- KOP SURAT --}}
<table class="table-header" style="border-bottom: 2px solid black;">
    <tr>
        <td style="width: 15%; text-align:center;">
            <img src="{{ public_path('assets/images/icons/bengkulu.png') }}" alt="" class="logo-img">
        </td>
        <td>
            <p class="department">PEMERINTAH PROVINSI {{ strtoupper($school->province ?? '-') }}</p>
            <p class="sub-department">{{ strtoupper($school->name) }}</p>
            <p class="address-1">
                {{ $school->address }}, {{ $school->village }}, {{ $school->district }},
                {{ $school->regency }}, {{ $school->province }}, {{ $school->postal_code }}
            </p>
            <p class="address-2">
                Telepon {{ $school->phone }}, Laman {{ $school->website }}, Pos-el {{ $school->email }}
            </p>
        </td>
        <td style="width: 15%; text-align:center;">
            @if ($school->logo_path)
            <img src="{{ public_path('storage/' . $school->logo_path) }}" alt="" class="logo-img">
            @else
            <img src="{{ public_path('assets/images/icons/smk.png') }}" alt="" class="logo-img">
            @endif
        </td>
    </tr>
</table>

{{-- JUDUL & NOMOR SURAT --}}
<table class="table-title">
    <tr>
        <td class="title">{{ $letterTitle }}</td>
    </tr>
    @if ($letterNumber)
    <tr>
        <td class="subtitle">Nomor: {{ $letterNumber }}</td>
    </tr>
    @endif
</table>