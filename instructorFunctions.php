<?php
    require_once 'database.php';

    $type = $_POST['type'] ?? 'All';

    if ($type === 'Partner') {
        $sql = "SELECT * FROM partnermodule pm 
                JOIN partner p ON p.partnerID = pm.partnerID 
                JOIN languagemodule l on l.langID = pm.langID;";

    } elseif ($type === 'Classroom') {
        $sql = "SELECT * FROM classmodule cm 
                join instructor i ON cm.classInstID = i.instID
                join users u ON i.userID = u.userID
                JOIN languagemodule lm ON lm.langID = cm.langID;";
    } else {
        $sql = "
        SELECT 
                l.langID, 
                l.moduleName, 
                p.partnerName, 
                'Partner'
            FROM partnermodule pm 
            JOIN partner p ON p.partnerID = pm.partnerID 
            JOIN languagemodule l ON l.langID = pm.langID

            UNION

            SELECT 
                lm.langID, 
                lm.moduleName, 
                u.username, 
                'Classroom'
            FROM classmodule cm 
            JOIN classinstructor ci ON cm.classInstID = ci.classInstID
            JOIN instructor i ON i.instID = ci.instID
            JOIN users u ON u.userID = i.userID
            JOIN languagemodule lm ON lm.langID = cm.langID;
            ";
    }

    $result = $conn->query($sql);

    while ($row = $result->fetch_assoc()) {
    ?>
    <div class="module-card">
        <img src="images/Module_Icon.jpg" alt="Module Icon" class="module-icon">
        <div class="module-info">
        <div class="module-title"><?= htmlspecialchars($row['title']) ?></div>
        <div class="module-creator">By <?= htmlspecialchars($row['username']) ?></div>
        </div>
        <a href="module-details.php?moduleId=<?= $row['moduleID'] ?>" class="search-icon-link">
        <img src="images/Search_Icon.jpg" alt="View Module" class="search-image-icon">
        </a>
    </div>
    <?php
    }
?>