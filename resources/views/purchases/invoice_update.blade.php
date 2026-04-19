@extends('layouts.pos')

@section('title', 'Purchase Invoice #' . $invoice->invoice_number)

@section('content')

<style>
    .sidebar{ display:none !important; }
    .main-wrapper{ margin-left:0 !important; }
    .table tbody td{ padding:2px !important; vertical-align: middle; }
    .form-control, .form-select{ padding:1px !important; font-size: 13px; border-color:#c0c0c0 !important; }
    .summary-box{
        padding:8px; 
        background:#f1f1f1; 
        border:1px solid #ddd; 
        margin-top:10px;
        font-weight:bold;
        text-align:right;
    }
    .deleteBtn {
        cursor: pointer;
        color: red;
        font-weight: bold;
        font-size: 16px;
        border: none;
        background: none;
    }
</style>

<div>
    <h3>Purchase Invoice #{{ $invoice->invoice_number }}</h3>

    <div class="mb-3">
        <strong>Company:</strong> {{ $invoice->company_name }} | <strong>Date:</strong> {{ $invoice->invoice_date }}
    </div>

    <form method="post" action="{{ route('invoice.add.row', $invoice->id) }}">
        @csrf
        <input type="hidden" name="invoice_id" value="{{ $invoice->id }}" />

        <table class="table table-bordered" id="productTable">
            <thead>
                <tr class="table-secondary">
                    <th>Order</th>
                    <th style="min-width:200px;">Name</th>
                    <th>Ingredient</th>
                    <th>Company</th>
                    <th>Qty</th>
                    <th>Bonus</th>
                    <th>Per Pack</th>
                    <th>Batch</th>
                    <th>Expiry</th>
                    <th>Alert</th>
                    <th>Pack Price</th>
                    <th>Disc %</th>
                    <th>Flat Disc</th>
                    <th>GST %</th>
                    <th>GST Flat</th>
                    <th>Final Price</th>
                    <th>Buy Price</th>
                    <th>Box Price</th>
                    <th>Sale Price</th>
                    <th>Action</th>
                </tr>
            </thead>
            <tbody id="rows">
                {{-- 1. Already Saved Rows (Static) --}}
                @foreach($products as $p)
                <tr data-id="{{ $p->id }}">
                    <td>{{ $p->sequnce }}</td>
                    <td>{{ $p->name }}</td>
                    <td>{{ $p->ingrediant }}</td>
                    <td>{{ $p->company }}</td>
                    <td>{{ $p->qty }}</td>
                    <td>{{ $p->bonus }}</td>
                    <td>{{ $p->perpack }}</td>
                    <td>{{ $p->batch }}</td>
                    <td>{{ $p->expiry }}</td>
                    <td>{{ $p->expiry_alert ? $p->expiry_alert . ' Month' : '' }}</td>
                    <td>{{ $p->packprice }}</td>
                    <td>{{ $p->discount_per }}</td>
                    <td>{{ $p->discount_fix }}</td>
                    <td>{{ $p->gst_per }}</td>
                    <td>{{ $p->gst_fix }}</td>
                    <td class="static-final">{{ $p->final_price }}</td>
                    <td>{{ $p->buy_price }}</td>
                    <td>{{ $p->box_price }}</td>
                    <td>{{ $p->sale_price }}</td>
                    <td>
                        <button type="button" class="deleteBtn" onclick="deleteSavedRow({{ $p->id }}, this)">×</button>
                    </td>
                </tr>
                @endforeach

                {{-- 2. The SINGLE Input Row (Submits to Controller) --}}
                <tr id="inputRow" class="table-info">
                    <td><input type="text" name="sequnce" class="form-control sequnce" value="{{ count($products) + 1 }}"></td>
                    <td><input type="text" name="product_name" class="form-control nameField" required ></td>
                    <td><input type="text" name="ingrediant" class="form-control ingredientField"></td>
                    <td><input type="text" name="company" class="form-control companyField"></td>
                    <td><input type="text" name="qty" class="form-control calc qtyField"></td>
                    <td><input type="text" name="bonus" class="form-control calc bonusField"></td>
                    <td><input type="text" name="perpack" class="form-control calc perPackField"></td>
                    <td><input type="text" name="batch" class="form-control"></td>
                    <td><input type="text" name="expiry" class="form-control expiryField" placeholder="DDMMYYYY"></td>
                    <td>
                        <select name="expiry_alert" class="form-select">
                            @for($i=1; $i<=6; $i++) <option value="{{$i}}">{{$i}} Month</option> @endfor
                        </select>
                    </td>
                    <td><input type="text" name="packprice" class="form-control calc packPriceField"></td>
                    <td><input type="text" name="discount_per" class="form-control calc discountPerField"></td>
                    <td><input type="text" name="discount_fix" class="form-control calc discountFlatField"></td>
                    <td><input type="text" name="gst_per" class="form-control calc gstPerField"></td>
                    <td><input type="text" name="gst_fix" class="form-control calc gstFlatField"></td>
                    <td><input type="text" name="final_price" class="form-control finalField" readonly></td>
                    <td><input type="text" name="buy_price" class="form-control buyPriceField" readonly></td>
                    <td><input type="text" name="box_price" class="form-control calc boxPriceField"></td>
                    <td><input type="text" name="sale_price" class="form-control salePriceField" readonly></td>
                    <td><button type="submit" class="btn btn-success btn-sm w-100">Save</button></td>
                </tr>
            </tbody>
        </table>
    </form>

    <div class="summary-box">
        Total Items: <span id="totalItems">0</span> &nbsp; | &nbsp; 
        Total Cost: <span id="totalCost">0.00</span>
    </div>

    <form method="post" action="{{ route('purchases.submitivnoice') }}" class="mt-3">
        @csrf
        <input type="hidden" name="id" value="{{ $invoice->id }}" />
        <div class="form-check mb-2">
            <input class="form-check-input" type="checkbox" id="conf" name="submitinv" value="1" required>
            <label class="form-check-label" for="conf">Confirm? Final Submit invoice</label>
        </div>
        <button class="btn btn-primary" type="submit">Submit Invoice</button>
        <a class="btn btn-warning" href="{{url('dashboard')}}">Dashboard</a>
    </form>
</div>

<script>

document.addEventListener("DOMContentLoaded", function() {
        // Focus on the nameField input row
        const nameInput = document.querySelector("#inputRow .nameField");
        if (nameInput) {
            nameInput.focus();
        }
    });


// --- 1. Calculation Logic (Exactly like before) ---
const inputRow = document.getElementById('inputRow');

function calculateRow() {
    let qty = parseFloat(inputRow.querySelector(".qtyField").value) || 0;
    let bonus = parseFloat(inputRow.querySelector(".bonusField").value) || 0;
    let perPack = parseFloat(inputRow.querySelector(".perPackField").value) || 1;
    let packPrice = parseFloat(inputRow.querySelector(".packPriceField").value) || 0;
    let dPercent = parseFloat(inputRow.querySelector(".discountPerField").value) || 0;
    let dFlat = parseFloat(inputRow.querySelector(".discountFlatField").value) || 0;
    let gstPercent = parseFloat(inputRow.querySelector(".gstPerField").value) || 0;
    let gstFlat = parseFloat(inputRow.querySelector(".gstFlatField").value) || 0;
    let boxPrice = parseFloat(inputRow.querySelector(".boxPriceField").value) || 0;

    let baseAmount = qty * packPrice;
    let discountAmount = (baseAmount * dPercent / 100) + dFlat;
    let gstAmount = (baseAmount * gstPercent / 100) + gstFlat;
    let finalPrice = baseAmount - discountAmount + gstAmount;

    let totalUnits = (qty + bonus) * perPack;
    let buyPrice = totalUnits > 0 ? finalPrice / totalUnits : 0;

    inputRow.querySelector(".finalField").value = finalPrice.toFixed(2);
    inputRow.querySelector(".buyPriceField").value = buyPrice.toFixed(2);

    if (boxPrice > 0 && perPack > 0) {
        inputRow.querySelector(".salePriceField").value = (boxPrice / perPack).toFixed(2);
    }
    
    updateSummary();
}

// Attach listeners to all "calc" fields in the input row
inputRow.querySelectorAll(".calc").forEach(input => {
    input.addEventListener("input", calculateRow);
});

// --- 2. Summary Logic (Totals everything) ---
function updateSummary() {
    let totalItems = 0;
    let totalCost = 0;

    // Static rows
    document.querySelectorAll(".static-final").forEach(td => {
        totalCost += parseFloat(td.innerText) || 0;
        totalItems++;
    });

    // Current input row (if it has a name)
    let currentFinal = parseFloat(inputRow.querySelector(".finalField").value) || 0;
    if (inputRow.querySelector(".nameField").value.trim() !== "") {
        totalCost += currentFinal;
        totalItems++;
    }

    document.getElementById("totalItems").innerText = totalItems;
    document.getElementById("totalCost").innerText = totalCost.toFixed(2);
}

// --- 3. Expiry Formatting ---
inputRow.querySelector(".expiryField").addEventListener("blur", function() {
    let val = this.value.trim();
    if (/^\d{8}$/.test(val)) {
        let d = val.substr(0, 2);
        let m = val.substr(2, 2);
        let y = val.substr(4, 4);
        this.value = `${d}/${m}/${y}`;
    }
});

// --- 4. Delete Saved Rows (AJAX) ---
function deleteSavedRow(id, btn) {
    if(!confirm("Delete this row?")) return;
    fetch("{{ route('invoice.delete.row', ':id') }}".replace(':id', id), {
        method: "DELETE",
        headers: { "X-CSRF-TOKEN": "{{ csrf_token() }}" }
    }).then(res => res.json()).then(res => {
        if(res.success) {
            btn.closest("tr").remove();
            updateSummary();
        }
    });
}

// Initial calculation
updateSummary();
</script>
@endsection