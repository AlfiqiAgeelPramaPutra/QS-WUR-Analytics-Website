<?php
include('koneksidb.php');
include('header.php');

// Periksa apakah permintaan AJAX untuk autocomplete
if (isset($_GET['action']) && $_GET['action'] == 'autocomplete') {
    $term = isset($_GET['term']) ? $_GET['term'] : '';
    
    if ($term) {
        $query = $conn->prepare("SELECT DISTINCT univ_name FROM overall WHERE univ_name LIKE ? LIMIT 10");
        $search_term = "%$term%";
        $query->bind_param("s", $search_term);
        $query->execute();
        $result = $query->get_result();
        
        $universities = [];
        while ($row = $result->fetch_assoc()) {
            $universities[] = $row['univ_name'];
        }

        echo json_encode($universities); // Kembalikan hasil dalam format JSON
        exit; // Hentikan eksekusi lebih lanjut
    }
}

// Default university
$default_univ = 'Telkom University';

// Get search query for the university
$search_univ = isset($_GET['univ']) ? $_GET['univ'] : $default_univ;

// Get available year range from the database
$year_range_query = $conn->prepare("SELECT MIN(Year) AS min_year, MAX(Year) AS max_year FROM overall");
$year_range_query->execute();
$year_range_result = $year_range_query->get_result();
$year_range = $year_range_result->fetch_assoc();

$min_year = $year_range['min_year'];
$max_year = $year_range['max_year'];

// Get year range from the query string
$start_year = isset($_GET['start-year']) ? intval($_GET['start-year']) : $min_year;
$end_year = isset($_GET['end-year']) ? intval($_GET['end-year']) : $max_year;

// Validate year range to ensure at least 4 years
if ($end_year - $start_year + 1 < 4) {
    $end_year = $start_year + 3; // Ensure at least 4 years range
}

// Cap end year to the maximum available year
if ($end_year > $max_year) {
    $end_year = $max_year;
}

// Prepare and execute query to fetch data for the selected university and year range
$query = $conn->prepare("SELECT * FROM overall WHERE univ_name LIKE ? AND Year BETWEEN ? AND ? ORDER BY Year ASC");
$univ_name_param = "%{$search_univ}%";
$query->bind_param("sii", $univ_name_param, $start_year, $end_year);
$query->execute();
$result = $query->get_result();

$data_univ = [];
$years = [];
$parameters = ['Academic_Reputation', 'Employer_Reputation', 'Faculty_Student_Ratio', 'Citations_Per_Faculty', 'International_Faculty_Ratio', 'International_Students_Ratio', 'International_Research_Network', 'Employment_Outcomes', 'Sustainability'];

while ($row = $result->fetch_assoc()) {
    $data_univ[$row['Year']] = $row;
    $years[] = $row['Year'];
}

