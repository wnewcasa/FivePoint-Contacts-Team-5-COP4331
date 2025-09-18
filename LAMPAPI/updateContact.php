<?php
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: POST, PUT");
header("Access-Control-Allow-Headers: Content-Type");
header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] !== 'POST' && $_SERVER['REQUEST_METHOD'] !== 'PUT') 
{
    returnWithError("Only POST and PUT methods allowed");
    exit;
}

try {
    $inData = getRequestInfo();
    
    if (!isset($inData["contactID"]) || !isset($inData["userID"])) 
    {
        returnWithError("Missing required fields: contactID and userID");
        exit;
    }
    
    $contactID = $inData["contactID"];
    $userID = $inData["userID"];
    
    $conn = new mysqli("localhost", "SmallProject", "team5Password!a", "COP4331");
    
    if ($conn->connect_error) 
    {
        returnWithError("Database connection failed: " . $conn->connect_error);
        exit;
    }
    
    $checkStmt = $conn->prepare("SELECT ID FROM Contacts WHERE ID = ? AND UserID = ?");
    $checkStmt->bind_param("ii", $contactID, $userID);
    $checkStmt->execute();
    $result = $checkStmt->get_result();
    
    if ($result->num_rows === 0) 
    {
        returnWithError("Contact not found or you don't have permission to update this contact");
        $checkStmt->close();
        $conn->close();
        exit;
    }
    $checkStmt->close();
    
    $updateFields = array();
    $params = array();
    $types = "";
    
    if (isset($inData["firstName"])) 
    {
        $updateFields[] = "FirstName = ?";
        $params[] = $inData["firstName"];
        $types .= "s";
    }
    
    if (isset($inData["lastName"])) 
    {
        $updateFields[] = "LastName = ?";
        $params[] = $inData["lastName"];
        $types .= "s";
    }
    
    if (isset($inData["phone"])) 
    {
        $updateFields[] = "Phone = ?";
        $params[] = $inData["phone"];
        $types .= "s";
    }
    
    if (isset($inData["email"])) 
    {
        $updateFields[] = "Email = ?";
        $params[] = $inData["email"];
        $types .= "s";
    }
    
    
    if (empty($updateFields)) 
    {
        returnWithError("No fields to update. Provide at least one of: firstName, lastName, phone, email");
        $conn->close();
        exit;
    }
    
    $params[] = $contactID;
    $params[] = $userID;
    $types .= "ii";
    
    $sql = "UPDATE Contacts SET " . implode(", ", $updateFields) . " WHERE ID = ? AND UserID = ?";
    
    $stmt = $conn->prepare($sql);
    
    if (!$stmt) 
    {
        returnWithError("Prepare failed: " . $conn->error);
        $conn->close();
        exit;
    }
    
    $stmt->bind_param($types, ...$params);
    
    if ($stmt->execute()) 
    {
        if ($stmt->affected_rows > 0) 
        {
            returnWithSuccess("Contact updated successfully");
        } 
        else 
        {
            returnWithError("No changes were made to the contact");
        }
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

function returnWithSuccess($message) 
{
    $retValue = array
    (
        "error" => "",
        "message" => $message
    );
    
    sendResultInfoAsJson(json_encode($retValue));
}

function sendResultInfoAsJson($obj) 
{
    echo $obj;
}
?>