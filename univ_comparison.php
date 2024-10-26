<?php
include('koneksidb.php');

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

$sql = "SELECT DISTINCT year FROM univ_info ORDER BY year ASC";
$year_result = $conn->query($sql);
$selected_year = isset($_GET['year']) ? $_GET['year'] : null;

$data_static = null; // Inisialisasi variabel
$parameters = []; // Inisialisasi variabel

if ($selected_year):
    // Bagian Statis - Kiri
    $static_univ = "Telkom University"; // Universitas statis di sebelah kiri

    if ($selected_year == 2022) {
        $parameters = ['Academic_Reputation', 'Employer_Reputation', 'Faculty_Student_Ratio', 'Citations_Per_Faculty', 'International_Faculty_Ratio', 'International_Students_Ratio'];
    } elseif ($selected_year == 2023) {
        $parameters = ['Academic_Reputation', 'Employer_Reputation', 'Faculty_Student_Ratio', 'Citations_Per_Faculty', 'International_Faculty_Ratio', 'International_Students_Ratio', 'International_Research_Network', 'Employment_Outcomes'];
    } else { // 2024 dan 2025
        $parameters = ['Academic_Reputation', 'Employer_Reputation', 'Faculty_Student_Ratio', 'Citations_Per_Faculty', 'International_Faculty_Ratio', 'International_Students_Ratio', 'International_Research_Network', 'Employment_Outcomes', 'Sustainability'];
    }

    // Query untuk Universitas Statis
    $query_static = $conn->prepare("SELECT * FROM overall WHERE univ_name = ? AND Year = ?");
    $query_static->bind_param("ss", $static_univ, $selected_year);
    $query_static->execute();
    $data_static = $query_static->get_result()->fetch_assoc();
endif;

// Inisialisasi variabel untuk data universitas yang dipilih
$data_univ = [null, null, null]; 
$univ_names = ["Telkom University", null, null]; // Inisialisasi nama universitas

if (isset($_GET['compare'])) {
    // Ambil data untuk universitas yang dipilih (tengah dan kanan)
    for ($i = 2; $i <= 3; $i++) {
        $univ_name = $_GET["univ{$i}"];
        $univ_names[$i] = $univ_name; // Simpan nama universitas
        $query_univ = $conn->prepare("SELECT * FROM overall WHERE univ_name LIKE ? AND Year = ?");
        $univ_name_param = "%$univ_name%";
        $query_univ->bind_param("ss", $univ_name_param, $selected_year);
        $query_univ->execute();
        $data_univ[$i] = $query_univ->get_result()->fetch_assoc();
    }
}

