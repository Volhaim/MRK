<?php
include 'db_connect.php';

if(isset($_POST['action'])){
    $action = $_POST['action'];
    
    // Get deadlines for specific month
    if($action == 'get_month_deadlines') {
        $year = isset($_POST['year']) ? (int)$_POST['year'] : date('Y');
        $month = isset($_POST['month']) ? (int)$_POST['month'] : date('m');
        
        $where = "";
        if($_SESSION['login_type'] == 2){
            $where = " AND p.manager_id = '{$_SESSION['login_id']}' ";
        }elseif($_SESSION['login_type'] == 3){
            $where = " AND concat('[',REPLACE(p.user_ids,',','],['),']') LIKE '%[{$_SESSION['login_id']}]%' ";
        }
        
        $query = "SELECT DATE(tl.deadline) as date, COUNT(*) as count 
                  FROM task_list tl 
                  JOIN project_list p ON tl.project_id = p.id 
                  WHERE YEAR(tl.deadline) = $year 
                  AND MONTH(tl.deadline) = $month 
                  AND tl.deadline IS NOT NULL $where
                  GROUP BY DATE(tl.deadline)";
        
        $result = $conn->query($query);
        $deadlines = array();
        
        if($result) {
            while($row = $result->fetch_assoc()) {
                $deadlines[] = $row;
            }
        }
        
        echo json_encode(['deadlines' => $deadlines]);
        exit;
    }
    
    // Get tasks with deadlines for specific date
    if($action == 'get_deadlines') {
        $date = isset($_POST['date']) ? $_POST['date'] : date('Y-m-d');
        
        $where = "";
        if($_SESSION['login_type'] == 2){
            $where = " AND p.manager_id = '{$_SESSION['login_id']}' ";
        }elseif($_SESSION['login_type'] == 3){
            $where = " AND concat('[',REPLACE(p.user_ids,',','],['),']') LIKE '%[{$_SESSION['login_id']}]%' ";
        }
        
        $query = "SELECT tl.id, tl.task as name, tl.status, p.name as project_name 
                  FROM task_list tl 
                  JOIN project_list p ON tl.project_id = p.id 
                  WHERE DATE(tl.deadline) = '$date' 
                  AND tl.deadline IS NOT NULL $where
                  ORDER BY tl.deadline ASC";
        
        $result = $conn->query($query);
        $tasks = array();
        $status_text = array("На рассмотрении","Начат","В работе","Завершено","Ожидание","Просрочен");
        $status_colors = array("info", "primary", "warning", "success", "secondary", "danger");
        
        if($result) {
            while($row = $result->fetch_assoc()) {
                $row['status_text'] = isset($status_text[$row['status']]) ? $status_text[$row['status']] : 'Неизвестно';
                $row['status_color'] = isset($status_colors[$row['status']]) ? $status_colors[$row['status']] : 'secondary';
                $tasks[] = $row;
            }
        }
        
        echo json_encode(['tasks' => $tasks]);
        exit;
    }
}
?>
