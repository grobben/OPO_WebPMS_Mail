<?php	

include('helpers/medoo/medoo.min.php');

/**
 * data class.
 */
class data {	
		
	/**
	 * __construct function.
	 * 
	 * @access public
	 * @return void
	 */
	public function getData() {	
							
		$data['application'] = $this->getApplicationData();	
		$data['projects'] = $this->getProjectsData();			
		$data['tickets'] = $this->getTicketsData();		
		
		return $data;
		
	}
	
	public function getApplicationData() {
		
		$xmlfile = file_get_contents('info.xml');
		$ob = simplexml_load_string($xmlfile);
		$json = json_encode($ob);
		$application = json_decode($json, true);
		
		return $application;
				
	}
		
	/**
	 * getProjectsData function.
	 * 
	 * @access public
	 * @return void
	 */
	public function getProjectsData() {
		
		$db = new medoo();
		
		$projects = $db->select('projects', '*', [
			'AND'=> [
				'progress[!]' => '100',
				'start[<=]'	=> date('Y-m-d')	
			]
		]);
					
		// Get Tasks
		$i = 0;
		foreach ($projects as $project) {					
			$projects[$i]['tasks'] = $this->getTasksData($project['id']);				
			$i++;
		}
			
		// Set deadlines
		$i = 0;
		foreach ($projects as $project) {		
										
			if ($project['end'] > date('Y-m-d', strtotime('tomorrow'))) {
				$deadline = 0;
			}
			else if ($project['end'] === date('Y-m-d', strtotime('tomorrow'))) {
				$deadline = 1;
			}
			else if ($project['end'] === date('Y-m-d', strtotime('today'))) {
				$deadline = 2;
			}
			else if ($project['end'] <= date('Y-m-d', strtotime('yesterday'))) {
				$deadline = 3;
				$deadlineDiff = floor((time() - strtotime($project['end'])) / (60 * 60 * 24));			
			}
												
			$projects[$i]['deadline'] = $deadline;	
			
			if ($deadline === 3) {
				$projects[$i]['days_past_deadline'] = $deadlineDiff;				
			}
			
			$i++;
			
		}
		
		// Set workers
		$i = 0;
		foreach ($projects as $project) {	
			
			$users = $db->select('project_has_workers', 'user_id', ['project_id' => $project['id']]);
				
			$projects[$i]['workers'] = '';
			$projects[$i]['project_managers'] = '';	
			
			foreach ($users as $user) {
				$user = $db->select('users', '*', ['id' => $user]);
				
				if (strtolower($user[0]['title']) === 'project manager' || strtolower($user[0]['title']) === 'assistent project manager' || strtolower($user[0]['title']) === 'junior project manager') {
					$projects[$i]['project_managers'] .= $user[0]['firstname'] . ' ';
				}
				else {
					$projects[$i]['workers'] .= $user[0]['firstname'] . ' ';
				}			
			}	
				
			$i++;
			
		}
		
		// Set time spent
		$i = 0;
		foreach ($projects as $project) {	
			
			$tracked_t = floor($projects[$i]['time_spent'] / 60);
			$tracked_h = floor($tracked_t / 60);
			$tracked_m = $tracked_t - ($tracked_h*60);
						
			$projects[$i]['time_spent'] = (intval($tracked_h) !== '0' ? $tracked_h . ' uur' : '') . (intval($tracked_h) !== 0 && intval($tracked_m) !== 0 ? ' en ' : '') . (intval($tracked_m) !== 0 ? $tracked_m . ' minuten ' : '');
				
			$i++;
			
		}				
				
		return $projects;
		
	}
	
	/**
	 * getTasksData function.
	 * 
	 * @access public
	 * @param mixed $projectId
	 * @return void
	 */
	public function getTasksData($projectId) {
		
		$db = new medoo();
		
		$tasks = $db->select('project_has_tasks', '*', [
			"AND" => [
				'project_id' => $projectId,
				'status' => 'open'
			]
		]);
		
		$i = 0;
		foreach ($tasks as $task) {	
			$worker = $db->get('users', 'firstname', ['id' => $task['user_id']]);			
			if ($worker == '0') {
				$worker = '-';
			}
			$tasks[$i]['worker'] = $worker;			
			$i++;
		}
		
		return $tasks;
		
	}
	
	/**
	 * getTicketsData function.
	 * 
	 * @access public
	 * @return void
	 */
	public function getTicketsData() {
		
		$db = new medoo();
		
		$tickets = $db->select('tickets', '*', [
			'status[!]' => 'closed'			
		]);
				
		$i = 0;
		foreach ($tickets as $ticket) {					
			$tickets[$i]['worker'] = $db->get('users', 'firstname', ['id' => $ticket['user_id']]);				
			$i++;
		}
		
		return $tickets;
		
	}

}