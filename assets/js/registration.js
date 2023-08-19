const check = document.querySelector('#check')
const submitBtn = document.querySelector('#submitBtn')

check.addEventListener('change', function() {
    // Activer ou désactiver le bouton en fonction de l'état de la case à cocher
    submitBtn.disabled = !this.checked;
});
