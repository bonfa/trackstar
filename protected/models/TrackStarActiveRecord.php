<?php


abstract class TrackStarActiverecord extends CActiveRecord
{

	/**
	* Prepares create_time, create_user_id, update_time, update_user_id attributes before performing validation
	*/
	protected function beforeValidate(){
		if($this->isNewRecord) {
			//set the create date, last updated date and the user doing the create (and update)
			$this->create_time = $this->update_time = new CDbExpression('NOW()');
			$this->create_user_id = $this->update_user_id=Yii::app()->user->id;
		}
		else {
			//not a new record -> change the last update time and the last update user
			$this->update_time = new CDbExpression('NOW()');
			$this->update_user_id = Yii::app()->user->id;
		}

		return parent::beforeValidate();
	}

}
