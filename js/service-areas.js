document.addEventListener('DOMContentLoaded', function () {
    const includedChoices = new Choices('#included-service-areas', { removeItemButton: true, searchEnabled: true });
    const excludedChoices = new Choices('#excluded-service-areas', { removeItemButton: true, searchEnabled: true });

    // Fetch service areas from the REST API
    fetch('/wp-json/wp/v2/service_areas')
        .then(response => response.json())
        .then(data => {
            data.forEach(area => {
                const optionIncluded = new Option(area.title.rendered, area.id);
                const optionExcluded = new Option(area.title.rendered, area.id);

                document.getElementById('included-service-areas').append(optionIncluded);
                document.getElementById('excluded-service-areas').append(optionExcluded);
            });
        });

    // Form submission logic
    document.getElementById('service-area-form').addEventListener('submit', function (event) {
        event.preventDefault();

        const includedServiceAreas = includedChoices.getValue(true);
        const excludedServiceAreas = excludedChoices.getValue(true);

        const formData = new FormData();
        formData.append('included_service_areas', JSON.stringify(includedServiceAreas));
        formData.append('excluded_service_areas', JSON.stringify(excludedServiceAreas));

        fetch('/wp-json/myplugin/v1/save_service_areas', { method: 'POST', body: formData })
            .then(response => response.json())
            .then(data => alert(data.message))
            .catch(error => console.error('Error:', error));
    });
});
