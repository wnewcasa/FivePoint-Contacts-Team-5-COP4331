
function switchTab(tab) {
  // Remove active class from all tabs & forms
  document.querySelectorAll(".tab").forEach(t => t.classList.remove("active"));
  document.querySelectorAll(".form").forEach(f => f.classList.remove("active"));

  // Add active class to selected tab & form
  if (tab === "login") {
    document.querySelector("#loginForm").classList.add("active");
    document.querySelectorAll(".tab")[0].classList.add("active");
  } else {
    document.querySelector("#signupForm").classList.add("active");
    document.querySelectorAll(".tab")[1].classList.add("active");
  }
}

// Handle signup form submit
document.getElementById("signupForm").addEventListener("submit", async function(e) {
  e.preventDefault();

  let signupData = {
    firstName: document.getElementById("signup-firstname").value,
    lastName: document.getElementById("signup-lastname").value,
    login: document.getElementById("signup-username").value,
    password: document.getElementById("signup-password").value
  };

  let response = await fetch("LAMPAPI/Register.php", {
    method: "POST",
    headers: { "Content-Type": "application/json" },
    body: JSON.stringify(signupData)
  });

  let result = await response.json();

  if (result.error === "") {
    sessionStorage.setItem("userID", result.id);
    sessionStorage.setItem("userFirstName", result.firstName);
    window.location.href = "contacts.html"; // redirect after success
  } else {
    alert("Signup failed: " + result.error);
  }
});

// Handle login form submit
document.getElementById("loginForm").addEventListener("submit", async function(e) {
  e.preventDefault();

  let loginData = {
    login: document.getElementById("login-username").value,
    password: document.getElementById("login-password").value
  };

  let response = await fetch("LAMPAPI/Login.php", {
    method: "POST",
    headers: { "Content-Type": "application/json" },
    body: JSON.stringify(loginData)
  });

  let result = await response.json();

  if (result.error === "") {
    sessionStorage.setItem("userID", result.id);
    sessionStorage.setItem("userFirstName", result.firstName);
    window.location.href = "contacts.html"; // redirect after success
  } else {
    alert("Login failed: " + result.error);
  }
});
