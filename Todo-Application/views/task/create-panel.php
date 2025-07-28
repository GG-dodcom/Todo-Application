<?php
use yii\helpers\Html;
use yii\widgets\ActiveForm;

/** @var yii\web\View $this */
/** @var app\models\Task $model */
?>

<div class="task-panel-header">
    Create Task
    <span class="close-panel" onclick="hideTaskPanel()">×</span>
</div>
<div class="task-panel-content">


    <?= $this->render('_form', [
        'model' => $model,
    ]) ?>

</div>


<?php
