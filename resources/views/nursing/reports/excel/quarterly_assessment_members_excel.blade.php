<table>
    <tr>
        <td colspan="6"
            style="font-size:16px; font-weight:bold; text-align:center;">
            @if ($company)
                {{ $company->comp_nm ?? $company->CompanyName ?? $company->organization_name ?? ''}}
            @endif
        </td>
    </tr>

    <tr>
        <td colspan="6"
            style="font-size:14px; font-weight:bold; text-align:center;">
            Quarterly Assessment Members
        </td>
    </tr>

    <tr>
        <td colspan="6" style="text-align:center;">
            {{ $shiftLabel }} Shift
        </td>
    </tr>

    <tr>
        <td colspan="6" style="text-align:center;">
            Assessment Month: {{ $selectedMonthName }}
        </td>
    </tr>

    <tr>
        <td colspan="6" style="text-align:center;">
            Admission Months Included:
            {{ implode(', ', $quarterMonthNames) }}
        </td>
    </tr>

    <tr>
        <td colspan="6">
            Total Members: {{ $rows->count() }}
        </td>
    </tr>

    <tr>
        <td colspan="6"></td>
    </tr>

    <thead>
        <tr>
            <th style="font-weight:bold; background-color:#D9EAF7; border:1px solid #000;">
                Participant ID
            </th>

            <th style="font-weight:bold; background-color:#D9EAF7; border:1px solid #000;">
                Last Name
            </th>

            <th style="font-weight:bold; background-color:#D9EAF7; border:1px solid #000;">
                First Name
            </th>

            <th style="font-weight:bold; background-color:#D9EAF7; border:1px solid #000;">
                Middle Name
            </th>

            <th style="font-weight:bold; background-color:#D9EAF7; border:1px solid #000;">
                Admission Date
            </th>

            <th style="font-weight:bold; background-color:#D9EAF7; border:1px solid #000;">
                Remarks
            </th>
        </tr>
    </thead>

    <tbody>
        @foreach ($rows as $row)
            <tr>
                <td style="border:1px solid #000;">
                    {{ $row->ParticipantId }}
                </td>

                <td style="border:1px solid #000;">
                    {{ $row->LastName }}
                </td>

                <td style="border:1px solid #000;">
                    {{ $row->FirstName }}
                </td>

                <td style="border:1px solid #000;">
                    {{ $row->MiddleName }}
                </td>

                <td style="border:1px solid #000;">
                    {{ $row->AdmissionDate
                        ? \Carbon\Carbon::parse($row->AdmissionDate)->format('m/d/Y')
                        : ''
                    }}
                </td>

                <td style="border:1px solid #000;">
                    {{-- Intentionally blank --}}
                </td>
            </tr>
        @endforeach
    </tbody>
</table>
