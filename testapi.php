<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Slide to Submit</title>
<link rel="stylesheet" href="styles.css">
</head>
<body>

<div class="container">
  <form method="post" id="myForm">
    <input type="text" name="username" placeholder="Enter your username" required>
    <input type="password" name="password" placeholder="Enter your password" required>
    <button type="submit" id="submitBtn">Slide to Submit</button>
  </form>
</div>

<script>
document.getElementById('submitBtn').addEventListener('click', function(event) {
  event.preventDefault(); // Prevent form submission
  
  // Slide the button to the right
  document.getElementById('submitBtn').style.left = '100%';
  
  // Submit the form after a short delay
  setTimeout(function() {
    document.getElementById('myForm').submit();
  }, 500);
});


</script>

</body>
</html>

<?php

// Handle form submission
if($_SERVER["REQUEST_METHOD"] == "POST") {
  $username = $_POST["username"];
  $password = $_POST["password"];
  
  // Process form data further as needed
  
  // For demonstration, simply echoing the submitted data
  echo "Username: " . $username . "<br>";
  echo "Password: " . $password;
}



?>