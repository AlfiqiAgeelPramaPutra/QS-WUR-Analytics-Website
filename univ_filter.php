<?php
include('koneksidb.php');
include('header.php');  

// Fetch distinct countries and regions from the database
$query_countries = "SELECT DISTINCT Country FROM overall ORDER BY Country ASC";
$result_countries = $conn->query($query_countries);

$countries = array();
if ($result_countries->num_rows > 0) {
    while ($row = $result_countries->fetch_assoc()) {
        $countries[] = $row['Country'];
    }
}

$query_regions = "SELECT DISTINCT Region FROM overall WHERE Region IS NOT NULL AND Region != '' AND Region != 'false' ORDER BY Region ASC";
$result_regions = $conn->query($query_regions);

$regions = array();
if ($result_regions->num_rows > 0) {
    while ($row = $result_regions->fetch_assoc()) {
        $regions[] = $row['Region'];
    }
}

// Handle the first step: selecting the year
// Ambil tahun dari database
$sql = "SELECT DISTINCT year FROM univ_info ORDER BY year ASC";
$year_result = $conn->query($sql);
$selected_year = isset($_GET['year']) ? $_GET['year'] : null;

$selected_parameter = isset($_GET['parameter']) ? $_GET['parameter'] : null;
$selected_domicile = isset($_GET['domicile']) ? $_GET['domicile'] : null;
$selected_region_country = isset($_GET['region_country']) ? $_GET['region_country'] : null;

