
    
    <form action="{{ route('sales.store') }}" method="POST" id="saleForm">
        @csrf

        <table class="table table-bordered" id="invoiceTable">
            <thead>
                <tr>
                    <th>Product</th>
                    <th>Stock</th>
                    <th>Quantity</th>
                    <th>Unit Price</th>
                    <th>Total</th>
                    <th><button type="button" id="addRow" class="btn btn-sm btn-success">+</button></th>
                </tr>
            </thead>
            <tbody>
                <tr>
                    <td>
                        <select name="products[0][product_id]" class="form-control product-select">
                            <option value="">-- Select --</option>
                            @foreach($products as $product)
                                <option value="{{ $product->id }}" data-price="{{ $product->sell_price }}" data-stock="{{ $product->current_stock }}">{{ $product->name }}</option>
                            @endforeach
                        </select>
                    </td>
                    <td class="stock">0</td>
                    <td><input type="number" name="products[0][quantity]" class="form-control quantity" min="1" value="1"></td>
                    <td><input type="number" name="products[0][unit_price]" class="form-control unit-price" step="0.01" value="0"></td>
                    <td class="total">0</td>
                    <td><button type="button" class="btn btn-sm btn-danger remove-row">x</button></td>
                </tr>
            </tbody>
        </table>

        <div class="mb-3">
            <label>Total: </label>
            <span id="grandTotal">0</span>
        </div>

        <div class="mb-3">
            <label>Paid Amount</label>
            <input type="number" name="paid_amount" id="paidAmount" class="form-control" step="0.01" value="0">
        </div>

        <div class="mb-3">
            <label>Due Amount: </label>
            <span id="dueAmount">0</span>
        </div>

        <button class="btn btn-primary">Complete Sale</button>
    </form>
</div>

<script>
let rowIndex = 1;

function calculateRow(row) {
    const quantity = parseFloat(row.querySelector('.quantity').value) || 0;
    const price = parseFloat(row.querySelector('.unit-price').value) || 0;
    const total = quantity * price;
    row.querySelector('.total').innerText = total.toFixed(2);
    calculateGrandTotal();
}

function calculateGrandTotal() {
    let grandTotal = 0;
    document.querySelectorAll('#invoiceTable tbody tr').forEach(row => {
        const total = parseFloat(row.querySelector('.total').innerText) || 0;
        grandTotal += total;
    });
    document.getElementById('grandTotal').innerText = grandTotal.toFixed(2);

    const paid = parseFloat(document.getElementById('paidAmount').value) || 0;
    document.getElementById('dueAmount').innerText = (grandTotal - paid).toFixed(2);
}

document.getElementById('invoiceTable').addEventListener('change', e => {
    if(e.target.classList.contains('product-select')){
        const row = e.target.closest('tr');
        const selected = e.target.selectedOptions[0];
        row.querySelector('.unit-price').value = selected.dataset.price;
        row.querySelector('.stock').innerText = selected.dataset.stock;
        calculateRow(row);
    }
});

document.getElementById('invoiceTable').addEventListener('input', e => {
    if(e.target.classList.contains('quantity') || e.target.classList.contains('unit-price')){
        const row = e.target.closest('tr');
        calculateRow(row);
    }
});

document.getElementById('addRow').addEventListener('click', () => {
    const tbody = document.querySelector('#invoiceTable tbody');
    const newRow = tbody.rows[0].cloneNode(true);

    // Reset values
    newRow.querySelector('.product-select').selectedIndex = 0;
    newRow.querySelector('.quantity').value = 1;
    newRow.querySelector('.unit-price').value = 0;
    newRow.querySelector('.stock').innerText = 0;
    newRow.querySelector('.total').innerText = 0;

    // Update input names
    newRow.querySelectorAll('select, input').forEach(input => {
        const name = input.getAttribute('name');
        input.setAttribute('name', name.replace(/\d+/, rowIndex));
    });

    tbody.appendChild(newRow);
    rowIndex++;
});

document.getElementById('invoiceTable').addEventListener('click', e => {
    if(e.target.classList.contains('remove-row')){
        const tbody = document.querySelector('#invoiceTable tbody');
        if(tbody.rows.length > 1){
            e.target.closest('tr').remove();
            calculateGrandTotal();
        }
    }
});

document.getElementById('paidAmount').addEventListener('input', calculateGrandTotal);
</script>
@endsection