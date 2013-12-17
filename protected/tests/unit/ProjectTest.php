<?php

class ProjectTest extends CDbTestCase{


	public $fixtures=array(
		'projects'=>'Project',
		'users'=>'User',
		'tbl_project_user_assignment'=>':tbl_project_user_assignment',
		'tbl_project_user_role'=>':tbl_project_user_role',
		'authAssign'=>':AuthAssignment'
		);

	
	public function testCreate(){
		//CREATE a new project
		//Creo l'oggetto project che è istanza del modello AR (model active record) da inserire nel database
		$newProject = new Project;
		
		//creo il record come array
		$newProjectName = 'Test Project Creation';
		
		$newProject->setAttributes(
			array(
				'name' => $newProjectName,
				'description' => 'This is a test for new project creation',
				//'create_time' => '2013-11-14 17:02:00',
				//'create_user_id' => 1,
				//'update_time' => '2013-11-14 17:02:00',
				//'update_user_id' => 1,	
			)
		);

		Yii::app()->user->setId($this->users('user1')->id);	
				
		//scrivo il recordo nel db grazie al metodo SAVE di CActiveRecord	
		$this->assertTrue($newProject->save());

		
		//READ back the newly created project
		//ottengo il modello del record nel database sottoforma di oggetto grazie al metodo MODEL di CActiveRecord
		//Di solito questo metodo si usa per chiamare altri metodi di questo livello
		//In questo caso ricerco per primaryKey grazie al metodo FINDBYPK
		$retrievedProject = Project::model()->findByPk($newProject->id);
		
		//Verifico di avere un'istanza della classe Project
		$this->assertTrue($retrievedProject instanceof Project);
		
		//Verifico che il nome dell'oggetto ritornato sia uguale al nome dell'oggetto inserito
		$this->assertEquals($newProjectName,$retrievedProject->name);

		$this->assertEquals(Yii::app()->user->id, $retrievedProject->create_user_id);

	}
	

	public function testRead(){
		$retrievedProject = $this->projects('project1');
		$this->assertTrue($retrievedProject instanceof Project);
		$this->assertEquals('Test Project 1', $retrievedProject->name);
	}


	public function testUpdate(){
		$project = $this->projects('project2');
		//UPDATE the newly created project
		//Definisco il nuovo nome del progetto
		$updatedProjectName = 'Updated Test Project 1';
		//Imposto il nome del progetto
		$project->name = $updatedProjectName;
		//Aggiorno l'oggetto con il metodo SAVE
		$this->assertTrue($project->save(false));

		//read back the record again to ensure the update worked
		$updatedProject = Project::model()->findByPk($project->id);
		$this->assertTrue($updatedProject instanceof Project);
		$this->assertEquals($updatedProjectName, $updatedProject->name);
	}


	public function testDelete(){
		$project = $this->projects('project2');
		//DELETE the project
		//prendo l'id del progetto che dev'essere eliminato
		$savedProjectId = $project->id;
		//verifico di aver eliminato l'oggetto
		$this->assertTrue($project->delete());
		//riprovo a cercare nella tabella l'oggetto con quell'id
		$deletedProject = Project::model()->findByPk($savedProjectId);
		//verifico di non averlo trovato
		$this->assertEquals(NULL, $deletedProject);
	}


	public function testGetUserOptions(){
		$project = $this->projects('project1');
		
		$options = $project->userOptions;

		$this->assertTrue(is_array($options));
		$this->assertTrue(count($options) > 0);
	}


	public function testUserRoleAssignment(){
		$project = $this->projects('project1');
		$user = $this->users('user1');
		$this->assertEquals(1, $project->associateUserToRole('owner',$user->id));
		$this->assertEquals(1, $project->removeUserFromRole('owner',$user->id));	
	}


	public function testIsInRole(){
		$row1 = $this->tbl_project_user_role['row1'];
		Yii::app()->user->setId($row1['user_id']);
		$project = Project::model()->findByPk($row1['project_id']);
		$this->assertTrue($project->isUserInRole('member'));
	}


	public function testUserAccessBasedOnProjectRole(){
		//simulo una finestra, in cui ho un project_id e mi trovo nel contesto di uno specifico progetto
		$row1 = $this->tbl_project_user_role['row1'];
		Yii::app()->user->setId($row1['user_id']);
		$project = Project::model()->findByPk($row1['project_id']);
		
		//Assegno la regola per l'utente riguardo al ruolo specifico
		$auth = Yii::app()->authManager;
		$bizRule = 'return isset($params["project"]) && $params["project"]->isUserInRole("member");';
		$auth->assign('member', $row1['user_id'], $bizRule);

		//Controllo i permessi dell'utente in funzione del progetto
		$params = array('project'=>$project);
		$this->assertTrue(Yii::app()->user->checkAccess('updateIssue', $params));
		$this->assertTrue(Yii::app()->user->checkAccess('readIssue',$params));
        $this->assertFalse(Yii::app()->user->checkAccess('updateProject',$params));
	

		//now ensure the user does not have any access to a project they are not associated with
		$project=Project::model()->findByPk(1);
		$params=array('project'=>$project);

		$this->assertFalse(Yii::app()->user->checkAccess('updateIssue',$params));
		$this->assertFalse(Yii::app()->user->checkAccess('readIssue',$params));
		$this->assertFalse(Yii::app()->user->checkAccess('updateProject',$params));
	}


		public function testUserAccessBasedOnProjectRole_V2(){
		//simulo una finestra, in cui ho un project_id e mi trovo nel contesto di uno specifico progetto
		$row2 = $this->tbl_project_user_role['row2'];
		Yii::app()->user->setId($row2['user_id']);
		$project = Project::model()->findByPk($row2['project_id']);
		
		//Assegno la regola per l'utente riguardo al ruolo specifico
		$auth = Yii::app()->authManager;
		$bizRule = 'return isset($params["project"]) && $params["project"]->isUserInRole("owner");';
		$auth->assign('owner', $row2['user_id'], $bizRule);

		//Controllo i permessi dell'utente in funzione del progetto
		$params = array('project'=>$project);
		$this->assertTrue(Yii::app()->user->checkAccess('createUser', $params));

	}


	public function testGetUserRoleOptions(){
		//simulo una finestra, in cui ho uno user_id e mi trovo nel contesto di uno specifico progetto
		$row1 = $this->tbl_project_user_role['row1'];
		Yii::app()->user->setId($row1['user_id']);
		$project = Project::model()->findByPk($row1['project_id']);

		$options = $project->getUserRoleOptions();
		$this->assertTrue(is_array($options));
		$this->assertTrue(count($options) > 0);
	}


	public function testAssociateUserToProject(){
		$project = $this->projects('project2');
		$user = $this->users('user1');
		
		$this->assertEquals(1, $project->associateUserToProject($user->id));
	}


	public function testIsUserInProject(){
		$project = $this->projects('project1');
		$user = $this->users('user1');

		$this->assertTrue($project->isUserInProject($user->id));
	}


	


}


?>