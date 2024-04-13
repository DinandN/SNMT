// Define a function to fetch MAC address information
function fetchMacAddressInfo() {
    // Select all the MAC address elements
    const macAddressElements = document.querySelectorAll(".macVendor");

    // Function to fetch MAC address information from the API
    function fetchMacAddressInfo(macAddressElement, index) {
        const macAddress = macAddressElement.innerText;
        const apiUrl = './proxy.php?macAddress=' + encodeURIComponent(macAddress);

        // Display a loading message in the table cell
        macAddressElement.innerText = 'Fetching...';

        // Make an asynchronous request to the proxy PHP script with a 1-second delay between requests
        setTimeout(function () {
            $.ajax({
                url: apiUrl,
                method: 'GET',
                success: function (data) {
                    macAddressElement.innerText = data; // Replace the content of the cell with the API response
                },
                error: function (jqXHR, textStatus, errorThrown) {
                    macAddressElement.innerText = 'Error: ' + textStatus; // Display the error message
                }
            });
        }, index * 1000); // Delay for 1 second * index (index starts from 0)
    }

    // Iterate through each MAC address element and fetch info
    macAddressElements.forEach(function (element, index) {
        fetchMacAddressInfo(element, index);
    });
}


