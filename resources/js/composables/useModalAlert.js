import Swal from 'sweetalert2';


export function useModalAlert() {
    const modalAlert = (title = 'Are you sure?', text = 'You won\'t be able to revert this!', icon = 'warning', delay = 2000) => {
        Swal.fire({
            title,
            text,
            icon,
            showConfirmButton: false,
            showCloseButton: false,
            timer: delay
        });
    };

    return {modalAlert};
}