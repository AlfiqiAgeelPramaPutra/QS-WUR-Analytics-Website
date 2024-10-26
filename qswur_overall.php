<?php
include('koneksidb.php');
include('header.php');

// Cek apakah ini adalah permintaan untuk autocomplete
if (isset($_GET['autocomplete']) && $_GET['autocomplete'] == 'true') {
    $term = isset($_GET['term']) ? $_GET['term'] : '';
    $year = isset($_GET['year']) ? intval($_GET['year']) : null;

    if ($term !== '' && $year !== null) {
        $query = $conn->prepare("SELECT DISTINCT univ_name FROM overall WHERE Year = ? AND univ_name LIKE ? LIMIT 10");
        $search_query_like = "%$term%";
        $query->bind_param('is', $year, $search_query_like);
        $query->execute();
        $result = $query->get_result();

        $univ_names = [];
        while ($row = $result->fetch_assoc()) {
            $univ_names[] = $row['univ_name'];
        }

        echo json_encode($univ_names);
    }

    $conn->close();
    exit;
}

// Ambil tahun dari parameter GET atau default ke 2022
$year = isset($_GET['year']) ? intval($_GET['year']) : 2022;
$search_query = isset($_GET['search']) ? $_GET['search'] : "";

// Batasi hasil per halaman
$limit = 50;
$page = isset($_GET['page']) ? intval($_GET['page']) : 1;
$offset = ($page - 1) * $limit;

// Query untuk mengambil data tahun untuk filter
$sql = "SELECT DISTINCT year FROM univ_info ORDER BY year ASC";
$year_result = $conn->query($sql);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>QS WUR Rankings International</title>
    <link rel="stylesheet" href="styles.css">
    <link rel="stylesheet" href="https://code.jquery.com/ui/1.12.1/themes/base/jquery-ui.css">
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://code.jquery.com/ui/1.12.1/jquery-ui.js"></script>
</head>
<body>

<!-- Filter Year -->
<div class="filter-container">
    <form action="qswur_overall.php" method="get">
        <label for="year-select">Select Year:</label>
        <select id="year-select" name="year">
            <option value="" disabled>Select Year</option>
            <?php
            if ($year_result->num_rows > 0) {
                while ($row = $year_result->fetch_assoc()) {
                    $db_year = $row['year'];
                    echo "<option value=\"$db_year\" " . (($year == $db_year) ? 'selected' : '') . ">$db_year</option>";
                }
            } else {
                echo "<option value=\"\">No years available</option>";
            }
            ?>
        </select>
        <button type="submit">Search</button>
    </form>
</div>

<!-- Pencarian Universitas -->
<div class="search-container">
    <form action="qswur_overall.php" method="get">
        <input type="hidden" name="year" value="<?php echo htmlspecialchars($year); ?>">
        <div class="filter-container">
            <input type="text" id="search" name="search" placeholder="Masukkan nama universitas" value="<?php echo htmlspecialchars($search_query); ?>">
            <button type="submit">Search</button>
        </div>
    </form>
</div>

<div class="data-container">
    <?php
    // Query untuk mengambil data sesuai tahun dan pencarian, diurutkan berdasarkan Rank
    $query = $conn->prepare("
        SELECT univ_id, univ_name, Region, Country, Overall_Score, Rank
        FROM overall
        WHERE Year = ? AND univ_name LIKE ?
        ORDER BY 
            CASE 
                WHEN Rank REGEXP '^[0-9]+$' THEN LPAD(Rank, 10, '0') 
                ELSE LPAD(SUBSTRING_INDEX(Rank, '-', 1), 10, '0')
            END ASC
        LIMIT ? OFFSET ?
    ");
    $search_query_like = "%$search_query%";
    $query->bind_param('isii', $year, $search_query_like, $limit, $offset);
    $query->execute();
    $result = $query->get_result();

    if ($result->num_rows > 0) {
        ?>
        <div class="table-container">
            <table id="sortable-table">
                <thead>
                    <tr>
                        <th>No</th> <!-- Kolom No tidak boleh diurutkan -->
                        <th>University Name</th>
                        <th>Region</th>
                        <th>Country</th>
                        <th>Overall Score</th>
                        <th>Rank</th>
                        <th>View Details</th>
                    </tr>
                </thead>
                <tbody>
                    <?php 
                    $no = $offset + 1;
                    while ($row = $result->fetch_assoc()): ?>
                        <tr>
                            <td><?php echo $no++; ?></td>
                            <td><?php echo htmlspecialchars($row['univ_name']); ?></td>
                            <td><?php echo htmlspecialchars($row['Region']); ?></td>
                            <td><?php echo htmlspecialchars($row['Country']); ?></td>
                            <td><?php echo htmlspecialchars($row['Overall_Score']); ?></td>
                            <td><?php echo htmlspecialchars($row['Rank']); ?></td>
                            <td><a href="detailuniv.php?univ_id=<?php echo urlencode($row['univ_id']); ?>&year=<?php echo $year; ?>">View Details</a></td>
                        </tr>
                    <?php endwhile; ?>
                </tbody>
            </table>
        </div>

        <div class="pagination">
            <?php
            $query_count = $conn->prepare("SELECT COUNT(*) AS total FROM overall WHERE Year = ? AND univ_name LIKE ?");
            $query_count->bind_param('is', $year, $search_query_like);
            $query_count->execute();
            $result_count = $query_count->get_result();
            $total_rows = $result_count->fetch_assoc()['total'];
            $total_pages = ceil($total_rows / $limit);

            for ($p = 1; $p <= $total_pages; $p++): ?>
                <a href="qswur_overall.php?year=<?php echo $year; ?>&page=<?php echo $p; ?>&search=<?php echo urlencode($search_query); ?>" class="<?php echo ($page == $p) ? 'active' : ''; ?>">
                    <?php echo $p; ?>
                </a>
            <?php endfor; ?>
        </div>

        <?php
    } else {
        echo "Tidak ada data tersedia.";
    }

    $conn->close();
    ?>
</div>

<!-- Script untuk Autocomplete -->
<script>
$(function() {
    $("#search").autocomplete({
        source: function(request, response) {
            $.ajax({
                url: "qswur_overall.php", 
                dataType: "json",
                data: {
                    autocomplete: true,  
                    term: request.term,
                    year: <?php echo $year; ?>
                },
                success: function(data) {
                    response(data);
                }
            });
        },
        minLength: 2, 
    });
});
</script>

<!-- Script untuk inisialisasi sorting di tabel -->
<script>
// Fungsi untuk menginisialisasi sorting tabel
document.addEventListener('DOMContentLoaded', function() {
    const table = document.getElementById('sortable-table');
    const headers = table.querySelectorAll('th');
    let ascending = true;

    headers.forEach((header, index) => {
        // Hindari menambahkan event listener untuk kolom "No" (index 0)
        if (index !== 0) {
            header.addEventListener('click', function() {
                sortTable(table, index, ascending);
                ascending = !ascending; // Toggle sorting order
            });
        }
    });

    function sortTable(table, col, asc) {
        const rows = Array.from(table.tBodies[0].rows);
        rows.sort((row1, row2) => {
            const cell1 = row1.cells[col].textContent.trim();
            const cell2 = row2.cells[col].textContent.trim();
            
            const isNumeric = !isNaN(cell1) && !isNaN(cell2);
            
            if (isNumeric) {
                return asc ? cell1 - cell2 : cell2 - cell1;
            } else {
                return asc ? cell1.localeCompare(cell2) : cell2.localeCompare(cell1);
            }
        });

        rows.forEach(row => table.tBodies[0].appendChild(row));
    }
});
</script>

</body>
</html>
