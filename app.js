/* app.js — RideBuddy Backend Connected (Async/Await)
   Converted from localStorage to PHP API
*/

(function () {
  const API_BASE = "api";
  const KEYS = {
    SESSION: "rb_session_user",
    SELECTED_RIDE_ID: "rb_selected_ride_id",
    SELECTED_BOOKING_ID: "rb_selected_booking_id",
  };

  async function apiCall(endpoint, method = "GET", body = null) {
    const options = {
      method,
      headers: { "Content-Type": "application/json" },
    };
    if (body) options.body = JSON.stringify(body);

    try {
      const res = await fetch(`${API_BASE}/${endpoint}`, options);

      // Check if we got JSON
      const contentType = res.headers.get("content-type");
      if (!contentType || !contentType.includes("application/json")) {
        // Received HTML or text? PHP is probably not working.
        const text = await res.text();
        console.warn("API returned non-JSON:", text.substring(0, 100)); // Debug log
        throw new Error("CRITICAL: PHP is not running. You must open this site via XAMPP Localhost (http://localhost/Ridebuddy), not VS Code Live Server or Netlify.");
      }

      const json = await res.json();
      return json;
    } catch (err) {
      console.error("API Error:", err);
      // Show the logical error if available, otherwise show the network error.
      // This helps debug Issues on InfinityFree vs Localhost.
      const errorMsg = err.message || "Unknown error";
      if (errorMsg.includes("CRITICAL")) {
        throw new Error(errorMsg);
      } else {
        throw new Error("Connection Error: " + errorMsg + "\n\nPossible causes:\n1. api/db.php has wrong credentials.\n2. You haven't uploaded the 'api' folder.\n3. The database server is down.");
      }
    }
  }

  function saveSession(user) {
    if (user) localStorage.setItem(KEYS.SESSION, JSON.stringify(user));
    else localStorage.removeItem(KEYS.SESSION);
  }

  function getSession() {
    try { return JSON.parse(localStorage.getItem(KEYS.SESSION)); }
    catch { return null; }
  }

  async function registerUser({ name, email, role, password }) {
    const res = await apiCall("auth.php?action=register", "POST", { name, email, role, password });
    if (res.status === "success") {
      saveSession(res.user);
      return res.user;
    } else {
      throw new Error(res.message);
    }
  }

  async function loginUser({ email, password }) {
    const res = await apiCall("auth.php?action=login", "POST", { email, password });
    if (res.status === "success") {
      saveSession(res.user);
      if (res.user.role === 'admin') {
        window.location.href = 'admin.html';
      }
      return res.user;
    } else {
      throw new Error(res.message);
    }
  }

  async function logoutUser() {
    await apiCall("auth.php?action=logout", "POST");
    saveSession(null);
    window.location.href = "index.html";
  }

  function isLoggedIn() {
    return !!getSession();
  }

  function getCurrentUser() {
    return getSession();
  }

  async function getAllRides() {
    return await apiCall("rides.php?action=getAll");
  }

  async function getRideById(id) {
    return await apiCall(`rides.php?action=getById&id=${id}`);
  }

  async function addRide(rideObj) {
    const res = await apiCall("rides.php?action=add", "POST", rideObj);
    if (res.status === "success") {
      return res.ride;
    } else {
      throw new Error(res.message);
    }
  }

  async function updateRide(id, patch) {
    const res = await apiCall(`rides.php?action=update&id=${id}`, "POST", patch);
    if (res.status !== "success") throw new Error(res.message);
  }

  function setSelectedRideId(id) {
    localStorage.setItem(KEYS.SELECTED_RIDE_ID, id);
  }

  function getSelectedRideId() {
    return localStorage.getItem(KEYS.SELECTED_RIDE_ID);
  }

  function setSelectedRide(ride) {
    localStorage.setItem("rb_selected_ride", JSON.stringify(ride));
    if (ride && ride.id) setSelectedRideId(ride.id);
  }

  function getSelectedRide() {
    try {
      return JSON.parse(localStorage.getItem("rb_selected_ride"));
    } catch (e) {
      return null;
    }
  }

  async function createBooking({ rideId, seats }) {
    const res = await apiCall("bookings.php?action=create", "POST", { rideId, seats });
    if (res.status === "success") {
      localStorage.setItem(KEYS.SELECTED_BOOKING_ID, res.booking.id);
      return res.booking;
    } else {
      throw new Error(res.message);
    }
  }

  async function getBookingById(bookingId) {
    return await apiCall(`bookings.php?action=getById&id=${bookingId}`);
  }

  async function getUserBookings(userId) {
    return await apiCall(`bookings.php?action=getByUser`);
  }

  async function markBookingPaid({ bookingId, method }) {
    const res = await apiCall("bookings.php?action=pay", "POST", { bookingId, method });
    if (res.status === "success") {
      return res.booking;
    } else {
      throw new Error(res.message);
    }
  }

  async function markBookingCompleted({ bookingId }) {
    const res = await apiCall("bookings.php?action=complete", "POST", { bookingId });
    if (res.status === "success") {
      return res.booking;
    }
    throw new Error("Failed to complete");
  }

  function getSelectedBookingId() {
    return localStorage.getItem(KEYS.SELECTED_BOOKING_ID);
  }

  function setSelectedBookingId(id) {
    localStorage.setItem(KEYS.SELECTED_BOOKING_ID, id);
  }

  async function submitRating({ bookingId, stars }) {
    const res = await apiCall("ratings.php?action=submit", "POST", { bookingId, stars });
    if (res.status !== "success") throw new Error(res.message);
  }

  async function getRatingForBooking(bookingId) {
    return await apiCall(`ratings.php?action=getByBooking&bookingId=${bookingId}`);
  }


  async function getRideChat(rideId) {
    return await apiCall(`chat.php?action=get&rideId=${rideId}`);
  }

  async function sendRideChatMessage(rideId, text) {
    return await apiCall("chat.php?action=send", "POST", { rideId, text });
  }


  window.registerUser = registerUser;
  window.loginUser = loginUser;
  window.logoutUser = logoutUser;
  window.isLoggedIn = isLoggedIn;
  window.getCurrentUser = getCurrentUser;

  window.getAllRides = getAllRides;
  window.getRideById = getRideById;
  window.addRide = addRide;
  window.updateRide = updateRide;

  window.setSelectedRideId = setSelectedRideId;
  window.getSelectedRideId = getSelectedRideId;
  window.setSelectedRide = setSelectedRide;
  window.getSelectedRide = getSelectedRide;

  window.createBooking = createBooking;
  window.getBookingById = getBookingById;
  window.getUserBookings = getUserBookings;
  window.markBookingPaid = markBookingPaid;
  window.markBookingCompleted = markBookingCompleted;

  window.getSelectedBookingId = getSelectedBookingId;
  window.setSelectedBookingId = setSelectedBookingId;

  window.submitRating = submitRating;
  window.getRatingForBooking = getRatingForBooking;

  window.getRideChat = getRideChat;
  window.sendRideChatMessage = sendRideChatMessage;

  window.storeRideData = addRide;

  // --- ADMIN FUNCTIONS ---
  async function getAdminStats() {
    const user = getCurrentUser();
    return await apiCall(`admin.php?action=getDashboardStats&admin_id=${user ? user.id : 0}`);
  }
  async function getAdminUsers() {
    const user = getCurrentUser();
    return await apiCall(`admin.php?action=getAllUsers&admin_id=${user ? user.id : 0}`);
  }
  async function adminDeleteUser(targetId) {
    const user = getCurrentUser();
    const res = await apiCall(`admin.php?action=deleteUser&admin_id=${user ? user.id : 0}`, "POST", { target_id: targetId });
    if (res.status !== 'success') throw new Error(res.message);
  }
  async function getAdminRides() {
    const user = getCurrentUser();
    return await apiCall(`admin.php?action=getAllRides&admin_id=${user ? user.id : 0}`);
  }
  async function adminDeleteRide(targetId) {
    const user = getCurrentUser();
    const res = await apiCall(`admin.php?action=deleteRide&admin_id=${user ? user.id : 0}`, "POST", { target_id: targetId });
    if (res.status !== 'success') throw new Error(res.message);
  }

  window.getAdminStats = getAdminStats;
  window.getAdminUsers = getAdminUsers;
  window.adminDeleteUser = adminDeleteUser;
  window.getAdminRides = getAdminRides;
  window.adminDeleteRide = adminDeleteRide;

})();
