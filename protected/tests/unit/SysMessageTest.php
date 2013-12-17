<?php

class SysMessageTest extends CDbTestCase{


	public $fixtures=array(
		'messages'=>'SysMessage',

		'projects'=>'Project',
		'users'=>'User',
		'tbl_project_user_assignment'=>':tbl_project_user_assignment',
		'tbl_project_user_role'=>':tbl_project_user_role',
		'authAssign'=>':AuthAssignment'

		);

	

	public function testGetlatest(){
		$message = SysMessage::getLatest();
		$this->assertTrue($message instanceof SysMessage);
	}
	
	


	


}


?>