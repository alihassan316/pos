@extends('layouts.pos')

<style>
    .sidebar{ display:none !important; }
    .main-wrapper{ margin-left:0 !important; }
    .table tbody td{ padding:2px !important; }
    .form-control, .form-select{ padding:1px !important; }
    .summary-box{
        padding:8px; 
        background:#f1f1f1; 
        border:1px solid #ddd; 
        margin-top:10px;
        font-weight:bold;
        text-align:right;
    }
    .form-control{
        border-color:#c0c0c0 !important;
    }
</style>

@section('content')

<h4 class="mb-3">New Purchase Entry</h4>

<form action="{{ route('purchases.store') }}" method="POST">
@csrf

{{-- Invoice Section --}}
<div class="card mb-4">
    <div class="card-body">
        <div class="row g-3">
            <div class="col-md-4">
                <label class="form-label">Company Name</label>
                <input type="text" name="company_name" class="form-control" required>
            </div>
            <div class="col-md-4">
                <label class="form-label">Contact</label>
                <input type="text" name="contact" class="form-control">
            </div>
            <div class="col-md-4">
                <label class="form-label">Invoice Number</label>
                <input type="text" name="invoice_number" class="form-control" required>
            </div>
            <div class="col-md-4">
                <label class="form-label">Invoice Date</label>
                <input type="date" name="invoice_date"
                    value="{{ old('invoice_date', \Carbon\Carbon::today()->format('Y-m-d')) }}"
                    class="form-control" required>
            </div>
            <div class="col-md-8">
                <label class="form-label">Notes</label>
                <textarea name="notes" class="form-control" rows="1"></textarea>
            </div>
        </div>
    </div>
</div>

{{-- ROW GENERATOR BOX --}}
<div class="card mb-3 p-3" style="background:#fafafa; border:1px solid #ddd;">
    <div class="row g-2 align-items-center">
        <div class="col-md-3">
            <label class="form-label">Number of Rows</label>
            <input type="number" id="rowCount" class="form-control" min="1" placeholder="Enter rows (e.g. 20)">
        </div>
        <div class="col-md-3 mt-4">
            <button type="button" id="generateRows" class="btn btn-success">Generate Rows</button>
        </div>
    </div>
</div>

{{-- Products Section --}}
<div class="card">
    <div class="card-body">

        <table class="table table-bordered" id="productTable">
            <thead>
                <tr>
                    <th>Name</th>
                    <th>Ingredient</th>
                    <th>Qty</th>
                    <th>Bonus</th>
                    <th>Per Pack</th>
                    <th>Batch</th>
                    <th>Expiry (dd/mm/yyyy)</th>
                    <th>Alert</th>
                    <th>Pack Price</th>
                    <th>Disc %</th>
                    <th>Flat Disc</th>
                    <th>GST %</th>
                    <th>GST Flat</th>
                    <th>Final Price</th>
                    <th>Buy Price</th>
                    <th>Sale Price</th>
                    <th></th>
                </tr>
            </thead>
            <tbody id="rows"></tbody>
        </table>

        <button type="button" class="btn btn-secondary" id="addRowBtn">+ Add Product</button>

        <div class="summary-box">
            Total Items: <span id="totalItems">0</span> |
            Total Final Price: <span id="totalFinal">0.00</span>
        </div>
    </div>
</div>

<div class="row">
    <div class="col-12">
        <input type="checkbox" id="checkforn" required />
        <label for="checkforn">Are you sure to submit?</label>
    </div>
</div>

<button type="submit" class="btn btn-primary mt-3">Save Purchase</button>
<a class="btn btn-warning mt-3" href="{{url('dashboard')}}">Dashboard</a>
</form>

<script>

// Keep session alive on ANY domain or localhost
setInterval(function() {
    fetch("{{ url('keep-alive') }}");
}, 120000);

let rowIndex = 0;

document.getElementById('addRowBtn').addEventListener('click', addRow);

// Add initial row on page load
window.onload = function() { addRow(); };

