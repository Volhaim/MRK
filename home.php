<?php include('db_connect.php') ?>
<?php
$twhere ="";
if($_SESSION['login_type'] != 1)
  $twhere = "  ";
?>
<!-- Info boxes -->
 <div class="col-12">
          <div class="card">
            <div class="card-body">
              Добро пожаловать, <?php echo $_SESSION['login_name'] ?>!
            </div>
          </div>
  </div>
  <hr>
  <?php 

    $where = "";
    if($_SESSION['login_type'] == 2){
      $where = " where manager_id = '{$_SESSION['login_id']}' ";
    }elseif($_SESSION['login_type'] == 3){
      $where = " where concat('[',REPLACE(user_ids,',','],['),']') LIKE '%[{$_SESSION['login_id']}]%' ";
    }
     $where2 = "";
    if($_SESSION['login_type'] == 2){
      $where2 = " where p.manager_id = '{$_SESSION['login_id']}' ";
    }elseif($_SESSION['login_type'] == 3){
      $where2 = " where concat('[',REPLACE(p.user_ids,',','],['),']') LIKE '%[{$_SESSION['login_id']}]%' ";
    }
    ?>
        
      <div class="row">
        <div class="col-md-8">
        <div class="card card-outline card-success">
          <div class="card-header">
            <b>Прогресс Проекта</b>
          </div>
          <div class="card-body p-0">
            <div class="table-responsive">
              <table class="table m-0 table-hover">
                <colgroup>
                  <col width="5%">
                  <col width="30%">
                  <col width="35%">
                  <col width="15%">
                  <col width="15%">
                </colgroup>
                <thead>
                  <th>№</th>
                  <th>Проект</th>
                  <th>Прогресс</th>
                  <th>Статус</th>
                  <th></th>
                </thead>
                <tbody>
                <?php
                $i = 1;
                $stat = array("На рассмотрении","Начат","В работе","Ожидание","Просрочен","Завершен");
                $where = "";
                if($_SESSION['login_type'] == 2){
                  $where = " where manager_id = '{$_SESSION['login_id']}' ";
                }elseif($_SESSION['login_type'] == 3){
                  $where = " where concat('[',REPLACE(user_ids,',','],['),']') LIKE '%[{$_SESSION['login_id']}]%' ";
                }
                $qry = $conn->query("SELECT * FROM project_list $where order by name asc");
                while($row= $qry->fetch_assoc()):
                  $prog= 0;
                $tprog = $conn->query("SELECT * FROM task_list where project_id = {$row['id']}")->num_rows;
                $cprog = $conn->query("SELECT * FROM task_list where project_id = {$row['id']} and status = 3")->num_rows;
                $prog = $tprog > 0 ? ($cprog/$tprog) * 100 : 0;
                $prog = $prog > 0 ?   number_format($prog,2) : $prog;
                ?>
                <tr>
                  <td class="text-center"><?php echo $i++ ?></td>
                  <td>
                    <p><b><?php echo $row['name'] ?></b></p>
                  </td>
                  <td>
                     <div class="progress">
                      <div class="progress-bar bg-success" role="progressbar" aria-valuenow="<?php echo $prog ?>" aria-valuemin="0" aria-valuemax="100" style="width: <?php echo $prog ?>%">
                      </div>
                    </div>
                    <small><?php echo $prog ?>%</small>
                  </td>
                  <td><span class="badge badge-primary"><?php echo $stat[$row['status']] ?></span></td>
                  <td>
                    <a class="btn btn-sm btn-outline-primary view_project" href="./index.php?page=view_project&id=<?php echo $row['id'] ?>" data-id="<?php echo $row['id'] ?>">Просмотр</a>
                  </td>
                </tr>
                <?php endwhile; ?>
                </tbody>
              </table>
            </div>
          </div>
        </div>
        </div>
        <div class="col-md-4">
            <!-- Statistics Card -->
            <div class="card card-outline card-info">
                <div class="card-header">
                    <b>Статистика</b>
                </div>
                <div class="card-body">
                    <div class="row text-center">
                        <div class="col-6 border-right">
                            <div class="description-block border-right">
                                <h5 class="description-header"><?php 
                                    $proj = $conn->query("SELECT * FROM project_list $where")->num_rows;
                                    echo $proj;
                                ?></h5>
                                <span class="description-text">Всего Проектов</span>
                            </div>
                        </div>
                        <div class="col-6">
                            <div class="description-block">
                                <h5 class="description-header"><?php 
                                    $tsk = $conn->query("SELECT tl.* FROM task_list tl JOIN project_list p ON tl.project_id = p.id $where2")->num_rows;
                                    echo $tsk;
                                ?></h5>
                                <span class="description-text">Всего Задач</span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
      </div>

