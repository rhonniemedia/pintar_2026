@props(['school', 'signer', 'signerRole' => 'Kepala Sekolah', 'date'])

<table class="table-footer">
    <tr>
        <td style="width: 50%;">&nbsp;</td>
        <td style="width: 50%;" class="sign">
            {{ $school->regency }}, {{ $date }}<br>
            {{ $signerRole }},<br><br><br><br>
            <strong>{{ $signer?->name_with_title ?? '-' }}</strong><br>
            @if ($signer?->current_grade_label)
            {{ $signer->current_grade_label }}<br>
            @endif
            NIP {{ $signer?->vault?->nip ?? '-' }}
        </td>
    </tr>
</table>