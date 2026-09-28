<h2 style="font-family:sans-serif;color:#0B3C91;">Monthly Summary — PECIT PSIS</h2>
<p>Period: {{ $report['month_label'] }} | Generated: {{ now()->format('M d, Y g:i A') }}</p>
<p>Cancelled and rejected requests are not counted. Low stock and out of stock are current inventory.</p>

<h3>Overview</h3>
<table border="1" cellpadding="6" cellspacing="0" width="100%">
    <tr style="background:#0B3C91;color:#fff;"><th>Faculty requests</th><th>Student purchases</th><th>Low stock</th><th>Out of stock</th></tr>
    <tr>
        <td>{{ $report['counts']['faculty_requests'] }}</td>
        <td>{{ $report['counts']['student_purchases'] }}</td>
        <td>{{ $report['counts']['low_stock'] }}</td>
        <td>{{ $report['counts']['out_of_stock'] }}</td>
    </tr>
</table>

<p><strong>Most requested overall:</strong>
    {{ $report['top_overall'] ? $report['top_overall']['item'].' ('.$report['top_overall']['total'].')' : 'None' }}
</p>

<h3>Most requested (faculty)</h3>
@if (count($report['faculty']))
<table border="1" cellpadding="6" cellspacing="0" width="100%">
    <tr style="background:#0B3C91;color:#fff;"><th>Item</th><th>Quantity</th></tr>
    @foreach ($report['faculty'] as $row)
        <tr><td>{{ $row['item'] }}</td><td>{{ $row['faculty_qty'] }}</td></tr>
    @endforeach
</table>
@else
<p>No faculty requests in {{ $report['month_label'] }}.</p>
@endif

<h3>Most purchased (students)</h3>
@if (count($report['student']))
<table border="1" cellpadding="6" cellspacing="0" width="100%">
    <tr style="background:#0B3C91;color:#fff;"><th>Item</th><th>Quantity</th></tr>
    @foreach ($report['student'] as $row)
        <tr><td>{{ $row['item'] }}</td><td>{{ $row['student_qty'] }}</td></tr>
    @endforeach
</table>
@else
<p>No student purchases in {{ $report['month_label'] }}.</p>
@endif

<h3>Need restock this month</h3>
@if (count($report['restock']))
<table border="1" cellpadding="6" cellspacing="0" width="100%">
    <tr style="background:#0B3C91;color:#fff;"><th>Item</th><th>Faculty</th><th>Student</th><th>Demand</th><th>Available</th><th>Suggest restock</th></tr>
    @foreach ($report['restock'] as $row)
        <tr>
            <td>{{ $row['item'] }}</td>
            <td>{{ $row['faculty_qty'] }}</td>
            <td>{{ $row['student_qty'] }}</td>
            <td>{{ $row['demand'] }}</td>
            <td>{{ $row['available'] }}</td>
            <td>{{ $row['recommended_reorder'] }} {{ $row['unit'] }}</td>
        </tr>
    @endforeach
</table>
@else
<p>No demanded items need restock in {{ $report['month_label'] }}.</p>
@endif

<h3>Current low stock</h3>
@if (count($report['low_stock_items']))
<table border="1" cellpadding="6" cellspacing="0" width="100%">
    <tr style="background:#0B3C91;color:#fff;"><th>Item</th><th>Available</th><th>Minimum</th></tr>
    @foreach ($report['low_stock_items'] as $row)
        <tr><td>{{ $row['item'] }}</td><td>{{ $row['available'] }}</td><td>{{ $row['minimum'] }}</td></tr>
    @endforeach
</table>
@else
<p>None.</p>
@endif
