// app.js
// Simple JSON "backend" using localStorage

const USERS_KEY = "rb_users";
const CURRENT_USER_KEY = "rb_currentUser";
const LOGGED_IN_KEY = "rb_isLoggedIn";

// Helper: load all users
function loadUsers() {
  const raw = localStorage.getItem(USERS_KEY);
  if (!raw) return [];
  try {
    return JSON.parse(raw);
  } catch (e) {
    console.error("Failed to parse users JSON", e);
    return [];
  }
}

// Helper: save users
function saveUsers(users) {
  localStorage.setItem(USERS_KEY, JSON.stringify(users));
}

// Register new user
function registerUser({ name, email, role, password }) {
  if (!name || !email || !role || !password) {
    throw new Error("Please fill all fields.");
  }

  // IUB email check (very simple)
  if (!email.endsWith("@iub.edu.bd")) {
    throw new Error("Please use a valid IUB email (ends with @iub.edu.bd).");
  }

  const users = loadUsers();

  // Unique email check
  const exists = users.some((u) => u.email.toLowerCase() === email.toLowerCase());
  if (exists) {
    throw new Error("An account with this email already exists.");
  }

  const newUser = {
    id: Date.now(),
    name,
    email,
    role,
    password, // (For real app: hash this!)
  };

  users.push(newUser);
  saveUsers(users);

  // Set current user / logged in
  localStorage.setItem(CURRENT_USER_KEY, JSON.stringify(newUser));
  localStorage.setItem(LOGGED_IN_KEY, "true");

  return newUser;
}

// Login user
function loginUser({ email, password }) {
  if (!email || !password) {
    throw new Error("Please enter email and password.");
  }

  const users = loadUsers();
  const user = users.find(
    (u) =>
      u.email.toLowerCase() === email.toLowerCase() &&
      u.password === password
  );

  if (!user) {
    throw new Error("Invalid email or password.");
  }

  localStorage.setItem(CURRENT_USER_KEY, JSON.stringify(user));
  localStorage.setItem(LOGGED_IN_KEY, "true");

  return user;
}

// Get current logged-in user
function getCurrentUser() {
  const raw = localStorage.getItem(CURRENT_USER_KEY);
  if (!raw) return null;
  try {
    return JSON.parse(raw);
  } catch (e) {
    console.error("Failed to parse current user", e);
    return null;
  }
}

// Check login status
function isLoggedIn() {
  return localStorage.getItem(LOGGED_IN_KEY) === "true";
}

// Logout
function logoutUser() {
  localStorage.removeItem(LOGGED_IN_KEY);
  localStorage.removeItem(CURRENT_USER_KEY);
}

// Optional: seed some demo rides (if needed later)
function seedDemoRidesOnce() {
  if (localStorage.getItem("rb_demoSeeded")) return;

  const rides = [
    {
      id: 1,
      from: "Bashundhara R/A",
      to: "IUB North Gate",
      time: "Tomorrow · 8:30 AM",
      price: 50,
      status: "Confirmed",
      seatText: "Seat: 1 reserved · ৳50",
    },
    {
      id: 2,
      from: "Mirpur 10",
      to: "IUB Main Gate",
      time: "Thu · 9:00 AM",
      price: 70,
      status: "Pending",
      seatText: "Seat: 1 reserved · ৳70",
    },
  ];

  localStorage.setItem("rb_rides", JSON.stringify(rides));
  localStorage.setItem("rb_demoSeeded", "true");
}

seedDemoRidesOnce();
