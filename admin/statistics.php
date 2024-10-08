<?php
require_once '../session_check.php';
check_login('admin');
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Chart Example</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/water.css@2/out/water.css">
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    
</head>
<body>
    <div class="controls">                                                                          <!--Koumpia-->
        <label for="startDate">Start Date: </label>
        <input type="date" id="startDate">
        <label for="endDate">End Date: </label>
        <input type="date" id="endDate">
        <button onclick="updateChart()">Update Chart</button>
    </div>
    <div class="chart-container">
        <canvas id="myChart"></canvas>
    </div>

    <script>                                                                                        // dhmiourgia chart bar
        var ctx = document.getElementById('myChart').getContext('2d');
        var myChart = new Chart(ctx, {
            type: 'bar',
            data: {
                labels: ['New Requests', 'New Offers', 'Completed Requests', 'Completed Offers'],   // labels
                datasets: [{
                    label: '# of Items',
                    data: [0, 0, 0, 0],
                    backgroundColor: [
                        'rgba(75, 192, 192, 0.2)',
                        'rgba(54, 162, 235, 0.2)',
                        'rgba(255, 206, 86, 0.2)',
                        'rgba(153, 102, 255, 0.2)'
                    ],
                    borderColor: [
                        'rgba(75, 192, 192, 1)',
                        'rgba(54, 162, 235, 1)',
                        'rgba(255, 206, 86, 1)',
                        'rgba(153, 102, 255, 1)'
                    ],
                    borderWidth: 1
                }]
            },
            options: {
                scales: {
                    y: {
                        beginAtZero: true
                    }
                }
            }
        });

        function updateChart() {
            var startDate = document.getElementById('startDate').value;                             // get user input
            var endDate = document.getElementById('endDate').value;

            fetch('fetch_chart_data.php', {                                                         // send HTTP POST requst to php file
                method: 'POST',
                headers: {
                    'Content-Type': 'application/x-www-form-urlencoded'
                },
                body: 'startDate=' + startDate + '&endDate=' + endDate
            })
            .then(response => response.json())
            .then(data => {
                myChart.data.datasets[0].data = [
                    data.newRequests,
                    data.newOffers,
                    data.completedRequests,
                    data.completedOffers
                ];
                myChart.update();
            })
            .catch(error => console.error('Error fetching data:', error));
        }
    </script>
    <br>
    <a href="admin_main_page.php" class="button">Main Page</a>
</body>
</html>
