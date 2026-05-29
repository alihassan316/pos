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

    #searchResults {
        position:absolute;
        background:white;
        border:1px solid #ccc;
        width:100%;
        z-index:9999;
        display:none;
        max-height:150px;
        overflow-y:auto;
    }
    #searchResults div {
        padding:4px;
        cursor:pointer;
        border-bottom:1px solid #eee;
        font-size:13px;
    }
    #searchResults div:hover {
        background:#f1f1f1;
    }
	
	#searchResults .result-item {
        padding: 6px 10px;
        cursor: pointer;
    }

    #searchResults .result-item {
        padding: 6px 10px;
        cursor: pointer;
    }

    /* Keyboard or mouse active state */
    #searchResults .result-item.active {
        background: #007bff !important;
        color: #fff !important;
    }

    /* Hover should temporarily activate the same style */
    #searchResults .result-item:hover {
        background: #007bff !important;
        color: #fff !important;
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
                    
                     <td style="position:relative;">
    <input type="text" name="product_name" class="form-control nameField" required autocomplete="off">
    <div id="searchResults"></div>
</td>
                    <td><input type="text" name="ingrediant" class="form-control ingredientField"></td>
                    <td><input type="text" name="company" class="form-control companyField"></td>
                    <td><input type="text" name="qty" class="form-control calc qtyField"></td>
                    <td><input type="text" name="bonus" class="form-control calc bonusField"></td>
                    <td><input type="text" name="perpack" class="form-control calc perPackField"></td>
                    <td><input type="text" name="batch" class="form-control batchField"></td>
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

const nameInput = document.querySelector(".nameField");
const resultBox = document.getElementById("searchResults");

let searchTimeout = null;
let activeIndex = -1;
let results = [];

function updateActive(items) {

    items.forEach((el, i) => {
        if (i === activeIndex) {
            el.style.background = "#007bff";
            el.style.color = "#000";
            el.scrollIntoView({ block: "nearest" });
        } else {
            el.style.background = "#fff";
            el.style.color = "#000";
        }
    });
}

nameInput.addEventListener("input", function () {
    const q = this.value.trim();

    if (q.length < 3) {
        resultBox.style.display = "none";
        return;
    }

    clearTimeout(searchTimeout);

    searchTimeout = setTimeout(() => {
        fetch(`{{ route('invoice.search.product') }}?q=${encodeURIComponent(q)}`)
            .then(res => res.json())
            .then(data => showSearchResults(data));
    }, 300);
});

function showSearchResults(data) {

    results = data;
    activeIndex = -1;

    if (!data.length) {
        resultBox.style.display = "none";
        return;
    }

    let html = "";

    data.forEach((item, index) => {
        html += `
            <div class="result-item"
                data-index="${index}"
                data-name="${item.name}"
                data-company="${item.company}"
                data-ingredient="${item.ingredient}"
				data-expiryField="${item.expiry}"
				data-qtyField="${item.qty}"
				data-bonus="${item.bonus}"
				data-batch="${item.batch_no}"
				data-buyprice="${item.buyprice}"
				data-boxprice="${item.box_price}"
				data-discount_percent="${item.discount_percent}"
				data-discount_flat="${item.discount_flat}"
				data-gst_percent="${item.gst_percent}"
				data-gst_flat="${item.gst_flat}"
				data-expiry_alert="${item.expiry_alert_months}"
                data-perpack="${item.items_per_box}">
                ${item.name} — ${item.company}
            </div>
        `;
    });

    resultBox.innerHTML = html;
    resultBox.style.display = "block";
	
	resultBox.querySelectorAll(".result-item").forEach(div => {
		div.addEventListener("click", function () {
			applyProductSelection(this);
		});
	});
}

nameInput.addEventListener("keydown", function (e) {

    const items = resultBox.querySelectorAll(".result-item");

    if (!items.length) return;

    // DOWN ARROW
    if (e.key === "ArrowDown") {
        e.preventDefault();
        activeIndex = (activeIndex + 1) % items.length;
        updateActive(items);
    }

    // UP ARROW
    if (e.key === "ArrowUp") {
        e.preventDefault();
        activeIndex = (activeIndex - 1 + items.length) % items.length;
        updateActive(items);
    }

    // ENTER or TAB → SELECT
    if (e.key === "Enter") {
        if (activeIndex >= 0) {
            e.preventDefault();
            items[activeIndex].click();
        }
    }
	
	if (e.key === "Tab") {

        if (activeIndex >= 0) {
          //  e.preventDefault();
          //  items[activeIndex].click();
        } else {
            // allow natural tab flow
            //resultBox.style.display = "none";
           // activeIndex = -1;
        }
    }
	
	
});



function applyProductSelection(itemDiv) {
    const name = itemDiv.getAttribute("data-name");
    const company = itemDiv.getAttribute("data-company");
    const ingredient = itemDiv.getAttribute("data-ingredient");
    const perpack = itemDiv.getAttribute("data-perpack");
	const expiry_alert = itemDiv.getAttribute("data-expiry_alert") || "";
	const expiry = itemDiv.getAttribute("data-expiryField") || "";
	const qtyField = itemDiv.getAttribute("data-qtyField") || ""; 
	const bonus = itemDiv.getAttribute("data-bonus") || ""; 
	const batch = itemDiv.getAttribute("data-batch") || ""; 
	const boxprice = itemDiv.getAttribute("data-boxprice") || ""; 
	const discount_percent = itemDiv.getAttribute("data-discount_percent") || ""; 
	const discount_flat = itemDiv.getAttribute("data-discount_flat") || ""; 
	const gst_percent = itemDiv.getAttribute("data-gst_percent") || ""; 
	const gst_flat = itemDiv.getAttribute("data-gst_flat") || ""; 
	const buyprice = itemDiv.getAttribute("data-buyprice") || ""; 

    // Fill fields
    inputRow.querySelector(".nameField").value = name;
    inputRow.querySelector(".ingredientField").value = ingredient;
    inputRow.querySelector(".companyField").value = company;
    inputRow.querySelector(".perPackField").value = perpack;
	inputRow.querySelector(".expiryField").value = expiry;
	inputRow.querySelector(".qtyField").value = qtyField;
	inputRow.querySelector(".bonusField").value = bonus;
	inputRow.querySelector(".batchField").value = batch;
	inputRow.querySelector(".packPriceField").value = buyprice;
	inputRow.querySelector(".boxPriceField").value = boxprice;
	inputRow.querySelector(".discountPerField").value = discount_percent;
	inputRow.querySelector(".discountFlatField").value = discount_flat;
	inputRow.querySelector(".gstPerField").value = gst_percent;
	inputRow.querySelector(".gstFlatField").value = gst_flat;
	
	//inputRow.querySelector(".expiry_alert").value = expiry_alert;
	inputRow.querySelector('select[name="expiry_alert"]').value = expiry_alert || "";

    resultBox.style.display = "none";

    calculateRow();
}

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