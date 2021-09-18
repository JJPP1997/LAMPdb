<?php
	//tested in Ubuntu with mysql-server,mysql_secure_installation, apache2, php, libapache2-mod-php, php-mysql
	/*Commands to setup Ubuntu machine
		sudo apt update
		sudo apt install apache2
		sudo ufw allow in "Apache"
		sudo apt install mysql-server
		sudo mysql_secure_installation (SKIP THE VALIDATE PASSWORD PLUGIN IF YOU WANT)
		sudo apt install php libapache2-mod-php php-mysql
	
	*/
	
	//localhost
	$host_name="127.0.0.1:3306";
	$db_name="accomodation_db";
	$user="root";
	$pasword="root";
	$accomodation_table_name="accomodation";
	
	
	// Turn on all error reporting for debuggin, can be comented for default values
	//error_reporting(-1);
	//mysqli_report(MYSQLI_REPORT_ALL );
	//ini_set("display_errors", "true");
	
	////////////////
	//--CLASSES (prepared for expansion of code)--
	//////////////
	
	//parent class that has common values
	class Accomodation{
		protected  $id,$name,$city,$aComunity;
		
		function __construct($id,$name,$city,$aComunity){
			
			$this->setId($id);
			$this->setName($name);
			$this->setCity($city);
			$this->setAComunity($aComunity);
			
		}
		//set
		public function setId($id) {
			$this->id = (int)$id;
		}
		public function setName($name) {
			$this->name = (string)$name;
		}
		
		public function setCity($city) {
			$this->city = (string)$city;
		}
		public function setAComunity($aComunity) {
			$this->aComunity = (string)$aComunity;
		}
		//get
		function getId(){
			return $this->id;
		}
		function getName(){
			return $this->name;
		}
		function getCity(){
			return $this->city;
		}
		function getAComunity(){
			return $this->aComunity;
		}
			
	}
	class Hotel extends Accomodation{
		private  $stars,$standardRoom;
		
		function __construct($id,$name,$city,$aComunity,$stars,$standardRoom){
			parent::__construct($id,$name,$city,$aComunity);
			$this->setStars($stars);
			$this->setstandardRoom($standardRoom);
		}
		//set
		public function setStars($stars) {
			$this->stars = (int)$stars;
		}
		public function setstandardRoom($standardRoom) {
			$this->standardRoom = (string)$standardRoom;
		}
		//get
		function getStars(){
			return $this->stars;
		}
		function getstandardRoom(){
			return $this->standardRoom;
		}
		//echoes the data
		function echoData(){
			echo $this->name.", ".$this->stars." estrellas,".$this->standardRoom.", ".$this->city.", ".$this->aComunity.".\n ";
		}
			
	}
	class Apartment extends Accomodation{
		private  $capacity,$availableApartments;
		
		function __construct($id,$name,$city,$aComunity,$capacity,$availableApartments){
			parent::__construct($id,$name,$city,$aComunity);
			$this->setCapacity($capacity);
			$this->setAvailableApartments($availableApartments);
		}
		//set
		public function setCapacity($capacity) {
			$this->capacity = (int)$capacity;
		}
		public function setAvailableApartments($availableApartments) {
			$this->availableApartments = (int)$availableApartments;
		}
		//get
		function getCapacity(){
			return $this->capacity;
		}
		function getAvailableApartments(){
			return $this->availableApartments;
		}
		//echoes the data 
		function echoData(){
			echo $this->name.", ".$this->availableApartments." apartamentos, ".$this->capacity." adultos, ".$this->city.", ".$this->aComunity.".\n ";
		}
			
	}
	
	////////////////
	//--DB SETUP--
	///////////////
	
	//if db is not instantiated of is missing tables they are created and filled with test cases
	try{
		// Attempt to connect to host and set connection as global conn
		$conn=new mysqli($host_name,$user,$pasword);
		
		//check conection to host
		if(mysqli_connect_errno()){
			$txt = "ERROR at conecting to db ". $conn->connect_error."\n";
			reportError($txt);
		
		}else{
			
			// Create DB if not exists
			$dbquery = "CREATE DATABASE IF NOT EXISTS ".$db_name;
		
			if(mysqli_query($conn, $dbquery)){
				//database is created succesfully and set as selected db
				echo "Database created succesfully \n";							
				mysqli_select_db($conn, $db_name);
				/* change character set to utf8mb4 to support all languages*/
				$conn->set_charset("utf8mb4");
				
				//if table not exist is created
				$tablequery="
					CREATE TABLE IF NOT EXISTS `".$accomodation_table_name."` (
						`id` INT(6)  AUTO_INCREMENT PRIMARY KEY,
						`type` VARCHAR(15) NOT NULL,
						`name` VARCHAR(500) NOT NULL,
						`city` VARCHAR(500) NOT NULL,
						`aComunity` VARCHAR(500) NOT NULL,
						`stars` INT(1) DEFAULT -1,
						`standardRoom` VARCHAR(100) DEFAULT 'datos no disponibles',
						`capacity` INT(2) DEFAULT -1,
						`availableApartments` INT(3) DEFAULT -1
						
					)";
					
				if(mysqli_query($conn, $tablequery)){
					//if table is created check for its content so it has at least some test entries
					echo "Table created succesfully \n";					  
					checkTableContent();
				}else{
					// If tablequery fails alert and write the ouput into a debug doc
					$txt = "ERROR at creating table ".$accomodation_table_name." : ".$conn->connect_error."\n";
					reportError($txt);
				}
		
			} else {
				// If dbquery fails alert and write the ouput into a debug doc
				$txt = "ERROR at creating database: ".$e.".\n";
				reportError($txt);
			}
		}
	}catch(exception $e){
		// If conexion fails alert and write the ouput into a debug doc
		$txt = "Exception at creating database: ".$e.".\n";
		reportError($txt);
		
		
	}
	
	////////////////
	//--MAIN LOOP--
	///////////////
	
	//this loops acts as the main would in a java app, it runs continuously until the program is exited
	$exit=false;
	echo "Welcome, type a maximun of 3 leters to search. If you want to exit the app send an empty string by pressing enter without typing. \n";
	while(!$exit){
		$text = readline("Enter the text to search; \n");
		//use if testing outside of LAMP 
		//$text="Hot";
		if(strlen($text)<4){
		
			//if the imput is empty exit the program
			if(strcmp($text, "")==0){
				$exit= true;
				mysqli_close($conn);
				exit("Good Bye \n");
			}
			//the text is trimmed and surrounded with double commas to avoid SQL injections
			searchText(trim($text));
		
		}else{
			echo "The text had too many letters, try again. \n";
		}
		//decoment to limit to 1 loop
		//$exit=true;
	}
	
	
	////////////////
	//--DB MANAGER FUNCS--
	///////////////
	
	//searchs the table for similar text, if found echo it												   
	function searchText($text){	
		try{
			//the special chars are appended since putting them directly in the sql statement causes an error 
			$text = "%$text%"; 
			if (!$GLOBALS["conn"]) {
					$txt="Connection failed at searchText: " . $GLOBALS["conn"]->connect_error."\n";
					reportError($txt);
				
			}else{
		
				$sql="SELECT * FROM `".$GLOBALS["accomodation_table_name"]."` WHERE `name`  LIKE  ? ORDER BY `name` DESC";	
				
				if ($stmt =  $GLOBALS["conn"]->prepare($sql)) {	
					/* execute statement */
					 $stmt->bind_param("s",$text) ;
					$stmt->execute();
					/* bind result variables */
					$stmt->store_result();
					
					if($stmt->num_rows > 0) {
						echo $stmt->num_rows." matches in DB \n";
						$stmt->bind_result($id,$type,$name,$city,$aComunity,$stars,$standardRoom,$capacity,$availableApartments);
						while($row=$stmt->fetch()){
							switch((string)$type) {
							  case 'hotel':
									$h =new Hotel($id,$name,$city,$aComunity,$stars,$standardRoom);
									$h->echoData();
								break;
							  case 'apartament':
									$a=new Apartment($id,$name,$city,$aComunity,$capacity,$availableApartments);
									$a->echoData();
								break;
							}
						}
						
					} else {
						echo "No matches in DB. \n";
						
					}
				
					
				} else {
					$txt="ERROR database not responding at line searchText, CONTACT AN ADMIN ".$GLOBALS["conn"]->connect_error."\n";
					reportError($txt);
				}

			}
		}catch(exception $e){
			$txt="Exception at searchText : ".$e."\n";
			reportError($txt);
		}
	}
	//insert a new Hotel into the table, recives an object Hotel						 
	function insertHotel($hotel){
		try{
			if (!$GLOBALS["conn"]) {
				$txt="Connection failed at insertHotel: " . $GLOBALS["conn"]->connect_error."\n";
				reportError($txt);
				
			}else{
				
				$sql="INSERT INTO `".$GLOBALS["accomodation_table_name"]."` 
				(`type`,`name`,`city`,`aComunity`,`stars`,`standardRoom`) VALUE ('hotel',?,?,?,?,?);";	
				if ($stmt =  $GLOBALS["conn"]->prepare($sql)) {
					
					$stmt->bind_param("sssis",$hotel->getName(),$hotel->getCity(),$hotel->getAComunity(),$hotel->getStars(),$hotel->getstandardRoom());	
					if($stmt->execute()){
						echo "New hotel inserted succesfully. \n";
					}
				}
			}
		
		}catch(exception $e){
			$txt="Exception at insertHotel : ".$e."\n";
			reportError($txt);
		}
		
	}
	//insert a new Aaprtment into the table, recives an object Apartment		
	function insertApartment($apartment){
		try{
			if (!$GLOBALS["conn"]) {
				$txt="Connection failed at insertApartment: " . $GLOBALS["conn"]->connect_error."\n";
				reportError($txt);
				
			}else{
				
				$sql="INSERT INTO `".$GLOBALS["accomodation_table_name"]."` 
				(`type`,`name`,`city`,`aComunity`,`capacity`,`availableApartments`) VALUE ('apartament',?,?,?,?,?);";	
				if ($stmt =  $GLOBALS["conn"]->prepare($sql)) {
					echo $apartment->getName();
					$stmt->bind_param("sssii",$apartment->getName(),$apartment->getCity(),$apartment->getAComunity(),$apartment->getCapacity(),$apartment->getAvailableApartments());	
					if($stmt->execute()){
						echo "New apartment inserted succesfully. \n";
					}
				}
			}
		
		}catch(exception $e){
			$txt="Exception at insertApartment : ".$e."\n";
			reportError($txt);
		}
		
	}
	// check if the table has content and fill it if empty
	function checkTableContent(){
		try{
			if (!$GLOBALS["conn"]) {
					$txt="Connection failed: " . $GLOBALS["conn"]->connect_error."\n";
					reportError($txt);
			
			}else{
				$sql="SELECT * FROM `".$GLOBALS["accomodation_table_name"]."`";	
				
				if ($stmt =  $GLOBALS["conn"]->prepare($sql)) {	
					/* bind result variables */
					$stmt->execute();
					$stmt->store_result();
					//if there is no rows it means the table is empty
					if($stmt->num_rows === 0) {
						$apart1=new Apartment(-1,"Apartamentos Sol y playa","Málaga","Málaga",6 ,50);
						$apart2=new Apartment(-1,"Apartamentos Beach","Almeria","Almeria",4 ,10);
						$hotel1=new Hotel(-1,"Hotel Azul","Valencia","Valencia",3 ,"habitación doble");
						$hotel2=new Hotel(-1,"Hotel Blanco","Almeria","Mojacar",4 ,"habitación sencilla");
						
						insertApartment($apart1);
						insertApartment($apart2);
						insertHotel($hotel1);
						insertHotel($hotel2);
						
					}
					
				} else {
					$txt="ERROR database not responding at line checkTableContent, CONTACT AN ADMIN: ". $GLOBALS["conn"]->connect_error."\n";
					reportError($txt);
				}

			}
		}catch(exception $e){
			$txt="Exception at checkTableContent : ".$e."\n";
			reportError($txt);
		}

	}
	
	////////////////
	//--MISC FUNCS--
	///////////////
	
	//records error in debug doc,close conection  and die 
	function reportError($message){
		
		$myfile = fopen("debug.txt", "a+") ;
		fwrite($myfile, date("Y-m-d H:i:s").": ".$message);
		fclose($myfile);
		mysqli_close($GLOBALS["conn"]);
		die($message);
	}

?>