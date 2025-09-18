<?php

	$inData = getRequestInfo();
	
	$conn = new mysqli("localhost", "SmallProject", "team5Password!a", "COP4331"); 	
	if( $conn->connect_error )
	{
		returnWithError( $conn->connect_error );
	}
	else
	{
		// First check if contact exists and belongs to the user
		$stmt = $conn->prepare("SELECT ID FROM Contacts WHERE ID=? AND UserID=?");
		$stmt->bind_param("ii", $inData["contactId"], $inData["userId"]);
		$stmt->execute();
		$result = $stmt->get_result();
		
		if( !$result->fetch_assoc() )
		{
			returnWithError("Contact not found or access denied");
		}
		else
		{
			// Delete the contact
			$stmt = $conn->prepare("DELETE FROM Contacts WHERE ID=? AND UserID=?");
			$stmt->bind_param("ii", $inData["contactId"], $inData["userId"]);
			
			if( $stmt->execute() )
			{
				if( $stmt->affected_rows > 0 )
				{
					returnWithSuccess("Contact deleted successfully");
				}
				else
				{
					returnWithError("Contact not found or already deleted");
				}
			}
			else
			{
				returnWithError("Delete failed: " . $stmt->error);
			}
		}

		$stmt->close();
		$conn->close();
	}
	
	function getRequestInfo()
	{
		return json_decode(file_get_contents('php://input'), true);
	}

	function sendResultInfoAsJson( $obj )
	{
		header('Content-type: application/json');
		echo $obj;
	}
	
	function returnWithError( $err )
	{
		$retValue = '{"error":"' . $err . '"}';
		sendResultInfoAsJson( $retValue );
	}
	
	function returnWithSuccess( $message )
	{
		$retValue = '{"message":"' . $message . '","error":""}';
		sendResultInfoAsJson( $retValue );
	}
	
?>