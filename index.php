<!DOCTYPE html>
<html lang="en">

<head>
    <link rel="stylesheet" href="./css/home.css">
    <link rel="manifest" href="manifest.json">
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
<?php include 'preloader.php'; ?>
    <header>
        <div class="container container-nav">
            <nav>
                <ul>
                    <li><a href="index.php">Home</a></li>
                    <li><a href="./home/ourservices.php">Our Service</a></li>
                    <li><a href="./home/getintouch.php">Get in Touch</a></li>
                    <li><a href="./home/signup.php">Join Us</a></li>
                </ul>
            </nav>
        </div>
    </header>

    <main>
        <div class="container">
            <section class="sec1">
                <div class="greeting">
                <div class="symbol">
                    <img src="./css/imgs/eaziplux.png" alt="eaziplux" >
                </div>
                    <p>The Best Online Platform For Cheap and Fast Subscription And Bills Payments</p>
                    <div class="sign">
                        <div class="joinlog" id="we">
                            <a href="./home/signup.php"><label>Register</label></a>
                        </div>
                        <div class="joinlog" id="me">
                            <a href="./home/login.php"><label>Login </label></a>
                        </div>
                    </div>
            </section>
            <!--<section>
                <div class="section2">
                    <div class="section2head">
                        <h2>GET TO KNOW US!</h2>
                    </div>
                    <div class="section2body">
                        <div class="columns">
                            <div class="col">
                                <h2>FAST DELIVERY<i class="fas fa-rocket"></i></h2>
                                <b> Enjoy instant, secure virtual top-ups for seamless connection, reliable transactions, and real-time updates – your key to staying
                                    connected effortlessly.</b>
                            </div>
                            <div class="col">
                                <h2>LOW-COST DISCOUNTS<i class="fas fa-coins"></i></h2>
                                <p> Stay connected without breaking the bank – affordable convenience at your
                                    fingertips.</p>
                            </div>
                        </div>
                        <div class="columns">
                            <div class="col">
                                <h2>FAST BILLS PAYMENT <i class="fas fa-money-bills"></i></h2>
                                <b>
                                    Swift and Seamless Bill Payments Await You! Experience the speed of Eazi Plux.
                                    Instantly settle bills with our efficient platform, ensuring a hassle-free
                                    experience.
                                    Fast, secure, and at your fingertips – managing bills has never been this quick!
                                </b>
                            </div>
                            <div class="col">
                                <h2>24/7 CUSTOMER SUPPORT<i class="fas fa-phone"></i></h2>
                                <b>Customer First, Always! At Eazi Plux, we take pride in our active customer service.
                                    Our dedicated support team is ready to assist you around the clock. Experience
                                    personalized care,
                                    quick resolutions, and a commitment to your satisfaction. Your connectivity journey
                                    is our priority!</b>
                            </div>
                        </div>
                    </div>
                </div>
            </section>-->
        </div>
    </main>
    
    <script type="text/javascript">
        var Tawk_API = Tawk_API || {}, Tawk_LoadStart = new Date();
        (function () {
            var s1 = document.createElement("script"), s0 = document.getElementsByTagName("script")[0];
            s1.async = true;
            s1.src = 'https://embed.tawk.to/658c01c070c9f2407f83aa82/1hilednbb';
            s1.charset = 'UTF-8';
            s1.setAttribute('crossorigin', '*');
            s0.parentNode.insertBefore(s1, s0);
        })();
    </script>
    <footer>
        <div class="footcontainer">
            <div class="allf">
                <p>&copy2023 EAZIPLUX. All Rights Reserved</p>
            </div>
            <div class="stamp">
                <p>Powered By Strive</p>
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

</html>