function addRow() {
    let html = `
        <tr>
            <td><input name="products[${rowIndex}][name]" class="form-control"></td>
            <td><input name="products[${rowIndex}][ingredient]" class="form-control"></td>
            <td><input name="products[${rowIndex}][qty]" class="form-control calc"></td>
            <td><input name="products[${rowIndex}][bonus]" class="form-control calc"></td>
            <td><input name="products[${rowIndex}][per_pack]" class="form-control"></td>
            <td><input name="products[${rowIndex}][batch_no]" class="form-control"></td>
            <td><input type="text" placeholder="dd/mm/yyyy"
                       name="products[${rowIndex}][expiry]" class="form-control expiryField"></td>
            <td>
                <select name="products[${rowIndex}][expiry_alert]" class="form-select">
                    <option value="1">1 Month</option>
                    <option value="2">2 Months</option>
                    <option value="3">3 Months</option>
                    <option value="4">4 Months</option>
                    <option value="5">5 Months</option>
                    <option value="6">6 Months</option>
                </select>
            </td>
            <td><input name="products[${rowIndex}][pack_price]" class="form-control calc"></td>
            <td><input name="products[${rowIndex}][discount_percent]" class="form-control calc"></td>
            <td><input name="products[${rowIndex}][discount_flat]" class="form-control calc"></td>
            <td><input name="products[${rowIndex}][gst_percent]" class="form-control calc"></td>
            <td><input name="products[${rowIndex}][gst_flat]" class="form-control calc"></td>
            <td><input name="products[${rowIndex}][final_price]" class="form-control final" readonly></td>
            <td><input name="products[${rowIndex}][buy_price]" class="form-control buy_price" readonly></td>
            <td><input name="products[${rowIndex}][sale_price]" class="form-control"></td>
            <td><button class="btn btn-danger btn-sm" onclick="removeRow(this)">X</button></td>
        </tr>
    `;
    document.getElementById('rows').insertAdjacentHTML('beforeend', html);
    rowIndex++;
    updateSummary();
}

function removeRow(btn) {
    btn.closest("tr").remove();
    updateSummary();
}

// Row generator
document.getElementById("generateRows").addEventListener("click", function () {
    let count = parseInt(document.getElementById("rowCount").value);

    if (isNaN(count) || count < 1) {
        alert("Please enter a valid number");
        return;
    }

    document.getElementById("rows").innerHTML = "";
    rowIndex = 0;

    for (let i = 0; i < count; i++) addRow();

    updateSummary();

    // Focus first row name
    setTimeout(() => {
        document.querySelector("#rows tr:first-child input").focus();
    }, 50);
});

// Auto calculation
document.addEventListener("input", function(e) {
    if(e.target.classList.contains("calc")){
        let tr = e.target.closest("tr");

        let name = tr.querySelector("[name*='name']").value.trim();
        if(name === ""){
            tr.querySelector(".final").value = "";
            tr.querySelector(".buy_price").value = "";
            updateSummary();
            return;
        }

        let qty       = parseFloat(tr.querySelector("[name*='qty']").value) || 0;
        let bonus     = parseFloat(tr.querySelector("[name*='bonus']").value) || 0;
        let perPack   = parseFloat(tr.querySelector("[name*='per_pack']").value) || 0;
        let packPrice = parseFloat(tr.querySelector("[name*='pack_price']").value) || 0;
        let dPercent  = parseFloat(tr.querySelector("[name*='discount_percent']").value) || 0;
        let dFlat     = parseFloat(tr.querySelector("[name*='discount_flat']").value) || 0;
        let gstPercent= parseFloat(tr.querySelector("[name*='gst_percent']").value) || 0;
        let gstFlat   = parseFloat(tr.querySelector("[name*='gst_flat']").value) || 0;

        let totalQty = qty + bonus;
        let totalUnits = totalQty * (perPack || 1);

        let baseAmount = qty * packPrice;
        let discountAmount = (baseAmount * dPercent / 100) + dFlat;
        let gstAmount = (baseAmount * gstPercent / 100) + gstFlat;

        let finalPrice = baseAmount - discountAmount + gstAmount;
        let buyPrice = totalUnits > 0 ? finalPrice / totalUnits : 0;

        tr.querySelector(".final").value = finalPrice.toFixed(2);
        tr.querySelector(".buy_price").value = buyPrice.toFixed(2);

        updateSummary();
    }
});

function updateSummary() {
    let rows = document.querySelectorAll("#rows tr");
    let totalItems = 0;
    let totalFinal = 0;

    rows.forEach(tr => {
        let name = tr.querySelector("[name*='name']").value.trim();
        if(name !== ""){
            totalItems++;
            let f = parseFloat(tr.querySelector(".final").value) || 0;
            totalFinal += f;
        }
    });

    document.getElementById("totalItems").innerText = totalItems;
    document.getElementById("totalFinal").innerText = totalFinal.toFixed(2);
}

</script>

@endsection