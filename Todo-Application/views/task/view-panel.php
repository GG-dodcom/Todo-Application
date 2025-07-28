<?php
use yii\helpers\Html;

?>

<div class="task-panel-header">
    Task Details
    <span class="close-panel" onclick="hideTaskPanel()">×</span>
</div>
<div class="task-panel-content">
    <p><strong>Task Name:</strong> <?= Html::encode($model->name) ?></p>
    <p><strong>Due Date:</strong> <?= Html::encode(Yii::$app->formatter->asDate($model->due_date)) ?></p>
    <p><strong>Status:</strong> <?= $model->is_complete ? 'Completed' : 'Pending' ?></p>
    <p><strong>Created At:</strong> <?= Html::encode(Yii::$app->formatter->asDatetime($model->created_at)) ?></p>
</div>