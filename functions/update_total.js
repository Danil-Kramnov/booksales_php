// update total price based on quantity
function updateTotal() {
    const qtyInput = document.getElementById('qty');
    const totalSpan = document.getElementById('total');
    
    if (qtyInput && totalSpan) {
        const price = parseFloat(qtyInput.dataset.price);
        const quantity = parseInt(qtyInput.value) || 1;
        const total = (price * quantity).toFixed(2);
        totalSpan.textContent = total;
    }
}

// event listener when page loads
document.addEventListener('DOMContentLoaded', function() {
    const qtyInput = document.getElementById('qty');
    if (qtyInput) {
        qtyInput.addEventListener('input', updateTotal);
    }
});