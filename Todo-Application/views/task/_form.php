<?php

use yii\helpers\Html;
use yii\widgets\ActiveForm;

/** @var yii\web\View $this */
/** @var app\models\Task $model */
/** @var yii\widgets\ActiveForm $form */
?>

<div class="task-form">

    <?php $form = ActiveForm::begin([
        'id' => $model->isNewRecord ? 'create-task-form' : 'update-task-form',
        'action' => $model->isNewRecord ? ['task/create'] : ['task/update', 'id' => $model->id],
        'options' => ['data-pjax' => false],
        'enableAjaxValidation' => true,
        'validationUrl' => $model->isNewRecord ? ['task/create', 'validate' => 1] : ['task/update', 'id' => $model->id, 'validate' => 1],
    ]); ?>

    <!-- Task Name -->
    <?= $form->field($model, 'name')->textInput(['maxlength' => true]) ?>

    <!-- Due Date (uses HTML5 calendar input) -->
    <?= $form->field($model, 'due_date')->input('date') ?>

    <!-- Completion Status (uses checkbox) -->
    <?= $form->field($model, 'is_complete')->checkbox() ?>

    <!-- Created At is auto-filled, not shown in form -->
    <!-- <?= $form->field($model, 'created_at')->textInput() ?> -->

    <div class="form-group">
        <?= Html::submitButton($model->isNewRecord ? 'Create Task' : 'Update Task', ['class' => 'btn btn-primary']) ?>
    </div>

    <?php ActiveForm::end(); ?>

    <?php if ($model->hasErrors()): ?>
        <div class="form-error">
            <?= $form->errorSummary($model, ['header' => '']); ?>
        </div>
    <?php endif; ?>

</div>
