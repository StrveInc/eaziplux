<!-- Preloader HTML/CSS -->
<style>
    /* Styling for preloader overlay */
    .preloader-overlay {
        position: fixed;
        top: 0;
        left: 0;
        width: 100%;
        height: 100%;
        background-color: rgba(255, 255, 255, 0.3);
        backdrop-filter: blur(8px);
        display: flex;
        justify-content: center;
        align-items: center;
        z-index: 9999;
    }

    /* Styling for preloader image */
    .preloader-img {
        width: 100px; /* Adjust size as needed */
        height: 100px; /* Adjust size as needed */
    }
</style>

<!-- Preloader HTML -->
<div class="preloader-overlay" id="preloader">
    <img src="./css/imgs/eazi.gif" class="preloader-img" alt="Loading...">
</div>

<!-- Preloader JavaScript -->
<script>
    // JavaScript to hide preloader when page is fully loaded
    window.addEventListener('load', function() {
        document.getElementById('preloader').style.display = 'none';
    });
</script>
