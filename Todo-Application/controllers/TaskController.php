<?php

namespace app\controllers;

use Yii;
use app\models\Task;
use yii\data\ActiveDataProvider;
use yii\web\Controller;
use yii\web\NotFoundHttpException;
use yii\web\Response;
use yii\filters\VerbFilter;

/**
 * TaskController implements the CRUD actions for Task model.
 */
class TaskController extends Controller
{
    /**
     * @inheritDoc
     */
    public function behaviors()
    {
        return array_merge(
            parent::behaviors(),
            [
                'verbs' => [
                    'class' => VerbFilter::className(),
                    'actions' => [
                        'delete' => ['POST'],
                        'toggle-complete' => ['POST'],
                    ],
                ],
                'access' => [
                    'class' => \yii\filters\AccessControl::class,
                    'rules' => [
                        [
                            'actions' => ['index', 'create', 'update', 'delete', 'calendar', 'view-panel', 'create-panel', 'update-panel', 'toggle-complete', 'calendar-data', 'calendar-panel'],
                            'allow' => true,
                            'roles' => ['@'],
                        ],
                    ],
                ],
            ]
        );
    }

    /**
     * Disable CSRF for calendar-data action since it's a GET request
     */
    public function beforeAction($action)
    {
        if ($action->id === 'calendar-data') {
            $this->enableCsrfValidation = false;
        }
        return parent::beforeAction($action);
    }

    /**
     * Lists all Task models.
     *
     * @return string
     */
    public function actionIndex()
    {
        $dataProvider = new ActiveDataProvider([
            'query' => Task::find(),
            /*
            'pagination' => [
                'pageSize' => 50
            ],
            'sort' => [
                'defaultOrder' => [
                    'id' => SORT_DESC,
                ]
            ],
            */
        ]);

        return $this->render('index', [
            'dataProvider' => $dataProvider,
        ]);
    }

    /**
     * Return JSON when called by AJAX instead of a full HTML page.
     * @param int $id ID
     * @return string|array
     * @throws NotFoundHttpException if the model cannot be found
     */
    public function actionView($id)
    {
        $task = $this->findModel($id); // Load the task model

        // If it's an AJAX request (from JavaScript)
        if (Yii::$app->request->isAjax) {
            Yii::$app->response->format = \yii\web\Response::FORMAT_JSON; // Return JSON format
            return [
                'name' => $task->name,
                'due_date' => $task->due_date,
                'is_complete' => (bool)$task->is_complete,
                'created_at' => $task->created_at,
            ];
        }

        // If not AJAX, just show the default view page
        return $this->render('view', [
            'model' => $task,
        ]);
    }

