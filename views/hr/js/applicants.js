// HR Applicants DataTable initialization

$(document).ready(function () {
    const table = $('#applicantsTable').DataTable({
        pageLength: 10,
        lengthChange: true,
        order: [[0, 'desc']], // Applicant_ID or Application_Date depending on column index
        language: {
            search: 'Search applicants:',
            lengthMenu: 'Show _MENU_ entries',
            info: 'Showing _START_ to _END_ of _TOTAL_ applicants',
        },
        columnDefs: [
            { targets: [0], visible: false }, // hide internal ID
        ]
    });
});