// Jika permintaan AJAX untuk grafik
if (isset($_GET['action']) && $_GET['action'] == 'fetch_chart_data') {
    $year = $_GET['year'];
    $univ1 = $_GET['univ1'];
    $univ2 = $_GET['univ2'];
    $univ3 = $_GET['univ3'];

    $universities = [$univ1, $univ2, $univ3];
    $data = ['univ1' => [], 'univ2' => [], 'univ3' => []];

    // Mengambil data untuk universitas yang dipilih
    foreach ($universities as $index => $univ_name) {
        $query_univ = $conn->prepare("SELECT * FROM overall WHERE univ_name LIKE ? AND Year = ?");
        $univ_name_param = "%$univ_name%";
        $query_univ->bind_param("ss", $univ_name_param, $year);
        $query_univ->execute();
        $result = $query_univ->get_result()->fetch_assoc();
    
        if ($result) {
            foreach ($parameters as $parameter) {
                $data["univ" . ($index + 1)][] = $result[$parameter . '_Score'];
                $rank_data["univ" . ($index + 1)][] = $result[$parameter . '_Rank'];
            }
            $data["univ" . ($index + 1)][] = $result['Overall_Score'];
            $rank_data["univ" . ($index + 1)][] = $result['Rank'];
        } else {
            // Isi dengan 0 jika data tidak ditemukan, termasuk Overall Score dan Rank
            $data["univ" . ($index + 1)] = array_fill(0, count($parameters) + 1, 0); 
            $rank_data["univ" . ($index + 1)] = array_fill(0, count($parameters) + 1, 0);
        }
    }
    
    $data_to_send = ['data' => $data, 'rank_data' => $rank_data, 'names' => $universities];
    echo json_encode($data_to_send);
    exit;
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>University Comparison</title>
    <link rel="stylesheet" href="styles.css">
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://code.jquery.com/ui/1.13.2/jquery-ui.min.js"></script>
    <link rel="stylesheet" href="https://code.jquery.com/ui/1.13.2/themes/base/jquery-ui.css">
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
</head>
<body>
<?php include('header.php'); ?> 
<form action="univ_comparison.php" method="get">
    <!-- Filter Tahun -->
    <div class="filter-container">
    <label for="year">Year:</label>
    <select id="year" name="year" onchange="this.form.submit()">
        <option value="">Select Year</option>
        <?php
        if ($year_result->num_rows > 0) {
            while ($row = $year_result->fetch_assoc()) {
                $db_year = $row['year'];
                echo "<option value=\"$db_year\" " . (($selected_year == $db_year) ? 'selected' : '') . ">$db_year</option>";
            }
        } else {
            echo "<option value=\"\">No years available</option>";
        }
        ?>
    </select>
    </div>
</form>

<div class="container">
    <div class="search-container card">
        <form action="univ_comparison.php" method="get">
            <input type="hidden" name="year" value="<?php echo $selected_year; ?>">
            <label for="univ1">University 1:</label>
            <input type="text" id="univ1" name="univ1" value="Telkom University" readonly>
            <label for="univ2">University 2:</label>
            <input type="text" id="univ2" name="univ2" placeholder="Enter University Name" value="<?php echo isset($_GET['univ2']) ? $_GET['univ2'] : ''; ?>" class="autocomplete">
            <label for="univ3">University 3:</label>
            <input type="text" id="univ3" name="univ3" placeholder="Enter University Name" value="<?php echo isset($_GET['univ3']) ? $_GET['univ3'] : ''; ?>" class="autocomplete">
            <button type="submit" name="compare">Compare</button>
        </form>
    </div>

    <?php if (isset($_GET['compare'])): ?>
    <div class="comparison-grid-wrapper">
    <!-- Bagian Kiri (Statis) -->
    <?php if ($data_static): ?>
        <div class="univ-container card">
            <h2><?php echo htmlspecialchars($data_static['univ_name']); ?></h2>
            <p>Location: <?php echo htmlspecialchars($data_static['Country']); ?></p>
            <p>Region: <?php echo htmlspecialchars($data_static['Region']); ?></p>
            <p>Overall Score: <?php echo htmlspecialchars($data_static['Overall_Score']); ?></p>
            <p>Overall Rank: <?php echo htmlspecialchars($data_static['Rank']); ?></p>
            <table class="univ-table">
                <thead>
                    <tr>
                        <th>Parameter</th>
                        <th>Score</th>
                        <th>Rank</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($parameters as $parameter): ?>
                        <tr>
                            <td><?php echo htmlspecialchars(str_replace('_', ' ', $parameter)); ?></td>
                            <td><?php echo htmlspecialchars($data_static[$parameter . '_Score']) ?: '-'; ?></td>
                            <td><?php echo htmlspecialchars($data_static[$parameter . '_Rank']) ?: '-'; ?></td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    <?php else: ?>
        <p>Static university data not found.</p>
    <?php endif; ?>

    <!-- Bagian Tengah dan Kanan -->
    <?php for ($i = 2; $i <= 3; $i++): ?>
        <div class="univ-container card">
            <?php if ($data_univ[$i]): ?>
                <h2><?php echo htmlspecialchars($data_univ[$i]['univ_name']); ?></h2>
                <p>Location: <?php echo htmlspecialchars($data_univ[$i]['Country']); ?></p>
                <p>Region: <?php echo htmlspecialchars($data_univ[$i]['Region']); ?></p>
                <p>Overall Score: <?php echo htmlspecialchars($data_univ[$i]['Overall_Score']); ?></p>
                <p>Overall Rank: <?php echo htmlspecialchars($data_univ[$i]['Rank']); ?></p>
                <table class="univ-table">
                    <thead>
                        <tr>
                            <th>Parameter</th>
                            <th>Score</th>
                            <th>Rank</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($parameters as $parameter): ?>
                            <tr>
                                <td><?php echo htmlspecialchars(str_replace('_', ' ', $parameter)); ?></td>
                                <td><?php echo htmlspecialchars($data_univ[$i][$parameter . '_Score']) ?: '-'; ?></td>
                                <td><?php echo htmlspecialchars($data_univ[$i][$parameter . '_Rank']) ?: '-'; ?></td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            <?php else: ?>
                <p>Data not found for this university.</p>
            <?php endif; ?>
        </div>
    <?php endfor; ?>
</div>



    <div class="chart-container-wrapper">
        <div class="chart-container score-chart">
            <canvas id="comparisonChart"></canvas>
        </div>
        
        <div class="chart-container rank-chart">
            <canvas id="rankChart"></canvas>
        </div>
    </div>



    <?php endif; ?>
</div>

<script>
    $(function() {
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

        function fetchUniversityData(year, univ1, univ2, univ3) {
            $.ajax({
                url: 'univ_comparison.php',
                type: 'GET',
                dataType: 'json',
                data: {
                    action: 'fetch_chart_data',
                    year: year,
                    univ1: univ1,
                    univ2: univ2,
                    univ3: univ3
                },
                success: function(data) {
                    createChart(data, year);
                    createRadarChart(data, year); // Panggil fungsi untuk membuat radar chart
                }
            });
        }

        function createChart(data, year) {
            const ctx = document.getElementById('comparisonChart').getContext('2d');
            if (window.myBarChart) {
                window.myBarChart.destroy();
            }

            const yearParams = getParametersForYear(year);

            window.myBarChart = new Chart(ctx, {
                type: 'bar',
                data: {
                    labels: yearParams.concat('Overall Score'),
                    datasets: [
                        {
                            label: data.names[0],
                            data: data.data.univ1,
                            backgroundColor: 'rgba(237, 30, 40, 0.2)',
                            borderColor: 'rgba(237, 30, 40, 1)',
                            borderWidth: 1
                        },
                        {
                            label: data.names[1],
                            data: data.data.univ2,
                            backgroundColor: 'rgba(54, 162, 235, 0.2)',
                            borderColor: 'rgba(54, 162, 235, 1)',
                            borderWidth: 1
                        },
                        {
                            label: data.names[2],
                            data: data.data.univ3,
                            backgroundColor: 'rgba(75, 192, 192, 0.2)',
                            borderColor: 'rgba(75, 192, 192, 1)',
                            borderWidth: 1
                        }
                    ]
                },
                options: {
                    responsive: true,
                    plugins: {
                        title: {
                            display: true,
                            text: 'Grafik Score' // Judul untuk Chart Score
                        }
                    },
                    scales: {
                        x: {
                            beginAtZero: true
                        }
                    }
                }
            });
        }

        function createRadarChart(data, year) {
            const ctx = document.getElementById('rankChart').getContext('2d');
            if (window.myRadarChart) {
                window.myRadarChart.destroy();
            }

            const yearParams = getParametersForYear(year);

            window.myRadarChart = new Chart(ctx, {
                type: 'radar',
                data: {
                    labels: yearParams.concat('Overall Rank'),
                    datasets: [
                        {
                            label: data.names[0],
                            data: data.rank_data.univ1.map(parseRank),
                            backgroundColor: 'rgba(237, 30, 40, 0.2)',
                            borderColor: 'rgba(237, 30, 40, 1)',
                            borderWidth: 2
                        },
                        {
                            label: data.names[1],
                            data: data.rank_data.univ2.map(parseRank),
                            backgroundColor: 'rgba(0, 123, 255, 0.2)',
                            borderColor: 'rgba(0, 123, 255, 1)',
                            borderWidth: 2
                        },
                        {
                            label: data.names[2],
                            data: data.rank_data.univ3.map(parseRank),
                            backgroundColor: 'rgba(40, 167, 69, 0.2)',
                            borderColor: 'rgba(40, 167, 69, 1)',
                            borderWidth: 2
                        }
                    ]
                },
                options: {
                    plugins: {
                        title: {
                            display: true,
                            text: 'Grafik Rank' // Judul untuk Chart Rank
                        }
                    },
                    scales: {
                        r: {
                            reverse: true,
                            beginAtZero: false,
                            min: 1,
                            max: 1800,
                            ticks: {
                                stepSize: 300,
                                callback: function(value) {
                                    return Math.round(value);
                                }
                            }
                        }
                    }
                }
            });
        }


        function getParametersForYear(year) {
            if (year == 2022) {
                return ['Academic Reputation', 'Employer Reputation', 'Faculty Student Ratio', 
                        'Citations Per Faculty', 'International Faculty Ratio', 'International Students Ratio'];
            } else if (year == 2023) {
                return ['Academic Reputation', 'Employer Reputation', 'Faculty Student Ratio', 
                        'Citations Per Faculty', 'International Faculty Ratio', 'International Students Ratio',
                        'International Research Network', 'Employment Outcomes'];
            } else { 
                return ['Academic Reputation', 'Employer Reputation', 'Faculty Student Ratio', 
                        'Citations Per Faculty', 'International Faculty Ratio', 'International Students Ratio',
                        'International Research Network', 'Employment Outcomes', 'Sustainability'];
            }
        }

        function parseRank(value) {
            if (/^\d+\+$/.test(value)) {
                return Math.floor(parseInt(value, 10));
            } else if (/^\d+-\d+$/.test(value)) {
                return Math.floor(parseInt(value.split('-')[0], 10));
            }
            return Math.floor(parseInt(value, 10));
        }

        const selectedYear = "<?php echo isset($_GET['year']) ? $_GET['year'] : ''; ?>";
        const univ1 = "Telkom University";
        const univ2 = "<?php echo isset($_GET['univ2']) ? $_GET['univ2'] : ''; ?>";
        const univ3 = "<?php echo isset($_GET['univ3']) ? $_GET['univ3'] : ''; ?>";

        if (selectedYear && univ2 && univ3) {
            fetchUniversityData(selectedYear, univ1, univ2, univ3);
        }
    });
</script>

</body>
</html>
