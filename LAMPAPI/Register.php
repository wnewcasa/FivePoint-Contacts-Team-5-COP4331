<?php

	$inData = getRequestInfo();
	
	$conn = new mysqli("localhost", "SmallProject", "team5Password!a", "COP4331");
	if( $conn->connect_error )
	{
		returnWithError( $conn->connect_error );
	}
	else
	{
		// First check if user already exists
		$stmt = $conn->prepare("SELECT ID FROM Users WHERE Login=?");
		if( !$stmt )
		{
			returnWithError( "Prepare failed: " . $conn->error );
		}
		else
		{
			$stmt->bind_param("s", $inData["login"]);
			$stmt->execute();
			$result = $stmt->get_result();
			
			if( $row = $result->fetch_assoc() )
			{
				returnWithError("User already exists");
			}
			else
			{
				// Insert new user
				$insertStmt = $conn->prepare("INSERT INTO Users (firstName, lastName, Login, Password) VALUES (?, ?, ?, ?)");
				if( !$insertStmt )
				{
					returnWithError( "Prepare failed: " . $conn->error );
				}
				else
				{
					$insertStmt->bind_param("ssss", $inData["firstName"], $inData["lastName"], $inData["login"], $inData["password"]);
					
					if( $insertStmt->execute() )
					{
						$newUserId = $conn->insert_id;
						returnWithInfo( $inData["firstName"], $inData["lastName"], $newUserId );
					}
					else
					{
						returnWithError("Registration failed: " . $insertStmt->error);
					}
					$insertStmt->close();
				}
			}
			$stmt->close();
		}
		$conn->close();
	}
	
	function getRequestInfo()
	{
		$json = file_get_contents('php://input');
		if (!$json) {
			return null;
		}
		return json_decode($json, true);
	}

	function sendResultInfoAsJson( $obj )
	{
		// Ensure we haven't already sent headers
		if (!headers_sent()) {
			header('Content-type: application/json');
		}
		echo $obj;
		exit(); // Ensure script stops after sending response
	}
	
	function returnWithError( $err )
	{
		$retValue = '{"id":0,"firstName":"","lastName":"","error":"' . $err . '"}';
		sendResultInfoAsJson( $retValue );
	}
	
	function returnWithInfo( $firstName, $lastName, $id )
	{
		$retValue = '{"id":' . $id . ',"firstName":"' . $firstName . '","lastName":"' . $lastName . '","error":""}';
		sendResultInfoAsJson( $retValue );
	}
	
?>