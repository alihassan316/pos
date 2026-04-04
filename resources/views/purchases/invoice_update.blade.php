@extends('layouts.pos')

@section('title', 'Purchase Invoice #' . $invoice->invoice_number)

@section('content')

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
        <strong>Company:</strong> {{ $invoice->company_name }}<br>
        <strong>Contact:</strong> {{ $invoice->contact }}<br>
        <strong>Date:</strong> {{ $invoice->invoice_date }}<br>
        <strong>Notes:</strong> {{ $invoice->notes }}
    </div>

    <table class="table table-bordered" id="productTable">
        <thead>
            <tr>
            	<th>Order</th>
                <th style="min-width:250px;">Name</th>
                <th>Ingredient</th>
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
                <th></th>
            </tr>
        </thead>
        <tbody id="rows">
            {{-- Already Saved Rows --}}
            @foreach($products as $p)
                <tr data-id="{{ $p->id }}">
                	<td>{{ $p->sequnce }}</td>
                    <td>{{ $p->name }}</td>
                    <td>{{ $p->ingrediant }}</td>
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
                    <td>{{ $p->final_price }}</td>
                    <td>{{ $p->buy_price }}</td>
                    <td>{{ $p->box_price }}</td>
                    <td>{{ $p->sale_price }}</td>
                    <td><button class="deleteBtn" onclick="deleteRow({{ $p->id }}, this)">×</button></td>
                </tr>
            @endforeach
        </tbody>
    </table>
    
    <div class="summary-box d-flex justify-content-between align-items-center"> 
        <button class="btn btn-primary mb-0" id="addRowBtn">+ Add New Row</button> 
        <div> Total Items: <span id="totalItems">0</span> &nbsp; | &nbsp; Total Cost: 
            <span id="totalCost">0.00</span> 
        </div> 
    </div>
    
    
    <form method="post" action="{{ route('purchases.submitivnoice') }}">
    	@csrf
    	<input type="hidden" name="id" value="{{ $invoice->id }}" />
        <div class="form-group">
        	<input type="checkbox" id="conf" name="submitinv" value="1" required />
            <label for="conf">
            	Confirm? Final Submit invoice
            </label>
                    
        </div>
        <div class="form-group">
        	<button class="btn btn-success mt-3" type="submit">Submit Inovice</button>
            <a class="btn btn-warning mt-3" href="{{url('dashboard')}}">Dashboard</a>
        </div>
    </form>
    
    
</div>

<script>
let invoiceId = {{ $invoice->id }};
let saveUrl = "{{ route('invoice.add.row', ':id') }}".replace(':id', {{ $invoice->id }});
let deleteUrl = "{{ route('invoice.delete.row', ':id') }}"; // pass :id dynamically
let rowIndex = 0;

addEditableRow();

// Update totals
function updateSummary() {
    let rows = document.querySelectorAll("#rows tr");
    let totalItems = 0;
    let totalCost = 0;

    rows.forEach(tr => {
        // Skip empty editable rows (no name)
        let nameInput = tr.querySelector(".nameField");
        let nameText = tr.cells[0]?.innerText || "";

        if ((nameInput && nameInput.value.trim() === "") || (!nameInput && nameText.trim() === "")) {
            return;
        }

        totalItems++;

        let finalInput = tr.querySelector(".finalField");
        let finalText = tr.cells[13]; // static final price column
        let val = 0;
        if(finalInput) val = parseFloat(finalInput.value) || 0;
        else if(finalText) val = parseFloat(finalText.innerText) || 0;
        totalCost += val;
    });

    document.getElementById("totalItems").innerText = totalItems;
    document.getElementById("totalCost").innerText = totalCost.toFixed(2);
}

// Add new editable row
document.getElementById("addRowBtn").addEventListener("click", function() {
    addEditableRow();
});

function attachExpiryHandler(input) {
    input.addEventListener("blur", function() {
        let val = this.value.trim();

        if (!val) {
            // Allow empty
            return;
        }

        let day, month, year;

        // Handle 8-digit number like 30032028
        if(/^\d{8}$/.test(val)) {
            day = val.substr(0,2);
            month = val.substr(2,2);
            year = val.substr(4,4);
        }
        // Handle 2/2/4 or 2-2-4 formats
        else if(/^\d{1,2}[-\/]\d{1,2}[-\/]\d{4}$/.test(val)) {
            [day, month, year] = val.split(/[-\/]/);
            if(day.length === 1) day = '0' + day;
            if(month.length === 1) month = '0' + month;
        }
        else {
            // Invalid input → leave empty
            this.value = "";
            return;
        }

        // Validate date
        let dateStr = `${year}-${month}-${day}`;
        let dateObj = new Date(dateStr);
        if(isNaN(dateObj.getTime())) {
            this.value = "";
        } else {
            // Format as dd/mm/yyyy
            this.value = `${day}/${month}/${year}`;
        }
    });
}

