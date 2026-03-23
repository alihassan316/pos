@extends('layouts.pos')

@section('title', 'New Sale')

@push('styles')
<link href="https://cdn.jsdelivr.net/npm/tom-select@2.3.1/dist/css/tom-select.bootstrap5.min.css" rel="stylesheet">
<style>
    .ts-wrapper { width: 100%; }
    .ts-control { min-height: 32px !important; padding: 3px 8px !important; font-size: 13px !important; border-radius: 6px !important; }
    .ts-dropdown { font-size: 13px; z-index: 9999 !important; }
    .ts-dropdown .option { padding: 7px 12px; }
    .manual-entry-row .product-manual-input { font-size: 13px; }
    .manual-badge { font-size: 10px; vertical-align: middle; }
</style>
@endpush

@section('content')
<div class="d-flex align-items-center gap-2 mb-4">
    <a href="{{ route('sales.index') }}" class="btn btn-sm btn-outline-secondary">
        <i class="bi bi-arrow-left"></i>
    </a>
    <div>
        <h4 class="mb-0 fw-bold text-dark">New Sale</h4>
        <small class="text-muted">Create a new invoice</small>
    </div>
</div>

<form action="{{ route('sales.store') }}" method="POST" id="saleForm">
@csrf
<div class="row g-4">
    <div class="col-lg-8">
        <div class="card">
            <div class="card-header d-flex justify-content-between align-items-center">
                <h5><i class="bi bi-cart3 me-2"></i>Sale Items</h5>
                <button type="button" id="addRow" class="btn btn-sm btn-outline-primary">
                    <i class="bi bi-plus-lg me-1"></i> Add Item
                </button>
            </div>
            <div class="card-body p-0">
                <table class="table mb-0" id="invoiceTable">
                    <thead>
                        <tr>
                            <th style="width:28%">Product</th>
                            <th style="width:10%">Stock</th>
                            <th style="width:13%">Qty</th>
                            <th style="width:14%">Unit Price</th>
                            <th style="width:14%">Discount</th>
                            <th style="width:14%">Total</th>
                            <th style="width:7%"></th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr>
                            <td><select name="products[0][product_id]" class="product-select"></select></td>
                            <td><span class="badge bg-secondary stock-badge">0</span></td>
                            <td><input type="number" name="products[0][quantity]" class="form-control form-control-sm quantity" min="1" value="1"></td>
                            <td><input type="number" name="products[0][unit_price]" class="form-control form-control-sm unit-price" step="0.01" value="0.00"></td>
                            <td><input type="number" name="products[0][discount]" class="form-control form-control-sm discount" step="0.01" value="0.00">
                            
                            	
                                <input type="hidden" name="products[0][item_discount_value]" class="item-discount-value" value="0">
                                <input type="hidden" name="products[0][item_discount_amount]" class="item-discount-amount" value="0">
                                <input type="hidden" name="products[0][item_discount_type]" class="item-discount-type" value="percent">
                                                        
                            </td>

                            <td class="fw-semibold row-total align-middle">Rs 0</td>
                            <td><button type="button" class="btn btn-sm btn-outline-danger remove-row"><i class="bi bi-trash"></i></button></td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <div class="col-lg-4">
        <div class="card">
            <div class="card-header">
                <h5><i class="bi bi-receipt me-2"></i>Payment</h5>
            </div>
            
            <!--
            <div class="card-body">
                <div class="d-flex justify-content-between py-2 border-bottom mb-2">
                    <span class="text-muted">Grand Total</span>
                    <span class="fw-bold fs-5" id="grandTotal">Rs 0</span>
                </div>
                <div class="mb-3">
                    <label class="form-label">Paid Amount</label>
                    <div class="input-group">
                        <span class="input-group-text">Rs</span>
                        <input type="number" name="paid_amount" id="paidAmount" class="form-control" step="0.01" value="0" min="0">
                    </div>
                </div>
                <div class="d-flex justify-content-between py-2 border-top mb-3">
                    <span class="text-muted">Due Amount</span>
                    <span class="fw-bold text-danger" id="dueAmount">Rs 0</span>
                </div>
                <input type="hidden" name="print" id="printFlag" value="0">
                <div class="d-grid gap-2">
                    <button type="submit" class="btn btn-primary py-2" onclick="document.getElementById('printFlag').value='0'">
                        <i class="bi bi-check-circle me-2"></i> Complete Sale
                    </button>
                    <button type="submit" class="btn btn-outline-dark py-2" onclick="document.getElementById('printFlag').value='1'">
                        <i class="bi bi-printer me-2"></i> Complete &amp; Print Invoice
                    </button>
                </div>
            </div>
            -->
            
            <div class="card-body">

    <!-- Subtotal -->
    <div class="d-flex justify-content-between py-2 border-bottom mb-2">
        <span class="text-muted">Sub Total</span>
        <span class="fw-bold fs-6" id="subTotal">Rs 0</span>
    </div>

    <!-- Discount Row -->
    <div class="mb-3 d-flex1 align-items-center gap-2">

        <!-- Checkbox = percentage -->
        <div class="mb-3 d-flex">
        	
            <div class="input-group">
        		<input type="checkbox" id="discountIsPercent" style="transform: scale(1.3);">&nbsp;
                Discount (%)
            </div>
            
    	</div>
        <!-- Discount value -->
        
        
        <div class="mb-3">
            
            <div class="input-group">
                <input type="number" id="discountValue" class="form-control" step="0.01"
               value="0" min="0" placeholder="Discount">
            </div>
        </div>
        
        
        <!-- Hidden fields to store in DB -->
        <input type="hidden" name="discount_type" id="hiddenDiscountType">
        <input type="hidden" name="discount_value" id="hiddenDiscountValue">
    
    </div>

    <!-- Grand Total -->
    <div class="d-flex justify-content-between py-2 border-top mb-2">
        <span class="text-muted">Grand Total</span>
        <span class="fw-bold fs-5" id="grandTotal">Rs 0</span>
    </div>

    <!-- Paid Amount -->
    <div class="mb-3">
        <label class="form-label">Paid Amount</label>
        <div class="input-group">
            <span class="input-group-text">Rs</span>
            <input type="number" name="paid_amount" id="paidAmount"
                   class="form-control" step="0.01" value="0" min="0">
        </div>
    </div>

    <!-- Due -->
    <div class="d-flex justify-content-between py-2 border-top mb-3">
        <span class="text-muted">Due Amount</span>
        <span class="fw-bold text-danger" id="dueAmount">Rs 0</span>
    </div>

    <input type="hidden" name="print" id="printFlag" value="0">

    <div class="d-grid gap-2">
        <button type="submit" class="btn btn-primary py-2"
            onclick="document.getElementById('printFlag').value='0'">
            <i class="bi bi-check-circle me-2"></i> Complete Sale
        </button>

        <button type="submit" class="btn btn-outline-dark py-2"
            onclick="document.getElementById('printFlag').value='1'">
            <i class="bi bi-printer me-2"></i> Complete & Print Invoice
        </button>
    </div>
