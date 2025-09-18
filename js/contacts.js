
    function openAddForm(){
      document.getElementById("addContact").style.display = "flex";
    }
    function closeAdd(){
      document.getElementById("addContact").style.display = "none";
    }

    function openEditForm(contact) {
      document.getElementById("editContact").style.display = "flex";

      document.getElementById("editContactId").value = contact.id;
      document.getElementById("editFirstName").value = contact.firstName;
      document.getElementById("editLastName").value = contact.lastName;
      document.getElementById("editNumber").value = contact.phone;
      document.getElementById("editEmail").value = contact.email;
    }

    function closeEdit() {
      document.getElementById("editContact").style.display = "none";
    }

    async function addContact(event){
      event.preventDefault();

      let userID = sessionStorage.getItem("userID");
      let firstName = document.getElementById("addFirstName").value;
      let lastName = document.getElementById("addLastName").value;
      let phone = document.getElementById("addNumber").value;
      let email = document.getElementById("addEmail").value;

      let contactData = {
        userID: userID,
        firstName: firstName,
        lastName: lastName,
        phone: phone,
        email: email
      };

      try {
        let response = await fetch("LAMPAPI/addContact.php", {
          method: "POST",
          headers: { "Content-Type": "application/json" },
          body: JSON.stringify(contactData)
        });

        let result = await response.json();

        if (result.error === "") {
          closeAdd();
          document.getElementById("addInput").reset();

          // Reload contacts to show new row with proper buttons
          loadContacts();
        } else {
          alert("Failed: " + result.error);
        }
      } catch (err) {
        console.error(err);
        alert("Server error, check console.");
      }
    }

async function loadContacts(searchTerm = "") {
  let userID = sessionStorage.getItem("userID");
  if (!userID) {
    alert("No user session found. Please log in again.");
    window.location.href = "index.html";
    return;
  }

  let searchData = { userID: userID, search: searchTerm };

  try {
    let response = await fetch("LAMPAPI/searchContact.php", {
      method: "POST",
      headers: { "Content-Type": "application/json" },
      body: JSON.stringify(searchData)
    });

    let result = await response.json();

    if (result.error === "") {
      let tbody = document.getElementById("contactBody");
      tbody.innerHTML = ""; // clear old rows

      result.results.forEach(contact => {
        let row = tbody.insertRow();
        row.insertCell(0).textContent = contact.firstName; // lowercase keys
        row.insertCell(1).textContent = contact.lastName;
        row.insertCell(2).textContent = contact.phone;
        row.insertCell(3).textContent = contact.email;

        let deleteButton = row.insertCell(4);
        
        deleteButton.innerHTML = `<button class="edit-btn" onclick='openEditForm(JSON.parse(\`${JSON.stringify(contact)}\`))'>&#9997;</button> <button class="delete-btn" onclick="deleteContact(${contact.id}, this)">&#128465;</button>`;
      });
    } else {
      alert("Load failed: " + result.error);
    }
  } catch (err) {
    console.error(err);
    alert("Server error, check console.");
  }
}

// Call on page load
window.onload = () => loadContacts();

// Hook up search bar
document.getElementById("searchBar").addEventListener("keyup", (e) => {
  loadContacts(e.target.value);
});

async function deleteContact(contactId, btn) {
  if (!confirm("Are you sure you want to delete this contact?")) return;

  let userId = sessionStorage.getItem("userID"); 

  try {
    let response = await fetch("LAMPAPI/deleteContact.php", {
      method: "POST",
      headers: { "Content-Type": "application/json" },
      body: JSON.stringify({
        contactId: parseInt(contactId), 
        userId: parseInt(userId)        
      })
    });

    let result = await response.json();

    if (result.error === "") {
      // Remove the row from the table dynamically
      if (btn) {
        let row = btn.closest("tr");
        if (row) row.remove();
      } else {
        loadContacts(); // fallback: reload the table
      }
    } else {
      alert("Failed: " + result.error);
    }
  } catch (err) {
    console.error(err);
    alert("Server error, check console.");
  }
}

async function submitEditContact(event) {
  event.preventDefault();

  const contactID = document.getElementById("editContactId").value;
  const userID = sessionStorage.getItem("userID");

  const updatedContact = {
  contactID: parseInt(document.getElementById("editContactId").value),
  userID: parseInt(sessionStorage.getItem("userID")),
  firstName: document.getElementById("editFirstName").value,
  lastName: document.getElementById("editLastName").value,
  phone: document.getElementById("editNumber").value,
  email: document.getElementById("editEmail").value
  };

  try {
    const response = await fetch("LAMPAPI/updateContact.php", {
      method: "POST", 
      headers: { "Content-Type": "application/json" },
      body: JSON.stringify(updatedContact)
    });

    const result = await response.json();

    if (result.error === "") {
      closeEdit();
      loadContacts(); // refresh the table dynamically
    } else {
      alert("Edit failed: " + result.error);
    }
  } catch (err) {
    console.error(err);
    alert("Server error, check console.");
  }
}
