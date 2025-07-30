<?php
use yii\helpers\Html;
use yii\widgets\ActiveForm;

?>

<div class="task-panel-header">
    Tasks for <?= Html::encode($date) ?>
    <span class="close-panel" onclick="hideTaskPanel()">×</span>
</div>
<div class="task-panel-content">
    <h3>Create New Task</h3>
    <?php $form = ActiveForm::begin([
        'id' => 'calendar-task-form',
        'action' => ['task/calendar-panel', 'date' => $date],
        'options' => ['data-pjax' => false],
    ]); ?>

    <?= $form->field($model, 'name')->textInput(['maxlength' => true]) ?>

    <?= $form->field($model, 'due_date')->hiddenInput(['value' => $date])->label(false) ?>

    <div class="form-group">
        <?= Html::submitButton('Save Task', ['class' => 'btn btn-success']) ?>
    </div>

    <?php if ($model->hasErrors()): ?>
        <div class="form-error">
            <?= $form->errorSummary($model, ['header' => '']) ?>
        </div>
    <?php endif; ?>

    <?php ActiveForm::end(); ?>

    <h3>Tasks</h3>
    <?php if (empty($tasks)): ?>
        <p>No tasks for this date.</p>
    <?php else: ?>
        <ul>
            <?php foreach ($tasks as $task): ?>
                <li>
                    <strong><?= Html::encode($task->name) ?></strong>
                    (<?= $task->is_complete ? 'Completed' : (strtotime($task->due_date) < strtotime(date('Y-m-d')) ? 'Overdue' : 'Pending') ?>)
                    <?= Html::a('View', '#', ['onclick' => "showTaskPanel($task->id); return false;"]) ?>
                    <?= Html::a('Edit', '#', ['onclick' => "showUpdatePanel($task->id); return false;"]) ?>
                    <?= Html::a('Delete', ['task/delete', 'id' => $task->id], ['data-method' => 'post', 'data-confirm' => 'Are you sure?']) ?>
                </li>
            <?php endforeach; ?>
        </ul>
    <?php endif; ?>
</div>