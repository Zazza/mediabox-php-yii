<?php

class SiteController extends Controller
{
    public function accessRules()
    {
        return array(
            array('allow',
                'actions'=>array('index', 'login'),
                //'roles'=>array('admin'),
                'users'=>array('*'),
            ),
            array('deny',
                'users'=>array('*'),
            ),
        );
    }

    public function beforeAction() {
        Init::vars();

        return true;
    }

	public function actionIndex()
	{
        if (Yii::app()->user->id) {
            $this->render('index');
        } else {
            $this->actionLogin();
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
        $model=new LoginForm;

        $error = false;

        // if it is ajax validation request
  //      if(isset($_POST['ajax']) && $_POST['ajax']==='login-form')
    //    {
      //      echo CActiveForm::validate($model);
        //    Yii::app()->end();
        //}

        // collect user input data
        if(isset($_POST['LoginForm']))
        {
            $model->attributes=$_POST['LoginForm'];
            // validate user input and redirect to the previous page if valid
            if($model->validate() && $model->login()) {
                $this->redirect("/");
            } else {
                $error = true;
            }
        }
        // display the login form
        $this->render('login', array('model'=>$model, 'error'=>$error));
    }

    /**
     * Logs out the current user and redirect to homepage.
     */
    public function actionLogout()
    {
        Yii::app()->user->logout();

        $this->redirect(Yii::app()->homeUrl);
    }
}