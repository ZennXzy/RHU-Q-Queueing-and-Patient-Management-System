const openBtn = document.getElementById('open-form-btn');
const closeBtn = document.getElementById('closeBtn');
const formModal = document.getElementById('queue-form');

openBtn.addEventListener('click', () => {
    formModal.classList.add('active');
    console.log('openForm clicked');
});

closeBtn.addEventListener('click', () => {
    formModal.classList.remove('active');
    console.log('CloseButton Clicked');
});