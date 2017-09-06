<head>
	<title>TODO List</title>
</head>
<?php
//"TODO-list By Matthew Weberman

ini_set('display_errors', '0');

$servername = "localhost";
$username = "root";
$password = "";
$dbname = "TODO";
$queryname = "listone";
$userinput = null;
$removeinput = null;

// Clear the GET vars from the url
session_start();
if (!empty($_GET))
{
	$_SESSION['got'] = $_GET;
    header('Location: http://localhost:8080/TODO/index.php');
    die;
}
else
{
    if (!empty($_SESSION['got']))
	{
        $_GET = $_SESSION['got'];
        unset($_SESSION['got']);
    }
}

// Format for displaying the MySQL table
echo 	"<style>
			* {
				margin-top:1em;
			}

			table, th, td {
				border: 1px solid black;
			}
			th {
				padding-left: 5px;
				padding-right: 5px;
			}
			td {
				padding: 15px;
			}
		</style>";
?>
<div align="center">
	<?php
	// Create connection
	$conn = new mysqli($servername, $username, $password);
		
	// Check connection
	if ($conn->connect_error) 
	{
		exit("Connection failed: " . $conn->connect_error . "<br />\n");
	}		
	// Connect to database or create it if it does not exist
	$sql = "USE " . $dbname;
	if ($conn->query($sql) === TRUE) 
	{
		//echo "Database connection successful<br /><br />\n";
	} 
	else 
	{
		$sql = "CREATE DATABASE " . $dbname;
		if ($conn->query($sql) === FALSE) 
		{
			exit("Error creating Database: " . $conn->error . "<br /><br />\n");
		}
		$sql = "USE " . $dbname;
		if ($conn->query($sql) === TRUE) 
		{
			echo "Database creation/connection successful<br /><br />\n";
			//Initialize the first table
			$sql = "CREATE TABLE " . $queryname . "(
				Task_Number INTEGER PRIMARY KEY NOT NULL AUTO_INCREMENT,
				Task_Description VARCHAR(70) NOT NULL
				)";
			if(!$conn->query($sql)){
				exit("Error initializing table: " . $conn->error . "<br /><br />\n");
			}
			echo "table initialized";
		}
		else 
		{
			exit("Error connecting to Database: " . $conn->error . "<br /><br />\n");
		}
	}
	// Get the name of the table/user (if time)


	// Run query & Display the updated TODO-list or "Empty"
	$sql = "SELECT * FROM " . $queryname;
	if($result = $conn->query($sql))
	{
		//echo "number of rows: " . $result->num_rows . "<br>";
		//Get the query file and have php execute the string	
		if ($result->num_rows == 0) 
		{
			echo "Your To-Do list is empty.\n<br />";
		}
		elseif ($result->num_rows >= 0) 
		{
			echo "<table id='tbl'><tr>";
			$field=$result->fetch_fields();
			// output column names  
			foreach ($field as $col)
			{
				echo "<th>".$col->name."</th>";
			}
			echo "</tr>";
			// output data of each row
			while($row = $result->fetch_row()) 
			{
				echo "<tr>";
				for ($i=0;$i<$result->field_count;$i++)
				{
					echo "<td>".$row[$i]."</td>";
				}
				echo "</tr>";
			}
			echo "</table>\n<br />";
		}
		else  
		{
			exit("Error finding data<br /><br />\n");
		}
	}
	else
	{
		exit("Error executing query: ". $conn->error . "<br /><br />\n");
	}
?>

<form action="index.php" method="get">
	<p>To add onto the To-Do list, type out your task below, then press "Submit"</p>
	<input type="text" name="userinput" id="userinput" size="80" maxlength="70">&nbsp;&nbsp;&nbsp;
	<input type="submit" name="submit" id="submit" value="Submit">
	<br><br>
	<p>To remove a task from the To-Do list, type the task's number below, then press "Remove"</p>
	<input type="text" name="removeinput" id="removeinput" size="10">&nbsp;&nbsp;&nbsp;
	<input type="submit" name="remove" id="remove" value="Remove">
</form> 

<?php
// Functions for buttons
function addTask($dbname, $queryname, $servername, $username, $password)
{
	$userinput = $_GET['userinput'];
	// Create connection
	$conn = new mysqli($servername, $username, $password);		
	// Check connection
	if ($conn->connect_error) 
	{
		exit("Connection failed when adding string: " . $conn->connect_error . "<br />\n");
	}		
	// Connect to database
	$sql = "USE " . $dbname;
	if ($conn->query($sql) === TRUE) 
	{
		//echo "Database connection successful<br /><br />\n";
	}
	$sql = "INSERT INTO " . $queryname . " (Task_Description)
				VALUES ('$userinput');";
	if ($conn->query($sql) === FALSE) 
	{
		exit("Error inserting value into  TODO list: " . $conn->error . "<br /><br />\n");
	}			
	else 
	{
		//echo "query submitted";
	}
	
	// Refresh the page so the use can view the updated list
	header("Refresh:0");
}

function removeTask($dbname, $queryname, $servername, $username, $password)
{
	$removeinput = $_GET['removeinput'];
	//Check if removeinput is an integer
	
	// Create connection
	$conn = new mysqli($servername, $username, $password);	
	// Check connection
	if ($conn->connect_error) 
	{
		exit("Connection failed when adding string: " . $conn->connect_error . "<br />\n");
	}		
	// Connect to database
	$sql = "USE " . $dbname;
	if ($conn->query($sql) === TRUE) 
	{
		//echo "Database connection successful<br /><br />\n";
	}
	// Make sure row number exists
	$sql = "SELECT * FROM " . $queryname . ";"; 	
	if($conn->query($sql)->num_rows >= $removeinput)
	{
		// Attempt to DELETE row with given id
		$sql = "DELETE FROM " . $queryname . 
					" WHERE Task_Number='$removeinput'";
		if($conn->query($sql))
		{	
			// Drop tables and remake them in order to reset the auto-increment
			$sql = "DROP TABLE IF EXISTS listtemp;";
			$conn->query($sql);
			
			$sql = "CREATE TABLE listtemp
					SELECT Task_Description
					FROM " .$queryname . ";";
			$conn->query($sql);
			
			$sql = "DROP TABLE " . $queryname . ";";
			$conn->query($sql);
			
			$sql = "CREATE TABLE " . $queryname . "(
					Task_Number INTEGER PRIMARY KEY NOT NULL AUTO_INCREMENT,
					Task_Description VARCHAR(70) NOT NULL
					)";
			$conn->query($sql);

			$sql = "INSERT INTO " . $queryname . " (Task_Description)
					SELECT * FROM listtemp;";
			$conn->query($sql);
			
			//echo "Task Deleted";
			header("Refresh:0");
		}
		else{
			exit("Deletion failed: ". $conn->connect_error . "<br />\n");
		}
	}
	else
	{
		echo "Unknown task number. Try again.";
	}
	
}

if(array_key_exists('submit',$_GET)){
	if ($_GET['userinput'] != "")
		addTask($dbname, $queryname, $servername, $username, $password);
	else
		echo "No user input detected.";
}

if(array_key_exists('remove',$_GET)){
	if (!filter_var($_GET['removeinput'], FILTER_VALIDATE_INT) || (intval($_GET['removeinput']) <= 0))
		echo "Removal input is not a vaild number.";
	elseif ($_GET['removeinput'] != "")
		removeTask($dbname, $queryname, $servername, $username, $password);
	else
		echo "Invalid input.";
}
?>
</div>