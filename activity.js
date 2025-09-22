
// Hàm thêm log vào localStorage
function logActivity(activity) {
    let logs = JSON.parse(localStorage.getItem("user_logs") || "[]");
    logs.push({
        event: activity,
        time: new Date().toISOString()
    });
    localStorage.setItem("user_logs", JSON.stringify(logs));
}

// Ví dụ: lưu khi vào trang
logActivity("Page loaded: " + location.pathname);

// Ví dụ: lưu khi click
document.addEventListener("click", function(e) {
    logActivity("Click on " + e.target.tagName + " - " + e.target.innerText);
});

// Hàm hiển thị log
function showLogs() {
    console.log(JSON.parse(localStorage.getItem("user_logs") || "[]"));
}


