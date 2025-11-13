// إدارة لوحة التحكم
document.addEventListener('DOMContentLoaded', function() {
    // تبديل الشريط الجانبي على الشاشات الصغيرة
    const sidebarToggle = document.querySelector('.sidebar-toggle');
    const sidebar = document.querySelector('.sidebar');
    
    if(sidebarToggle) {
        sidebarToggle.addEventListener('click', function() {
            sidebar.classList.toggle('active');
        });
    }
    
    // تأكيد الحذف
    const deleteButtons = document.querySelectorAll('.btn-delete');
    deleteButtons.forEach(button => {
        button.addEventListener('click', function(e) {
            if(!confirm('هل أنت متأكد من الحذف؟ لا يمكن التراجع عن هذا الإجراء.')) {
                e.preventDefault();
            }
        });
    });
    
    // إدارة رفع الملفات
    const fileInputs = document.querySelectorAll('input[type="file"]');
    fileInputs.forEach(input => {
        input.addEventListener('change', function() {
            const fileName = this.files[0]?.name || 'لم يتم اختيار ملف';
            const label = this.previousElementSibling;
            if(label && label.tagName === 'LABEL') {
                label.textContent = fileName;
            }
        });
    });
    
    // تحديث حالة الطلب تلقائياً
    const statusForms = document.querySelectorAll('.status-form');
    statusForms.forEach(form => {
        const select = form.querySelector('select');
        select.addEventListener('change', function() {
            // إظهار مؤشر تحميل
            const originalText = this.options[this.selectedIndex].text;
            this.disabled = true;
            
            // إرسال النموذج
            form.submit();
        });
    });
    
    // البحث في الجداول
    const addSearchFunctionality = () => {
        const tables = document.querySelectorAll('.data-table');
        tables.forEach(table => {
            const headerRow = table.querySelector('thead tr');
            const searchHeader = document.createElement('th');
            searchHeader.innerHTML = '<input type="text" placeholder="بحث..." class="table-search">';
            headerRow.appendChild(searchHeader);
            
            const searchInput = searchHeader.querySelector('input');
            searchInput.addEventListener('input', function() {
                const searchTerm = this.value.toLowerCase();
                const rows = table.querySelectorAll('tbody tr');
                
                rows.forEach(row => {
                    const text = row.textContent.toLowerCase();
                    if(text.includes(searchTerm)) {
                        row.style.display = '';
                    } else {
                        row.style.display = 'none';
                    }
                });
            });
        });
    };
    
    // تفعيل البحث في الجداول
    addSearchFunctionality();
});

// وظائف مساعدة
const Admin = {
    // تحويل التاريخ
    formatDate: function(dateString) {
        const date = new Date(dateString);
        return date.toLocaleDateString('ar-YE');
    },
    
    // تنسيق الأرقام
    formatNumber: function(number) {
        return new Intl.NumberFormat('ar-YE').format(number);
    },
    
    // إظهار رسالة نجاح
    showSuccess: function(message) {
        this.showMessage(message, 'success');
    },
    
    // إظهار رسالة خطأ
    showError: function(message) {
        this.showMessage(message, 'error');
    },
    
    // إظهار رسالة
    showMessage: function(message, type) {
        const alert = document.createElement('div');
        alert.className = `alert alert-${type}`;
        alert.textContent = message;
        
        document.querySelector('.main-content').insertBefore(alert, document.querySelector('.main-content').firstChild);
        
        setTimeout(() => {
            alert.remove();
        }, 5000);
    },
    
    // تحميل الصورة ومعاينتها
    previewImage: function(input, previewElement) {
        if (input.files && input.files[0]) {
            const reader = new FileReader();
            
            reader.onload = function(e) {
                previewElement.innerHTML = `<img src="${e.target.result}" alt="معاينة الصورة" style="max-width: 200px; max-height: 200px;">`;
            };
            
            reader.readAsDataURL(input.files[0]);
        }
    }
};

// تصدير الكائن للاستخدام العالمي
window.Admin = Admin;