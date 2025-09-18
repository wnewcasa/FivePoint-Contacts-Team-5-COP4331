<?php
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: POST, GET");
header("Access-Control-Allow-Headers: Content-Type");
header('Content-Type: application/json');

try 
{
    // Get input data
    if ($_SERVER['REQUEST_METHOD'] === 'POST') 
    {
        $inData = json_decode(file_get_contents('php://input'), true);
    } 
    else 
    {
        $inData = array(
            "userID" => isset($_GET['userID']) ? $_GET['userID'] : null,
            "search" => isset($_GET['search']) ? $_GET['search'] : ""
        );
    }

    if (!isset($inData["userID"]) || !$inData["userID"]) 
    {
        returnWithError("Missing required field: userID");
        exit;
    }

    $userID = $inData["userID"];
    $searchTerm = isset($inData["search"]) ? trim($inData["search"]) : "";

    // Connect to database
    $conn = new mysqli("localhost", "SmallProject", "team5Password!a", "COP4331");
    if ($conn->connect_error) 
    {
        returnWithError("Database connection failed: " . $conn->connect_error);
        exit;
    }

    // Build query
    $sql = "SELECT ID, FirstName, LastName, Phone, Email FROM Contacts WHERE UserID = ?";
    $params = [$userID];
    $types = "i";

    if ($searchTerm !== "") 
    {
        $sql .= " AND (FirstName LIKE ? OR LastName LIKE ? OR Phone LIKE ? OR Email LIKE ?)";
        $searchPattern = "%" . $searchTerm . "%";
        $params[] = $searchPattern;
        $params[] = $searchPattern;
        $params[] = $searchPattern;
        $params[] = $searchPattern;
        $types .= "ssss";
    }

    $sql .= " ORDER BY LastName, FirstName";

    $stmt = $conn->prepare($sql);
    if (!$stmt) 
    {
        returnWithError("Prepare failed: " . $conn->error);
        $conn->close();
        exit;
    }

    // Bind params dynamically
    $stmt->bind_param($types, ...$params);

    if ($stmt->execute()) 
    {
        $result = $stmt->get_result();
        $contacts = array();

        while ($row = $result->fetch_assoc()) 
        {
            $contacts[] = array(
                "id" => $row["ID"],
                "firstName" => $row["FirstName"],
                "lastName" => $row["LastName"],
                "phone" => $row["Phone"],
                "email" => $row["Email"]
            );
        }

        returnWithSuccess("Load completed", $contacts, count($contacts));
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

function returnWithError($err) 
{
    $retValue = array("error" => $err);
    sendResultInfoAsJson(json_encode($retValue));
}

function returnWithSuccess($message, $contacts, $count) 
{
    $retValue = array(
        "error" => "",
        "message" => $message,
        "results" => $contacts,
        "count" => $count
    );
    sendResultInfoAsJson(json_encode($retValue));
}

function sendResultInfoAsJson($obj) 
{
    echo $obj;
}
?>