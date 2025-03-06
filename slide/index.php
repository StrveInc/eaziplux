<!doctype html>
<html>
<head>
<meta charset="utf-8">
<meta http-equiv="X-UA-Compatible" content="IE=edge,chrome=1">
<meta name="viewport" content="width=device-width, initial-scale=1">
	<title>jQuery slide-to-submit plugin Demo</title>
<link href="http://www.jqueryscript.net/css/jquerysctipttop.css" rel="stylesheet" type="text/css">
	<link rel="stylesheet" href="css/demo.css">
	<link rel="stylesheet" href="css/slide-to-submit.css">
	<meta name="viewport" content="width=device-width">
</head>
<body><div id="jquery-script-menu">
<div class="jquery-script-center">
<ul>
<li><a href="http://www.jqueryscript.net/form/jQuery-Plugin-To-Submit-A-Form-By-Sliding-slide-to-submit.html">Download This Plugin</a></li>
<li><a href="http://www.jqueryscript.net/">Back To jQueryScript.Net</a></li>
</ul>
<div class="jquery-script-ads"><script type="text/javascript"><!--
google_ad_client = "ca-pub-2783044520727903";
/* jQuery_demo */
google_ad_slot = "2780937993";
google_ad_width = 728;
google_ad_height = 90;
//-->
</script>
<script type="text/javascript"
src="http://pagead2.googlesyndication.com/pagead/show_ads.js">
</script></div>
<div class="jquery-script-clear"></div>
</div>
</div>
<h1 style="margin-top:150px;">jQuery slide-to-submit plugin Demo</h1>
<form method="POST" id="form1">
  <label for="name">Name</label>
	<input type="text" name="name" required>
	<label for="email">Email</label>
	<input type="email" name="email">
	<div class="slide-submit">
		<div class="slide-submit-text">Slide To Submit</div>
		<div class="slide-submit-thumb">»</div>
	</div>
</form>
<script src="http://code.jquery.com/jquery-1.12.3.min.js"></script>
<script src="js/slide-to-submit.js"></script>
<script>
	$('.slide-submit').slideToSubmit({
		submitDelay: 1000,
		successText: 'Looks Like You\x27re Human!'
	});
	
	// Demo only
	$("#form1").submit(function(e) {
		e.preventDefault();
		$('#form1').slideUp();
	});
</script><script type="text/javascript">

  var _gaq = _gaq || [];
  _gaq.push(['_setAccount', 'UA-36251023-1']);
  _gaq.push(['_setDomainName', 'jqueryscript.net']);
  _gaq.push(['_trackPageview']);

  (function() {
    var ga = document.createElement('script'); ga.type = 'text/javascript'; ga.async = true;
    ga.src = ('https:' == document.location.protocol ? 'https://ssl' : 'http://www') + '.google-analytics.com/ga.js';
    var s = document.getElementsByTagName('script')[0]; s.parentNode.insertBefore(ga, s);
  })();

</script>
</body>
</html>

<?php
// Check if form is submitted
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Retrieve and sanitize form data
    $name = htmlspecialchars($_POST['name']);
    $email = htmlspecialchars($_POST['email']);

    // Output the submitted data (you can process or save to database here)
    echo "<h2>Submitted Data:</h2>";
    echo "<p>Name: $name</p>";
    echo "<p>Email: $email</p>";
}
?>