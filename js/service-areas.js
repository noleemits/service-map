document.addEventListener('DOMContentLoaded', function () {
    let isSubmitting = false;

    // Function to initialize Tom Select
    function initializeTomSelect() {
        // Destroy any existing Tom Select instance before re-initializing
        if (document.getElementById('included-service-areas').tomselect) {
            document.getElementById('included-service-areas').tomselect.destroy();
        }
        if (document.getElementById('excluded-service-areas').tomselect) {
            document.getElementById('excluded-service-areas').tomselect.destroy();
        }

        // Initialize Tom Select for included and excluded service areas
        const includedSelect = new TomSelect('#included-service-areas', { plugins: ['remove_button'], create: false });
        const excludedSelect = new TomSelect('#excluded-service-areas', { plugins: ['remove_button'], create: false });

        return { includedSelect, excludedSelect };
    }

    // Function to filter "Not Covered" options based on "Included" selections
    function filterExcludedOptions(selectedIncludedAreas) {
        console.log("filterExcludedOptions called");

        const excludedSelect = document.getElementById('excluded-service-areas').tomselect;
        excludedSelect.clearOptions(); // Clear all options first

        const allServiceAreas = [...document.querySelectorAll('#included-service-areas option')];

        allServiceAreas.forEach(option => {
            if (!selectedIncludedAreas.includes(option.value)) {
                excludedSelect.addOption({ value: option.value, text: option.text }); // Add only unselected areas
            }
        });
        excludedSelect.refreshOptions(); // Refresh Tom Select
    }

    // Function to filter "Included" options based on "Not Covered" selections
    function filterIncludedOptions(selectedExcludedAreas) {
        console.log("filterIncludedOptions defined");

        const includedSelect = document.getElementById('included-service-areas').tomselect;
        includedSelect.clearOptions(); // Clear all options first

        const allServiceAreas = [...document.querySelectorAll('#excluded-service-areas option')];

        allServiceAreas.forEach(option => {
            if (!selectedExcludedAreas.includes(option.value)) {
                includedSelect.addOption({ value: option.value, text: option.text }); // Add only unselected areas
            }
        });
        includedSelect.refreshOptions(); // Refresh Tom Select
    }

    // Fetch service areas from the REST API
    fetch('/wp-json/wp/v2/service_areas')
        .then(response => response.json())
        .then(data => {
            // Clear previous options before appending new ones
            document.getElementById('included-service-areas').innerHTML = ''; // Clear included options
            document.getElementById('excluded-service-areas').innerHTML = ''; // Clear excluded options

            // Loop through service areas and append options
            data.forEach(area => {
                const optionIncluded = new Option(area.title.rendered, area.id, false, false);
                const optionExcluded = new Option(area.title.rendered, area.id, false, false);

                document.getElementById('included-service-areas').appendChild(optionIncluded);
                document.getElementById('excluded-service-areas').appendChild(optionExcluded);
            });

            // Initialize Tom Select after appending options
            const selects = initializeTomSelect();
            const { includedSelect, excludedSelect } = selects;

            // Pre-select existing service areas
            const existingSelectedAreas = Array.isArray(existingServiceAreaData.selected) ? existingServiceAreaData.selected : [];
            const existingNotCoveredAreas = Array.isArray(existingServiceAreaData.not_covered) ? existingServiceAreaData.not_covered : [];

            // Set pre-selected values in Tom Select
            includedSelect.setValue(existingSelectedAreas);
            excludedSelect.setValue(existingNotCoveredAreas);

            // Filter options based on pre-selected values
            filterExcludedOptions(existingSelectedAreas);
            filterIncludedOptions(existingNotCoveredAreas);

            // Filter options when selection changes
            includedSelect.on('change', function () {
                filterExcludedOptions(includedSelect.getValue()); // No need for safety check since it's defined
            });

            excludedSelect.on('change', function () {
                filterIncludedOptions(excludedSelect.getValue()); // No need for safety check since it's defined
            });
        })
        .catch(error => console.error('Error fetching service areas:', error));


    // Form submission logic
    document.getElementById('service-area-form').addEventListener('submit', function (event) {
        event.preventDefault();

        if (isSubmitting) return;
        isSubmitting = true;

        console.log('Form submitted!');

        // Get selected included and excluded service areas
        const includedServiceAreas = document.getElementById('included-service-areas').tomselect.getValue();
        const excludedServiceAreas = document.getElementById('excluded-service-areas').tomselect.getValue();

        console.log('Included Service Areas:', includedServiceAreas);
        console.log('Excluded Service Areas:', excludedServiceAreas);

        const formData = new FormData();
        formData.append('included_service_areas', JSON.stringify(includedServiceAreas));
        formData.append('excluded_service_areas', JSON.stringify(excludedServiceAreas));

        fetch('/wp-json/myplugin/v1/save_service_areas', {
            method: 'POST',
            body: formData,
            headers: {
                'X-WP-Nonce': wpApiSettings.nonce // Pass the nonce in the request headers
            }
        })
            .then(response => response.json())
            .then(data => {
                console.log(data);
                alert(data.message);
                isSubmitting = false;
                location.reload();
            })
            .catch(error => {
                console.error('Error:', error);
                isSubmitting = false;
            });
    });
});