?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>University Yearly Comparison</title>
    <link rel="stylesheet" href="styles.css">
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://code.jquery.com/ui/1.13.2/jquery-ui.min.js"></script>
    <link rel="stylesheet" href="https://code.jquery.com/ui/1.13.2/themes/base/jquery-ui.css">
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

    <style>
        .year-range-container {
            display: flex;
            justify-content: center;
            margin-bottom: 30px;
        }

        .year-range-form {
            display: flex;
            gap: 10px;
            align-items: center;
            border: 1px solid #ddd;
            padding: 15px;
            border-radius: 5px;
            background-color: #fff;
            box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
        }

        .year-range-form label {
            margin-right: 10px;
        }

        .year-range-form input {
            width: 80px;
            padding: 8px;
            border-radius: 5px;
            border: 1px solid #ddd;
        }

        .year-range-form button {
            padding: 8px 15px;
            border-radius: 5px;
            border: none;
            background-color: #ed1e28;
            color: white;
            cursor: pointer;
        }

        .year-range-form button:hover {
            background-color: #b6252a;
        }

        /* Container for Charts */
        .chart-container {
            position: relative;
            width: 100%;
            height: 300px;
        }

        /* Search Container */
        .search-container {
            border: 1px solid #ddd;
            padding: 15px;
            border-radius: 5px;
            margin-bottom: 40px;
            margin-top: 30px;
            background-color: #fff;
            box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
        }

        /* Search Input and Button */
        .search-container input {
            width: calc(100% - 90px);
            padding: 8px;
            border-radius: 5px;
            border: 1px solid #ddd;
            margin-right: 10px;
        }

        .search-container button {
            padding: 8px 15px;
            border-radius: 5px;
            border: none;
            background-color: #ed1e28;
            color: white;
            cursor: pointer;
        }

        .search-container button:hover {
            background-color: #b6252a;
        }

        /* Centered Titles */
        .centered-title {
            text-align: center;
            margin: 20px 0;
        }

        /* General Styles */
        .centered-title {
            text-align: center;
            margin-top: 0; /* Adjust margin as needed */
        }

        .table-container {
            width: 100%;
            max-width: 1400px; /* Adjust max-width as needed */
            margin: 0 auto; /* Center the container */
            margin-bottom: 50px;
            padding: 20px; /* Add horizontal padding */
            box-shadow: 0 0 10px rgba(0, 0, 0, 0.2); /* Optional: Add shadow to container */
            background-color: #fff; /* Optional: Set background color */
            border-radius: 8px; /* Optional: Add rounded corners */
        }

        .data-table {
            width: 100%;
            border-collapse: collapse;
        }

        .data-table th, .data-table td {
            border: 1px solid #ddd;
            padding: 8px; /* Adjusted padding */
            text-align: center;
            color: black;
        }

        .data-table th {
            background-color: #f4f4f4;
            font-weight: bold;
        }

        .data-table td p {
            margin: 0;
            line-height: 1.3;
        }

        .data-table tr:nth-child(even) {
            background-color: #f9f9f9;
        }

        .data-table tr:hover {
            background-color: #eaeaea;
        }

        /* Adjustments for Overall Column */
        .overall-col {
            width: 200px; /* Increase width for Overall column */
        }


        /* Comparison Grid for Charts */
        .comparison-grid {
            display: flex;
            gap: 10px;
            margin-bottom: 20px;
            padding-left: 60px; 
            padding-right: 60px; 
        }

        /* Score and Rank Charts */
        .score-charts, .rank-charts {
            flex: 1;
        }

        /* Chart Card Styles */
        .card {
            border: 2px solid #ddd;
            padding: 5px;
            border-radius: 5px;
            margin-bottom: 20px;
        }

        /* Differentiated Styles for Data Cards */
        .data-container .card {
            background-color: #ffffff; /* White background for data cards */
            border: 1px solid #ccc; /* Lighter border for data cards */
            box-shadow: 0 1px 2px rgba(0, 0, 0, 0.05);
        }

        /* Differentiated Styles for Chart Cards */
        .comparison-grid .card {
            background-color: #f4f4f4; /* Slightly darker background for chart cards */
            border: 1px solid #aaa; /* Darker border for chart cards */
            box-shadow: 0 2px 4px rgba(0, 0, 0, 0.15);
        }

    </style>
</head>
<body>

