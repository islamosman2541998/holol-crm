window.initTomSelect = function () {
    document.querySelectorAll('.js-tom-select').forEach((element) => {
        if (element.tomselect) {
            return;
        }

        new TomSelect(element, {
            create: false,
            allowEmptyOption: true,
        });
    });
};

window.initFlatpickr = function () {
    document.querySelectorAll('.js-date-picker').forEach((element) => {
        if (element._flatpickr) {
            return;
        }

        flatpickr(element, {
            dateFormat: 'Y-m-d',
            allowInput: true,
        });
    });

    document.querySelectorAll('.js-datetime-picker').forEach((element) => {
        if (element._flatpickr) {
            return;
        }

        flatpickr(element, {
            enableTime: true,
            dateFormat: 'Y-m-d H:i',
            allowInput: true,
            time_24hr: true,
        });
    });
};

document.addEventListener('DOMContentLoaded', () => {
    window.initTomSelect();
    window.initFlatpickr();
});

document.addEventListener('livewire:navigated', () => {
    window.initTomSelect();
    window.initFlatpickr();
});

window.showToast = function (type = 'success', message = 'تمت العملية بنجاح') {
    toastr.options = {
        closeButton: true,
        progressBar: true,
        positionClass: 'toast-top-left',
        timeOut: 3000,
        extendedTimeOut: 1000,
        newestOnTop: true,
        preventDuplicates: true,
        rtl: true,
    };

    toastr[type](message);
};

window.addEventListener('toast', (event) => {
    const type = event.detail.type || 'success';
    const message = event.detail.message || 'تمت العملية بنجاح';

    window.showToast(type, message);
});
document.addEventListener('submit', function (event) {
    const form = event.target;

    if (!form.classList.contains('js-delete-form')) {
        return;
    }

    event.preventDefault();

    Swal.fire({
        title: 'هل أنت متأكد؟',
        text: 'لا يمكن التراجع عن هذه العملية',
        icon: 'warning',
        showCancelButton: true,
        confirmButtonText: 'نعم، احذف',
        cancelButtonText: 'إلغاء',
        confirmButtonColor: '#dc3545',
    }).then((result) => {
        if (result.isConfirmed) {
            form.submit();
        }
    });
});