// Attach to already existing rows
document.querySelectorAll(".expiryField").forEach(input => attachExpiryHandler(input));


function addEditableRow() {
    let tr = document.createElement("tr");
	
	let nextSeq = document.querySelectorAll("#rows tr").length + 1;

    tr.innerHTML = `
		<td><input type="text" class="form-control sequnce" value="${nextSeq}" /></td>
        <td><input type="text" class="form-control nameField" /></td>
        <td><input type="text" class="form-control ingredientField" /></td>
        <td><input type="text" class="form-control calc qtyField" /></td>
        <td><input type="text" class="form-control calc bonusField" /></td>
        <td><input type="text" class="form-control calc perPackField" /></td>
        <td><input type="text" class="form-control batchField" /></td>
        <td><input type="text" class="form-control expiryField" /></td>
        <td><select type="text" class="form-control expiryAlertField">
			<option value="1">1 Month</option>
			<option value="2">2 Month</option>
			<option value="3">3 Month</option>
			<option value="4">4 Month</option>
			<option value="5">5 Month</option>
			<option value="6">6 Month</option>
			
		</select>
		</td>
        <td><input type="text" class="form-control calc packPriceField" /></td>
        <td><input type="text" class="form-control calc discountPerField" /></td>
        <td><input type="text" class="form-control calc discountFlatField" /></td>
        <td><input type="text" class="form-control calc gstPerField" /></td>
        <td><input type="text" class="form-control calc gstFlatField" /></td>
        <td><input type="text" class="form-control finalField" readonly /></td>
        <td><input type="text" class="form-control buyPriceField" readonly /></td>
        <td><input type="text" class="form-control calc boxPriceField" /></td>
        <td><input type="text" class="form-control salePriceField" /></td>
        <td><button class="btn btn-success btn-sm saveRow">Save</button></td>
    `;

    document.getElementById("rows").appendChild(tr);

    // Attach save click
    tr.querySelector(".saveRow").addEventListener("click", function () {
        saveRow(tr);
    });

    // Attach calculation listeners
    tr.querySelectorAll(".calc").forEach(input => input.addEventListener("input", () => calculateRow(tr)));
    tr.querySelector(".boxPriceField").addEventListener("input", () => calculateRow(tr));
    tr.querySelector(".perPackField").addEventListener("input", () => calculateRow(tr));

    // Attach expiry handler to this new row
    attachExpiryHandler(tr.querySelector(".expiryField"));

    rowIndex++;
}

// Save row
function saveRow(tr) {
    let data = {
		sequnce: tr.querySelector(".sequnce").value,
        name: tr.querySelector(".nameField").value,
        ingrediant: tr.querySelector(".ingredientField").value,
        qty: tr.querySelector(".qtyField").value,
        bonus: tr.querySelector(".bonusField").value,
        perpack: tr.querySelector(".perPackField").value,
        batch: tr.querySelector(".batchField").value,
        expiry: formatDateForMySQL(tr.querySelector(".expiryField").value),
        expiry_alert: tr.querySelector(".expiryAlertField").value,
        packprice: tr.querySelector(".packPriceField").value,
        discount_per: tr.querySelector(".discountPerField").value,
        discount_fix: tr.querySelector(".discountFlatField").value,
        gst_per: tr.querySelector(".gstPerField").value,
        gst_fix: tr.querySelector(".gstFlatField").value,
        final_price: tr.querySelector(".finalField").value,
        buy_price: tr.querySelector(".buyPriceField").value,
        box_price: tr.querySelector(".boxPriceField").value,
        sale_price: tr.querySelector(".salePriceField").value,
    };

    fetch(saveUrl, {
        method: "POST",
        headers: {
            "Content-Type": "application/json",
            "X-CSRF-TOKEN": "{{ csrf_token() }}",
        },
        body: JSON.stringify(data),
    })
    .then(res => res.json())
    .then(res => {
        if(res.success && res.row.id) {
            convertToStaticRow(tr, res.row);
            addEditableRow();
            let newRow = document.querySelector("#rows tr:last-child .nameField");
            if(newRow) newRow.focus();
        }
    });
}