<div class="main-content-univ-comp">
    <div class="search-container">
        <form class="search-form" method="GET" action="telu_comparison.php">
            <label for="univ">Search University:</label>
            <input type="text" id="univ" name="univ" placeholder="Enter University Name" value="<?php echo isset($_GET['univ']) ? $_GET['univ'] : ''; ?>" class="autocomplete">
            <button type="submit">Search</button>
        </form>
    </div>
    
    <div class="year-range-container">
        <form class="year-range-form" method="GET" action="telu_comparison.php">
            <!-- Hidden input to carry over the selected university name -->
            <input type="hidden" name="univ" value="<?php echo htmlspecialchars($search_univ); ?>">

            <label for="start-year">Start Year:</label>
            <input type="number" id="start-year" name="start-year" min="<?php echo $min_year; ?>" max="<?php echo $max_year; ?>" value="<?php echo htmlspecialchars($start_year); ?>">
            
            <label for="end-year">End Year:</label>
            <input type="number" id="end-year" name="end-year" min="<?php echo $min_year; ?>" max="<?php echo $max_year; ?>" value="<?php echo htmlspecialchars($end_year); ?>">
            
            <button type="submit">Apply Year Range</button>
        </form>
    </div>



    <?php if ($data_univ): ?>
        <div class="table-container">
            <div class="university-name">
                <h2 class="centered-title"><?php echo htmlspecialchars($search_univ); ?></h2>
            </div>
            <table class="data-table">
                <thead>
                    <tr>
                        <th>Year</th>
                        <th class="overall-col">Overall</th>
                        <?php foreach ($parameters as $parameter): ?>
                            <th><?php echo htmlspecialchars(str_replace('_', ' ', $parameter)); ?></th>
                        <?php endforeach; ?>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($data_univ as $year => $data): ?>
                        <tr>
                            <td><?php echo htmlspecialchars($year); ?></td>
                            <td class="overall-col">
                                <?php if (isset($data['Overall_Score'])): ?>
                                    <p>Score: <?php echo htmlspecialchars($data['Overall_Score']); ?></p>
                                    <p>Rank: <?php echo htmlspecialchars($data['Rank']); ?></p>
                                <?php else: ?>
                                    <!-- Leave cell empty -->
                                <?php endif; ?>
                            </td>
                            <?php foreach ($parameters as $parameter): ?>
                                <td>
                                    <?php if (isset($data[$parameter . '_Score'])): ?>
                                        <p>Score: <?php echo htmlspecialchars($data[$parameter . '_Score']); ?></p>
                                        <p>Rank: <?php echo htmlspecialchars($data[$parameter . '_Rank']); ?></p>
                                    <?php else: ?>
                                        <!-- Leave cell empty -->
                                    <?php endif; ?>
                                </td>
                            <?php endforeach; ?>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    <?php endif; ?>


    <?php if ($data_univ): ?>
        <div class="comparison-grid">
            <!-- Score Charts -->
            <div class="score-charts">
                <div class="card overall-score-chart">
                    <h3>Overall Score Comparison</h3>
                    <canvas id="OverallScoreChart" class="chart-container"></canvas>
                </div>

                <?php foreach ($parameters as $parameter): ?>
                    <div class="card parameter-chart">
                        <h3><?php echo htmlspecialchars(str_replace('_', ' ', $parameter)); ?> Score Comparison</h3>
                        <canvas id="<?php echo $parameter; ?>ScoreChart" class="chart-container"></canvas>
                    </div>
                <?php endforeach; ?>
            </div>

            <!-- Rank Charts -->
            <div class="rank-charts">
                <div class="card overall-rank-chart">
                    <h3>Overall Rank Comparison</h3>
                    <canvas id="OverallRankChart" class="chart-container"></canvas>
                </div>

                <?php foreach ($parameters as $parameter): ?>
                    <div class="card parameter-rank-chart">
                        <h3><?php echo htmlspecialchars(str_replace('_', ' ', $parameter)); ?> Rank Comparison</h3>
                        <canvas id="<?php echo $parameter; ?>RankChart" class="chart-container"></canvas>
                    </div>
                <?php endforeach; ?>
            </div>
        </div>
    <?php endif; ?>
</div>

