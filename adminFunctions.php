<?php
  require_once 'database.php';
  require_once 'authFunctions.php';

  $type = $_POST['type'] ?? 'All';

  // Load the modules based on the type
  if ($type === 'Partner') {
      $sql = "SELECT p.partnerName AS 'uploader' , lm.moduleName, lm.langID FROM partnermodule pm 
              JOIN partner p ON p.partnerID = pm.partnerID 
              JOIN languagemodule lm on lm.langID = pm.langID;";

  } elseif ($type === 'Classroom') {
      $sql = " SELECT 
                lm.langID, 
                lm.moduleName, 
                u.username AS 'uploader'
              FROM classmodule cm 
              JOIN classinstructor ci ON cm.classInstID = ci.classInstID
              JOIN instructor i ON i.instID = ci.instID
              JOIN users u ON u.userID = i.userID
              JOIN languagemodule lm ON lm.langID = cm.langID;";
  } else {
      $sql = "
        SELECT 
          p.partnerName AS 'uploader' , 
          lm.moduleName, 
          lm.langID 
        FROM partnermodule pm 
        JOIN partner p ON p.partnerID = pm.partnerID 
        JOIN languagemodule lm on lm.langID = pm.langID

        UNION

        SELECT 
          u.username AS 'uploader',
          lm.moduleName, 
          lm.langID 
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
        <div class="module-title"><?= htmlspecialchars($row['moduleName']) ?></div>
        <div class="module-creator">By <?= htmlspecialchars($row['uploader']) ?></div>
      </div>
        <button onclick="">
          <img src="images/Search_Icon.jpg" alt="View Module" class="search-image-icon">
        </button>
      </a>
    </div>
  <?php
  }
?>
