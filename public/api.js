const API_BASE_URL = "/api";

// دالة موحدة لإرسال الطلبات إلى Laravel API مع التوكن
async function apiRequest(endpoint, method = "GET", body = null) {
    const token = sessionStorage.getItem("authToken");

    const headers = {
        "Content-Type": "application/json",
        "Accept": "application/json"
    };

    if (token) {
        headers["Authorization"] = `Bearer ${token}`;
    }

    const config = { method, headers };
    if (body) {
        config.body = JSON.stringify(body);
    }

    const response = await fetch(`${API_BASE_URL}${endpoint}`, config);

    if (response.status === 401 && !endpoint.includes("/login")) {
        sessionStorage.clear();
        window.location.replace("index.html");
        throw new Error("Session expired");
    }

    const data = await response.json().catch(() => ({}));

    if (!response.ok) {
        throw new Error(data.message || "Request failed");
    }

    return data;
}

// دالة تسجيل الخروج ومسح الجلسة
function logoutUser() {
    apiRequest("/logout", "POST").finally(() => {
        sessionStorage.clear();
        window.location.replace("index.html");
    });
}
