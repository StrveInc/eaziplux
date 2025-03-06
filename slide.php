<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Slide to Submit Example</title>
    <link rel="stylesheet" href="./slide/css/slide-to-submit.css">
</head>
<body>

<form id="form-demo">
    <label for="name">Name:</label>
    <input type="text" id="name" name="name" required><br><br>
    <label for="email">Email:</label>
    <input type="email" id="email" name="email" required><br><br>
    <div class="slide-submit">
            <div class="slide-submit-text">»</div>
            <div class="slide-submit-thumb">»</div>
        </div>
</form>

<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/animate.css/4.1.1/animate.min.js"></script>
<script>
    $(document).ready(function () {
        $('#slide-submit').click(function () {
            $(this).addClass('animate__animated animate__fadeOutRightBig');
            setTimeout(function () {
                $('#form-demo').submit();
            }, 1000); // Delay for animation duration
        });

        $('#form-demo').submit(function (event) {
            event.preventDefault(); // Prevent default form submission
            const formData = {
                name: $('#name').val(),
                email: $('#email').val()
            };
            console.log('Form submitted successfully:', formData);
            alert('Form submitted successfully!');
            $('#form-demo')[0].reset(); // Reset the form
            $('#slide-submit').removeClass('animate__animated animate__fadeOutRightBig');
        });
    });
</script>

</body>
</html>