    /**
     * Creates a new Task model.
     * Handles the full process of creating a task, including form submission and saving.
     * If creation is successful, the browser will be redirected to the 'view' page.
     * @return string|\yii\web\Response
     */
    public function actionCreate()
    {
        // Create a new instance of the Task model, which represents a task in the database.
        // This is like creating a blank form to fill with new task data.
        $model = new Task();

        // Check if the request is an AJAX (Asynchronous JavaScript and XML) request.
        // AJAX is used when the page updates without reloading, like when submitting a form in the panel.
        if (Yii::$app->request->isAjax) {
            // Load data from the form submission (POST request) into the model.
            // $model->load(...) checks if the form data (e.g., name, due_date) is valid and fills the model.
            // $model->save() tries to save the data to the database.
            if ($model->load(Yii::$app->request->post()) && $model->save()) {
                // If the data is saved successfully, prepare a JSON response.
                // Yii::$app->response->format = \yii\web\Response::FORMAT_JSON tells Yii to return data in JSON format.
                Yii::$app->response->format = \yii\web\Response::FORMAT_JSON;
                // Return a JSON object with success status, a message, and the new task's ID.
                return [
                    'success' => true, // Indicates the operation was successful.
                    'message' => 'Task created successfully', // A message to show the user.
                    'id' => $model->id, // The ID of the newly created task.
                ];
            }
            // If the save fails (e.g., due to validation errors), render the 'create-panel' view again.
            // $this->renderAjax() returns the view as HTML without the full page layout, good for AJAX.
            // ['model' => $model] passes the model (with errors) to the view to show error messages.
            return $this->renderAjax('create-panel', ['model' => $model]);
        }

        // Render index page and trigger create-panel
        // If the request is not AJAX (e.g., user typed the URL directly), handle it differently.
        // $this->getView() gets the current view object to add JavaScript code.
        // registerJs adds JavaScript to run when the page is ready.
        // This script calls showCreatePanel() to open the create panel automatically.

        $this->getView()->registerJs('
            $(document).ready(function() {
                showCreatePanel(); // This function (defined in index.php) loads the create-panel via AJAX.
            });
        ', \yii\web\View::POS_READY); // POS_READY means run this JavaScript after the page loads.

        // Create a data provider to fetch all tasks for the index page grid.
        // ActiveDataProvider helps display a list of tasks in a grid view.
        $dataProvider = new ActiveDataProvider([
            'query' => Task::find(), // Task::find() gets all tasks from the database.
        ]);

        // Render the 'index' view, which shows the task list and panel.
        // 'dataProvider' => $dataProvider passes the task data to the view to display in the grid.
        return $this->render('index', [
            'dataProvider' => $dataProvider,
        ]);
    }


    /**
     * Updates an existing Task model.
     * Handles the full process of updating a task, including form submission and saving.
     * If update is successful, the browser will be redirected to the 'view' page.
     * @param int $id ID of the task to update
     * @return string|\yii\web\Response
     * @throws NotFoundHttpException if the model cannot be found
     */
    public function actionUpdate($id)
    {
        $model = $this->findModel($id);

        if (Yii::$app->request->isAjax) {
            if ($model->load(Yii::$app->request->post()) && $model->save()) {
                Yii::$app->response->format = \yii\web\Response::FORMAT_JSON;
                return [
                    'success' => true,
                    'message' => 'Task updated successfully',
                    'id' => $model->id,
                ];
            }
            // Return form with errors on failure
            return $this->renderAjax('update-panel', ['model' => $model]);
        }

        if ($this->request->isPost && $model->load($this->request->post()) && $model->save()) {
            return $this->redirect(['view', 'id' => $model->id]);
        }

        $this->getView()->registerJs('
            $(document).ready(function() {
                showUpdatePanel(' . $id . '); // This function (defined in index.php) loads the update-panel for the task with this ID via AJAX.
            });
        ', \yii\web\View::POS_READY);

        $dataProvider = new ActiveDataProvider([
            'query' => Task::find(), // Task::find() gets all tasks from the database.
        ]);

        return $this->render('index', [
            'dataProvider' => $dataProvider,
        ]);
    }

    /**
     * Deletes an existing Task model.
     * If deletion is successful, the browser will be redirected to the 'index' page.
     * @param int $id ID
     * @return \yii\web\Response
     * @throws NotFoundHttpException if the model cannot be found
     */
    public function actionDelete($id)
    {
        $this->findModel($id)->delete();

        return $this->redirect(['index']);
    }

    /**
     * Finds the Task model based on its primary key value.
     * If the model is not found, a 404 HTTP exception will be thrown.
     * @param int $id ID
     * @return Task the loaded model
     * @throws NotFoundHttpException if the model cannot be found
     */
    protected function findModel($id)
    {
        if (($model = Task::findOne(['id' => $id])) !== null) {
            return $model;
        }

        throw new NotFoundHttpException('The requested page does not exist.');
    }

    /**
     * Toggles the is_complete status of a task
     * @param integer $id
     * @return \yii\web\Response
     * @throws NotFoundHttpException if the model cannot be found
     */
    public function actionToggleComplete($id)
    {
        $model = $this->findModel($id); // find the task by ID
        $model->is_complete = !$model->is_complete; // toggle true/false
        $model->save(false); // skip validation, just save
        return $this->redirect(['index']); // return to list page
    }

    /**
     * Displays task details in a side panel via AJAX.
     * This is a helper action to load the view-panel view for a specific task.
     * @param int $id ID
     * @return string
     * @throws NotFoundHttpException if the model cannot be found
     */
    public function actionViewPanel($id)
    {
        $model = $this->findModel($id);
        return $this->renderPartial('view-panel', [
            'model' => $model,
        ]);
    }

    /**
     * Renders the create-panel view for a new task via AJAX.
     * This is a helper action called by JavaScript to load the initial create form.
     * It does not handle saving; that’s done by actionCreate.
     * @return string
     */
    public function actionCreatePanel()
    {
        $model = new Task();
        return $this->renderPartial('create-panel', ['model' => $model]);
    }

    /**
     * Renders the update-panel view for an existing task via AJAX.
     * This is a helper action called by JavaScript to load the initial update form.
     * It does not handle saving; that’s done by actionUpdate.
     * @param int $id ID of the task to update
     * @return string
     * @throws NotFoundHttpException if the model cannot be found
     */
    public function actionUpdatePanel($id)
    {
        $model = $this->findModel($id);
        return $this->renderPartial('update-panel', ['model' => $model]);
    }

    /**
     * Displays a calendar view of tasks.
     * @return string
     */
    public function actionCalendar()
    {
        return $this->render('calendar');
    }

    /**
     * Generates JSON data for FullCalendar events.
     * @param int $year
     * @param int $month
     * @return array
     */
    public function actionCalendarData($year = null, $month = null)
    {
        try {
            $query = Task::find();
            if ($year && $month) {
                $startDate = sprintf('%d-%02d-01', $year, $month + 1);
                $endDate = date('Y-m-t', strtotime($startDate));
                $query->where(['between', 'due_date', $startDate, $endDate]);
            }
            $tasks = $query->all();
            $events = [];
            $today = date('Y-m-d');
            foreach ($tasks as $task) {
                if ($task->due_date) {
                    $events[] = [
                        'id' => $task->id,
                        'title' => $task->name,
                        'start' => $task->due_date,
                        'className' => $task->is_complete ? 'task-completed' : (strtotime($task->due_date) < strtotime($today) ? 'task-overdue' : 'task-pending')
                    ];
                }
            }
            Yii::$app->response->format = \yii\web\Response::FORMAT_JSON;
            return $events;
        } catch (\Exception $e) {
            Yii::error($e->getMessage(), __METHOD__);
            Yii::$app->response->format = \yii\web\Response::FORMAT_JSON;
            return ['error' => 'Failed to load calendar data: ' . $e->getMessage()];
        }
    }

    /**
     * Displays a panel for a specific date to view/add tasks.
     * @param string $date The selected date in YYYY-MM-DD format
     * @return string
     * @throws NotFoundHttpException if the model cannot be found
     */
    public function actionCalendarPanel($date)
    {
        $model = new Task();
        $model->due_date = $date; // Pre-fill due date
        $tasks = Task::find()->where(['due_date' => $date])->all();

        if (Yii::$app->request->isAjax) {
            if ($model->load(Yii::$app->request->post()) && $model->save()) {
                Yii::$app->response->format = \yii\web\Response::FORMAT_JSON;
                return [
                    'success' => true,
                    'message' => 'Task created successfully',
                    'id' => $model->id,
                ];
            }
            return $this->renderAjax('_calendar_panel', [
                'model' => $model,
                'tasks' => $tasks,
                'date' => $date,
            ]);
        }

        return $this->redirect(['calendar']);
    }
}
