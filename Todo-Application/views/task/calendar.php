<?php
use yii\helpers\Html;
use yii\helpers\Url;
use yii\web\View;

$this->title = 'Task Calendar';
$this->params['breadcrumbs'][] = ['label' => 'Tasks List', 'url' => ['index']];
$this->params['breadcrumbs'][] = $this->title;

// Register FullCalendar library
$this->registerJsFile('https://cdn.jsdelivr.net/npm/fullcalendar@6.1.15/index.global.min.js', ['position' => View::POS_HEAD]);

// Register CSRF token for AJAX
$this->registerJs("var csrfToken = '" . Yii::$app->request->csrfToken . "';", View::POS_HEAD);

// Register CSS for calendar and task panel styling
$this->registerCss('
    .calendar-container {
        max-width: 900px;
        margin: 20px auto;
        font-family: Arial, sans-serif;
    }
    .fc-event.task-completed {
        background-color: #d4edda;
        border-color: #155724;
        color: #155724;
    }
    .fc-event.task-pending {
        background-color: #fff3cd;
        border-color: #856404;
        color: #856404;
    }
    .fc-event.task-overdue {
        background-color: #f8d7da;
        border-color: #721c24;
        color: #721c24;
    }
    .fc-daygrid-day {
        cursor: pointer;
    }
    .fc-daygrid-day.fc-day-today {
        background-color: #e7f1ff;
        border: 2px solid #007bff;
    }
    .task-panel {
        position: fixed;
        top: 56px;
        right: -400px;
        width: 400px;
        height: calc(100% - 56px);
        background: #f8f9fa;
        box-shadow: -2px 0 10px rgba(0, 0, 0, 0.2);
        transition: right 0.3s ease-in-out;
        z-index: 1000;
        overflow-y: auto;
        border-left: 1px solid #e9ecef;
    }
    .task-panel.active {
        right: 0;
    }
    .task-panel-header {
        background: #28a745;
        color: white;
        padding: 15px;
        font-size: 18px;
        font-weight: bold;
        display: flex;
        align-items: center;
        border-bottom: 1px solid #218838;
    }
    .task-panel-header::before {
        content: "●";
        margin-right: 10px;
        font-size: 20px;
    }
    .task-panel-content {
        padding: 20px;
    }
    .task-panel-content p {
        margin: 10px 0;
        color: #333;
    }
    .close-panel {
        cursor: pointer;
        color: white;
        margin-left: auto;
        padding: 0 10px;
        transition: background-color 0.3s, color 0.3s;
    }
    .close-panel:hover {
        background-color: #218838;
        color: #fff;
    }
    .form-error {
        color: #dc3545;
        font-size: 0.875em;
        margin-top: 5px;
        background: #ffebee;
        padding: 5px;
        border-radius: 3px;
    }
    .has-error .help-block {
        color: #dc3545;
    }
');

// Register JavaScript for FullCalendar initialization and panel interaction
$this->registerJs('
    document.addEventListener("DOMContentLoaded", function() {
        var calendarEl = document.getElementById("calendar");
        var calendar = new FullCalendar.Calendar(calendarEl, {
            initialView: "dayGridMonth",
            events: "' . Url::to(['task/calendar-data']) . '",
            eventClick: function(info) {
                showCalendarPanel(info.event.startStr);
                info.jsEvent.preventDefault();
            },
            dateClick: function(info) {
                showCalendarPanel(info.dateStr);
            },
            eventContent: function(arg) {
                return { html: "<span>" + arg.event.title + "</span>" };
            }
        });
        calendar.render();
    });

    function showCalendarPanel(date) {
        $.get("' . Url::to(['task/calendar-panel']) . '&date=" + date, function(data) {
            $("#task-panel").html(data).addClass("active");
            bindCalendarFormHandler(); // Bind after injecting new content
        });
    }

    function hideTaskPanel() {
        $("#task-panel").removeClass("active");
    }

    function showTaskPanel(taskId) {
        $.get("' . Url::to(['task/view-panel']) . '&id=" + taskId, function(data) {
            $("#task-panel").html(data).addClass("active");
        });
    }

    function showUpdatePanel(taskId) {
        $.get("' . Url::to(['task/update-panel']) . '&id=" + taskId, function(data) {
            $("#task-panel").html(data).addClass("active");
        });
    }

    // Handle form submission via AJAX
    function bindCalendarFormHandler() {
    $(document).off("submit", "#task-panel form"); // Unbind old handler
        $(document).on("submit", "#task-panel form", function(e) {
            e.preventDefault();
            var form = $(this);
            $.ajax({
                url: form.attr("action"),
                method: "POST",
                data: form.serialize() + "&_csrf=" + csrfToken,
                dataType: "json",
                success: function(response) {
                    if (response.success) {
                        alert(response.message);
                        hideTaskPanel();
                        location.reload(); // Refresh to update calendar
                    } else {
                        $("#task-panel").html(response.html);
                        bindCalendarFormHandler(); // Re-bind after replacing form
                    }
                },
                error: function(xhr, status, error) {
                    console.log("Error: " + error);
                    alert("An error occurred. Please try again.");
                }
            });
        });
    }
', View::POS_END);
?>

<div class="task-index">
    <h1><?= Html::encode($this->title) ?></h1>

    <p>
        <?= Html::a('Back to List View', ['index'], ['class' => 'btn btn-primary']) ?>
    </p>

    <div class="calendar-container">
        <div id="calendar"></div>
    </div>

    <?php echo $this->render('_panel'); ?>
</div>