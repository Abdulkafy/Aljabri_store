// إدارة صفحة المنتجات
document.addEventListener('DOMContentLoaded', function() {
    // إضافة أحداث للفلترة
    document.getElementById('categoryFilter').addEventListener('change', filterProducts);
    document.getElementById('sortFilter').addEventListener('change', sortProducts);
    document.getElementById('productSearch').addEventListener('input', searchProducts);
});

// فلترة المنتجات
function filterProducts() {
    const category = document.getElementById('categoryFilter').value;
    const products = document.querySelectorAll('.product-card');
    
    products.forEach(product => {
        if(category === '' || product.getAttribute('data-category') === category) {
            product.style.display = 'block';
        } else {
            product.style.display = 'none';
        }
    });
}

// ترتيب المنتجات
function sortProducts() {
    const sortBy = document.getElementById('sortFilter').value;
    const productsGrid = document.getElementById('productsGrid');
    const products = Array.from(document.querySelectorAll('.product-card'));
    
    products.sort((a, b) => {
        const priceA = parseFloat(a.getAttribute('data-price'));
        const priceB = parseFloat(b.getAttribute('data-price'));
        
        switch(sortBy) {
            case 'price_low':
                return priceA - priceB;
            case 'price_high':
                return priceB - priceA;
            case 'newest':
            default:
                return 0; // في التطبيق الحقيقي، ستستخدم تاريخ الإضافة
        }
    });
    
    // إعادة ترتيب المنتجات في الشبكة
    products.forEach(product => {
        productsGrid.appendChild(product);
    });
}

// بحث المنتجات
function searchProducts() {
    const searchTerm = document.getElementById('productSearch').value.toLowerCase();
    const products = document.querySelectorAll('.product-card');
    
    products.forEach(product => {
        const productName = product.querySelector('h3').textContent.toLowerCase();
        const productDescription = product.querySelector('.product-description').textContent.toLowerCase();
        
        if(productName.includes(searchTerm) || productDescription.includes(searchTerm)) {
            product.style.display = 'block';
        } else {
            product.style.display = 'none';
        }
    });
}