<?php 
// Получаем данные из базы для диаграммы
$status_counts = array();
for($i = 0; $i <= 4; $i++){
    $status_counts[$i] = $conn->query("SELECT * FROM task_list where status = $i")->num_rows;
}
?>

<div class="row">
<div class="col-md-6">
        <div class="card card-outline card-primary" style="height: 400px;">
            <div class="card-header">
                <h3 class="card-title"><i class="fa fa-chart-bar mr-1"></i> Статистика задач</h3>
            </div>
            <div class="card-body">
                <div style="height: 400px;">
                    <canvas id="taskChart" style="min-height: 300px; height: 300px; max-height: 300px; max-width: 100%;"></canvas>
                </div>
            </div>
        </div>
    </div>

<script>
document.addEventListener("DOMContentLoaded", function() {
    var ctx = document.getElementById('taskChart').getContext('2d');
    
    // Данные для диаграммы (эти значения можно подставить из PHP)
    var taskData = {
        labels: ['В ожидании', 'В процессе', 'Завершено', 'На удержании', 'Просрочено'],
        datasets: [{
            data: [
                <?php echo $conn->query("SELECT * FROM task_list where status = 0")->num_rows; ?>,
                <?php echo $conn->query("SELECT * FROM task_list where status = 1")->num_rows; ?>,
                <?php echo $conn->query("SELECT * FROM task_list where status = 2")->num_rows; ?>,
                <?php echo $conn->query("SELECT * FROM task_list where status = 3")->num_rows; ?>,
                <?php echo $conn->query("SELECT * FROM task_list where status = 4")->num_rows; ?>
            ],
            backgroundColor: ['#6c757d', '#17a2b8', '#28a745', '#ffc107', '#dc3545'],
        }]
    };

    var myChart = new Chart(ctx, {
        type: 'pie', // Можно изменить на 'bar' или 'doughnut'
        data: taskData,
        options: {
            maintainAspectRatio: false,
            responsive: true,
            legend: {
                display: true,
                position: 'right'
            }
        }
    });
});
</script>

        <!-- Monthly Deadlines Calendar -->
        <div class="col-md-6">
            <div class="card card-outline card-danger">
                <div class="card-header">
                    <h5><i class="fa fa-calendar-alt mr-1"></i><b> Дедлайны</b></h5>
                </div>
                <div class="card-body p-2">
                    <div class="calendar-container" id="deadlineCalendar">
                        <!-- Calendar will be generated by JavaScript -->
                    </div>
                </div>
            </div>
        </div>
      </div>

      <script>
        var weeklyChart = null;
        
        // Weekly Statistics Chart
        function loadWeeklyStats() {
            const daysRU = ['Пн', 'Вт', 'Ср', 'Чт', 'Пт', 'Сб', 'Вс'];
            
            // Get data from server
            fetch('ajax. php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/x-www-form-urlencoded'
                },
                body: 'action=get_weekly_stats'
            })
            .then(response => response.json())
            .then(data => {
                const ctx = document.getElementById('weeklyTasksChart');
                if (! ctx) {
                    console.error('Canvas element not found');
                    return;
                }
                
                // Destroy previous chart if exists
                if (weeklyChart) {
                    weeklyChart.destroy();
                }
                
                // Update summary numbers
                document.getElementById('inProgressCount').textContent = data.totalInProgress;
                document.getElementById('completedCount').textContent = data.totalCompleted;
                document.getElementById('overdueCount').textContent = data.totalOverdue;
                
                weeklyChart = new Chart(ctx, {
                    type: 'bar',
                    data: {
                        labels: daysRU,
                        datasets: [
                            {
                                label: 'В Работе',
                                data: data.inProgress,
                                backgroundColor: '#007bff',
                                borderRadius: 3,
                                borderSkipped: false
                            },
                            {
                                label: 'Завершено',
                                data: data.completed,
                                backgroundColor: '#28a745',
                                borderRadius: 3,
                                borderSkipped: false
                            },
                            {
                                label: 'Просрочено',
                                data: data.overdue,
                                backgroundColor: '#dc3545',
                                borderRadius: 3,
                                borderSkipped: false
                            }
                        ]
                    },
                    options: {
                        responsive: true,
                        maintainAspectRatio: false,
                        plugins:  {
                            legend: {
                                display: true,
                                position: 'bottom'
                            }
                        },
                        scales: {
                            x: {
                                stacked: false
                            },
                            y:  {
                                stacked: false,
                                beginAtZero:  true
                            }
                        }
                    }
                });
            })
            .catch(error => console.error('Error loading weekly stats:', error));
        }

        // Calendar with Deadlines
        function loadDeadlinesCalendar() {
            const today = new Date();
            let currentYear = today.getFullYear();
            let currentMonth = today.getMonth();
            
            function renderCalendar() {
                const monthNames = ["Январь", "Февраль", "Март", "Апрель", "Май", "Июнь",
                    "Июль", "Август", "Сентябрь", "Октябрь", "Ноябрь", "Декабрь"];
                const dayNames = ['Пн', 'Вт', 'Ср', 'Чт', 'Пт', 'Сб', 'Вс'];
                
                const firstDay = new Date(currentYear, currentMonth, 1);
                const lastDay = new Date(currentYear, currentMonth + 1, 0);
                const daysInMonth = lastDay.getDate();
                let startDay = firstDay.getDay() === 0 ? 6 : firstDay.getDay() - 1;
                
                let calendarHTML = '<div class="calendar-nav">';
                calendarHTML += '<button class="btn btn-sm btn-light" id="prevBtn">&larr;</button>';
                calendarHTML += '<span class="calendar-month-year">' + monthNames[currentMonth] + ' ' + currentYear + '</span>';
                calendarHTML += '<button class="btn btn-sm btn-light" id="nextBtn">&rarr;</button>';
                calendarHTML += '</div>';
                
                calendarHTML += '<div class="calendar-weekdays">';
                dayNames.forEach(day => {
                    calendarHTML += '<div class="weekday">' + day + '</div>';
                });
                calendarHTML += '</div>';
                
                calendarHTML += '<div class="calendar-grid">';
                
                // Empty cells before first day
                for (let i = 0; i < startDay; i++) {
                    calendarHTML += '<div class="calendar-day-empty"></div>';
                }
                
                // Days of month
                for (let day = 1; day <= daysInMonth; day++) {
                    const dateStr = currentYear + '-' + String(currentMonth + 1).padStart(2, '0') + '-' + String(day).padStart(2, '0');
                    calendarHTML += '<div class="calendar-day" data-date="' + dateStr + '">';
                    calendarHTML += '<span class="day-num">' + day + '</span>';
                    calendarHTML += '</div>';
                }
                
                calendarHTML += '</div>';
                
                const container = document.getElementById('deadlineCalendar');
                container.innerHTML = calendarHTML;
                
                // Attach event listeners
                document.getElementById('prevBtn').addEventListener('click', previousMonth);
                document.getElementById('nextBtn').addEventListener('click', nextMonth);
                
                // Add click handlers to calendar days
                document.querySelectorAll('.calendar-day').forEach(day => {
                    day.addEventListener('click', function() {
                        selectDate(this);
                    });
                    day.addEventListener('mouseenter', function() {
                        const dateStr = this.getAttribute('data-date');
                        if (this.classList.contains('has-deadline')) {
                            loadDeadlinePreview(this, dateStr);
                        }
                    });
                });
                
                // Fetch and mark days with deadlines
                fetch('ajax.php', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/x-www-form-urlencoded'
                    },
                    body: 'action=get_month_deadlines&year=' + currentYear + '&month=' + (currentMonth + 1)
                })
                .then(response => response.json())
                .then(data => {
                    if (data.deadlines) {
                        data.deadlines.forEach(deadline => {
                            const dayCell = document.querySelector('[data-date="' + deadline. date + '"]');
                            if (dayCell) {
                                dayCell.classList.add('has-deadline');
                                dayCell.setAttribute('data-count', deadline.count);
                            }
                        });
                    }
                })
                .catch(error => console.error('Error loading deadlines:', error));
            }
            
            window.previousMonth = function() {
                currentMonth--;
                if (currentMonth < 0) {
                    currentMonth = 11;
                    currentYear--;
                }
                removeDeadlinePopup();
                renderCalendar();
            };
            
            window.nextMonth = function() {
                currentMonth++;
                if (currentMonth > 11) {
                    currentMonth = 0;
                    currentYear++;
                }
                removeDeadlinePopup();
                renderCalendar();
            };
            
            window.selectDate = function(element) {
                removeDeadlinePopup();
                const dateStr = element.getAttribute('data-date');
                if (! dateStr) return;
                
                fetch('ajax.php', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/x-www-form-urlencoded'
                    },
                    body: 'action=get_deadlines&date=' + dateStr
                })
                .then(response => response.json())
                .then(data => {
                    if (data.tasks && data.tasks.length > 0) {
                        showDeadlinePopup(element, data);
                    }
                })
                .catch(error => console. error('Error loading deadlines:', error));
            };
            
            window.loadDeadlinePreview = function(element, dateStr) {
                fetch('ajax.php', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/x-www-form-urlencoded'
                    },
                    body: 'action=get_deadlines&date=' + dateStr
                })
                .then(response => response.json())
                .then(data => {
                    if (data.tasks && data.tasks. length > 0) {
                        showDeadlinePopup(element, data);
                    }
                })
                .catch(error => console.error('Error loading deadlines:', error));
            };
            
            window.removeDeadlinePopup = function() {
                const popup = document.querySelector('.deadline-popup');
                if (popup) popup.remove();
            };
            
            window.showDeadlinePopup = function(element, data) {
                removeDeadlinePopup();
                
                let popupHTML = '<div class="deadline-popup">';
                popupHTML += '<div class="popup-title">Задачи</div>';
                data.tasks.forEach(task => {
                    const statusColors = {
                        0: 'info',
                        1: 'primary',
                        2: 'warning',
                        3: 'success',
                        4: 'secondary',
                        5: 'danger'
                    };
                    const color = statusColors[task.status] || 'secondary';
                    popupHTML += '<div class="popup-task">';
                    popupHTML += '<strong>' + task.name + '</strong><br>';
                    popupHTML += '<small>' + task.project_name + '</small><br>';
                    popupHTML += '<span class="badge badge-' + color + '">' + task.status_text + '</span>';
                    popupHTML += '</div>';
                });
                popupHTML += '</div>';
                
                document.body.insertAdjacentHTML('beforeend', popupHTML);
                
                const popup = document.querySelector('.deadline-popup');
                const rect = element.getBoundingClientRect();
                popup.style.top = (rect.top + window.scrollY + rect.height + 5) + 'px';
                popup.style.left = (rect.left + window.scrollX - (popup.offsetWidth - rect.width) / 2) + 'px';
            };
            
            renderCalendar();
        }

        // Initialize on page load
        document.addEventListener('DOMContentLoaded', function() {
            loadWeeklyStats();
            loadDeadlinesCalendar();
        });

        // Close popup on click outside
        document.addEventListener('click', function(e) {
            if (!e.target.closest('.calendar-day') && !e.target.closest('. deadline-popup')) {
                removeDeadlinePopup();
            }
        });
      </script>

      <style>
        .calendar-nav {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 12px;
            padding:  8px;
            background-color: #f8f9fa;
            border-radius: 4px;
        }

        .calendar-month-year {
            font-size: 14px;
            font-weight: bold;
            min-width: 120px;
            text-align: center;
        }

        .calendar-weekdays {
            display: grid;
            grid-template-columns: repeat(7, 1fr);
            gap: 4px;
            margin-bottom:  4px;
        }

        . weekday {
            text-align: center;
            font-weight: bold;
            padding: 5px;
            background-color:  #e9ecef;
            border-radius: 3px;
            font-size: 11px;
            color: #555;
        }

        .calendar-grid {
            display: grid;
            grid-template-columns: repeat(7, 1fr);
            gap: 3px;
        }

        .calendar-day-empty {
            aspect-ratio: 1;
            background-color: #fafafa;
            border-radius: 3px;
        }

        .calendar-day {
            aspect-ratio: 1;
            display: flex;
            align-items: center;
            justify-content: center;
            border: 1px solid #dee2e6;
            border-radius: 3px;
            cursor: pointer;
            background-color: #fff;
            transition: all 0.2s ease;
            position: relative;
            min-height: auto;
        }

        .calendar-day:hover {
            background-color: #e8f4f8;
            border-color: #007bff;
        }

        .calendar-day.has-deadline {
            background:  linear-gradient(135deg, #fff9e6 0%, #fffaf0 100%);
            border: 2px solid #ffc107;
            box-shadow: 0 0 6px rgba(255, 193, 7, 0.3);
            font-weight: bold;
        }

        .calendar-day.has-deadline: hover {
            box-shadow: 0 0 10px rgba(255, 193, 7, 0.5);
            background:  linear-gradient(135deg, #fff7d6 0%, #fffce6 100%);
        }

        .day-num {
            font-size: 13px;
        }

        .deadline-popup {
            position: fixed;
            background-color: white;
            border: 2px solid #007bff;
            border-radius: 4px;
            padding: 10px;
            box-shadow:  0 4px 12px rgba(0, 0, 0, 0.15);
            z-index: 1000;
            min-width: 250px;
            max-width:  350px;
        }

        .popup-title {
            font-weight: bold;
            margin-bottom: 8px;
            padding-bottom: 6px;
            border-bottom: 2px solid #007bff;
            font-size: 13px;
            color: #333;
        }

        .popup-task {
            padding: 8px;
            border-left: 3px solid #ffc107;
            background-color: #f9f9f9;
            margin-bottom: 6px;
            border-radius:  2px;
            font-size: 12px;
        }

        .popup-task strong {
            display:  block;
            margin-bottom:  2px;
            color: #333;
            font-size: 12px;
        }

        .popup-task small {
            display: block;
            margin-bottom: 4px;
            color: #666;
            font-size: 11px;
        }

        .popup-task .badge {
            font-size: 10px;
        }

        .description-block {
            padding: 10px;
        }

        .description-header {
            font-size: 20px;
            font-weight:  bold;
            margin:  0 0 5px 0;
        }

        .description-text {
            font-size: 12px;
            color: #999;
        }
      </style>
