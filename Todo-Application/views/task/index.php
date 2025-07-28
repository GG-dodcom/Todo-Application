<?php
use yii\grid\ActionColumn;
use yii\grid\GridView;
use yii\helpers\Html;
use app\models\Task;
use yii\web\View;
use yii\helpers\Url;

$this->title = 'Tasks List';
$this->params['breadcrumbs'][] = $this->title;

// Register CSS with improved close button styling

// Register CSRF token for AJAX
$this->registerJs("var csrfToken = '" . Yii::$app->request->csrfToken . "';", View::POS_HEAD);

$this->registerCss('
    .task-panel {
        position: fixed;
        top: 56px;
        right: -400px;
        width: 400px;
        height: calc(100% - 56px); /* Adjust height to account for top offset */
        background: white;
        box-shadow: -2px 0 5px rgba(0,0,0,0.3);
        transition: right 0.3s ease-in-out;
        z-index: 1000;
        overflow-y: auto;
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
    }
    .close-panel {
        cursor: pointer;
        color: white;
        margin-left: auto;
        padding: 0 10px;
        transition: background-color 0.3s;
    }
    .close-panel:hover {
        background-color: rgba(0, 0, 0, 0.2); /* Subtle hover feedback */
    }
    .form-error {
        color: #dc3545;
        font-size: 0.875em;
        margin-top: 5px;
    }
    .has-error .help-block {
        color: #dc3545;
    }
');

// Register JavaScript
$this->registerJs('
    function showTaskPanel(taskId) {
        $.get("' . Url::to(['task/view-panel']) . '&id=" + taskId, function(data) {
            $("#task-panel").html(data).addClass("active");
        });
    }
    function showCreatePanel() {
        $.get("' . Url::to(['task/create-panel']) . '", function(data) {
            $("#task-panel").html(data).addClass("active");
        });
    }
    function showUpdatePanel(taskId) {
        $.get("' . Url::to(['task/update-panel']) . '&id=" + taskId, function(data) {
            $("#task-panel").html(data).addClass("active");
        });
    }
    function hideTaskPanel() {
        $("#task-panel").removeClass("active");
    }
    // Handle form submission via AJAX
    $(document).on("submit", "#task-panel form", function(e) {
        e.preventDefault();
        var form = $(this);
        $.ajax({
            url: form.attr("action"),
            method: "POST",
            data: form.serialize() + "&" + csrfToken, // Include CSRF token
            dataType: "json",
            success: function(response) {
            if (response.success) {
                alert(response.message);
                hideTaskPanel();
                location.reload(); // Refresh to reflect changes
            } else {
                    $("#task-panel").html(response.html); // Expect HTML with errors
                }
            },
            error: function(xhr, status, error) {
                console.log("Error: " + error);
                alert("An error occurred. Please try again.");
            }
        });
    });
', View::POS_END);
?>

<div class="task-index">

    <!-- Page Title -->
    <h1><?= Html::encode($this->title) ?></h1>

    <!-- Button to create a new task -->
    <p>
        <?= Html::a('Create Task', '#', ['class' => 'btn btn-success', 'onclick' => 'showCreatePanel(); return false;']) ?>
        <?= Html::a('Calendar View', ['calendar'], ['class' => 'btn btn-primary']) ?>

    </p>

    <!-- Yii GridView to list all tasks -->
    <?= GridView::widget([
        'dataProvider' => $dataProvider,
        'columns' => [
            ['class' => 'yii\grid\SerialColumn'], // Auto-increment row numbers

            // 'id',
            'name',
            'due_date:date',

            // Display task completion status with colored badges
            [
              'attribute' => 'is_complete',
              'format' => 'raw',
              'value' => function ($model) {
                  return $model->is_complete
                      ? '<span class="badge bg-success">Completed</span>'
                      : '<span class="badge bg-warning text-dark">Pending</span>';
              },
            ],

            'created_at:datetime',

            // Button to toggle task complete/incomplete
            [
                'label' => 'Toggle',
                'format' => 'raw', // allows HTML inside the button
                'value' => function ($model) {
                    return Html::a(
                        $model->is_complete ? 'Mark Incomplete' : 'Mark Complete',
                        ['task/toggle-complete', 'id' => $model->id],
                        [
                            'class' => 'btn btn-sm ' . ($model->is_complete ? 'btn-warning' : 'btn-success'),
                            'data-method' => 'post', // important for security (POST request)
                        ]
                    );
                },
            ],

            // Action buttons: view, update, delete
            [
              'class' => 'yii\grid\ActionColumn',
            'template' => '{view} {update} {delete}',
              'buttons' => [
                  'view' => function ($url, $model) {
                      return Html::a(
                          '<i class="bi bi-eye"></i>',
                          '#',
                          [
                          'class' => 'btn btn-sm btn-primary',
                          'onclick' => 'showTaskPanel(' . $model->id . '); return false;',
                              'title' => 'View Details',
                          ]
                      );
                  },
                    'update' => function ($url, $model) {
                        return Html::a(
                            '<i class="bi bi-pencil"></i>',
                            '#',
                            [
                                'class' => 'btn btn-sm btn-primary',
                                'onclick' => 'showUpdatePanel(' . $model->id . '); return false;',
                                'title' => 'Update Task',
                            ]
                        );
                    },
                ],
            ],
        ],
    ]); ?>

<div id="task-panel" class="task-panel"></div>