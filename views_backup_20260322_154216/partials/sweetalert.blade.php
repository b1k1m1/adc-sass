@if(session('success'))
<script>
   document.addEventListener('DOMContentLoaded', function () {
        setTimeout(() => {
            Swal.fire({
                icon: 'success',
                title: 'Success!',
                text: @json(session('success')),
                showConfirmButton: false,
                timer: 3000,
                backdrop: true,
                customClass: {
                    popup: 'swal2-superpopup'
                },
                didOpen: () => {
                    const popup = document.querySelector('.swal2-popup');
    if (popup) {
        popup.style.zIndex = '999999';
        popup.style.position = 'fixed';
        popup.style.top = '20%';
        popup.style.left = '50%';
        popup.style.transform = 'translateX(-50%)';
        popup.style.opacity = '1';
        popup.style.display = 'block';

        // Restore color and padding like default SweetAlert
        popup.style.backgroundColor = '#fff';
        popup.style.color = '#545454';
        popup.style.borderRadius = '10px';
        popup.style.boxShadow = '0 0 20px rgba(0,0,0,0.1)';
        popup.style.padding = '1.5rem';

        // Optional: restore title size
        const title = popup.querySelector('.swal2-title');
        if (title) {
            title.style.fontSize = '1.4rem';
            title.style.fontWeight = '600';
        }

        const icon = popup.querySelector('.swal2-icon');
        if (icon) {
            icon.style.borderColor = '#a5dc86';
            icon.style.color = '#a5dc86';
        }
    }
                }
            });
        }, 600); // Wait for Bootstrap theme transitions
    });
</script>
@endif
