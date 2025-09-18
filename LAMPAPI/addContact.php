<?php
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: POST");
header("Access-Control-Allow-Headers: Content-Type");
header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    returnWithError("Only POST method allowed");
    exit;
}

try 
{
    $inData = getRequestInfo();
    
    if (!isset($inData["userID"]) || !isset($inData["firstName"]) || !isset($inData["lastName"]) || !isset($inData["phone"]) || !isset($inData["email"])) 
	{
        returnWithError("Missing required fields: userID, firstName, lastName, phone, email");
        exit;
    }
    
    $userID = $inData["userID"];
    $firstName = $inData["firstName"];
    $lastName = $inData["lastName"];
    $phone = $inData["phone"];
    $email = $inData["email"];
    
    $conn = new mysqli("localhost", "SmallProject", "team5Password!a", "COP4331");
    
    if ($conn->connect_error) 
	{
        returnWithError("Database connection failed: " . $conn->connect_error);
        exit;
    }
    
    $stmt = $conn->prepare("INSERT INTO Contacts (FirstName, LastName, Phone, Email, UserID) VALUES (?, ?, ?, ?, ?)");
    
    if (!$stmt) 
	{
        returnWithError("Prepare failed: " . $conn->error);
        $conn->close();
        exit;
    }
    
    $stmt->bind_param("ssssi", $firstName, $lastName, $phone, $email, $userID);
    
    if ($stmt->execute()) 
	{
        $contactId = $conn->insert_id;
        returnWithSuccess("Contact added successfully", $contactId);
    } 
	else 
	{
        returnWithError("Execute failed: " . $stmt->error);
    }
    
    $stmt->close();
    $conn->close();
    
} 
catch (Exception $e) 
{
    returnWithError("Server error: " . $e->getMessage());
}

function getRequestInfo() 
{
    $input = file_get_contents('php://input');
    $data = json_decode($input, true);
    
    if (json_last_error() !== JSON_ERROR_NONE) 
	{
        returnWithError("Invalid JSON format");
        exit;
    }
    
    return $data;
}

function returnWithError($err) 
{
    $retValue = array("error" => $err);
    sendResultInfoAsJson(json_encode($retValue));
}

function returnWithSuccess($message, $contactId = null) 
{
    $retValue = array
	(
        "error" => "",
        "message" => $message
    );
    
    if ($contactId !== null) 
	{
        $retValue["contactId"] = $contactId;
    }
    
    sendResultInfoAsJson(json_encode($retValue));
}

function sendResultInfoAsJson($obj) 
{
    echo $obj;
}
?>