// Convert editable row → static row
function convertToStaticRow(tr, row) {
    function v(x) { return x === null || x === undefined ? "" : x; }

    tr.dataset.id = row.id; // store ID for delete
    tr.innerHTML = `
		<td>${v(row.sequnce)}</td>
        <td>${v(row.name)}</td>
        <td>${v(row.ingrediant)}</td>
        <td>${v(row.qty)}</td>
        <td>${v(row.bonus)}</td>
        <td>${v(row.perpack)}</td>
        <td>${v(row.batch)}</td>
        <td>${v(row.expiry)}</td>
        <td>${row.expiry_alert ? row.expiry_alert + ' Month' : ''}</td>
        <td>${v(row.packprice)}</td>
        <td>${v(row.discount_per)}</td>
        <td>${v(row.discount_fix)}</td>
        <td>${v(row.gst_per)}</td>
        <td>${v(row.gst_fix)}</td>
        <td>${v(row.final_price)}</td>
        <td>${v(row.buy_price)}</td>
        <td>${v(row.box_price)}</td>
        <td>${v(row.sale_price)}</td>
        <td><button class="deleteBtn" onclick="deleteRow(${row.id}, this)">×</button></td>
    `;
    updateSummary();
}

// Delete row
function deleteRow(id, btn) {
    if(!confirm("Are you sure you want to delete this row?")) return;

    let url = deleteUrl.replace(':id', id);

    fetch(url, {
        method: "DELETE",
        headers: {
            "X-CSRF-TOKEN": "{{ csrf_token() }}",
        }
    })
    .then(res => res.json())
    .then(res => {
        if(res.success) {
            let tr = btn.closest("tr");
            tr.remove();
            updateSummary();
        }
    });
}

// Format expiry date
function formatDateForMySQL(dateStr) {
    if(!dateStr) return null;
    const parts = dateStr.split('/');
    if(parts.length !== 3) return null;
    return `${parts[2]}-${parts[1].padStart(2,'0')}-${parts[0].padStart(2,'0')}`;
}

// Calculate row prices
function calculateRow(tr) {
    let name = tr.querySelector(".nameField").value.trim();
    if(name === "") {
        tr.querySelector(".finalField").value = "";
        tr.querySelector(".buyPriceField").value = "";
        tr.querySelector(".salePriceField").value = "";
        updateSummary();
        return;
    }

    let qty = parseFloat(tr.querySelector(".qtyField").value) || 0;
    let bonus = parseFloat(tr.querySelector(".bonusField").value) || 0;
    let perPack = parseFloat(tr.querySelector(".perPackField").value) || 1;
    let packPrice = parseFloat(tr.querySelector(".packPriceField").value) || 0;
    let dPercent = parseFloat(tr.querySelector(".discountPerField").value) || 0;
    let dFlat = parseFloat(tr.querySelector(".discountFlatField").value) || 0;
    let gstPercent = parseFloat(tr.querySelector(".gstPerField").value) || 0;
    let gstFlat = parseFloat(tr.querySelector(".gstFlatField").value) || 0;
    let boxPrice = parseFloat(tr.querySelector(".boxPriceField").value) || 0;

    let totalQty = qty + bonus;
    let totalUnits = totalQty * (perPack || 1);
    let baseAmount = qty * packPrice;
    let discountAmount = (baseAmount * dPercent / 100) + dFlat;
    let gstAmount = (baseAmount * gstPercent / 100) + gstFlat;
    let finalPrice = baseAmount - discountAmount + gstAmount;
    let buyPrice = totalUnits > 0 ? finalPrice / totalUnits : 0;

    tr.querySelector(".finalField").value = finalPrice.toFixed(2);
    tr.querySelector(".buyPriceField").value = buyPrice.toFixed(2);

    let salePrice = (boxPrice > 0 && perPack > 0) ? (boxPrice / perPack).toFixed(2) : "";
    tr.querySelector(".salePriceField").value = salePrice;

    updateSummary();
}

// Initial summary update
window.addEventListener('DOMContentLoaded', () => updateSummary());



// Default close date if user input is invalid





</script>

@endsection