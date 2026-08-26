<!DOCTYPE html>
<html>
<head><meta charset="utf-8"><title>Payment Slip</title>
<style>body{font-family:DejaVu Sans,sans-serif;font-size:12px;color:#111} .header{border-bottom:3px solid #0B3C91;padding-bottom:10px;margin-bottom:20px} .gold{color:#F4B400} table{width:100%;border-collapse:collapse;margin-top:15px} th,td{border:1px solid #ddd;padding:8px;text-align:left}</style>
</head>
<body>
<div class="header">
    <h2 style="color:#0B3C91;margin:0">PECIT Smart Inventory System</h2>
    <p>Payment Slip — <span class="gold">{{ $purchase->purchase_number }}</span></p>
</div>
<p><strong>Student:</strong> {{ $purchase->user?->name }} ({{ $purchase->user?->email }})</p>
<p><strong>Date:</strong> {{ $purchase->created_at?->format('F d, Y') }}</p>
@if ($payment = $purchase->payments->first())
<p><strong>Payment Reference:</strong> {{ $payment->reference_number }}</p>
@endif
<table>
    <thead><tr><th>Item</th><th>Size</th><th>Qty</th><th>Unit Price</th><th>Subtotal</th></tr></thead>
    <tbody>
    @foreach ($purchase->items as $line)
        <tr><td>{{ $line->inventory?->item_name }}</td><td>{{ $line->size ?? '—' }}</td><td>{{ $line->quantity }}</td><td>₱{{ number_format($line->unit_price,2) }}</td><td>₱{{ number_format($line->subtotal,2) }}</td></tr>
    @endforeach
    </tbody>
</table>
<h3 style="text-align:right;color:#0B3C91">Total: ₱{{ number_format($purchase->total_amount, 2) }}</h3>
<p style="margin-top:30px;font-size:10px">Present this slip to the PECIT Accounting Office for payment verification.</p>
</body>
</html>
