document.addEventListener('DOMContentLoaded', function () {
    const confirmOverlay = document.getElementById('confirmOverlay');
    const confirmBtn = document.getElementById('confirmBtn');
    const cancelBtn = document.getElementById('cancelBtn');
    const dataForm = document.getElementById('dataForm');
    const payBtn = document.getElementById('payBtn');
    const confirmNetwork = document.getElementById('confirmNetwork');
    const confirmPhone = document.getElementById('confirmPhone');
    const confirmPlan = document.getElementById('confirmPlan');
    const confirmAmount = document.getElementById('confirmAmount');

    if (!confirmOverlay || !dataForm || !payBtn) return;

    payBtn.addEventListener('click', function(e) {
        // Fill in the confirmation details
        const networkInput = document.getElementById('networkInput');
        const networkOption = document.querySelector('.custom-select .options div[data-value="' + networkInput.value + '"]');
        confirmNetwork.textContent = networkOption ? networkOption.getAttribute('data-label') : '';
        confirmPhone.textContent = dataForm.number.value;
        const selectedPlan = dataForm.plans.options[dataForm.plans.selectedIndex];
        confirmPlan.textContent = selectedPlan ? selectedPlan.getAttribute('data-displayname') : '';
        confirmAmount.textContent = selectedPlan ? '₦' + selectedPlan.getAttribute('data-price') : '';
        confirmOverlay.style.display = 'flex';
    });

    confirmBtn.addEventListener('click', function() {
        confirmOverlay.style.display = 'none';
        document.getElementById('preloader').style.display = 'flex';
        dataForm.submit();
    });

    cancelBtn.addEventListener('click', function() {
        confirmOverlay.style.display = 'none';
    });
});