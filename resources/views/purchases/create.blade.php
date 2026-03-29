@extends('layouts.pos')

<style>
    .sidebar{ display:none !important; }
    .main-wrapper{ margin-left:0px !important; }
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
                <input type="date" name="invoice_date" value="{{ old('invoice_date', \Carbon\Carbon::today()->format('Y-m-d')) }}" class="form-control" required>
            </div>
            <div class="col-md-8">
                <label class="form-label">Notes</label>
                <textarea name="notes" class="form-control" rows="1"></textarea>
            </div>
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

        {{-- Summary --}}
        <div class="summary-box">
            Total Items: <span id="totalItems">0</span> |
            Total Final Price: <span id="totalFinal">0.00</span>
        </div>
    </div>
</div>

<button type="submit" class="btn btn-primary mt-3">Save Purchase</button>
<a class="btn btn-warning mt-3" href="{{url('dashboard')}}">Dashboard</a>
</form>

<script>

setInterval(function() {
	fetch("{{ url('keep-alive') }}");
}, 120000);


let rowIndex = 0;

document.getElementById('addRowBtn').addEventListener('click', addRow);

// Add initial row
window.onload = function() { addRow(); };

// Add product row
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

// Remove row
function removeRow(btn) {
    btn.closest("tr").remove();
    updateSummary();
}

// Fix invalid expiry date
document.addEventListener("blur", function(e) {
    if(e.target.classList.contains("expiryField")) {
        let val = e.target.value.trim();
        let p = val.split("/");
        if(p.length !== 3) return;
        let d = Math.max(1, Math.min(31, parseInt(p[0] || 1)));
        let m = Math.max(1, Math.min(12, parseInt(p[1] || 1)));
        let y = parseInt(p[2] || new Date().getFullYear());
        e.target.value = `${String(d).padStart(2,'0')}/${String(m).padStart(2,'0')}/${y}`;
    }
}, true);

// Auto calculation
document.addEventListener("input", function(e) {
    if(e.target.classList.contains("calc")){
        let tr = e.target.closest("tr");

        // Only calculate if product name is entered
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

        // Base amount (only paid qty)
        let baseAmount = qty * packPrice;

        // Discount amount
        let discountAmount = (baseAmount * dPercent / 100) + dFlat;

        // GST calculated on original packPrice * qty (ignoring discount)
        let gstAmount = (baseAmount * gstPercent / 100) + gstFlat;

        // Final price = baseAmount - discount + GST
        let finalPrice = baseAmount - discountAmount + gstAmount;

        // Buy price per unit including bonus & GST
        let buyPrice = totalUnits > 0 ? finalPrice / totalUnits : 0;

        // Update fields
        tr.querySelector(".final").value = finalPrice.toFixed(2);
        tr.querySelector(".buy_price").value = buyPrice.toFixed(2);

        updateSummary();
    }
});
// Update summary
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


// Enhanced Enter navigation inside product table
/*
document.querySelector("#productTable").addEventListener("keydown", function(e){
    if(e.key === "Enter"){
        e.preventDefault(); // prevent default form submit / button click

        let input = e.target;
        let tr = input.closest("tr");
        let rows = Array.from(document.querySelectorAll("#rows tr"));
        let inputs = Array.from(tr.querySelectorAll("input, select, textarea"));
        let inputIdx = inputs.indexOf(input);
        let rowIdx = rows.indexOf(tr);

        // If not last input in the row, move to next input
        if(inputIdx < inputs.length - 1){
            inputs[inputIdx + 1].focus();
        }
        else {
            // Last input of the row reached
            if(rowIdx === rows.length - 1){
                // Last row: add new row
                addRow();

                // Refresh rows array after adding new row
                rows = Array.from(document.querySelectorAll("#rows tr"));
                let newRow = rows[rows.length - 1];

                // Focus first input of the new row
                newRow.querySelector("[name*='name']").focus();
            } else {
                // Move to first input of next row
                rows[rowIdx + 1].querySelector("[name*='name']").focus();
            }
        }
    }
});
*/


</script>

@endsection