</div>
            
            
            
            
            
        </div>
    </div>
</div>
</form>
@endsection

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/tom-select@2.3.1/dist/js/tom-select.complete.min.js"></script>
<script>
(function () {
    var rowIndex = 1;
    var searchUrl = "{{ route('products.search') }}";
    var cache = {};
    var activeTs = null;

    function stockColor(s) {
        return s == 0 ? "#dc3545" : s <= 5 ? "#fd7e14" : "#198754";
    }
    function updateStockBadge(badge, stock) {
        badge.textContent = stock;
        badge.className = "badge stock-badge " + (stock == 0 ? "bg-danger" : stock <= 5 ? "bg-warning text-dark" : "bg-success");
    }
	
	function calculateRow(row) {
		let qty = parseFloat(row.querySelector(".quantity").value) || 0;
		let price = parseFloat(row.querySelector(".unit-price").value) || 0;
		let discountPercent = parseFloat(row.querySelector(".discount").value) || 0;
	
		// discount amount
		let discountAmount = (qty * price) * (discountPercent / 100);
	
		// final row total
		let total = (qty * price) - discountAmount;
		if (total < 0) total = 0;
	
		// update UI
		row.querySelector(".row-total").textContent = "Rs " + total.toLocaleString();
	
		// save exact discount in hidden fields
		row.querySelector(".item-discount-value").value = discountPercent;
		row.querySelector(".item-discount-amount").value = discountAmount.toFixed(2);
		row.querySelector(".item-discount-type").value = "percent";
	
		calculateGrandTotal();
}
	
    function calculateRow_old(row) {
        var qty = parseFloat(row.querySelector(".quantity").value) || 0;
        var price = parseFloat(row.querySelector(".unit-price").value) || 0;
		var discount = parseFloat(row.querySelector(".discount").value) || 0;
		var total = (qty * price) - (qty * price * (discount/100));
		//var total = (qty * price) - discount;
		if (total < 0) total = 0;

		row.querySelector(".row-total").textContent = "Rs " + total.toLocaleString();
        //row.querySelector(".row-total").textContent = "Rs " + (qty * price).toLocaleString();
        calculateGrandTotal();
    }
	
	document.getElementById("discountValue").addEventListener("input", calculateGrandTotal);
document.getElementById("discountIsPercent").addEventListener("change", calculateGrandTotal);
	
	function calculateGrandTotal() {
		var subtotal = 0;
	
		document.querySelectorAll(".row-total").forEach(function(el) {
			subtotal += parseFloat(el.textContent.replace(/[^0-9.]/g, '')) || 0;
		});
	
		document.getElementById("subTotal").textContent = "Rs " + subtotal.toLocaleString();
	
		var discountValue = parseFloat(document.getElementById("discountValue").value) || 0;
		var isPercent = document.getElementById("discountIsPercent").checked;
	
		var discountAmount = 0;
	
		// apply only if discountValue > 0
		if (discountValue > 0) {
			if (isPercent) {
				discountAmount = subtotal * (discountValue / 100);
			} else {
				discountAmount = discountValue;
			}
		}
	
		if (discountAmount > subtotal) discountAmount = subtotal;
	
		// save to DB
		document.getElementById("hiddenDiscountType").value = discountValue > 0 ? (isPercent ? "percent" : "fixed") : "none";
		document.getElementById("hiddenDiscountValue").value = discountValue > 0 ? discountValue : 0;
	
		var grandTotal = subtotal - discountAmount;
		if (grandTotal < 0) grandTotal = 0;
	
		document.getElementById("grandTotal").textContent = "Rs " + grandTotal.toLocaleString();
	
		// Auto-fill paid amount = grand total
		document.getElementById("paidAmount").value = grandTotal.toFixed(2);
	
		// Due always 0 in auto-pay mode
		document.getElementById("dueAmount").textContent = "Rs 0";
	}
	
    function calculateGrandTotal_total() {
        var total = 0;
        document.querySelectorAll(".row-total").forEach(function(el) {
            total += parseFloat(el.textContent.replace(/[^0-9.]/g, '')) || 0;
        });
        document.getElementById("grandTotal").textContent = "Rs " + total.toLocaleString();
        var paid = parseFloat(document.getElementById("paidAmount").value) || 0;
        var due = total - paid;
        document.getElementById("dueAmount").textContent = "Rs " + (due < 0 ? 0 : due).toLocaleString();
    }

    function initTomSelect(selectEl) {
        return new TomSelect(selectEl, {
            valueField: "value",
            labelField: "text",
            searchField: ["text","sku","barcode"], // <- search name, sku, barcode
            placeholder: "Search product by name, SKU or barcode...",
            loadThrottle: 300,
            create: false,
            onFocus: function() { activeTs = this; },
            load: function(query, callback) {
    if (!query.length) return callback(); // return if empty input

    fetch(searchUrl + "?q=" + encodeURIComponent(query))
        .then(r => r.json())
        .then(data => {
            // filter results for partial matches
            const filtered = data.filter(p => 
                p.text.toLowerCase().includes(query.toLowerCase()) ||  // partial match on name
                (p.sku && p.sku.toLowerCase().includes(query.toLowerCase())) || // partial SKU
                (p.barcode && p.barcode.toLowerCase().includes(query.toLowerCase())) // partial barcode
            );

            filtered.forEach(p => { cache[p.value] = p; }); // store in cache
            callback(filtered); // pass filtered list to TomSelect
        })
        .catch(() => callback());
},
            render: {
                option: function(data) {
                    return `<div style="display:flex;justify-content:space-between;align-items:center;gap:8px;">
                        <span>${data.text}${data.sku ? " ("+data.sku+")" : ""}</span>
                        <small style="color:${stockColor(data.stock)};font-size:11px;font-weight:600;white-space:nowrap;">Stock: ${data.stock}</small>
                    </div>`;
                },
                item: function(data) {
                    return `<span>${data.text}${data.sku ? " ("+data.sku+")" : ""}</span>`;
                },
                loading: function() {
                    return `<div style="padding:8px 12px;color:#94a3b8;font-size:13px;">Loading...</div>`;
                },
                no_results: function(data) {
                    var q = data.input || "";
                    return `<div class="no-results" style="padding:8px 12px;">
                        <span style="color:#94a3b8;font-size:13px;">No product found for "${q}"</span><br>
                        <button type="button" class="btn btn-sm btn-outline-warning mt-2 enter-manually-btn" data-name="${q}">
                        <i class="bi bi-pencil-square me-1"></i>Enter "${q}" manually</button>
                    </div>`;
                }
            },
            onChange: function(value) {
                var row = selectEl.closest("tr");
                var product = cache[value];
                if (product) {
                    row.querySelector(".unit-price").value = parseFloat(product.price).toFixed(2);
                    updateStockBadge(row.querySelector(".stock-badge"), product.stock);
					if (product.discount) {
						row.querySelector(".discount").value = parseFloat(product.discount).toFixed(2);
					} else {
						row.querySelector(".discount").value = "0.00";
					}
                } else {
                    row.querySelector(".unit-price").value = "0.00";
                    updateStockBadge(row.querySelector(".stock-badge"), 0);
                }
                calculateRow(row);
            }
        });
    }

    document.querySelectorAll(".product-select").forEach(el => initTomSelect(el).load(""));
    document.getElementById("invoiceTable").addEventListener("input", e => {
        if (e.target.classList.contains("quantity") || e.target.classList.contains("unit-price") || e.target.classList.contains("discount")) {
            calculateRow(e.target.closest("tr"));
        }
    });
    document.getElementById("addRow").addEventListener("click", function() {
        var tbody = document.querySelector("#invoiceTable tbody");
        var tr = document.createElement("tr");
        var i = rowIndex;
        tr.innerHTML =
            `<td><select name="products[${i}][product_id]" class="product-select"></select></td>
            <td><span class="badge bg-secondary stock-badge">0</span></td>
            <td><input type="number" name="products[${i}][quantity]" class="form-control form-control-sm quantity" min="1" value="1"></td>
            <td><input type="number" name="products[${i}][unit_price]" class="form-control form-control-sm unit-price" step="0.01" value="0.00"></td>
			<td><input type="number" name="products[${i}][discount]" class="form-control form-control-sm discount" step="0.01" value="0.00">
			 <input type="hidden" name="products[${i}][item_discount_value]" class="item-discount-value" value="0">
    <input type="hidden" name="products[${i}][item_discount_amount]" class="item-discount-amount" value="0">
    <input type="hidden" name="products[${i}][item_discount_type]" class="item-discount-type" value="percent">
			</td>
            <td class="fw-semibold row-total align-middle">Rs 0</td>
            <td><button type="button" class="btn btn-sm btn-outline-danger remove-row"><i class="bi bi-trash"></i></button></td>`;
        tbody.appendChild(tr);
        initTomSelect(tr.querySelector(".product-select")).load("");
        rowIndex++;
    });
    document.getElementById("invoiceTable").addEventListener("click", e => {
        if (e.target.closest(".remove-row")) {
            var tbody = document.querySelector("#invoiceTable tbody");
            if (tbody.rows.length > 1) {
                e.target.closest("tr").remove();
                calculateGrandTotal();
            }
        }
    });
    document.getElementById("paidAmount").addEventListener("input", calculateGrandTotal);

    function switchToManual(row, productName) {
        var td = row.querySelector("td:first-child");
        var idx = row.querySelector("[name*=product_id]").name.match(/\[(\d+)\]/)[1];
        var sel = td.querySelector(".product-select");
        if (sel && sel.tomselect) { sel.tomselect.destroy(); }
        td.innerHTML =
            `<div class="d-flex align-items-center gap-1">
                <input type="text" name="products[${idx}][custom_name]" class="form-control form-control-sm product-manual-input" placeholder="Product name" value="${productName}" required>
                <input type="hidden" name="products[${idx}][product_id]" value="0">
                <span class="badge bg-warning text-dark manual-badge">Manual</span>
            </div>`;
        var badge = row.querySelector(".stock-badge");
        badge.textContent = "-";
        badge.className = "badge bg-secondary stock-badge";
        row.classList.add("manual-entry-row");
        row.querySelector(".unit-price").focus();
    }
    document.addEventListener("click", e => {
        var btn = e.target.closest(".enter-manually-btn");
        if (!btn) return;
        var name = btn.getAttribute("data-name") || "";
        if (activeTs) {
            var row = activeTs.input.closest("tr");
            if (row) {
                activeTs.close();
                switchToManual(row, name);
            }
        }
    });
})();


document.addEventListener("keydown", function(e) {
    // Ctrl + N = Add new row
    if (e.ctrlKey && e.key.toLowerCase() === "n") {
        e.preventDefault(); // stop browser from opening new window
        document.getElementById("addRow").click(); // trigger existing Add Row button
        // focus last row product input
        var tbody = document.querySelector("#invoiceTable tbody");
        var lastRow = tbody.rows[tbody.rows.length - 1];
        var input = lastRow.querySelector(".product-select");
        if (input && input.tomselect) {
            input.tomselect.focus();
        }
    }

    // Ctrl + S = Submit sale
    if (e.ctrlKey && e.key.toLowerCase() === "s") {
        e.preventDefault();
        document.getElementById("printFlag").value = 0; // normal save
        document.getElementById("saleForm").submit();
    }

    // Ctrl + P = Submit & Print
    if (e.ctrlKey && e.key.toLowerCase() === "p") {
        e.preventDefault();
        document.getElementById("printFlag").value = 1; // print mode
        document.getElementById("saleForm").submit();
    }
});


</script>
@endpush