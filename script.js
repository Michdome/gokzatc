function generateLinks() {
    var domain = document.getElementById("domain").value;

    if (!domain) {
        alert("Please enter a domain.");
        return;
    }

    // Make an AJAX request to send the domain to the PHP script
    var xhr = new XMLHttpRequest();
    xhr.open("POST", "api/woo.php", true);
    xhr.setRequestHeader("Content-Type", "application/x-www-form-urlencoded");

    xhr.onreadystatechange = function() {
        if (xhr.readyState === 4 && xhr.status === 200) {
            document.getElementById("result").value = xhr.responseText;
        }
    };

    xhr.send("domain=" + encodeURIComponent(domain));
}