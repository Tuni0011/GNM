let links = document.querySelectorAll("[data-delete]");

for (let link of links) {
    link.addEventListener("click", function (e) {
        e.preventDefault();

        // Ensure 'this' refers to the clicked element
        let currentLink = this;

        if (confirm("Are You sure ?")) {
            fetch(currentLink.getAttribute("href"), {
                method: "DELETE",
                headers: {
                    "X-CSRF-TOKEN": "XMLHttpRequest",
                    "Content-Type": "application/json",
                },
                body: JSON.stringify({ "_token": currentLink.dataset.token })
            }).then(response => {
                if (!response.ok) {
                    throw new Error(`HTTP error! Status: ${response.status}`);
                }
                return response.json();
            }).then(data => {
                console.log('Response Data:', data);
                if (data.success) {
                    currentLink.parentElement.remove();
                } else {
                    alert("succes");
                }
            }).catch(error => {
                console.error('Fetch Error:', error.message);
                console.log('Response Status:', error.message.split('Status: ')[1]); // Extract status from the error message
            });
        }
    });
}