// Determine parameters based on selected year
$parameters = [];
if ($selected_year) {
    $parameters = [
        'Overall_Score' => 'Overall Score', // Added Overall Score parameter
        'Academic_Reputation_Score' => 'Academic Reputation',
        'Citations_Per_Faculty_Score' => 'Citations per Faculty',
        'Faculty_Student_Ratio_Score' => 'Faculty-Student Ratio',
        'Employer_Reputation_Score' => 'Employer Reputation',
        'International_Students_Ratio_Score' => 'International Students Ratio',
        'International_Faculty_Ratio_Score' => 'International Faculty Ratio'
    ];

    if ($selected_year >= 2023) {
        $parameters['Employment_Outcomes_Score'] = 'Employment Outcomes';
        $parameters['International_Research_Network_Score'] = 'International Research Network';
    }

    if ($selected_year >= 2024) {
        $parameters['Sustainability_Score'] = 'Sustainability';
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>QS WUR Rankings by Filter</title>
    <link rel="stylesheet" href="styles.css">
    <style>
        .centered-text {
            text-align: center;
            font-size: 18px;
            margin-top: 20px;
        }
        .data-container table {
            width: 100%;
            border-collapse: collapse;
            color: #000;
        }
        .data-container th, .data-container td {
            border: 1px solid #ddd;
            padding: 8px;
            border-color: #000;
        }
        .data-container th {
            background-color: rgba(0, 0, 0, 0.8);
            color: white;
        }
        .data-container .telkom-row {
            font-weight: bold;
            border-top: 2px solid #ddd;
            border-bottom: 2px solid #ddd;
            background-color: #ffdddd;
        }
        .pagination a.active {
            font-weight: bold;
            color: red;
        }
        .info-box {
            margin: 20px 0;
            padding: 10px;
            border: 1px solid #ddd;
            background-color: #f9f9f9;
            text-align: center;
        }
    </style>
</head>
<body>

<form action="" method="get">
    <!-- Step 1: Select Year -->
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

    <?php if ($selected_year): ?>
    <!-- Step 2: Select Parameter and Domicile -->
    <div class="filter-container">
        <label for="parameter">Parameter:</label>
        <select id="parameter" name="parameter">
            <?php foreach ($parameters as $value => $label): ?>
                <option value="<?php echo $value; ?>" <?php echo ($selected_parameter == $value) ? 'selected' : ''; ?>>
                    <?php echo $label; ?>
                </option>
            <?php endforeach; ?>
        </select>

        <label for="domicile">Filter by:</label>
        <select name="domicile" id="domicile" onchange="this.form.submit()">
            <option value="">Select Filter</option>
            <option value="Region" <?php echo ($selected_domicile == 'Region') ? 'selected' : ''; ?>>Region</option>
            <option value="Country" <?php echo ($selected_domicile == 'Country') ? 'selected' : ''; ?>>Country</option>
        </select>

        <label for="region_country">Region/Country:</label>
        <select name="region_country" id="region_country">
            <?php if ($selected_domicile == 'Region'): ?>
                <optgroup label="Region">
                    <?php foreach ($regions as $region): ?>
                        <option value="<?php echo $region; ?>" <?php echo ($selected_region_country == $region) ? 'selected' : ''; ?>>
                            <?php echo $region; ?>
                        </option>
                    <?php endforeach; ?>
                </optgroup>
            <?php elseif ($selected_domicile == 'Country'): ?>
                <optgroup label="Country">
                    <?php foreach ($countries as $country): ?>
                        <option value="<?php echo $country; ?>" <?php echo ($selected_region_country == $country) ? 'selected' : ''; ?>>
                            <?php echo $country; ?>
                        </option>
                    <?php endforeach; ?>
                </optgroup>
            <?php endif; ?>
        </select>

        <button type="submit">Apply Filters</button>
    </div>
    <?php endif; ?>
</form>

<?php
if ($selected_year) {
    $parameter = $selected_parameter;
    $domicile = $selected_domicile;
    $region_country = $selected_region_country;

    if ($domicile && $region_country && $parameter) {
        // Pagination settings
        $limit = 50;
        $page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
        $start = ($page - 1) * $limit;

        // Prepare query for total records count
        $query_count = "SELECT COUNT(*) AS total FROM overall WHERE Year = ? AND $domicile = ?";
        $stmt_count = $conn->prepare($query_count);
        $stmt_count->bind_param("ss", $selected_year, $region_country);
        $stmt_count->execute();
        $result_count = $stmt_count->get_result();
        $total_rows = $result_count->fetch_assoc()['total'];
        $total_pages = ceil($total_rows / $limit);

        // Fetch Telkom University data and determine its rank in the selected filter (Region or Country)
        $query_telkom = "SELECT univ_name, Region, Country, IFNULL($parameter, 0) as Score,
                         (SELECT COUNT(*) + 1 FROM overall WHERE Year = ? AND $domicile = ? AND IFNULL($parameter, 0) > (SELECT IFNULL($parameter, 0) FROM overall WHERE Year = ? AND univ_name = 'Telkom University')) AS FilterRank,
                         (SELECT COUNT(*) + 1 FROM overall WHERE Year = ? AND IFNULL($parameter, 0) > (SELECT IFNULL($parameter, 0) FROM overall WHERE Year = ? AND univ_name = 'Telkom University')) AS WorldRank
                         FROM overall WHERE Year = ? AND univ_name = 'Telkom University'";
        $stmt_telkom = $conn->prepare($query_telkom);
        $stmt_telkom->bind_param("ssssss", $selected_year, $region_country, $selected_year, $selected_year, $selected_year, $selected_year);
        $stmt_telkom->execute();
        $result_telkom = $stmt_telkom->get_result();
        $telkom_row = $result_telkom->fetch_assoc();

        $telkom_global_rank = null;
        $telkom_filter_rank = null;
        $telkom_page = null;
        if ($telkom_row) {
            $telkom_global_rank = $telkom_row['WorldRank'];
            $telkom_filter_rank = $telkom_row['FilterRank'];

            // Determine page number for Telkom University
            $telkom_page = ceil($telkom_filter_rank / $limit);
        }

        // Prepare query with pagination
        $query = "SELECT univ_name, Region, Country, IFNULL($parameter, 0) as Score,
                  (SELECT COUNT(*) + 1 FROM overall WHERE Year = ? AND IFNULL($parameter, 0) > IFNULL(o.$parameter, 0)) AS WorldRank
                  FROM overall o
                  WHERE Year = ? AND $domicile = ? AND (univ_name != 'Telkom University' OR ($domicile = 'Region' AND '$region_country' != 'Asia') OR ($domicile = 'Country' AND '$region_country' != 'Indonesia'))
                  ORDER BY Score DESC LIMIT ?, ?";
        $stmt = $conn->prepare($query);
        $stmt->bind_param("sssii", $selected_year, $selected_year, $region_country, $start, $limit);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($result->num_rows > 0) {
            $inserted_telkom = false;
            $rows = [];
            $telkom_row_number = $telkom_filter_rank; // Initialize Telkom row number based on filter rank

            // Collect rows from result
            while ($row = $result->fetch_assoc()) {
                $rows[] = $row;
            }

            echo "<div class='data-container'>";
            echo "<table>";
            echo "<tr><th>No</th><th>University Name</th><th>Region</th><th>Country</th><th>Rank</th><th>World Rank</th><th>Score</th></tr>";

            $counter = $start + 1;

            foreach ($rows as $index => $row) {
                if (!$inserted_telkom && $telkom_row && $row['Score'] <= $telkom_row['Score']) {
                    echo "<tr class='telkom-row'><td>{$telkom_row_number}</td><td>Telkom University</td><td>{$telkom_row['Region']}</td><td>{$telkom_row['Country']}</td><td>{$telkom_row_number}</td><td>{$telkom_row['WorldRank']}</td><td>{$telkom_row['Score']}</td></tr>";
                    $inserted_telkom = true;
                    $counter++;
                    $telkom_row_number++; // Increment Telkom row number for next page
                }
                echo "<tr><td>{$counter}</td><td>{$row['univ_name']}</td><td>{$row['Region']}</td><td>{$row['Country']}</td><td>{$counter}</td><td>{$row['WorldRank']}</td><td>{$row['Score']}</td></tr>";
                $counter++;
            }

            // Add Telkom University row if not inserted
            if (!$inserted_telkom && $telkom_row) {
                echo "<tr class='telkom-row'><td>{$telkom_row_number}</td><td>Telkom University</td><td>{$telkom_row['Region']}</td><td>{$telkom_row['Country']}</td><td>{$telkom_row_number}</td><td>{$telkom_row['WorldRank']}</td><td>{$telkom_row['Score']}</td></tr>";
            }

            echo "</table>";
            echo "</div>";

            // Info Box
            echo "<div class='info-box'>";
            if ($telkom_row) {
                echo "<p>Telkom University berada pada posisi {$telkom_filter_rank} di {$region_country}, dan posisi {$telkom_global_rank} di tingkat dunia, terdapat pada halaman {$telkom_page}.</p>";
            } else {
                echo "<p>Data Telkom University tidak tersedia untuk filter yang dipilih.</p>";
            }
            echo "</div>";

            // Pagination links
            echo "<div class='pagination'>";
            for ($i = 1; $i <= $total_pages; $i++) {
                $active = ($i == $page) ? 'active' : '';
                echo "<a href='univ_filter.php?year={$selected_year}&parameter={$selected_parameter}&domicile={$selected_domicile}&region_country={$selected_region_country}&page={$i}' class='{$active}'>{$i}</a> ";
            }
            echo "</div>";
        } 
    } else {
        echo "<p class='centered-text'>Isi semua parameter sebelum melihat data.</p>";
    }
}
?>

</body>
</html>
