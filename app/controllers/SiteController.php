<?php

class SiteController extends Controller
{
    public function filters()
    {
        return array(
            'accessControl',
        );
    }

    public function accessRules()
    {
        return array(
            array('allow',
                'actions'=>array('index', 'login', 'error'),
                'users'=>array('*'),
            ),
            array('allow',
                'actions'=>array('logout', 'set', 'get'),
                'users'=>array('@'),
            ),
            array('deny',
                'users'=>array('*'),
            ),
        );
    }

    public function beforeAction($action) {
        Init::vars();

    	return parent::beforeAction($action);
    }

	public function actionIndex()
	{
        if (Yii::app()->user->id) {
            $this->render('index');
        } else {
            $this->render('login');
        }
	}

	/**
	 * This is the action to handle external exceptions.
	 */
	public function actionError()
	{
		if($error=Yii::app()->errorHandler->error)
		{
			if(Yii::app()->request->isAjaxRequest)
				echo $error['message'];
			else
				$this->render('error', $error);
		}
	}

    /**
     * Displays the login page
     */
    public function actionLogin()
    {
        if (count($_POST) > 0) {
            $model=new LoginForm;

            $model->username = $_POST['login'];
            $model->password = $_POST['password'];

            if($model->validate() && $model->login($_POST['session'])) {
                echo json_encode(array("error"=>false));
            } else {
                echo json_encode(array("error"=>true));
            }
        } else {
            $this->render('login');
        }
    }

    /**
     * Logs out the current user and redirect to homepage.
     */
    public function actionLogout()
    {
        Yii::app()->user->logout();
        unset(Yii::app()->session["access_token"]);

        $this->redirect(Yii::app()->homeUrl);
    }

    public function actionSet()
    {
        if ( (isset($_POST["param"])) and (isset($_POST["value"])) ) {
            Yii::app()->session[$_POST["param"]] = $_POST["value"];

            echo "";
        }
    }

    public function actionGet()
    {
        if (isset($_POST["param"])) {
            echo Yii::app()->session[$_POST["param"]];
        }
    }
}