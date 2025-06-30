<?php
session_start();
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Price List</title>
    <link rel="stylesheet" href="../css/price-list.css">
    <script src="https://kit.fontawesome.com/49c5823e25.js" crossorigin="anonymous"></script>
    <style>
        body {
            font-family: "Poppins", sans-serif;
            background-color: #000;
            color: #fff;
            margin: 0;
            overflow: hidden;
            padding: 0;
        }

        .container {
            max-width: 800px;
            margin: 20px auto;
            padding: 20px;
            text-align: center;
        }

        .network-select {
            width: 100%;
            padding: 10px;
            margin-bottom: 20px;
            border-radius: 5px;
            border: 1px solid #ffbf00;
            background-color: #000;
            color: #fff;
            font-size: 16px;
        }

        .price-list {
            margin-top: 20px;
            text-align: left;
        }

        .price-list h2 {
            color: #ffbf00;
            margin-bottom: 10px;
        }

        .price-list ul {
            list-style: none;
            padding: 0;
            overflow-y: auto;
        }

        .price-list li {
            padding: 10px;
            border: 1px solid #ffbf00;
            border-radius: 5px;
            margin-bottom: 10px;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        .price-list li span {
            font-size: 16px;
        }
    </style>
</head>

<body>
    <div class="container">
        <h1>Price List</h1>
        <p>Select a network to view available plans and prices:</p>
        <select id="networkSelect" class="network-select">
            <option value="mtn">MTN</option>
            <option value="airtel">Airtel</option>
            <option value="glo">Glo</option>
            <option value="9mobile">9mobile</option>
        </select>

        <div class="price-list" id="priceList">
            <h2>Plans</h2>
            <div style="overflow-y: auto; max-height: 350px; border: 1px solid #ffbf00; border-radius: 5px; padding: 10px;">
            <ul id="plansList">
                <li>Loading plans...</li>
            </ul>
            <div>
        </div>
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', function () {
            const networkSelect = document.getElementById('networkSelect');
            const plansList = document.getElementById('plansList');

            // Function to fetch and display plans
            function loadPlans(network) {
                plansList.innerHTML = '<li>Loading plans...</li>';
                let service = '';
                switch (network) {
                    case 'mtn':
                        service = 'mtn_gifting';
                        break;
                    case 'airtel':
                        service = 'airtel_gifting';
                        break;
                    case 'glo':
                        service = 'glo_data';
                        break;
                    case '9mobile':
                        service = 'etisalat_data';
                        break;
                    default:
                        service = '';
                }

                if (!service) {
                    plansList.innerHTML = '<li>No plans available</li>';
                    return;
                }

                fetch('plans_proxy.php?service=' + encodeURIComponent(service))
                    .then(res => res.json())
                    .then(data => {
                        if (data && data.plans) {
                            plansList.innerHTML = '';
                            data.plans.forEach(plan => {
                                let markup = 0;
                                if (plan.price >= 100 && plan.price <= 499) {
                                    markup = 0.10;
                                } else if (plan.price >= 500 && plan.price <= 1999) {
                                    markup = 0.08;
                                } else if (plan.price >= 2000) {
                                    markup = 0.05;
                                }
                                let price = Math.ceil(plan.price * (1 + markup));
                                let listItem = document.createElement('li');
                                listItem.innerHTML = `
                                    <span>${plan.displayName}</span>
                                    <span>₦${price}</span>
                                `;
                                plansList.appendChild(listItem);
                            });
                        } else {
                            plansList.innerHTML = '<li>No plans found</li>';
                        }
                    })
                    .catch(() => {
                        plansList.innerHTML = '<li>Service unavailable</li>';
                    });
            }

            // Load plans for the default network on page load
            loadPlans(networkSelect.value);

            // Update plans when the network is changed
            networkSelect.addEventListener('change', function () {
                loadPlans(this.value);
            });
        });
    </script>
</body>

</html>