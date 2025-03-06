<?php session_start(); ?>
<!DOCTYPE html>
<html lang="en">
<head>
    <script async src="https://pagead2.googlesyndication.com/pagead/js/adsbygoogle.js?client=ca-pub-7203048318705000" crossorigin="anonymous"></script>
    <link rel="stylesheet" href="./css/receipt.css">
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>FAILED</title>
</head>
<body>
    <main>
        <div class="container">
            <div class="logo">
                <img src="./css/imgs/failedbig.png" >   
            </div>
            <h2>TRANSACTION FAILED</h2>
            <div class="gb">
                <img src="./css/imgs/eazipluxpure.png">
            </div>
            <p> <?php echo $_SESSION['message']; ?> </p>
            <a href="./home/dashboard.php"><input type="submit" name="close" value="CLOSE"></a>
        </div>
    </main>


        <script type="text/javascript">
var Tawk_API=Tawk_API||{}, Tawk_LoadStart=new Date();
(function(){
var s1=document.createElement("script"),s0=document.getElementsByTagName("script")[0];
s1.async=true;
s1.src='https://embed.tawk.to/658c01c070c9f2407f83aa82/1hilednbb';
s1.charset='UTF-8';
s1.setAttribute('crossorigin','*');
s0.parentNode.insertBefore(s1,s0);
})();
</script>
</body>
</html>
<?php 

?>