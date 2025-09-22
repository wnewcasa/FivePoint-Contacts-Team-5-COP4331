
// ---------- Modal Controls ----------
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

// ---------- Phone Formatter ----------
function formatPhoneNumber(input) {
  let value = input.value.replace(/\D/g, ""); // keep digits
  if (value.length > 10) value = value.substring(0, 10);

  let formatted = value;
  if (value.length > 6) {
    formatted = `(${value.substring(0, 3)}) - ${value.substring(3, 6)} - ${value.substring(6)}`;
  } else if (value.length > 3) {
    formatted = `(${value.substring(0, 3)}) - ${value.substring(3)}`;
  } else if (value.length > 0) {
    formatted = `(${value}`;
  }

  input.value = formatted;
}

// ---------- Validation ----------
function isValidPhone(phone) {
  const digits = phone.replace(/\D/g, "");
  return digits.length === 10;
}
function isValidEmail(email) {
  const re = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
  return re.test(email);
}

// ---------- Add Contact ----------
async function addContact(event){
  event.preventDefault();

  let userID = sessionStorage.getItem("userID");
  let firstName = document.getElementById("addFirstName").value;
  let lastName = document.getElementById("addLastName").value;
  let phone = document.getElementById("addNumber").value;
  let email = document.getElementById("addEmail").value;

  // validate before sending
  if (!isValidPhone(phone)) {
    alert("Please enter a valid phone number in format (XXX) - XXX - XXXX");
    return;
  }
  if (!isValidEmail(email)) {
    alert("Please enter a valid email address.");
    return;
  }

  let contactData = { userID, firstName, lastName, phone, email };

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
      loadContacts();
    } else {
      alert("Failed: " + result.error);
    }
  } catch (err) {
    console.error(err);
    alert("Server error, check console.");
  }
}

// ---------- Load Contacts ----------
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
      tbody.innerHTML = "";

      result.results.forEach(contact => {
        let row = tbody.insertRow();
        row.insertCell(0).textContent = contact.firstName;
        row.insertCell(1).textContent = contact.lastName;
        row.insertCell(2).textContent = contact.phone;
        row.insertCell(3).textContent = contact.email;

        let actionCell = row.insertCell(4);
        actionCell.innerHTML = `
          <button class="edit-btn" 
            onclick='openEditForm(JSON.parse(\`${JSON.stringify(contact)}\`))'>&#9997;</button> 
          <button class="delete-btn" onclick="deleteContact(${contact.id}, this)">&#128465;</button>`;
      });
    } else {
      alert("Load failed: " + result.error);
    }
  } catch (err) {
    console.error(err);
    alert("Server error, check console.");
  }
}

// ---------- Delete Contact ----------
async function deleteContact(contactId, btn) {
  if (!confirm("Are you sure you want to delete this contact?")) return;

  let userId = sessionStorage.getItem("userID");

  try {
    let response = await fetch("LAMPAPI/deleteContact.php", {
      method: "POST",
      headers: { "Content-Type": "application/json" },
      body: JSON.stringify({ contactId: parseInt(contactId), userId: parseInt(userId) })
    });

    let result = await response.json();

    if (result.error === "") {
      if (btn) {
        let row = btn.closest("tr");
        if (row) row.remove();
      } else {
        loadContacts();
      }
    } else {
      alert("Failed: " + result.error);
    }
  } catch (err) {
    console.error(err);
    alert("Server error, check console.");
  }
}

// ---------- Edit Contact ----------
async function submitEditContact(event) {
  event.preventDefault();

  const updatedContact = {
    contactID: parseInt(document.getElementById("editContactId").value),
    userID: parseInt(sessionStorage.getItem("userID")),
    firstName: document.getElementById("editFirstName").value,
    lastName: document.getElementById("editLastName").value,
    phone: document.getElementById("editNumber").value,
    email: document.getElementById("editEmail").value
  };

  // validate before sending
  if (!isValidPhone(updatedContact.phone)) {
    alert("Please enter a valid phone number in format (XXX) - XXX - XXXX");
    return;
  }
  if (!isValidEmail(updatedContact.email)) {
    alert("Please enter a valid email address.");
    return;
  }

  try {
    const response = await fetch("LAMPAPI/updateContact.php", {
      method: "POST", 
      headers: { "Content-Type": "application/json" },
      body: JSON.stringify(updatedContact)
    });

    const result = await response.json();

    if (result.error === "") {
      closeEdit();
      loadContacts();
    } else {
      alert("Edit failed: " + result.error);
    }
  } catch (err) {
    console.error(err);
    alert("Server error, check console.");
  }
}

// ---------- Startup ----------
window.onload = () => {
  loadContacts();

  // attach auto-formatters
  document.getElementById("addNumber").addEventListener("input", e => formatPhoneNumber(e.target));
  document.getElementById("editNumber").addEventListener("input", e => formatPhoneNumber(e.target));

  // hook up search bar
  document.getElementById("searchBar").addEventListener("keyup", e => {
    loadContacts(e.target.value);
  });
};




}
