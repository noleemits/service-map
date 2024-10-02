document.addEventListener('DOMContentLoaded', function () {
    let isSubmitting = false;

    // Function to initialize Choices.js
    function initializeChoices() {
        // Destroy any existing Choices.js instance before re-initializing
        if (document.getElementById('included-service-areas').choicesInstance) {
            document.getElementById('included-service-areas').choicesInstance.destroy();
        }
        if (document.getElementById('excluded-service-areas').choicesInstance) {
            document.getElementById('excluded-service-areas').choicesInstance.destroy();
        }

        // Initialize Choices.js for included and excluded service areas
        const includedSelect = new Choices('#included-service-areas', {
            removeItemButton: true,
            shouldSort: false,
            placeholder: true,
            searchEnabled: true,
        });

        const excludedSelect = new Choices('#excluded-service-areas', {
            removeItemButton: true,
            shouldSort: false,
            placeholder: true,
            searchEnabled: true,
        });

        // Store the instance for later use
        document.getElementById('included-service-areas').choicesInstance = includedSelect;
        document.getElementById('excluded-service-areas').choicesInstance = excludedSelect;

        return { includedSelect, excludedSelect };
    }

    // Function to filter options between included and excluded selects
    function filterOptions(includedSelect, excludedSelect) {
        const selectedIncluded = includedSelect.getValue(true);  // Get selected values from included
        const selectedExcluded = excludedSelect.getValue(true);  // Get selected values from excluded

        // Get all available options in both selects
        const includedOptions = [...document.querySelectorAll('#included-service-areas option')];
        const excludedOptions = [...document.querySelectorAll('#excluded-service-areas option')];

        // Clear excluded select and add only unselected included areas
        excludedSelect.clearChoices();
        includedOptions.forEach(option => {
            if (!selectedIncluded.includes(option.value)) {
                excludedSelect.setChoices([{ value: option.value, label: option.text }], 'value', 'label', false);
            }
        });

        // Clear included select and add only unselected excluded areas
        includedSelect.clearChoices();
        excludedOptions.forEach(option => {
            if (!selectedExcluded.includes(option.value)) {
                includedSelect.setChoices([{ value: option.value, label: option.text }], 'value', 'label', false);
            }
        });
    }

    // Fetch service areas from the REST API
    fetch('/wp-json/wp/v2/service_areas')
        .then(response => response.json())
        .then(data => {
            document.getElementById('included-service-areas').innerHTML = '';
            document.getElementById('excluded-service-areas').innerHTML = '';

            // Loop through service areas and append options
            data.forEach(area => {
                const optionIncluded = new Option(area.title.rendered, area.id, false, false);
                const optionExcluded = new Option(area.title.rendered, area.id, false, false);

                document.getElementById('included-service-areas').appendChild(optionIncluded);
                document.getElementById('excluded-service-areas').appendChild(optionExcluded);
            });

            // Initialize Choices.js after appending options
            const { includedSelect, excludedSelect } = initializeChoices();

            // Pre-select existing service areas
            const existingSelectedAreas = Array.isArray(existingServiceAreaData.selected) ? existingServiceAreaData.selected : [];
            const existingNotCoveredAreas = Array.isArray(existingServiceAreaData.not_covered) ? existingServiceAreaData.not_covered : [];

            // Set pre-selected values in Choices.js
            includedSelect.setChoiceByValue(existingSelectedAreas);
            excludedSelect.setChoiceByValue(existingNotCoveredAreas);

            // Filter options based on pre-selected values
            filterOptions(includedSelect, excludedSelect);

            // Filter options when selection changes
            document.querySelector('#included-service-areas').addEventListener('change', function () {
                filterOptions(includedSelect, excludedSelect);
            });

            document.querySelector('#excluded-service-areas').addEventListener('change', function () {
                filterOptions(includedSelect, excludedSelect);
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
        const includedServiceAreas = document.getElementById('included-service-areas').choicesInstance.getValue(true);
        const excludedServiceAreas = document.getElementById('excluded-service-areas').choicesInstance.getValue(true);

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
