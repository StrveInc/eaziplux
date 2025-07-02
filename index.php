<!DOCTYPE html>
<html lang="en">

<head>
    <link rel="stylesheet" href="./css/home.css">
    <link rel="manifest" href="manifest.json">
    <link
        href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;600;700&display=swap"
        rel="stylesheet">
        <link href="https://fonts.googleapis.com/css2?family=Audiowide&display=swap" rel="stylesheet">

    <link
        href="https://fonts.googleapis.com/css2?family=Poppins:ital,wght@0,100;0,200;0,300;0,400;0,500;0,600;0,700;0,800;0,900;1,100;1,200;1,300;1,400;1,500;1,600;1,700;1,800;1,900&display=swap"
        rel="stylesheet">
    <link rel="icon" type="image/png" size="662x662" href="./css/imgs/eazicon.png">
    <script src="https://kit.fontawesome.com/49c5823e25.js" crossorigin="anonymous"></script>
    <meta name="description"
        content="Manage your mobile data and pay bills seamlessly with Eazi Plux. Enjoy a convienient and secure platform for handling all your mobile-related transactions.">
    <meta charset="UTF-8">
    <meta name="keywords"
        content="discounted mobile data, airtime deals, bills payment app, online payment, mobile recharge, discounted airtime, bill management, digital transactions, cheap airtime, cheap data, Eazi Plux, best cheap data ">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="google-site-verification" content="2C-9r_1lFbvzBCAMqcq3p8EoPsKWrm_9aiWJWioJiVg" />
    <meta name="author" content="Vickman Tech">
    <title>EAZI PLUX - Best Platform for data and bills payment.</title>

    <script async src="https://pagead2.googlesyndication.com/pagead/js/adsbygoogle.js?client=ca-pub-7203048318705000"
        crossorigin="anonymous"></script>


    <!-- Google tag (gtag.js) -->
    <script async src="https://www.googletagmanager.com/gtag/js?id=G-VVN0P5EYQP">
    </script>
    <script>
        window.dataLayer = window.dataLayer || [];
        function gtag() { dataLayer.push(arguments); }
        gtag('js', new Date());

        gtag('config', 'G-VVN0P5EYQP');
    </script>
</head>

<body>
<?php include 'preloader.php'; 
?>
    <header>
        <div class="head">
            <div class="logoDiv">
                <div class="logoImg">
                    <img src="./css/imgs/eazipluxpure.png" alt="Eazi Plux Logo" />
                </div>
                <div class="logoText">
                    EAZIPLUX
                </div>
            </div>
            <div class="menu">
                <div class="li"><a href="index.php" class="<?php echo $current_page == 'index.php' ? 'active' : ''; ?>">Home</a></div>
                <div class="li"><a href="priceList.php">Data Prices</a></div>
                <div class="li"><a href="rateCalculator.php">Rate Calculator</a></div>
                <!-- <div class="li"><a href="./home/signup.php">Join Us</a></div> -->
            </div>
        </div>  
    </header>

    <main>
        <div class="heroSection" style="color: white;">
        <div class="dynamic-text-box">
        <h2 id="dynamic-heading">Fast, Reliable & Secure Payments</h2>
        <p id="dynamic-subtext">
        Eaziplux ensures your bills and recharges are processed instantly with bank-grade security and uptime.
  </p>
</div>
<button class="getStartedButton" onclick="window.location.href='./home/signup.php';">
    Get Started
</button>

        </div>
        <div class="phonelay">
            <img src="./css/imgs/phonelay.png" alt="Phone Image" class="phoneImage">
        </div>
        <!-- Add more sections as needed -->        

    </main>
    <footer style="color: #ccc; position: absolute; bottom: 10px; width: 90%; left: 25px; font-size: 12px; text-align: center; border: 0px solid #ccc;">
    <div class="footer">
        <p>&copy; Powered by Strive inc.</p>
    </div>
</footer>
</body>
<script>
    if("serviceWorker" in navigator){
        navigator.serviceWorker.register('service-worker.js')
        .then(function(registration){
            console.log('Service worker registration successful: ', registration);
        })
        .catch(function(error){
            console.warn('service worker registration: ', error);
        });
    }
</script>

<script>
    var loader = document.getElementById('preloader');

    window.addEventListener("load", function () {
        loader.style.display = "none"
    })

</script>
<script>
  const messages = [
    {
      title: "Fast, Reliable & Secure Payments",
      desc: "Eaziplux ensures your bills and recharges are processed instantly with bank-grade security and uptime."
    },
    {
      title: "Buy Data & Airtime Instantly",
      desc: "Top up your device or gift airtime to others in seconds, anytime, anywhere."
    },
    {
      title: "Redeem Gift Cards with Ease",
      desc: "Turn gift cards into real value instantly — fast verification, smooth payouts."
    },
    {
      title: "Track Transactions in Real Time",
      desc: "Monitor every payment and recharge with a detailed breakdown in your dashboard."
    },
    {
      title: "Experience Seamless Utility Payments",
      desc: "Eaziplux supports NEPA, TV, and other utilities — no more late payments."
    }
  ];

  let index = 0;
  const heading = document.getElementById("dynamic-heading");
  const subtext = document.getElementById("dynamic-subtext");

  setInterval(() => {
    heading.style.opacity = 0;
    subtext.style.opacity = 0;

    setTimeout(() => {
      index = (index + 1) % messages.length;
      heading.textContent = messages[index].title;
      subtext.textContent = messages[index].desc;
      heading.style.opacity = 1;
      subtext.style.opacity = 1;
    }, 300);
  }, 4000);
</script>


</html>