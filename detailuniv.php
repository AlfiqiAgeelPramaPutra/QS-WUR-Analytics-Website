<?php
include('koneksidb.php');
include('header.php');  
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>University Details</title>
    <link rel="stylesheet" href="styles.css">
</head>
<body>

<div class="info-container">
    <?php
    include('koneksidb.php');

    $univ_id = isset($_GET['univ_id']) ? intval($_GET['univ_id']) : 0;
    $year = isset($_GET['year']) ? intval($_GET['year']) : 0;

    $query = "SELECT * FROM overall WHERE univ_id = $univ_id AND Year = $year";
    $result = $conn->query($query);

    if ($result->num_rows > 0) {
        $row = $result->fetch_assoc();
        ?>
        <div class="info-details">
            <div class="info-item">
                <strong>University Name:</strong>
                <span><?php echo htmlspecialchars($row['univ_name']); ?></span>
            </div>
            <div class="info-item">
                <strong>Region:</strong>
                <span><?php echo htmlspecialchars($row['Region']); ?></span>
            </div>
            <div class="info-item">
                <strong>Country:</strong>
                <span><?php echo htmlspecialchars($row['Country']); ?></span>
            </div>
            <div class="info-item">
                <strong>Overall Score:</strong>
                <span><?php echo htmlspecialchars($row['Overall_Score']); ?></span>
            </div>
            <div class="info-item">
                <strong>Rank:</strong>
                <span><?php echo htmlspecialchars($row['Rank']); ?></span>
            </div>
            <div class="info-item">
                <strong>Year:</strong>
                <span><?php echo htmlspecialchars($row['Year']); ?></span>
            </div>
        </div>
        <?php
    } else {
        echo "<p>Data not available for the selected university and year.</p>";
    }

    $conn->close();
    ?>
</div>

<div class="parameters-container-wrapper">
    <div class="parameters-container">
        <?php
        include('koneksidb.php');

        $query = "SELECT * FROM overall WHERE univ_id = $univ_id AND Year = $year";
        $result = $conn->query($query);

        if ($result->num_rows > 0) {
            $row = $result->fetch_assoc();
            ?>
            <div class="parameters-details">
                <div class="parameters-item">
                    <strong>Academic Reputation Score:</strong>
                    <span><?php echo htmlspecialchars($row['Academic_Reputation_Score']); ?></span>
                </div>
                <div class="parameters-item">
                    <strong>Citations Per Faculty Score:</strong>
                    <span><?php echo htmlspecialchars($row['Citations_Per_Faculty_Score']); ?></span>
                </div>
                <div class="parameters-item">
                    <strong>Faculty Student Ratio Score:</strong>
                    <span><?php echo htmlspecialchars($row['Faculty_Student_Ratio_Score']); ?></span>
                </div>
                <div class="parameters-item">
                    <strong>Employer Reputation Score:</strong>
                    <span><?php echo htmlspecialchars($row['Employer_Reputation_Score']); ?></span>
                </div>
                <?php if ($year >= 2023): ?>
                <div class="parameters-item">
                    <strong>Employment Outcomes Score:</strong>
                    <span><?php echo htmlspecialchars($row['Employment_Outcomes_Score']); ?></span>
                </div>
                <div class="parameters-item">
                    <strong>International Research Network Score:</strong>
                    <span><?php echo htmlspecialchars($row['International_Research_Network_Score']); ?></span>
                </div>
                <?php endif; ?>
                <div class="parameters-item">
                    <strong>International Students Ratio Score:</strong>
                    <span><?php echo htmlspecialchars($row['International_Students_Ratio_Score']); ?></span>
                </div>
                <div class="parameters-item">
                    <strong>International Faculty Ratio Score:</strong>
                    <span><?php echo htmlspecialchars($row['International_Faculty_Ratio_Score']); ?></span>
                </div>
                <?php if ($year >= 2024): ?>
                <div class="parameters-item">
                    <strong>Sustainability Score:</strong>
                    <span><?php echo htmlspecialchars($row['Sustainability_Score']); ?></span>
                </div>
                <?php endif; ?>
            </div>
            <?php
        } else {
            echo "<p>Data not available for the selected university and year.</p>";
        }

        $conn->close();
        ?>
    </div>

    <div class="parameters-container">
        <?php
        include('koneksidb.php');

        $query = "SELECT * FROM overall WHERE univ_id = $univ_id AND Year = $year";
        $result = $conn->query($query);

        if ($result->num_rows > 0) {
            $row = $result->fetch_assoc();
            ?>
            <div class="parameters-details">
                <div class="parameters-item">
                    <strong>Academic Reputation Rank:</strong>
                    <span><?php echo htmlspecialchars($row['Academic_Reputation_Rank']); ?></span>
                </div>
                <div class="parameters-item">
                    <strong>Citations Per Faculty Rank:</strong>
                    <span><?php echo htmlspecialchars($row['Citations_Per_Faculty_Rank']); ?></span>
                </div>
                <div class="parameters-item">
                    <strong>Faculty Student Ratio Rank:</strong>
                    <span><?php echo htmlspecialchars($row['Faculty_Student_Ratio_Rank']); ?></span>
                </div>
                <div class="parameters-item">
                    <strong>Employer Reputation Rank:</strong>
                    <span><?php echo htmlspecialchars($row['Employer_Reputation_Rank']); ?></span>
                </div>
                <?php if ($year >= 2023): ?>
                <div class="parameters-item">
                    <strong>Employment Outcomes Rank:</strong>
                    <span><?php echo htmlspecialchars($row['Employment_Outcomes_Rank']); ?></span>
                </div>
                <div class="parameters-item">
                    <strong>International Research Network Rank:</strong>
                    <span><?php echo htmlspecialchars($row['International_Research_Network_Rank']); ?></span>
                </div>
                <?php endif; ?>
                <div class="parameters-item">
                    <strong>International Students Ratio Rank:</strong>
                    <span><?php echo htmlspecialchars($row['International_Students_Ratio_Rank']); ?></span>
                </div>
                <div class="parameters-item">
                    <strong>International Faculty Ratio Rank:</strong>
                    <span><?php echo htmlspecialchars($row['International_Faculty_Ratio_Rank']); ?></span>
                </div>
                <?php if ($year >= 2024): ?>
                <div class="parameters-item">
                    <strong>Sustainability Rank:</strong>
                    <span><?php echo htmlspecialchars($row['Sustainability_Rank']); ?></span>
                </div>
                <?php endif; ?>
            </div>
            <?php
        } else {
            echo "<p>Data not available for the selected university and year.</p>";
        }

        $conn->close();
        ?>
    </div>
</div>

</body>
</html>
