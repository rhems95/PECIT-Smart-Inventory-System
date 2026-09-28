<h2 style="font-family:sans-serif;color:#0B3C91;">Supplies Issuance Log — PECIT PSIS</h2>
<p>Period: {{ $report['range_label'] }} | Generated: {{ now()->format('M d, Y g:i A') }}</p>
<p>Released faculty requests and student purchases only. Cancelled and rejected are not included.</p>

<p>
    Faculty lines: {{ $report['totals']['faculty_lines'] }}
    (₱{{ number_format($report['totals']['faculty_amount'], 2) }})
    · Student lines: {{ $report['totals']['student_lines'] }}
    (₱{{ number_format($report['totals']['student_amount'], 2) }})
</p>

<h3>Per department</h3>
<table border="1" cellpadding="6" cellspacing="0" width="100%">
    <tr style="background:#0B3C91;color:#fff;">
        <th>Department</th><th>Faculty qty</th><th>Student qty</th><th>Faculty amount</th><th>Student amount</th>
    </tr>
    @forelse ($report['departments'] as $row)
        <tr>
            <td>{{ $row['department'] }}</td>
            <td>{{ $row['faculty_qty'] }}</td>
            <td>{{ $row['student_qty'] }}</td>
            <td>{{ number_format($row['faculty_amount'], 2) }}</td>
            <td>{{ number_format($row['student_amount'], 2) }}</td>
        </tr>
    @empty
        <tr><td colspan="5">No released items in this period.</td></tr>
    @endforelse
</table>

@if ($report['source'] !== 'student')
<h3>Faculty</h3>
<table border="1" cellpadding="6" cellspacing="0" width="100%">
    <tr style="background:#0B3C91;color:#fff;">
        <th>Date</th><th>Articles/Items</th><th>QTY</th><th>UNIT</th><th>UNIT PRICE</th><th>TOTAL AMOUNT</th><th>SEMESTER</th><th>DEPARTMENT</th>
    </tr>
    @forelse ($report['faculty'] as $row)
        <tr>
            <td>{{ $row['date']?->format('m/d/Y') }}</td>
            <td>{{ $row['item'] }}</td>
            <td>{{ $row['qty'] }}</td>
            <td>{{ $row['unit'] }}</td>
            <td>{{ number_format($row['unit_price'], 2) }}</td>
            <td>{{ number_format($row['total_amount'], 2) }}</td>
            <td>{{ $row['semester'] }}</td>
            <td>{{ $row['department'] }}</td>
        </tr>
    @empty
        <tr><td colspan="8">No faculty releases in this period.</td></tr>
    @endforelse
</table>
@endif

@if ($report['source'] !== 'faculty')
<h3>Students</h3>
<table border="1" cellpadding="6" cellspacing="0" width="100%">
    <tr style="background:#0B3C91;color:#fff;">
        <th>Date</th><th>Articles/Items</th><th>QTY</th><th>UNIT</th><th>UNIT PRICE</th><th>TOTAL AMOUNT</th><th>SEMESTER</th><th>DEPARTMENT</th>
    </tr>
    @forelse ($report['student'] as $row)
        <tr>
            <td>{{ $row['date']?->format('m/d/Y') }}</td>
            <td>{{ $row['item'] }}</td>
            <td>{{ $row['qty'] }}</td>
            <td>{{ $row['unit'] }}</td>
            <td>{{ number_format($row['unit_price'], 2) }}</td>
            <td>{{ number_format($row['total_amount'], 2) }}</td>
            <td>{{ $row['semester'] }}</td>
            <td>{{ $row['department'] }}</td>
        </tr>
    @empty
        <tr><td colspan="8">No student releases in this period.</td></tr>
    @endforelse
</table>
@endif
