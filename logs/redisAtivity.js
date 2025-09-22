function sendActivity(eventName) {
    fetch("log.php", {
        method: "POST",
        headers: {"Content-Type": "application/json"},
        body: JSON.stringify({
            event: eventName,
            path: location.pathname
        })
    });
}

sendActivity("Page loaded: " + location.pathname);

document.addEventListener("click", e => {
    sendActivity("Click: " + e.target.tagName + " - " + e.target.innerText);
});