<script>
    $(".autocomplete").autocomplete({
        source: function(request, response) {
            $.ajax({
                url: "univ_comparison.php",
                type: "GET",
                dataType: "json",
                data: {
                    action: 'autocomplete',
                    term: request.term
                },
                success: function(data) {
                    response(data);
                }
            });
        }
    });

    document.addEventListener('DOMContentLoaded', function () {
        <?php if ($data_univ): ?>
        const dataUniv = <?php echo json_encode($data_univ); ?>;
        const selectedYears = <?php echo json_encode($years); ?>;
        const parameters = <?php echo json_encode($parameters); ?>;

        parameters.forEach(parameter => {
            // Score charts
            const scoreCtx = document.getElementById(`${parameter}ScoreChart`).getContext('2d');
            const scoreData = selectedYears.map(year => parseFloat(dataUniv[year][`${parameter}_Score`]));

            new Chart(scoreCtx, {
                type: 'line',
                data: {
                    labels: selectedYears,
                    datasets: [{
                        label: parameter.replace('_', ' ') + ' Score',
                        data: scoreData,
                        backgroundColor: 'rgba(75, 192, 192, 0.2)', // Area di bawah garis
                        borderColor: 'rgba(75, 192, 192, 1)', // Garis
                        borderWidth: 2, // Ketebalan garis
                        fill: true
                    }]
                },
                options: {
                    scales: {
                        y: {
                            beginAtZero: true
                        }
                    },
                    plugins: {
                        title: {
                            display: true,
                            text: parameter.replace('_', ' ') + ' Score Comparison'
                        }
                    }
                }
            });

            // Rank charts
            const rankCtx = document.getElementById(`${parameter}RankChart`).getContext('2d');
            const rankData = selectedYears.map(year => parseFloat(dataUniv[year][`${parameter}_Rank`]));

            new Chart(rankCtx, {
                type: 'line',
                data: {
                    labels: selectedYears,
                    datasets: [{
                        label: parameter.replace('_', ' ') + ' Rank',
                        data: rankData,
                        backgroundColor: 'rgba(255, 99, 132, 0.2)', // Area di bawah garis
                        borderColor: 'rgba(255, 99, 132, 1)', // Garis
                        borderWidth: 2, // Ketebalan garis
                        fill: true
                    }]
                },
                options: {
                    scales: {
                        y: {
                            beginAtZero: false,
                            reverse: true, // Membalikkan urutan sumbu Y
                            suggestedMin: Math.min(...rankData) - 10,
                            suggestedMax: Math.max(...rankData) + 10
                        }
                    },
                    plugins: {
                        title: {
                            display: true,
                            text: parameter.replace('_', ' ') + ' Rank Comparison'
                        }
                    }
                }
            });
        });

        // Overall Rank Chart
        const overallRankCtx = document.getElementById('OverallRankChart').getContext('2d');
        const overallRankData = selectedYears.map(year => parseFloat(dataUniv[year]['Rank']));

        new Chart(overallRankCtx, {
            type: 'line',
            data: {
                labels: selectedYears,
                datasets: [{
                    label: 'Overall Rank',
                    data: overallRankData,
                    backgroundColor: 'rgba(255, 99, 132, 0.2)', // Area di bawah garis
                    borderColor: 'rgba(255, 99, 132, 1)', // Garis
                    borderWidth: 2, // Ketebalan garis
                    fill: true
                }]
            },
            options: {
                scales: {
                    y: {
                        beginAtZero: false,
                        reverse: true, // Membalikkan urutan sumbu Y
                        suggestedMin: Math.min(...overallRankData) - 10,
                        suggestedMax: Math.max(...overallRankData) + 10
                    }
                },
                plugins: {
                    title: {
                        display: true,
                        text: 'Overall Rank Comparison'
                    }
                }
            }
        });

        // Overall Score Chart
        const overallScoreCtx = document.getElementById('OverallScoreChart').getContext('2d');
        const overallScoreData = selectedYears.map(year => parseFloat(dataUniv[year]['Overall_Score']));

        new Chart(overallScoreCtx, {
            type: 'line',
            data: {
                labels: selectedYears,
                datasets: [{
                    label: 'Overall Score',
                    data: overallScoreData,
                    backgroundColor: 'rgba(75, 192, 192, 0.2)', // Area di bawah garis
                    borderColor: 'rgba(75, 192, 192, 1)', // Garis
                    borderWidth: 2, // Ketebalan garis
                    fill: true
                }]
            },
            options: {
                scales: {
                    y: {
                        beginAtZero: true
                    }
                },
                plugins: {
                    title: {
                        display: true,
                        text: 'Overall Score Comparison'
                    }
                }
            }
        });
        <?php endif; ?>
    });

</script>

</body>
</html>
